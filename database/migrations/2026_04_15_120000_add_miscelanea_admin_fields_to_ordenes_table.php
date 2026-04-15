<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            if (! Schema::hasColumn('ordenes', 'horas_labor')) {
                $table->string('horas_labor')->nullable()->after('accion_correctiva');
            }

            if (! Schema::hasColumn('ordenes', 'miscelanea_costo_total')) {
                $table->decimal('miscelanea_costo_total', 12, 2)->nullable()->after('estado');
            }

            if (! Schema::hasColumn('ordenes', 'miscelanea_precio_venta')) {
                $table->decimal('miscelanea_precio_venta', 12, 2)->nullable()->after('miscelanea_costo_total');
            }

            if (! Schema::hasColumn('ordenes', 'miscelanea_observaciones_admin')) {
                $table->text('miscelanea_observaciones_admin')->nullable()->after('miscelanea_precio_venta');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            foreach ([
                'horas_labor',
                'miscelanea_costo_total',
                'miscelanea_precio_venta',
                'miscelanea_observaciones_admin',
            ] as $column) {
                if (Schema::hasColumn('ordenes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
