<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cartas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_id')
                ->constrained('ordenes')
                ->cascadeOnDelete();
            $table->string('item', 20)->nullable();
            $table->string('tarea')->nullable();
            $table->string('titulo');
            $table->string('remanente')->nullable();
            $table->string('completado')->nullable();
            $table->string('siguiente')->nullable();
            $table->text('notas')->nullable();
            $table->text('accion_correctiva')->nullable();
            $table->string('descripcion_componente')->nullable();
            $table->unsignedInteger('cantidad')->nullable();
            $table->string('numero_parte')->nullable();
            $table->string('numero_serie_removido')->nullable();
            $table->string('numero_serie_instalado')->nullable();
            $table->text('observaciones')->nullable();
            $table->date('fecha_termino')->nullable();
            $table->decimal('horas_labor', 10, 2)->nullable();
            $table->string('auxiliar')->nullable();
            $table->string('tecnico')->nullable();
            $table->string('inspector')->nullable();
            $table->timestamps();

            $table->index(['orden_id', 'item', 'id'], 'cartas_orden_item_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cartas');
    }
};

