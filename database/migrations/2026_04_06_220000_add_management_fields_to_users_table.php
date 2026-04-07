<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('rol_nombre')->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('puesto')->nullable();
            $table->string('estado', 50)->default('Activo');
            $table->json('permisos')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'rol_nombre',
                'telefono',
                'puesto',
                'estado',
                'permisos',
            ]);
        });
    }
};
