<?php

namespace App\Http\Controllers;

use App\Models\User;
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

        return response()->json([
            'success' => true,
            'token' => $user->createToken('api-token')->plainTextToken,
            'user' => [
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
            ],
        ]);
    }
}
