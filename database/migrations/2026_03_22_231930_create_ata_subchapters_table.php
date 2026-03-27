<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ata_subchapters', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ata_chapter_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('codigo');
            $table->string('descripcion');

            // PRO
            $table->integer('intervalo_horas')->nullable();
            $table->integer('intervalo_ciclos')->nullable();
            $table->integer('intervalo_dias')->nullable();
            $table->string('tipo_mantenimiento')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ata_subchapters');
    }
};
