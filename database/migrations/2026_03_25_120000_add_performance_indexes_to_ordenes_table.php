<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            $table->index(['fecha', 'id'], 'ordenes_fecha_id_index');
            $table->index('estado', 'ordenes_estado_index');
            $table->index('matricula', 'ordenes_matricula_index');
            $table->index(['anio', 'area_id', 'consecutivo'], 'ordenes_anio_area_consecutivo_index');
        });
    }

    public function down(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            $table->dropIndex('ordenes_fecha_id_index');
            $table->dropIndex('ordenes_estado_index');
            $table->dropIndex('ordenes_matricula_index');
            $table->dropIndex('ordenes_anio_area_consecutivo_index');
        });
    }
};
