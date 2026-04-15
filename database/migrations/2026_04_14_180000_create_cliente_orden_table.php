<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cliente_orden', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('orden_id')->constrained('ordenes')->cascadeOnDelete();
            $table->timestamps();

            // Unique constraint to prevent duplicate assignments
            $table->unique(['cliente_id', 'orden_id']);
            
            // Indexes for performance
            $table->index('cliente_id');
            $table->index('orden_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_orden');
    }
};
