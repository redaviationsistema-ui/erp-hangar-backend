<?php

namespace Database\Seeders;

use App\Models\TipoOrden;
use Illuminate\Database\Seeder;

class TipoOrdenSeeder extends Seeder
{
    public function run(): void
    {
        foreach (AreaSeeder::catalog() as $item) {
            TipoOrden::updateOrCreate(
                ['codigo' => $item['codigo']],
                [
                    'codigo' => $item['codigo'],
                    'nombre' => $item['nombre'],
                ]
            );
        }
    }
}
