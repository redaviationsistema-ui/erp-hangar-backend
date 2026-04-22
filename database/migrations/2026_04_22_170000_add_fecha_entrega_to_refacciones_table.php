<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('refacciones', function (Blueprint $table) {
            if (! Schema::hasColumn('refacciones', 'fecha_entrega')) {
                $table->date('fecha_entrega')->nullable()->after('precio_venta');
            }
        });
    }

    public function down(): void
    {
        Schema::table('refacciones', function (Blueprint $table) {
            if (Schema::hasColumn('refacciones', 'fecha_entrega')) {
                $table->dropColumn('fecha_entrega');
            }
        });
    }
};
