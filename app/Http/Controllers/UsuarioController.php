<?php

namespace App\Http\Controllers;

use App\Http\Resources\UsuarioResource;
use App\Models\Area;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeManagement($request, 'usuarios');

        $payload = $this->cacheOrFetch(
            $this->cacheKey('index', $request->query()),
            now()->addMinutes(5),
            function () use ($request) {
                $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);
                $search = trim((string) $request->input('search', ''));

                $query = User::query()
                    ->select([
                        'id',
                        'name',
                        'email',
                        'telefono',
                        'puesto',
                        'rol',
                        'rol_nombre',
                        'area_id',
                        'estado',
                        'permisos',
                        'created_at',
                        'updated_at',
                    ])
                    ->with('area:id,codigo,nombre')
                    ->orderBy('name');

                if ($search !== '') {
                    $searchLower = mb_strtolower($search, 'UTF-8');

                    $query->where(function ($builder) use ($searchLower) {
                        $builder
                            ->whereRaw('lower(name) like ?', ["%{$searchLower}%"])
                            ->orWhereRaw('lower(email) like ?', ["%{$searchLower}%"])
                            ->orWhereRaw('lower(rol) like ?', ["%{$searchLower}%"])
                            ->orWhereRaw('lower(rol_nombre) like ?', ["%{$searchLower}%"])
                            ->orWhereHas('area', function ($areaQuery) use ($searchLower) {
                                $areaQuery
                                    ->whereRaw('lower(codigo) like ?', ["%{$searchLower}%"])
                                    ->orWhereRaw('lower(nombre) like ?', ["%{$searchLower}%"]);
                            });
                    });
                }

                $usuarios = $query->paginate($perPage);

                return [
                    'success' => true,
                    'data' => UsuarioResource::collection($usuarios->getCollection())->resolve(),
                    'meta' => $this->meta($usuarios),
                ];
            }
        );

        return response()->json($payload);
    }

    public function store(Request $request)
    {
        $this->authorizeManagement($request, 'usuarios');

        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'telefono' => 'nullable|string|max:50',
            'puesto' => 'nullable|string|max:255',
            'rol' => 'required|string|max:50',
            'area_codigo' => 'nullable|string|exists:areas,codigo',
            'estado' => 'nullable|string|max:50',
            'permisos' => 'nullable|array',
            'permisos.*' => 'string|max:80',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $rolePayload = $this->normalizeRolePayload($data['rol']);

        $usuario = User::create([
            'name' => $data['nombre'],
            'email' => $data['email'],
            'password' => $data['password'],
            'telefono' => $data['telefono'] ?? null,
            'puesto' => $data['puesto'] ?? null,
            'rol' => $rolePayload['rol'],
            'rol_nombre' => $rolePayload['rol_nombre'],
            'area_id' => $this->resolveAreaId($data['area_codigo'] ?? null),
            'estado' => $data['estado'] ?? 'Activo',
            'permisos' => $this->normalizePermissions($data['permisos'] ?? []),
        ])->load('area:id,codigo,nombre');

        $this->bustCache();

        return response()->json([
            'success' => true,
            'message' => 'Usuario creado correctamente.',
            'data' => UsuarioResource::make($usuario)->resolve(),
        ], 201);
    }

    public function show(Request $request, User $usuario)
    {
        $this->authorizeManagement($request, 'usuarios');

        $payload = $this->cacheOrFetch(
            $this->cacheKey('show', ['id' => $usuario->getKey()]),
            now()->addMinutes(5),
            function () use ($usuario) {
                $usuario->load('area:id,codigo,nombre');

                return [
                    'success' => true,
                    'data' => UsuarioResource::make($usuario)->resolve(),
                ];
            }
        );

        return response()->json($payload);
    }

    public function update(Request $request, User $usuario)
    {
        $this->authorizeManagement($request, 'usuarios');

        $data = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $usuario->getKey(),
            'telefono' => 'sometimes|nullable|string|max:50',
            'puesto' => 'sometimes|nullable|string|max:255',
            'rol' => 'sometimes|string|max:50',
            'area_codigo' => 'sometimes|nullable|string|exists:areas,codigo',
            'estado' => 'sometimes|string|max:50',
            'permisos' => 'sometimes|array',
            'permisos.*' => 'string|max:80',
            'password' => 'sometimes|nullable|string|min:6|confirmed',
        ]);

        $payload = [];

        if (array_key_exists('nombre', $data)) {
            $payload['name'] = $data['nombre'];
        }
        if (array_key_exists('email', $data)) {
            $payload['email'] = $data['email'];
        }
        if (array_key_exists('telefono', $data)) {
            $payload['telefono'] = $data['telefono'];
        }
        if (array_key_exists('puesto', $data)) {
            $payload['puesto'] = $data['puesto'];
        }
        if (array_key_exists('estado', $data)) {
            $payload['estado'] = $data['estado'];
        }
        if (array_key_exists('permisos', $data)) {
            $payload['permisos'] = $this->normalizePermissions($data['permisos'] ?? []);
        }
        if (array_key_exists('area_codigo', $data)) {
            $payload['area_id'] = $this->resolveAreaId($data['area_codigo']);
        }
        if (! empty($data['password'] ?? null)) {
            $payload['password'] = $data['password'];
        }
        if (array_key_exists('rol', $data)) {
            $payload = array_merge($payload, $this->normalizeRolePayload($data['rol']));
        }

        $usuario->update($payload);
        $usuario->load('area:id,codigo,nombre');
        $this->bustCache();

        return response()->json([
            'success' => true,
            'message' => 'Usuario actualizado correctamente.',
            'data' => UsuarioResource::make($usuario)->resolve(),
        ]);
    }

    public function destroy(Request $request, User $usuario)
    {
        $this->authorizeManagement($request, 'usuarios');

        $usuario->delete();
        $this->bustCache();

        return response()->json([
            'success' => true,
            'message' => 'Usuario eliminado correctamente.',
        ]);
    }

    private function authorizeManagement(Request $request, string $module): void
    {
        $user = $request->user();
        $permissions = $this->normalizePermissions($user?->permisos ?? []);

        $allowed = in_array($user?->rol, ['admin', 'supervisor'], true)
            || strcasecmp((string) $user?->email, 'ing@redaviation.com') === 0
            || in_array(strtolower((string) $user?->rol_nombre), ['ingenieria', 'ingeniero', 'engineering', 'engineer', 'ing'], true)
            || in_array('usuarios_crud', $permissions, true)
            || in_array('usuarios.manage', $permissions, true)
            || in_array('manage_users', $permissions, true);

        abort_unless($allowed, 403, "No tienes permisos para gestionar {$module}.");
    }

    private function normalizeRolePayload(string $role): array
    {
        $normalized = strtolower(trim($role));

        return match ($normalized) {
            'admin' => ['rol' => 'admin', 'rol_nombre' => 'admin'],
            'supervisor' => ['rol' => 'supervisor', 'rol_nombre' => 'supervisor'],
            'administracion', 'admin_precios', 'administrador_precios' => [
                'rol' => 'administracion',
                'rol_nombre' => 'administracion',
            ],
            'jefe_area' => ['rol' => 'tecnico', 'rol_nombre' => 'jefe_area'],
            'calidad' => ['rol' => 'tecnico', 'rol_nombre' => 'calidad'],
            'ingenieria', 'ingeniero', 'engineering', 'engineer', 'ing' => [
                'rol' => 'tecnico',
                'rol_nombre' => 'ingenieria',
            ],
            'tecnico_area', 'tecnico' => ['rol' => 'tecnico', 'rol_nombre' => 'tecnico_area'],
            default => ['rol' => 'tecnico', 'rol_nombre' => $normalized === '' ? 'tecnico_area' : $normalized],
        };
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

        return 'usuarios:' . Cache::get('usuarios_cache_version', 1) . ':' . $action . ':' . md5(json_encode($params));
    }

    private function bustCache(): void
    {
        Cache::forever('usuarios_cache_version', (int) Cache::get('usuarios_cache_version', 1) + 1);
    }
}
