<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Audit\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::query()
            ->leftJoin('areas as area', 'area.id', '=', 'users.area_id')
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.password',
                'users.area_id',
                'users.rol',
                'area.codigo as area_codigo',
                'area.numero as area_numero',
                'area.nombre as area_nombre',
            ])
            ->where('users.email', $credentials['email'])
            ->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
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
        $user = User::query()
            ->leftJoin('areas as area', 'area.id', '=', 'users.area_id')
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.area_id',
                'users.rol',
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

        app()->terminating(function () use ($user) {
            AuditLogger::log('logout', 'Cierre de sesion del usuario autenticado.', [
                'user_id' => $user?->id,
                'entity_type' => 'session',
                'entity_id' => $user?->id,
                'entity_label' => $user?->email,
            ]);
        });

        $user?->currentAccessToken()?->delete();

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
            'email' => $user->email,
            'rol' => $user->rol,
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
}
