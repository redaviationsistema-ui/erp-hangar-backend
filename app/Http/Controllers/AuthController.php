<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// 🔥 IMPORTS IMPORTANTES
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // ✅ VALIDACIÓN
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // 🔍 BUSCAR USUARIO
        $user = User::where('email', $request->email)->first();

        // ❌ VALIDAR CREDENCIALES
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => 'Credenciales inválidas'
            ], 401);
        }

        // 🔐 CREAR TOKEN (Laravel Sanctum)
        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user->name,
            'area' => $user->area?->codigo // 🔥 evita error null
        ]);
    }
}