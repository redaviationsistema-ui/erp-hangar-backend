<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_chunk_referencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manual_chunk_id')->constrained('manual_chunks')->cascadeOnDelete();
            $table->string('tipo', 100)->default('keyword');
            $table->string('valor');
            $table->timestamps();

            $table->index(['manual_chunk_id', 'tipo']);
            $table->index(['tipo', 'valor']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_chunk_referencias');
    }
};
