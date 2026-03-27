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
    }
}
