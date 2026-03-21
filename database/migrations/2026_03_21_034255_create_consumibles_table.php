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
        Schema::create('consumibles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_id')
                ->constrained('ordenes')
                ->cascadeOnDelete();
            $table->string('nombre');
            $table->integer('cantidad')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consumibles');
    }
};
