<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manuales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aeronave_id')->nullable()->constrained('aeronaves')->nullOnDelete();
            $table->string('nombre');
            $table->string('tipo_manual', 100);
            $table->string('fabricante')->nullable();
            $table->string('aeronave_modelo')->nullable();
            $table->string('revision', 100)->nullable();
            $table->string('idioma', 10)->default('es');
            $table->string('estado', 50)->default('vigente');
            $table->string('archivo_path')->nullable();
            $table->date('vigente_desde')->nullable();
            $table->date('vigente_hasta')->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamps();

            $table->index(['aeronave_id', 'estado']);
            $table->index(['aeronave_modelo', 'revision']);
            $table->index(['tipo_manual', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manuales');
    }
};
