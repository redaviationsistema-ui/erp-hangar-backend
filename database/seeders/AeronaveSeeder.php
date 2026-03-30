<?php

namespace Database\Seeders;

use App\Models\Aeronave;
use Illuminate\Database\Seeder;

class AeronaveSeeder extends Seeder
{
    public function run(): void
    {
        Aeronave::updateOrCreate(
            ['matricula' => 'XA-ABC'],
            [
                'cliente' => 'Red Aviation',
                'fabricante' => 'Cessna',
                'modelo' => '172',
                'numero_serie' => 'C172-2026',
                'estado' => 'activa',
            ]
        );

        Aeronave::updateOrCreate(
            ['matricula' => 'XA-MMN'],
            [
                'cliente' => '-',
                'fabricante' => 'Learjet',
                'modelo' => '35A',
                'numero_serie' => '221',
                'estado' => 'activa',
                'notas' => 'Seed basado en orden CESA-HANG25-097.',
            ]
        );
    }
}
