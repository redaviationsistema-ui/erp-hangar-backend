<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manual_id')->constrained('manuales')->cascadeOnDelete();
            $table->foreignId('ata_chapter_id')->nullable()->constrained('ata_chapters')->nullOnDelete();
            $table->foreignId('ata_subchapter_id')->nullable()->constrained('ata_subchapters')->nullOnDelete();
            $table->string('codigo_seccion')->nullable();
            $table->string('titulo')->nullable();
            $table->string('tipo_contenido', 100)->default('general');
            $table->unsignedInteger('pagina_inicio')->nullable();
            $table->unsignedInteger('pagina_fin')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->text('resumen')->nullable();
            $table->json('keywords')->nullable();
            $table->json('embedding')->nullable();
            $table->longText('texto');
            $table->timestamps();

            $table->index(['manual_id', 'orden']);
            $table->index(['ata_chapter_id', 'ata_subchapter_id']);
            $table->index(['tipo_contenido', 'pagina_inicio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_chunks');
    }
};
