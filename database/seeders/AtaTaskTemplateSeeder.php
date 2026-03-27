<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AtaSubchapter;
use App\Models\AtaTaskTemplate;
use App\Models\Area;

class AtaTaskTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $areas = Area::all()->keyBy('nombre');

        $tasks = [

            // 🔹 ATA 05
            '05-20' => [
                ['titulo' => 'Inspección programada de mantenimiento', 'area' => 'PLANEADOR'],
            ],

            // 🔹 ATA 21
            '21-10' => [
                ['titulo' => 'Inspección sistema distribución aire', 'area' => 'AVIONICS'],
            ],
            '21-30' => [
                ['titulo' => 'Prueba de presurización', 'area' => 'AVIONICS'],
            ],

            // 🔹 ATA 22
            '22-10' => [
                ['titulo' => 'Prueba de autopiloto', 'area' => 'AVIONICS'],
            ],

            // 🔹 ATA 23
            '23-20' => [
                ['titulo' => 'Prueba comunicación VHF', 'area' => 'AVIONICS'],
            ],

            // 🔹 ATA 24
            '24-10' => [
                ['titulo' => 'Inspección de generadores', 'area' => 'AVIONICS'],
            ],
            '24-20' => [
                ['titulo' => 'Verificación sistema AC', 'area' => 'AVIONICS'],
            ],
            '24-30' => [
                ['titulo' => 'Chequeo de baterías', 'area' => 'BATERIAS'],
            ],

            // 🔹 ATA 25
            '25-20' => [
                ['titulo' => 'Revisión interior cabina', 'area' => 'VESTIDURAS'],
            ],

            // 🔹 ATA 26
            '26-10' => [
                ['titulo' => 'Inspección detección de fuego', 'area' => 'SALVAMENTO ESPECIALIZADO'],
            ],

            // 🔹 ATA 27
            '27-10' => [
                ['titulo' => 'Inspección de Aileron', 'area' => 'PLANEADOR'],
            ],
            '27-20' => [
                ['titulo' => 'Inspección de Rudder', 'area' => 'PLANEADOR'],
            ],
            '27-30' => [
                ['titulo' => 'Inspección de Elevator', 'area' => 'PLANEADOR'],
            ],

            // 🔹 ATA 28
            '28-10' => [
                ['titulo' => 'Inspección de tanques', 'area' => 'PLANEADOR'],
            ],
            '28-20' => [
                ['titulo' => 'Revisión de bombas de combustible', 'area' => 'PLANEADOR'],
            ],

            // 🔹 ATA 29
            '29-10' => [
                ['titulo' => 'Inspección sistema hidráulico', 'area' => 'PLANEADOR'],
            ],

            // 🔹 ATA 30
            '30-10' => [
                ['titulo' => 'Inspección sistema anti-hielo', 'area' => 'PLANEADOR'],
            ],

            // 🔹 ATA 31
            '31-10' => [
                ['titulo' => 'Revisión instrumentos', 'area' => 'AVIONICS'],
            ],

            // 🔹 ATA 32
            '32-10' => [
                ['titulo' => 'Inspección tren principal', 'area' => 'TRENES'],
            ],
            '32-30' => [
                ['titulo' => 'Inspección de frenos', 'area' => 'FRENOS'],
            ],
            '32-40' => [
                ['titulo' => 'Cambio de llantas', 'area' => 'TRENES'],
            ],

            // 🔹 ATA 33
            '33-20' => [
                ['titulo' => 'Revisión luces exteriores', 'area' => 'AVIONICS'],
            ],

            // 🔹 ATA 34
            '34-20' => [
                ['titulo' => 'Prueba navegación', 'area' => 'AVIONICS'],
            ],

            // 🔹 ATA 35
            '35-10' => [
                ['titulo' => 'Inspección oxígeno tripulación', 'area' => 'SALVAMENTO ESPECIALIZADO'],
            ],

            // 🔹 ATA 36
            '36-10' => [
                ['titulo' => 'Inspección sistema neumático', 'area' => 'PLANEADOR'],
            ],

            // 🔹 ATA 38
            '38-10' => [
                ['titulo' => 'Revisión sistema de agua', 'area' => 'PLANEADOR'],
            ],

            // 🔹 ATA 49
            '49-10' => [
                ['titulo' => 'Inspección APU', 'area' => 'MOTORES RECIPROCOS'],
            ],

            // 🔹 ATA 52
            '52-10' => [
                ['titulo' => 'Inspección puertas', 'area' => 'ESTRUCTURAS'],
            ],

            // 🔹 ATA 53
            '53-10' => [
                ['titulo' => 'Inspección fuselaje', 'area' => 'ESTRUCTURAS'],
            ],

            // 🔹 ATA 57
            '57-10' => [
                ['titulo' => 'Inspección alas', 'area' => 'ESTRUCTURAS'],
            ],

            // 🔹 ATA 61
            '61-00' => [
                ['titulo' => 'Inspección hélice', 'area' => 'HELICES'],
            ],

            // 🔹 ATA 72
            '72-00' => [
                ['titulo' => 'Inspección motor', 'area' => 'MOTORES RECIPROCOS'],
            ],

        ];

        foreach ($tasks as $codigo => $lista) {

            $sub = AtaSubchapter::where('codigo', $codigo)->first();

            if (!$sub) continue;

            foreach ($lista as $task) {

                $area = $areas[$task['area']] ?? null;

                AtaTaskTemplate::updateOrCreate(
                    [
                        'ata_subchapter_id' => $sub->id,
                        'titulo' => $task['titulo'],
                    ],
                    [
                        'area_id' => $area?->id,
                        'descripcion' => $task['titulo'],
                        'tipo' => 'INSPECCION',
                        'tiempo_estimado_min' => 30,
                        'prioridad' => 'MEDIA',
                    ]
                );
            }
        }
    }
}