<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $user = DB::table('users')
            ->select('id', 'name', 'email', 'telefono', 'estado')
            ->where('email', 'cliente.demo@redaviation.com')
            ->first();

        if (! $user) {
            return;
        }

        DB::table('clientes')->updateOrInsert(
            ['email' => $user->email],
            [
                'nombre_comercial' => $user->name ?: 'Cliente Demo',
                'razon_social' => 'Cliente Demo SA de CV',
                'contacto_nombre' => $user->name ?: 'Cliente Demo',
                'telefono' => $user->telefono,
                'estatus' => $user->estado ?: 'Activo',
                'notas' => 'Migrado automaticamente desde la tabla users.',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('users')
            ->where('id', $user->id)
            ->delete();
    }

    public function down(): void
    {
        $cliente = DB::table('clientes')
            ->select('id', 'nombre_comercial', 'contacto_nombre', 'email', 'telefono', 'estatus')
            ->where('email', 'cliente.demo@redaviation.com')
            ->first();

        if (! $cliente) {
            return;
        }

        $exists = DB::table('users')
            ->where('email', $cliente->email)
            ->exists();

        if (! $exists) {
            DB::table('users')->insert([
                'name' => $cliente->contacto_nombre ?: $cliente->nombre_comercial ?: 'Cliente Demo',
                'email' => $cliente->email,
                'email_verified_at' => null,
                'password' => bcrypt('123456'),
                'area_id' => null,
                'rol' => 'tecnico',
                'rol_nombre' => 'tecnico_area',
                'telefono' => $cliente->telefono,
                'puesto' => 'Cliente',
                'estado' => $cliente->estatus ?: 'Activo',
                'permisos' => json_encode([]),
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('clientes')
            ->where('id', $cliente->id)
            ->delete();
    }
};
