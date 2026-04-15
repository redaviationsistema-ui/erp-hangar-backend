<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClienteResource;
use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ClienteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorizeManagement($request);

        $payload = $this->cacheOrFetch(
            $this->cacheKey('index', $request->query()),
            Carbon::now()->addMinutes(5),
            function () use ($request) {
                $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);
                $search = trim((string) $request->input('search', ''));
                $searchLower = mb_strtolower($search, 'UTF-8');

                $query = Cliente::query()
                    ->select([
                        'id',
                        'nombre_comercial',
                        'razon_social',
                        'rfc',
                        'contacto_nombre',
                        'email',
                        'telefono',
                        'ciudad',
                        'estatus',
                        'notas',
                        'ot_asignada_id',
                        'contrasena_portal',
                        'created_at',
                        'updated_at',
                    ])
                    ->withCount('ordenesAsignadas')
                    ->with([
                        'otAsignadaOrden:id,folio',
                    ])
                    ->orderBy('nombre_comercial');

                if ($search !== '') {
                    $query->where(function ($builder) use ($searchLower) {
                        $builder
                            ->whereRaw('lower(nombre_comercial) like ?', ["%{$searchLower}%"])
                            ->orWhereRaw('lower(razon_social) like ?', ["%{$searchLower}%"])
                            ->orWhereRaw('lower(rfc) like ?', ["%{$searchLower}%"])
                            ->orWhereRaw('lower(contacto_nombre) like ?', ["%{$searchLower}%"])
                            ->orWhereRaw('lower(email) like ?', ["%{$searchLower}%"]);
                    });
                }

                $clientes = $query->paginate($perPage);

                return [
                    'success' => true,
                    'data' => array_map(
                        fn (Cliente $cliente): array => $this->transformCliente($cliente, $request),
                        $clientes->getCollection()->all()
                    ),
                    'meta' => $this->meta($clientes),
                ];
            }
        );

        return new JsonResponse($payload);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeManagement($request);

        $data = $request->validate([
            'nombre_comercial' => 'required|string|max:255',
            'razon_social' => 'nullable|string|max:255',
            'rfc' => 'nullable|string|max:20',
            'contacto_nombre' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'password' => 'nullable|string|min:6|max:255',
            'contrasena' => 'nullable|string|min:6|max:255',
            'telefono' => 'nullable|string|max:50',
            'ciudad' => 'nullable|string|max:255',
            'estatus' => 'nullable|string|max:50',
            'notas' => 'nullable|string',
            'ot_id' => 'nullable|integer|exists:ordenes,id',
            'ot_asignada_id' => 'nullable|integer|exists:ordenes,id',
            'ot_ids' => 'nullable|array',
            'ot_ids.*' => 'integer|exists:ordenes,id',
            'ot_asignadas_ids' => 'nullable|array',
            'ot_asignadas_ids.*' => 'integer|exists:ordenes,id',
        ]);

        $clienteData = [
            ...$data,
            'password' => $data['password'] ?? $data['contrasena'] ?? null,
            'contrasena_portal' => $data['contrasena'] ?? $data['password'] ?? null,
            'ot_asignada_id' => $data['ot_asignada_id'] ?? $data['ot_id'] ?? null,
            'estatus' => $data['estatus'] ?? 'Activo',
        ];

        // Remove the ot_ids from clienteData since it's not a direct column
        unset($clienteData['ot_ids']);
        unset($clienteData['ot_asignadas_ids']);

        $cliente = new Cliente();
        $cliente->fill($clienteData);
        $cliente->save();

        // Sync multiple OTs
        $otIds = $data['ot_ids'] ?? $data['ot_asignadas_ids'] ?? [];
        if (!empty($otIds)) {
            $cliente->ordenesAsignadas()->sync($otIds);
        }

        $cliente->load('otAsignadaOrden.area', 'ordenesAsignadas');
        $this->bustCache();

        return new JsonResponse([
            'success' => true,
            'message' => 'Cliente creado correctamente.',
            'data' => $this->transformCliente($cliente, $request),
        ], 201);
    }

    public function show(Request $request, Cliente $cliente): JsonResponse
    {
        $this->authorizeManagement($request);

        $payload = $this->cacheOrFetch(
            $this->cacheKey('show', ['id' => $cliente->id]),
            Carbon::now()->addMinutes(5),
            function () use ($cliente, $request) {
                $previewOrders = $cliente->ordenesAsignadas()
                    ->select(['ordenes.id', 'ordenes.area_id', 'ordenes.folio', 'ordenes.estado', 'ordenes.descripcion', 'ordenes.matricula'])
                    ->with('area:id,nombre,codigo')
                    ->latest('ordenes.id')
                    ->limit(10)
                    ->get();

                $cliente->load([
                    'otAsignadaOrden:id,area_id,folio,estado,descripcion,matricula',
                    'otAsignadaOrden.area:id,nombre,codigo',
                ])->loadCount('ordenesAsignadas');
                $cliente->setRelation('ordenesAsignadasPreview', $previewOrders);

                return [
                    'success' => true,
                    'data' => $this->transformCliente($cliente, $request),
                ];
            }
        );

        return new JsonResponse($payload);
    }

    public function update(Request $request, Cliente $cliente): JsonResponse
    {
        $this->authorizeManagement($request);

        $data = $request->validate([
            'nombre_comercial' => 'sometimes|string|max:255',
            'razon_social' => 'sometimes|nullable|string|max:255',
            'rfc' => 'sometimes|nullable|string|max:20',
            'contacto_nombre' => 'sometimes|nullable|string|max:255',
            'email' => 'sometimes|nullable|email|max:255',
            'password' => 'sometimes|nullable|string|min:6|max:255',
            'contrasena' => 'sometimes|nullable|string|min:6|max:255',
            'telefono' => 'sometimes|nullable|string|max:50',
            'ciudad' => 'sometimes|nullable|string|max:255',
            'estatus' => 'sometimes|nullable|string|max:50',
            'notas' => 'sometimes|nullable|string',
            'ot_id' => 'sometimes|nullable|integer|exists:ordenes,id',
            'ot_asignada_id' => 'sometimes|nullable|integer|exists:ordenes,id',
            'ot_ids' => 'sometimes|nullable|array',
            'ot_ids.*' => 'integer|exists:ordenes,id',
            'ot_asignadas_ids' => 'sometimes|nullable|array',
            'ot_asignadas_ids.*' => 'integer|exists:ordenes,id',
        ]);

        $payload = $data;

        if (array_key_exists('password', $data) || array_key_exists('contrasena', $data)) {
            $plainPassword = $data['contrasena'] ?? $data['password'] ?? null;
            $payload['password'] = $plainPassword;
            $payload['contrasena_portal'] = $plainPassword;
        }

        if (array_key_exists('ot_asignada_id', $data) || array_key_exists('ot_id', $data)) {
            $payload['ot_asignada_id'] = $data['ot_asignada_id'] ?? $data['ot_id'] ?? null;
        }

        // Remove the ot_ids from payload since it's not a direct column
        unset($payload['ot_ids']);
        unset($payload['ot_asignadas_ids']);

        $cliente->fill($payload);
        $cliente->save();

        // Sync multiple OTs if provided
        if (array_key_exists('ot_ids', $data) || array_key_exists('ot_asignadas_ids', $data)) {
            $otIds = $data['ot_ids'] ?? $data['ot_asignadas_ids'] ?? [];
            $cliente->ordenesAsignadas()->sync($otIds);
        }

        $cliente->load('otAsignadaOrden.area', 'ordenesAsignadas');
        $this->bustCache();

        return new JsonResponse([
            'success' => true,
            'message' => 'Cliente actualizado correctamente.',
            'data' => $this->transformCliente($cliente, $request),
        ]);
    }

    public function destroy(Request $request, Cliente $cliente): JsonResponse
    {
        $this->authorizeManagement($request);

        $cliente->delete();
        $this->bustCache();

        return new JsonResponse([
            'success' => true,
            'message' => 'Cliente eliminado correctamente.',
        ]);
    }

    private function transformCliente(Cliente $cliente, Request $request): array
    {
        return (new ClienteResource($cliente))->toArray($request);
    }

    private function authorizeManagement(Request $request): void
    {
        $user = $request->user();
        $permissions = $this->normalizePermissions($user?->permisos ?? []);

        $allowed = in_array($user?->rol, ['admin', 'supervisor'], true)
            || strcasecmp((string) $user?->email, 'ing@redaviation.com') === 0
            || in_array(strtolower((string) $user?->rol_nombre), ['ingenieria', 'ingeniero', 'engineering', 'engineer', 'ing'], true)
            || in_array('clientes_crud', $permissions, true)
            || in_array('clientes.manage', $permissions, true)
            || in_array('manage_clients', $permissions, true)
            || in_array('usuarios_crud', $permissions, true)
            || in_array('manage_users', $permissions, true);

        if (! $allowed) {
            throw new HttpException(403, 'No tienes permisos para gestionar clientes.');
        }
    }

    private function normalizePermissions(mixed $permissions): array
    {
        if (is_string($permissions)) {
            $decoded = json_decode($permissions, true);
            $permissions = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($permissions)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn ($permission) => trim((string) $permission),
            $permissions
        )));
    }

    private function meta(LengthAwarePaginator $paginator): array
    {
        return [
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'has_more_pages' => $paginator->hasMorePages(),
        ];
    }

    private function cacheKey(string $action, array $params = []): string
    {
        ksort($params);

        return 'clientes:' . Cache::get('clientes_cache_version', 2) . ':' . $action . ':' . md5(json_encode($params));
    }

    private function bustCache(): void
    {
        Cache::forever('clientes_cache_version', (int) Cache::get('clientes_cache_version', 1) + 1);
    }
}
