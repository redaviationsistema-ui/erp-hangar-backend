<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ata_task_templates', function (Blueprint $table) {
            $table->id();

            // 🔥 Relación con SubATA
            $table->foreignId('ata_subchapter_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // 🔥 Relación con Área
            $table->foreignId('area_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            // 🔥 Datos de la tarea
            $table->string('titulo');
            $table->text('descripcion')->nullable();

            $table->string('tipo')->default('INSPECCION');
            $table->integer('tiempo_estimado_min')->nullable();
            $table->string('prioridad')->default('MEDIA');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ata_task_templates');
    }
};