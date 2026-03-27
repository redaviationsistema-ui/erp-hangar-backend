<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            $table->index(['area_id', 'fecha', 'id'], 'ordenes_area_fecha_id_index');
            $table->index(['estado', 'fecha', 'id'], 'ordenes_estado_fecha_id_index');
            $table->index(['tipo_id', 'fecha', 'id'], 'ordenes_tipo_fecha_id_index');
            $table->index(['ata_chapter_id', 'fecha', 'id'], 'ordenes_ata_chapter_fecha_id_index');
            $table->index(['ata_subchapter_id', 'fecha', 'id'], 'ordenes_ata_subchapter_fecha_id_index');
        });

        Schema::table('motores', function (Blueprint $table) {
            $table->index(['aeronave_id', 'numero_serie'], 'motores_aeronave_numero_serie_index');
        });
    }

    public function down(): void
    {
        Schema::table('motores', function (Blueprint $table) {
            $table->dropIndex('motores_aeronave_numero_serie_index');
        });

        Schema::table('ordenes', function (Blueprint $table) {
            $table->dropIndex('ordenes_area_fecha_id_index');
            $table->dropIndex('ordenes_estado_fecha_id_index');
            $table->dropIndex('ordenes_tipo_fecha_id_index');
            $table->dropIndex('ordenes_ata_chapter_fecha_id_index');
            $table->dropIndex('ordenes_ata_subchapter_fecha_id_index');
        });
    }
};
