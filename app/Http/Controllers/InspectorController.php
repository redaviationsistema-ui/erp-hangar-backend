<?php

namespace App\Http\Controllers;

use App\Http\Resources\InspectorResource;
use App\Models\Area;
use App\Models\Inspector;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class InspectorController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeManagement($request);

        $payload = $this->cacheOrFetch(
            $this->cacheKey('index', $request->query()),
            now()->addMinutes(5),
            function () use ($request) {
                $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);
                $search = trim((string) $request->input('search', ''));
                $tipo = trim((string) $request->input('tipo', ''));
                $estado = trim((string) $request->input('estado', ''));
                $areaCodigo = strtoupper(trim((string) $request->input('area_codigo', '')));

                $query = Inspector::query()
                    ->select([
                        'id',
                        'personal_tecnico_id',
                        'user_id',
                        'area_id',
                        'nombre',
                        'email',
                        'telefono',
                        'puesto',
                        'especialidad',
                        'tipo',
                        'estado',
                        'notas',
                        'created_at',
                        'updated_at',
                    ])
                    ->with([
                        'area:id,codigo,nombre',
                        'personalTecnico:id,nombre,tipo',
                        'usuario:id,name,email',
                    ])
                    ->orderBy('nombre');

                if ($tipo !== '') {
                    $query->where('tipo', $tipo);
                }

                if ($estado !== '') {
                    $query->where('estado', $estado);
                }

                if ($areaCodigo !== '' && $areaCodigo !== 'GENERAL') {
                    $query->whereHas('area', fn ($areaQuery) => $areaQuery->where('codigo', $areaCodigo));
                }

                if ($search !== '') {
                    $searchLower = mb_strtolower($search, 'UTF-8');

                    $query->where(function ($builder) use ($searchLower) {
                        $builder
                            ->whereRaw('lower(nombre) like ?', ["%{$searchLower}%"])
                            ->orWhereRaw('lower(email) like ?', ["%{$searchLower}%"])
                            ->orWhereRaw('lower(puesto) like ?', ["%{$searchLower}%"])
                            ->orWhereRaw('lower(especialidad) like ?', ["%{$searchLower}%"])
                            ->orWhereRaw('lower(tipo) like ?', ["%{$searchLower}%"])
                            ->orWhereHas('area', function ($areaQuery) use ($searchLower) {
                                $areaQuery
                                    ->whereRaw('lower(codigo) like ?', ["%{$searchLower}%"])
                                    ->orWhereRaw('lower(nombre) like ?', ["%{$searchLower}%"]);
                            });
                    });
                }

                $inspectores = $query->paginate($perPage);

                return [
                    'success' => true,
                    'data' => InspectorResource::collection($inspectores->getCollection())->resolve(),
                    'meta' => $this->meta($inspectores),
                ];
            }
        );

        return response()->json($payload);
    }

    public function store(Request $request)
    {
        $this->authorizeManagement($request);

        $data = $request->validate([
            'personal_tecnico_id' => 'nullable|integer|exists:personal_tecnico,id',
            'user_id' => 'nullable|integer|exists:users,id',
            'usuario_id' => 'nullable|integer|exists:users,id',
            'nombre' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:inspectores,email',
            'telefono' => 'nullable|string|max:50',
            'puesto' => 'nullable|string|max:255',
            'especialidad' => 'nullable|string|max:255',
            'tipo' => 'nullable|string|max:50',
            'area_codigo' => 'nullable|string|exists:areas,codigo',
            'estado' => 'nullable|string|max:50',
            'notas' => 'nullable|string',
        ]);

        $inspector = Inspector::create([
            'personal_tecnico_id' => $data['personal_tecnico_id'] ?? null,
            'user_id' => $data['user_id'] ?? $data['usuario_id'] ?? null,
            'area_id' => $this->resolveAreaId($data['area_codigo'] ?? null),
            'nombre' => $data['nombre'],
            'email' => $data['email'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'puesto' => $data['puesto'] ?? null,
            'especialidad' => $data['especialidad'] ?? null,
            'tipo' => $this->normalizeTipo($data['tipo'] ?? 'inspector'),
            'estado' => $data['estado'] ?? 'Activo',
            'notas' => $data['notas'] ?? null,
        ])->load(['area:id,codigo,nombre', 'personalTecnico:id,nombre,tipo', 'usuario:id,name,email']);

        $this->bustCache();

        return response()->json([
            'success' => true,
            'message' => 'Inspector creado correctamente.',
            'data' => InspectorResource::make($inspector)->resolve(),
        ], 201);
    }

    public function show(Request $request, Inspector $inspector)
    {
        $this->authorizeManagement($request);

        $payload = $this->cacheOrFetch(
            $this->cacheKey('show', ['id' => $inspector->getKey()]),
            now()->addMinutes(5),
            function () use ($inspector) {
                $inspector->load(['area:id,codigo,nombre', 'personalTecnico:id,nombre,tipo', 'usuario:id,name,email']);

                return [
                    'success' => true,
                    'data' => InspectorResource::make($inspector)->resolve(),
                ];
            }
        );

        return response()->json($payload);
    }

    public function update(Request $request, Inspector $inspector)
    {
        $this->authorizeManagement($request);

        $data = $request->validate([
            'personal_tecnico_id' => 'sometimes|nullable|integer|exists:personal_tecnico,id',
            'user_id' => 'sometimes|nullable|integer|exists:users,id',
            'usuario_id' => 'sometimes|nullable|integer|exists:users,id',
            'nombre' => 'sometimes|string|max:255',
            'email' => 'sometimes|nullable|email|max:255|unique:inspectores,email,'.$inspector->getKey(),
            'telefono' => 'sometimes|nullable|string|max:50',
            'puesto' => 'sometimes|nullable|string|max:255',
            'especialidad' => 'sometimes|nullable|string|max:255',
            'tipo' => 'sometimes|string|max:50',
            'area_codigo' => 'sometimes|nullable|string|exists:areas,codigo',
            'estado' => 'sometimes|string|max:50',
            'notas' => 'sometimes|nullable|string',
        ]);

        $payload = [];

        foreach (['personal_tecnico_id', 'nombre', 'email', 'telefono', 'puesto', 'especialidad', 'estado', 'notas'] as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        if (array_key_exists('tipo', $data)) {
            $payload['tipo'] = $this->normalizeTipo($data['tipo']);
        }

        if (array_key_exists('area_codigo', $data)) {
            $payload['area_id'] = $this->resolveAreaId($data['area_codigo']);
        }

        if (array_key_exists('user_id', $data) || array_key_exists('usuario_id', $data)) {
            $payload['user_id'] = $data['user_id'] ?? $data['usuario_id'] ?? null;
        }

        $inspector->update($payload);
        $inspector->load(['area:id,codigo,nombre', 'personalTecnico:id,nombre,tipo', 'usuario:id,name,email']);
        $this->bustCache();

        return response()->json([
            'success' => true,
            'message' => 'Inspector actualizado correctamente.',
            'data' => InspectorResource::make($inspector)->resolve(),
        ]);
    }

    public function destroy(Request $request, Inspector $inspector)
    {
        $this->authorizeManagement($request);

        $inspector->delete();
        $this->bustCache();

        return response()->json([
            'success' => true,
            'message' => 'Inspector eliminado correctamente.',
        ]);
    }

    private function authorizeManagement(Request $request): void
    {
        $user = $request->user();
        $permissions = $this->normalizePermissions($user?->permisos ?? []);

        $allowed = in_array($user?->rol, ['admin', 'supervisor'], true)
            || strcasecmp((string) $user?->email, 'ing@redaviation.com') === 0
            || in_array(strtolower((string) $user?->rol_nombre), ['calidad', 'inspector', 'ingenieria', 'ingeniero', 'engineering', 'engineer', 'ing'], true)
            || in_array('inspectores_crud', $permissions, true)
            || in_array('inspectores.manage', $permissions, true)
            || in_array('manage_inspectors', $permissions, true)
            || in_array('personal_tecnico_crud', $permissions, true)
            || in_array('usuarios_crud', $permissions, true)
            || in_array('manage_users', $permissions, true);

        abort_unless($allowed, 403, 'No tienes permisos para gestionar inspectores.');
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

    private function normalizeTipo(string $tipo): string
    {
        $normalized = strtolower(trim($tipo));

        return match ($normalized) {
            'calidad' => 'calidad',
            'supervisor' => 'supervisor',
            default => 'inspector',
        };
    }

    private function resolveAreaId(?string $areaCode): ?int
    {
        $normalized = strtoupper(trim((string) $areaCode));
        if ($normalized === '' || $normalized === 'GENERAL') {
            return null;
        }

        return Area::query()->where('codigo', $normalized)->value('id');
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

        return 'inspectores:'.Cache::get('inspectores_cache_version', 1).':'.$action.':'.md5(json_encode($params));
    }

    private function bustCache(): void
    {
        Cache::forever('inspectores_cache_version', (int) Cache::get('inspectores_cache_version', 1) + 1);
    }
}
