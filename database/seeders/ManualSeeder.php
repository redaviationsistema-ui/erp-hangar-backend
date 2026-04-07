<?php

namespace Database\Seeders;

use App\Models\Aeronave;
use App\Models\AtaChapter;
use App\Models\AtaSubchapter;
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

        $learjetManual = Manual::updateOrCreate(
            ['nombre' => 'Learjet 35A AMM Rev A7'],
            [
                'aeronave_id' => Aeronave::query()->where('matricula', 'XA-MMN')->value('id'),
                'tipo_manual' => 'AMM',
                'fabricante' => 'Learjet',
                'aeronave_modelo' => 'Learjet 35A',
                'revision' => 'A7',
                'idioma' => 'es',
                'estado' => 'vigente',
                'archivo_path' => null,
                'descripcion' => 'Manual de prueba para busquedas contextuales y discrepancias.',
            ]
        );

        $learjetManual->chunks()->delete();

        $brakesChunk = $learjetManual->chunks()->create([
            'ata_chapter_id' => AtaChapter::query()->where('codigo', '32')->value('id'),
            'ata_subchapter_id' => AtaSubchapter::query()->where('codigo', '32-40')->value('id'),
            'codigo_seccion' => '32-40-00',
            'titulo' => 'Inspeccion de frenos por vibracion en aterrizaje',
            'tipo_contenido' => 'procedimiento',
            'pagina_inicio' => 12,
            'pagina_fin' => 18,
            'orden' => 1,
            'resumen' => 'Procedimiento de inspeccion para vibracion en frenos durante el aterrizaje.',
            'keywords' => ['frenos', 'vibracion', 'aterrizaje', 'Learjet 35A'],
            'texto' => 'Verifique vibracion en frenos y desgaste de ruedas durante el aterrizaje en aeronaves Learjet 35A.',
        ]);

        $brakesChunk->referencias()->createMany([
            ['tipo' => 'keyword', 'valor' => 'frenos'],
            ['tipo' => 'keyword', 'valor' => 'vibracion'],
        ]);

        $oilChunk = $learjetManual->chunks()->create([
            'ata_chapter_id' => AtaChapter::query()->where('codigo', '79')->value('id'),
            'ata_subchapter_id' => AtaSubchapter::query()->where('codigo', '79-00')->value('id'),
            'codigo_seccion' => '79-00-00',
            'titulo' => 'Control de fuga de aceite en motor izquierdo',
            'tipo_contenido' => 'procedimiento',
            'pagina_inicio' => 34,
            'pagina_fin' => 39,
            'orden' => 2,
            'resumen' => 'Diagnostico para fuga de aceite en motor izquierdo.',
            'keywords' => ['aceite', 'fuga', 'motor izquierdo'],
            'texto' => 'Inspeccione fuga de aceite en motor izquierdo, lineas de lubricacion y sellos del Learjet 35A.',
        ]);

        $oilChunk->referencias()->createMany([
            ['tipo' => 'keyword', 'valor' => 'aceite'],
            ['tipo' => 'keyword', 'valor' => 'motor izquierdo'],
        ]);
    }
}
