<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_comercial');
            $table->string('razon_social')->nullable();
            $table->string('rfc', 20)->nullable();
            $table->string('contacto_nombre')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('ciudad')->nullable();
            $table->string('estatus', 50)->default('Activo');
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index('nombre_comercial', 'clientes_nombre_comercial_index');
            $table->index('rfc', 'clientes_rfc_index');
            $table->index('estatus', 'clientes_estatus_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
