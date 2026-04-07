<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AreaSeeder::class,
            TipoOrdenSeeder::class,
            AtaChapterSeeder::class,
            AtaTaskTemplateSeeder::class,
            AeronaveSeeder::class,
            MotorSeeder::class,
            ManualSeeder::class,
            UserSeeder::class,
            ClienteSeeder::class,
            OrdenSeeder::class,
        ]);
    }
}
