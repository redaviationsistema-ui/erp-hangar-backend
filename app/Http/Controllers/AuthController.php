<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\User;
use App\Support\Audit\AuditLogger;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $cliente = Cliente::query()
            ->withCount('ordenesAsignadas')
            ->with([
                'otAsignadaOrden:id,area_id,folio,estado,descripcion,matricula',
                'otAsignadaOrden.area:id,nombre,codigo',
            ])
            ->where('email', $credentials['email'])
            ->first();

        if ($cliente) {
            $portalPassword = $this->resolveClientePortalPassword($cliente);
            $matchesHashedPassword = ! empty($cliente->password)
                && Hash::check($credentials['password'], $cliente->password);
            $matchesPortalPassword = $portalPassword !== null
                && hash_equals($portalPassword, (string) $credentials['password']);

            if (! $matchesHashedPassword && ! $matchesPortalPassword) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciales invalidas.',
                ], 401);
            }

            if (! $matchesHashedPassword) {
                $cliente->forceFill([
                    'password' => $credentials['password'],
                ])->save();
            }

            $token = $cliente->createToken('api-token')->plainTextToken;

            app()->terminating(function () use ($cliente) {
                AuditLogger::log('login', "Inicio de sesion del cliente {$cliente->email}.", [
                    'entity_type' => 'session',
                    'entity_id' => $cliente->id,
                    'entity_label' => $cliente->email,
                    'context' => [
                        'auth_model' => 'cliente',
                    ],
                ]);
            });

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => $this->serializeCliente($cliente),
            ]);
        }

        $user = $this->findUserByEmail($credentials['email']);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales invalidas.',
            ], 401);
        }

        if (! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales invalidas.',
            ], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        app()->terminating(function () use ($user) {
            AuditLogger::log('login', "Inicio de sesion del usuario {$user->email}.", [
                'user_id' => $user->id,
                'entity_type' => 'session',
                'entity_id' => $user->id,
                'entity_label' => $user->email,
                'context' => [
                    'area_id' => $user->area_id,
                    'rol' => $user->rol,
                ],
            ]);
        });

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $this->serializeUser($user),
        ]);
    }

    public function me(Request $request)
    {
        $actor = $request->user();

        if ($actor instanceof Cliente) {
            $cliente = Cliente::query()
                ->withCount('ordenesAsignadas')
                ->with([
                    'otAsignadaOrden:id,area_id,folio,estado,descripcion,matricula',
                    'otAsignadaOrden.area:id,nombre,codigo',
                ])
                ->findOrFail($actor->id);

            return response()->json([
                'success' => true,
                'user' => $this->serializeCliente($cliente),
            ]);
        }

        $user = User::query()
            ->leftJoin('areas as area', 'area.id', '=', 'users.area_id')
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.password',
                'users.area_id',
                'users.rol',
                'users.rol_nombre',
                'users.telefono',
                'users.puesto',
                'users.estado',
                'users.permisos',
                'area.codigo as area_codigo',
                'area.numero as area_numero',
                'area.nombre as area_nombre',
            ])
            ->where('users.id', $request->user()->id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'user' => $this->serializeUser($user),
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => true,
                'message' => 'No habia una sesion activa.',
            ]);
        }

        app()->terminating(function () use ($user) {
            AuditLogger::log('logout', 'Cierre de sesion del usuario autenticado.', [
                'user_id' => $user?->id,
                'entity_type' => 'session',
                'entity_id' => $user?->id,
                'entity_label' => $user?->email,
            ]);
        });

        $user->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesion cerrada correctamente.',
        ]);
    }

    private function serializeUser(object $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'nombre' => $user->name,
            'email' => $user->email,
            'rol' => $user->rol,
            'rol_nombre' => $user->rol_nombre ?: $user->rol,
            'telefono' => $user->telefono,
            'puesto' => $user->puesto,
            'estado' => $user->estado ?: 'Activo',
            'permisos' => $this->normalizePermissions($user->permisos ?? []),
            'area_id' => $user->area_id ?? 0,
            'area' => $user->area_id ? [
                'id' => $user->area_id,
                'codigo' => $user->area_codigo,
                'numero' => $user->area_numero,
                'nombre' => $user->area_nombre,
            ] : [
                'id' => 0,
                'codigo' => 'GENERAL',
                'numero' => '00',
                'nombre' => 'GENERAL',
            ],
        ];
    }

    private function serializeCliente(Cliente $cliente): array
    {
        $otAsignada = $cliente->otAsignadaOrden;
        $previewOrders = $cliente->ordenesAsignadas()
            ->select(['ordenes.id', 'ordenes.area_id', 'ordenes.folio', 'ordenes.estado', 'ordenes.descripcion', 'ordenes.matricula'])
            ->with('area:id,nombre')
            ->latest('ordenes.id')
            ->limit(10)
            ->get();

        if ($previewOrders->isEmpty() && $otAsignada) {
            $previewOrders = collect([$otAsignada]);
        }

        $assignedOrderIds = $previewOrders->pluck('id')->filter()->values();
        $assignedOrdersCount = is_numeric($cliente->ordenes_asignadas_count ?? null)
            ? (int) $cliente->ordenes_asignadas_count
            : $assignedOrderIds->count();

        return [
            'id' => $cliente->id,
            'name' => $cliente->contacto_nombre ?: $cliente->nombre_comercial,
            'nombre' => $cliente->contacto_nombre ?: $cliente->nombre_comercial,
            'email' => $cliente->email,
            'rol' => 'cliente',
            'rol_nombre' => 'cliente',
            'telefono' => $cliente->telefono,
            'puesto' => 'Cliente',
            'estado' => $cliente->estatus ?: 'Activo',
            'permisos' => [],
            'area_id' => 0,
            'area' => [
                'id' => 0,
                'codigo' => 'CLIENTE',
                'numero' => '00',
                'nombre' => 'CLIENTE',
            ],
            'cliente' => [
                'id' => $cliente->id,
                'nombre_comercial' => $cliente->nombre_comercial,
                'razon_social' => $cliente->razon_social,
                'rfc' => $cliente->rfc,
                'contacto_nombre' => $cliente->contacto_nombre,
                'ciudad' => $cliente->ciudad,
                'estatus' => $cliente->estatus ?: 'Activo',
                'ot_asignada_id' => $cliente->ot_asignada_id,
                'ot_asignada' => $otAsignada?->folio,
                'ot_asignadas_ids' => $assignedOrderIds,
                'contrasena' => $cliente->contrasena_portal,
                'ordenes_trabajo_count' => $assignedOrdersCount,
                'ordenes_trabajo' => $previewOrders->map(fn ($orden) => [
                    'id' => $orden->id,
                    'folio' => $orden->folio,
                    'estado' => $orden->estado,
                    'descripcion' => $orden->descripcion,
                    'matricula' => $orden->matricula,
                    'area_nombre' => $orden->area?->nombre,
                ])->values(),
            ],
        ];
    }

    private function resolveClientePortalPassword(Cliente $cliente): ?string
    {
        $rawPortalPassword = $cliente->getRawOriginal('contrasena_portal');

        if (! is_string($rawPortalPassword) || trim($rawPortalPassword) === '') {
            return null;
        }

        try {
            return (string) Crypt::decryptString($rawPortalPassword);
        } catch (DecryptException) {
            return trim($rawPortalPassword);
        }
    }

    private function findUserByEmail(string $email): ?object
    {
        return User::query()
            ->leftJoin('areas as area', 'area.id', '=', 'users.area_id')
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.password',
                'users.area_id',
                'users.rol',
                'users.rol_nombre',
                'users.telefono',
                'users.puesto',
                'users.estado',
                'users.permisos',
                'area.codigo as area_codigo',
                'area.numero as area_numero',
                'area.nombre as area_nombre',
            ])
            ->where('users.email', $email)
            ->first();
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
}
