<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            $table->index(['orden_id', 'orden', 'id'], 'tareas_orden_sort_index');
            $table->index('area_id', 'tareas_area_id_index');
            $table->index('ata_task_template_id', 'tareas_ata_task_template_id_index');
        });

        Schema::table('ata_subchapters', function (Blueprint $table) {
            $table->index(['ata_chapter_id', 'codigo'], 'ata_subchapters_chapter_codigo_index');
        });

        Schema::table('ata_task_templates', function (Blueprint $table) {
            $table->index(['ata_subchapter_id', 'titulo'], 'ata_task_templates_subchapter_titulo_index');
            $table->index(['area_id', 'ata_subchapter_id'], 'ata_task_templates_area_subchapter_index');
        });

        Schema::table('areas', function (Blueprint $table) {
            $table->index('numero', 'areas_numero_index');
        });
    }

    public function down(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->dropIndex('areas_numero_index');
        });

        Schema::table('ata_task_templates', function (Blueprint $table) {
            $table->dropIndex('ata_task_templates_subchapter_titulo_index');
            $table->dropIndex('ata_task_templates_area_subchapter_index');
        });

        Schema::table('ata_subchapters', function (Blueprint $table) {
            $table->dropIndex('ata_subchapters_chapter_codigo_index');
        });

        Schema::table('tareas', function (Blueprint $table) {
            $table->dropIndex('tareas_orden_sort_index');
            $table->dropIndex('tareas_area_id_index');
            $table->dropIndex('tareas_ata_task_template_id_index');
        });
    }
};
