<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->index('email', 'clientes_email_index');
        });

        Schema::table('ordenes', function (Blueprint $table) {
            $table->index('cliente', 'ordenes_cliente_index');
            $table->index(['cliente', 'fecha', 'id'], 'ordenes_cliente_fecha_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            $table->dropIndex('ordenes_cliente_fecha_id_index');
            $table->dropIndex('ordenes_cliente_index');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropIndex('clientes_email_index');
        });
    }
};
