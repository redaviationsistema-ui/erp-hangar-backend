<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClienteResource;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeManagement($request);

        $payload = Cache::remember(
            $this->cacheKey('index', $request->query()),
            now()->addMinutes(5),
            function () use ($request) {
                $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);
                $search = trim((string) $request->input('search', ''));

                $query = Cliente::query()->orderBy('nombre_comercial');

                if ($search !== '') {
                    $query->where(function ($builder) use ($search) {
                        $builder
                            ->where('nombre_comercial', 'like', "%{$search}%")
                            ->orWhere('razon_social', 'like', "%{$search}%")
                            ->orWhere('rfc', 'like', "%{$search}%")
                            ->orWhere('contacto_nombre', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                }

                $clientes = $query->paginate($perPage);

                return [
                    'success' => true,
                    'data' => ClienteResource::collection($clientes->getCollection())->resolve(),
                    'meta' => $this->meta($clientes),
                ];
            }
        );

        return response()->json($payload);
    }

    public function store(Request $request)
    {
        $this->authorizeManagement($request);

        $data = $request->validate([
            'nombre_comercial' => 'required|string|max:255',
            'razon_social' => 'nullable|string|max:255',
            'rfc' => 'nullable|string|max:20',
            'contacto_nombre' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'password' => 'nullable|string|min:6|max:255',
            'telefono' => 'nullable|string|max:50',
            'ciudad' => 'nullable|string|max:255',
            'estatus' => 'nullable|string|max:50',
            'notas' => 'nullable|string',
        ]);

        $cliente = Cliente::create([
            ...$data,
            'estatus' => $data['estatus'] ?? 'Activo',
        ]);

        $this->bustCache();

        return response()->json([
            'success' => true,
            'message' => 'Cliente creado correctamente.',
            'data' => (new ClienteResource($cliente))->resolve(),
        ], 201);
    }

    public function show(Request $request, Cliente $cliente)
    {
        $this->authorizeManagement($request);

        $payload = Cache::remember(
            $this->cacheKey('show', ['id' => $cliente->id]),
            now()->addMinutes(5),
            fn () => [
                'success' => true,
                'data' => (new ClienteResource($cliente))->resolve(),
            ]
        );

        return response()->json($payload);
    }

    public function update(Request $request, Cliente $cliente)
    {
        $this->authorizeManagement($request);

        $data = $request->validate([
            'nombre_comercial' => 'sometimes|string|max:255',
            'razon_social' => 'sometimes|nullable|string|max:255',
            'rfc' => 'sometimes|nullable|string|max:20',
            'contacto_nombre' => 'sometimes|nullable|string|max:255',
            'email' => 'sometimes|nullable|email|max:255',
            'password' => 'sometimes|nullable|string|min:6|max:255',
            'telefono' => 'sometimes|nullable|string|max:50',
            'ciudad' => 'sometimes|nullable|string|max:255',
            'estatus' => 'sometimes|nullable|string|max:50',
            'notas' => 'sometimes|nullable|string',
        ]);

        $cliente->update($data);
        $this->bustCache();

        return response()->json([
            'success' => true,
            'message' => 'Cliente actualizado correctamente.',
            'data' => (new ClienteResource($cliente))->resolve(),
        ]);
    }

    public function destroy(Request $request, Cliente $cliente)
    {
        $this->authorizeManagement($request);

        $cliente->delete();
        $this->bustCache();

        return response()->json([
            'success' => true,
            'message' => 'Cliente eliminado correctamente.',
        ]);
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

        abort_unless($allowed, 403, 'No tienes permisos para gestionar clientes.');
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

        return 'clientes:' . Cache::get('clientes_cache_version', 1) . ':' . $action . ':' . md5(json_encode($params));
    }

    private function bustCache(): void
    {
        Cache::forever('clientes_cache_version', (int) Cache::get('clientes_cache_version', 1) + 1);
    }
}
