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
        Schema::create('ndt', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_id')
                ->constrained('ordenes')
                ->cascadeOnDelete();
            $table->string('tipo_prueba');
            $table->string('resultado')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ndt');
    }
};
