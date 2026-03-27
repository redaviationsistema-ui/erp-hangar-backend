<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aeronave_id')->constrained('aeronaves')->cascadeOnDelete();
            $table->string('posicion')->nullable();
            $table->string('fabricante')->nullable();
            $table->string('modelo')->nullable();
            $table->string('numero_parte')->nullable();
            $table->string('numero_serie')->unique();
            $table->decimal('tiempo_total', 12, 2)->nullable();
            $table->decimal('ciclos_totales', 12, 2)->nullable();
            $table->string('estado')->default('instalado');
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index(['aeronave_id', 'posicion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('motores');
    }
};
