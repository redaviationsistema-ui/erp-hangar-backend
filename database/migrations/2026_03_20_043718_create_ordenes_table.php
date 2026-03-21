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
        Schema::create('ordenes', function (Blueprint $table) {
            $table->id();
            $table->string('folio')->unique();
            $table->foreignId('tipo_id')->constrained('tipo_ordenes');
            $table->foreignId('user_id')->constrained('users');
            $table->integer('consecutivo');
            $table->year('anio');
            $table->date('fecha');
            $table->string('estado')->default('abierta');
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordenes');
    }
};
