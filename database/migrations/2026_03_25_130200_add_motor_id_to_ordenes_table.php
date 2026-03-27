<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            $table->foreignId('motor_id')->nullable()->after('ata_subchapter_id')->constrained('motores')->nullOnDelete();
            $table->index('motor_id', 'ordenes_motor_id_index');
        });

        $ordenes = DB::table('ordenes')
            ->whereNotNull('matricula')
            ->orderBy('id')
            ->get();

        foreach ($ordenes as $orden) {
            $aeronaveId = null;

            if ($orden->matricula) {
                $aeronaveId = DB::table('aeronaves')->updateOrInsert(
                    ['matricula' => $orden->matricula],
                    [
                        'cliente' => $orden->cliente,
                        'modelo' => $orden->aeronave_modelo,
                        'numero_serie' => $orden->aeronave_serie,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );

                $aeronaveId = DB::table('aeronaves')->where('matricula', $orden->matricula)->value('id');
            }

            if (! $aeronaveId || ! $orden->componente_numero_serie) {
                continue;
            }

            DB::table('motores')->updateOrInsert(
                ['numero_serie' => $orden->componente_numero_serie],
                [
                    'aeronave_id' => $aeronaveId,
                    'modelo' => $orden->componente_modelo,
                    'numero_parte' => $orden->componente_numero_parte,
                    'tiempo_total' => $orden->componente_tiempo_total,
                    'ciclos_totales' => $orden->componente_ciclos_totales,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $motorId = DB::table('motores')->where('numero_serie', $orden->componente_numero_serie)->value('id');

            DB::table('ordenes')->where('id', $orden->id)->update([
                'motor_id' => $motorId,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            $table->dropIndex('ordenes_motor_id_index');
            $table->dropConstrainedForeignId('motor_id');
        });
    }
};
