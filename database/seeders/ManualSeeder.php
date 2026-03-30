<?php

namespace Database\Seeders;

use App\Models\AtaChapter;
use App\Models\AtaSubchapter;
use App\Models\Manual;
use Illuminate\Database\Seeder;

class ManualSeeder extends Seeder
{
    public function run(): void
    {
        $aeronave = \App\Models\Aeronave::where('modelo', 'Learjet 35A')->first()
            ?? \App\Models\Aeronave::where('modelo', 'LEARJET 35A')->first();
        $ata32 = AtaChapter::where('codigo', '32')->first();
        $sub3240 = AtaSubchapter::where('codigo', '32-40')->first();
        $ata79 = AtaChapter::where('codigo', '79')->first();

        $manual = Manual::updateOrCreate(
            [
                'nombre' => 'Learjet 35A AMM Rev A7',
                'tipo_manual' => 'AMM',
                'aeronave_modelo' => 'Learjet 35A',
                'revision' => 'A7',
            ],
            [
                'aeronave_id' => $aeronave?->id,
                'fabricante' => 'Bombardier Learjet',
                'idioma' => 'en',
                'estado' => 'vigente',
                'descripcion' => 'Manual de ejemplo para busqueda contextual por ATA.',
            ]
        );

        $manual->chunks()->delete();

        $chunk1 = $manual->chunks()->create([
            'ata_chapter_id' => $ata32?->id,
            'ata_subchapter_id' => $sub3240?->id,
            'codigo_seccion' => '32-40-00',
            'titulo' => 'Wheels and Brakes Inspection',
            'tipo_contenido' => 'procedimiento',
            'pagina_inicio' => 212,
            'pagina_fin' => 219,
            'orden' => 1,
            'resumen' => 'Inspeccion de vibracion, desgaste, fuga y condicion general del sistema de frenos.',
            'keywords' => ['brakes', 'wheels', 'vibration', 'inspection'],
            'texto' => 'Inspect wheel brake assemblies for vibration symptoms, uneven wear, hydraulic leaks and heat damage before return to service.',
        ]);

        $chunk1->referencias()->createMany([
            ['tipo' => 'keyword', 'valor' => 'brakes'],
            ['tipo' => 'keyword', 'valor' => 'vibration'],
            ['tipo' => 'procedimiento', 'valor' => 'brake inspection'],
        ]);

        $chunk2 = $manual->chunks()->create([
            'ata_chapter_id' => $ata32?->id,
            'ata_subchapter_id' => $sub3240?->id,
            'codigo_seccion' => '32-40-01',
            'titulo' => 'Brake Wear Limits',
            'tipo_contenido' => 'warning',
            'pagina_inicio' => 220,
            'pagina_fin' => 221,
            'orden' => 2,
            'resumen' => 'Advertencias de desgaste y temperatura en frenos.',
            'keywords' => ['warning', 'brakes', 'wear'],
            'texto' => 'Do not dispatch the aircraft if brake wear exceeds allowable limits or if thermal damage is visible on wheel components.',
        ]);

        $chunk2->referencias()->createMany([
            ['tipo' => 'warning', 'valor' => 'wear limits'],
            ['tipo' => 'keyword', 'valor' => 'wheel'],
        ]);

        $chunk3 = $manual->chunks()->create([
            'ata_chapter_id' => $ata79?->id,
            'codigo_seccion' => '79-00-00',
            'titulo' => 'Engine Oil Leak Inspection',
            'tipo_contenido' => 'procedimiento',
            'pagina_inicio' => 480,
            'pagina_fin' => 486,
            'orden' => 3,
            'resumen' => 'Inspeccion de fuga de aceite alrededor de motor, lineas y conexiones.',
            'keywords' => ['oil', 'engine', 'leak', 'inspection'],
            'texto' => 'Inspect engine oil lines, scavenge fittings and accessory case areas for leaks before troubleshooting engine performance abnormalities.',
        ]);

        $chunk3->referencias()->createMany([
            ['tipo' => 'keyword', 'valor' => 'aceite'],
            ['tipo' => 'keyword', 'valor' => 'fuga'],
            ['tipo' => 'procedimiento', 'valor' => 'oil leak inspection'],
        ]);
    }
}
