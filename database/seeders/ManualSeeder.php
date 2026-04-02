<?php

namespace Database\Seeders;

use App\Models\Manual;
use Illuminate\Database\Seeder;

class ManualSeeder extends Seeder
{
    public function run(): void
    {
        $manuales = [
            [
                'archivo_path' => 'A:\\RED AVIATION\\ALFA\\CESA-BATT25-004 - XB-SBG - INGRESAN A DEEP CYCLE BATERIA.xlsx',
                'nombre' => 'CESA-BATT25-004 - XB-SBG - INGRESAN A DEEP CYCLE BATERIA',
                'tipo_manual' => 'XLSX',
                'idioma' => 'es',
                'estado' => 'vigente',
                'descripcion' => 'Registro documental sembrado para base de datos.',
            ],
            [
                'archivo_path' => 'A:\\RED AVIATION\\ALFA\\CESA-ESTR25-030 - XA-ZYZ - APLICACION DE PRIMER  A AIR INLET ASSY ENGINE NACELLE RH y LH.xlsx',
                'nombre' => 'CESA-ESTR25-030 - XA-ZYZ - APLICACION DE PRIMER  A AIR INLET ASSY ENGINE NACELLE RH y LH',
                'tipo_manual' => 'XLSX',
                'idioma' => 'es',
                'estado' => 'vigente',
                'descripcion' => 'Registro documental sembrado para base de datos.',
            ],
        ];

        foreach ($manuales as $manual) {
            Manual::updateOrCreate(
                ['archivo_path' => $manual['archivo_path']],
                $manual
            );
        }
    }
}
