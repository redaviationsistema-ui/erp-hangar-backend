<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('area_id', 'users_area_id_index');
            $table->index('name', 'users_name_index');
            $table->index('rol', 'users_rol_index');
            $table->index('rol_nombre', 'users_rol_nombre_index');
        });

        Schema::table('discrepancias', function (Blueprint $table) {
            $table->index('orden_id', 'discrepancias_orden_id_index');
        });

        Schema::table('refacciones', function (Blueprint $table) {
            $table->index('orden_id', 'refacciones_orden_id_index');
        });

        Schema::table('consumibles', function (Blueprint $table) {
            $table->index('orden_id', 'consumibles_orden_id_index');
        });

        Schema::table('herramientas', function (Blueprint $table) {
            $table->index('orden_id', 'herramientas_orden_id_index');
        });

        Schema::table('ndt', function (Blueprint $table) {
            $table->index('orden_id', 'ndt_orden_id_index');
        });

        Schema::table('taller_externos', function (Blueprint $table) {
            $table->index('orden_id', 'taller_externos_orden_id_index');
        });

        Schema::table('mediciones', function (Blueprint $table) {
            $table->index('orden_id', 'mediciones_orden_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('mediciones', function (Blueprint $table) {
            $table->dropIndex('mediciones_orden_id_index');
        });

        Schema::table('taller_externos', function (Blueprint $table) {
            $table->dropIndex('taller_externos_orden_id_index');
        });

        Schema::table('ndt', function (Blueprint $table) {
            $table->dropIndex('ndt_orden_id_index');
        });

        Schema::table('herramientas', function (Blueprint $table) {
            $table->dropIndex('herramientas_orden_id_index');
        });

        Schema::table('consumibles', function (Blueprint $table) {
            $table->dropIndex('consumibles_orden_id_index');
        });

        Schema::table('refacciones', function (Blueprint $table) {
            $table->dropIndex('refacciones_orden_id_index');
        });

        Schema::table('discrepancias', function (Blueprint $table) {
            $table->dropIndex('discrepancias_orden_id_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_area_id_index');
            $table->dropIndex('users_name_index');
            $table->dropIndex('users_rol_index');
            $table->dropIndex('users_rol_nombre_index');
        });
    }
};
