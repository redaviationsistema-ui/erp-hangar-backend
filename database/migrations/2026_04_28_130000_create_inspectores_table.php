<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspectores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personal_tecnico_id')->nullable()->constrained('personal_tecnico')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->string('nombre');
            $table->string('email')->nullable()->unique();
            $table->string('telefono', 50)->nullable();
            $table->string('puesto')->nullable();
            $table->string('especialidad')->nullable();
            $table->string('tipo', 50)->default('inspector');
            $table->string('estado', 50)->default('Activo');
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index(['estado', 'tipo']);
            $table->index(['area_id', 'estado']);
            $table->index('nombre');
        });

        $now = now();
        $insertedEmails = [];

        if (Schema::hasTable('personal_tecnico')) {
            $personal = DB::table('personal_tecnico')
                ->select([
                    'id',
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
                ])
                ->whereIn('tipo', ['inspector', 'calidad', 'supervisor'])
                ->orderBy('nombre')
                ->get();

            foreach ($personal as $item) {
                $email = trim((string) $item->email);
                $emailKey = strtolower($email);
                if ($emailKey !== '' && in_array($emailKey, $insertedEmails, true)) {
                    continue;
                }

                DB::table('inspectores')->insert([
                    'personal_tecnico_id' => $item->id,
                    'user_id' => $item->user_id,
                    'area_id' => $item->area_id,
                    'nombre' => $item->nombre,
                    'email' => $email === '' ? null : $email,
                    'telefono' => $item->telefono,
                    'puesto' => $item->puesto,
                    'especialidad' => $item->especialidad,
                    'tipo' => $this->normalizeTipo($item->tipo),
                    'estado' => $item->estado ?: 'Activo',
                    'notas' => $item->notas,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                if ($emailKey !== '') {
                    $insertedEmails[] = $emailKey;
                }
            }
        }

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
                    ->whereIn('rol_nombre', ['calidad', 'inspector', 'supervisor'])
                    ->orWhereIn('rol', ['supervisor']);
            })
            ->orderBy('name')
            ->get();

        foreach ($usuarios as $usuario) {
            $email = trim((string) $usuario->email);
            $emailKey = strtolower($email);
            if ($emailKey !== '' && in_array($emailKey, $insertedEmails, true)) {
                continue;
            }

            DB::table('inspectores')->insert([
                'personal_tecnico_id' => null,
                'user_id' => $usuario->id,
                'area_id' => $usuario->area_id,
                'nombre' => $usuario->name,
                'email' => $email === '' ? null : $email,
                'telefono' => $usuario->telefono,
                'puesto' => $usuario->puesto,
                'especialidad' => null,
                'tipo' => $this->normalizeTipo($usuario->rol_nombre ?: $usuario->rol),
                'estado' => $usuario->estado ?: 'Activo',
                'notas' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if ($emailKey !== '') {
                $insertedEmails[] = $emailKey;
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inspectores');
    }

    private function normalizeTipo(?string $tipo): string
    {
        $normalized = strtolower(trim((string) $tipo));

        return match ($normalized) {
            'calidad' => 'calidad',
            'supervisor' => 'supervisor',
            default => 'inspector',
        };
    }
};
