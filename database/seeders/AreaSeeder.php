<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        foreach (self::catalog() as $area) {
            Area::updateOrCreate(['codigo' => $area['codigo']], $area);
        }
    }

    public static function catalog(): array
    {
        return [
            ['nombre' => 'AVIONICS', 'codigo' => 'AVCS', 'numero' => '01'],
            ['nombre' => 'PLANEADOR', 'codigo' => 'HANG', 'numero' => '02'],
            ['nombre' => 'BATERIAS', 'codigo' => 'BATT', 'numero' => '03'],
            ['nombre' => 'FRENOS', 'codigo' => 'FREN', 'numero' => '04'],
            ['nombre' => 'TRENES', 'codigo' => 'TREN', 'numero' => '05'],
            ['nombre' => 'HELICOPTEROS', 'codigo' => 'HELI', 'numero' => '06'],
            ['nombre' => 'HELICES', 'codigo' => 'PROP', 'numero' => '07'],
            ['nombre' => 'MOTORES RECIPROCOS', 'codigo' => 'PIST', 'numero' => '08'],
            ['nombre' => 'VESTIDURAS', 'codigo' => 'VEST', 'numero' => '09'],
            ['nombre' => 'ESTRUCTURAS', 'codigo' => 'ESTR', 'numero' => '10'],
            ['nombre' => 'TORNO', 'codigo' => 'TORN', 'numero' => '11'],
            ['nombre' => 'SALVAMENTO ESPECIALIZADO', 'codigo' => 'SALV', 'numero' => '12'],
            ['nombre' => 'SOLDADURA ESPECIALIZADA', 'codigo' => 'SOLD', 'numero' => '13'],
        ];
    }
}
