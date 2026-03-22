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
        try {
            // 🔍 1. VALIDACIÓN
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            // 🔍 2. BUSCAR USUARIO
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'error' => 'Usuario no encontrado'
                ], 404);
            }

            // 🔍 3. VALIDAR PASSWORD
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'error' => 'Contraseña incorrecta'
                ], 401);
            }

            // 🔐 4. CREAR TOKEN (🔥 CLAVE)
            $token = $user->createToken('api-token')->plainTextToken;

            // 🔍 5. RESPUESTA FINAL
            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => $user->name,
                'email' => $user->email,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Error de validación',
                'details' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error del servidor',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}