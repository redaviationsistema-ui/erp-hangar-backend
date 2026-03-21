<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TipoOrden;

class TipoOrdenSeeder extends Seeder
{

    public function run()
    {
        $data = [
            ['nombre' => 'AVIONICS', 'codigo' => 'AVCS'],
            ['nombre' => 'PLANEADOR', 'codigo' => 'HANG'],
            ['nombre' => 'BATERIAS', 'codigo' => 'BATT'],
            ['nombre' => 'FRENOS', 'codigo' => 'FREN'],
            ['nombre' => 'TRENES', 'codigo' => 'TREN'],
            ['nombre' => 'HELICOPTEROS', 'codigo' => 'HELI'],
            ['nombre' => 'HELICES', 'codigo' => 'PROP'],
            ['nombre' => 'MOTORES RECIPROCOS', 'codigo' => 'PIST'],
            ['nombre' => 'VESTIDURAS', 'codigo' => 'VEST'],
            ['nombre' => 'ESTRUCTURAS', 'codigo' => 'ESTR'],
            ['nombre' => 'TORNO', 'codigo' => 'TORN'],
            ['nombre' => 'SALVAMENTO ESPECIALIZADO', 'codigo' => 'SALV'],
            ['nombre' => 'SOLDADURA ESPECIALIZADA', 'codigo' => 'SOLD'],
        ];

        foreach ($data as $item) {
            TipoOrden::updateOrCreate(
                ['codigo' => $item['codigo']], // 🔑 clave única
                $item
            );
        }
    }
}
