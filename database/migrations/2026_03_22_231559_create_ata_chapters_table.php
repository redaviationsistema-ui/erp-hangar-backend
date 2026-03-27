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
        Schema::create('ata_chapters', function (Blueprint $table) {
            $table->id();
            $table->string('codigo'); // Ej: 21
            $table->string('descripcion'); // Ej: Air Conditioning
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ata_chapters');
    }
};
