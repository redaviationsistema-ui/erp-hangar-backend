<?php

namespace Database\Seeders;

use App\Models\Aeronave;
use App\Models\Motor;
use Illuminate\Database\Seeder;

class MotorSeeder extends Seeder
{
    public function run(): void
    {
        $aeronave = Aeronave::where('matricula', 'XA-ABC')->firstOrFail();

        Motor::updateOrCreate(
            ['numero_serie' => 'SN-GEN-2026'],
            [
                'aeronave_id' => $aeronave->id,
                'posicion' => 'MOTOR 1',
                'fabricante' => 'Continental',
                'modelo' => 'GCU-24',
                'numero_parte' => 'GEN-24-010',
                'tiempo_total' => 1200.50,
                'ciclos_totales' => 820,
                'estado' => 'instalado',
            ]
        );
    }
}
