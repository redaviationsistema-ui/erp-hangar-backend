<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_tecnico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->string('nombre');
            $table->string('email')->nullable()->unique();
            $table->string('telefono', 50)->nullable();
            $table->string('puesto')->nullable();
            $table->string('especialidad')->nullable();
            $table->string('tipo', 50)->default('tecnico');
            $table->string('estado', 50)->default('Activo');
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index(['estado', 'tipo']);
            $table->index(['area_id', 'estado']);
            $table->index('nombre');
        });

        $now = now();
        $usuarios = DB::table('users')
            ->select([
                'id',
                'area_id',
                'name',
                'email',
                'telefono',
                'puesto',
                'rol',
                'rol_nombre',
                'estado',
            ])
            ->where(function ($query) {
                $query
                    ->whereIn('rol', ['tecnico', 'supervisor'])
                    ->orWhereIn('rol_nombre', [
                        'tecnico_area',
                        'jefe_area',
                        'calidad',
                        'ingenieria',
                        'ingeniero',
                        'engineering',
                        'engineer',
                        'ing',
                    ]);
            })
            ->orderBy('name')
            ->get();

        foreach ($usuarios as $usuario) {
            DB::table('personal_tecnico')->insert([
                'user_id' => $usuario->id,
                'area_id' => $usuario->area_id,
                'nombre' => $usuario->name,
                'email' => $usuario->email,
                'telefono' => $usuario->telefono,
                'puesto' => $usuario->puesto,
                'especialidad' => null,
                'tipo' => match (strtolower(trim((string) ($usuario->rol_nombre ?: $usuario->rol)))) {
                    'calidad' => 'inspector',
                    'ingenieria', 'ingeniero', 'engineering', 'engineer', 'ing' => 'ingeniero',
                    'supervisor' => 'supervisor',
                    default => 'tecnico',
                },
                'estado' => $usuario->estado ?: 'Activo',
                'notas' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_tecnico');
    }
};
