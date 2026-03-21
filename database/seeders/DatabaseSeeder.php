<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use App\Models\Area;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 🔥 1. CREAR ÁREAS (SIN DUPLICADOS)
        $areas = [
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

        foreach ($areas as $area) {
            Area::updateOrCreate(
                ['codigo' => $area['codigo']], // 🔑 evita duplicados
                $area
            );
        }

        // 🔥 2. CREAR TIPOS DE ORDEN (CLAVE PARA EVITAR TU ERROR)
        $this->call(TipoOrdenSeeder::class);

        // 🔍 3. OBTENER ÁREA AVCS
        $area = Area::where('codigo', 'AVCS')->first();

        // 🔥 4. CREAR USUARIO
        // 🔥 4. CREAR USUARIOS

        User::updateOrCreate(
            ['email' => 'kevin@test.com'],
            [
                'name' => 'Kevin',
                'password' => Hash::make('123456'),
                'area_id' => $area->id
            ]
        );

        User::updateOrCreate(
            ['email' => 'luis@test.com'],
            [
                'name' => 'Jose luis',
                'password' => Hash::make('123456'),
                'area_id' => $area->id
            ]
        );

        User::updateOrCreate(
            ['email' => 'maria@test.com'],
            [
                'name' => 'María López',
                'password' => Hash::make('123456'),
                'area_id' => $area->id
            ]
        );

        // 🔥 5. CREAR ÓRDENES (DESPUÉS DE TODO)
        $this->call(OrdenSeeder::class);
    }
}