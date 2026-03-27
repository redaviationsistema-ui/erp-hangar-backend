<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\AtaSubchapter;
use App\Models\Motor;
use App\Models\Orden;
use App\Models\Tarea;
use App\Models\TipoOrden;
use App\Models\User;
use App\Services\OrdenService;
use Illuminate\Database\Seeder;

class OrdenSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrFail();
        $this->seedAvionicsExample($user);
        $this->seedRequestedExamples($user);
    }

    private function seedAvionicsExample(User $user): void
    {
        $area = Area::where('codigo', 'AVCS')->firstOrFail();
        $tipo = TipoOrden::where('codigo', 'AVCS')->firstOrFail();
        $subchapter = AtaSubchapter::where('codigo', '24-10')->first();
        $motor = Motor::where('numero_serie', 'SN-GEN-2026')->first();
        $folio = app(OrdenService::class)->generarFolio($area);

        $orden = Orden::updateOrCreate(
            ['folio' => $folio['folio']],
            [
                'area_id' => $area->id,
                'tipo_id' => $tipo->id,
                'user_id' => $user->id,
                'ata_chapter_id' => $subchapter?->ata_chapter_id,
                'ata_subchapter_id' => $subchapter?->id,
                'motor_id' => $motor?->id,
                'consecutivo' => $folio['consecutivo'],
                'anio' => $folio['anio'],
                'fecha' => now()->toDateString(),
                'cliente' => 'Red Aviation',
                'matricula' => 'XA-ABC',
                'aeronave_modelo' => 'Cessna 172',
                'aeronave_serie' => 'C172-2026',
                'tiempo_total' => 2450.50,
                'ciclos_totales' => 1320,
                'descripcion' => 'Orden de mantenimiento preventivo ATA 24-10.',
                'trabajo_descripcion' => 'Inspeccion de generadores y cableado asociado.',
                'componente_descripcion' => 'Generador principal',
                'componente_modelo' => 'GCU-24',
                'componente_numero_parte' => 'GEN-24-010',
                'componente_numero_serie' => 'SN-GEN-2026',
                'tipo_tarea' => 'INSPECCION',
                'intervalo' => '300 HRS',
                'accion_correctiva' => 'Cambio preventivo de terminal sulfatada.',
                'tecnico_responsable' => 'Kevin',
                'inspector' => 'Supervisor AVCS',
                'fecha_inicio' => now()->toDateString(),
                'fecha_termino' => now()->addDay()->toDateString(),
                'estado' => 'en_proceso',
            ]
        );

        $this->syncOrderDetails($orden, $area, [
            'tareas' => [
                [
                    'titulo' => 'Inspeccion de generador',
                    'descripcion' => 'Verificar conexiones, torque y condition del generador.',
                    'orden' => 1,
                    'tipo' => 'INSPECCION',
                    'prioridad' => 'MEDIA',
                    'tiempo_estimado_min' => 45,
                    'estado' => 'pendiente',
                    'tecnico' => 'Kevin',
                ],
                [
                    'titulo' => 'Prueba funcional del sistema electrico',
                    'descripcion' => 'Confirmar voltaje, amperaje y estabilidad del generador despues del ajuste.',
                    'orden' => 2,
                    'tipo' => 'PRUEBA',
                    'prioridad' => 'ALTA',
                    'tiempo_estimado_min' => 30,
                    'estado' => 'pendiente',
                    'tecnico' => 'Kevin',
                ],
            ],
            'discrepancias' => [
                [
                    'item' => '01',
                    'descripcion' => 'Terminal de salida con signos de corrosion.',
                    'accion_correctiva' => 'Limpieza y reemplazo de terminal.',
                    'status' => 'abierta',
                    'inspector' => 'Supervisor AVCS',
                    'fecha_inicio' => now()->toDateString(),
                ],
            ],
            'refacciones' => [
                [
                    'item' => 'R1',
                    'descripcion' => 'Terminal electrica tipo MIL',
                    'nombre' => 'Terminal tipo MIL',
                    'cantidad' => 2,
                    'numero_parte' => 'MIL-TERM-01',
                    'status' => 'recibida',
                    'area_procedencia' => 'ALMACEN AVCS',
                    'costo_total' => 1250.00,
                    'precio_venta' => 1600.00,
                ],
            ],
            'consumibles' => [
                [
                    'item' => 'C1',
                    'descripcion' => 'Limpiador de contactos',
                    'nombre' => 'Limpiador electrico',
                    'cantidad' => 1,
                ],
            ],
            'herramientas' => [
                [
                    'item' => 'H1',
                    'descripcion' => 'Multimetro calibrado',
                    'nombre' => 'Multimetro',
                    'cantidad' => 1,
                ],
            ],
            'ndt' => [
                [
                    'item' => '1',
                    'tipo_prueba' => 'Inspeccion visual',
                    'cantidad' => 1,
                    'resultado' => 'Sin grietas visibles',
                ],
            ],
            'talleres_externos' => [
                [
                    'item' => '1',
                    'proveedor' => 'Taller Aeronautico MX',
                    'tarea' => 'Certificacion de generador',
                    'trabajo_realizado' => 'Revision documental y certificacion.',
                    'costo' => 15000,
                    'precio_venta' => 18000,
                ],
            ],
            'mediciones' => [
                [
                    'item' => 'M1',
                    'tecnico' => 'Kevin',
                    'descripcion' => 'Voltaje de salida del generador',
                    'manual_od' => '28.5V',
                    'resultado_od' => '28.4V',
                    'parametro' => 'Voltaje',
                    'valor' => '28.4',
                    'unidad' => 'V',
                ],
            ],
        ]);
    }

    private function seedRequestedExamples(User $user): void
    {
        $examples = [
            [
                'folio' => 'CESA-TREN25-033',
                'area_codigo' => 'TREN',
                'anio' => 2025,
                'consecutivo' => 33,
                'fecha' => '2025-11-28',
                'matricula' => 'XB-MSZ',
                'cliente' => '-',
                'aeronave_modelo' => 'BEECHCRAFT 400A',
                'aeronave_serie' => 'RK-247',
                'descripcion' => 'Desensamble, Limpieza e inspeccion del MLG WHEEL RH',
                'trabajo_descripcion' => 'Desensamble, Limpieza e inspeccion del MLG WHEEL RH',
                'componente_descripcion' => 'MLG WHEEL RH',
                'componente_numero_parte' => '5010720-1',
                'componente_numero_serie' => 'NOV08-2408',
                'tipo_tarea' => 'Desensamble, Limpieza e inspeccion',
                'intervalo' => null,
                'accion_correctiva' => 'SE REALIZA DESARMADO DE COMPONENTE, LIMPIEZA DE ACCESWORIOS, APLICACION DE REMOVEDOR, ENVIO Y RECEPCION DE MATERIAL INSPECCION DE NDT., APLICACION DE PRIMMER, APLICACION DE PINTURA, ARMADO DE CONJUNTO QUEDANDO PENDIENTE LA INSTALACION DE TORNILLOS Y TORQUE DE LOS MISMOS.',
                'tecnico_responsable' => 'Tec. Hilario Gutierrez Hernandez',
                'inspector' => 'Supervisor TREN',
                'estado' => 'cerrada',
                'fecha_termino' => '2026-01-15',
                'tareas' => [
                    [
                        'titulo' => 'RRESEPCION DE COMPONENTE',
                        'descripcion' => 'Fecha: 28-11-25. H/H: 0.5. Tecnico: Tec. Hilario Gutierrez Hernandez.',
                        'orden' => 1,
                        'tipo' => 'INSPECCION',
                        'prioridad' => 'ALTA',
                        'tiempo_estimado_min' => 30,
                        'estado' => 'completada',
                        'tecnico' => 'Tec. Hilario Gutierrez Hernandez',
                    ],
                    [
                        'titulo' => 'DESARMADO TOTAL',
                        'descripcion' => 'Fecha: 28-11-25. H/H: 2.5. Tecnico: Tec. Hilario Gutierrez Hernandez.',
                        'orden' => 2,
                        'tipo' => 'DESENSAMBLE',
                        'prioridad' => 'ALTA',
                        'tiempo_estimado_min' => 150,
                        'estado' => 'completada',
                        'tecnico' => 'Tec. Hilario Gutierrez Hernandez',
                    ],
                    [
                        'titulo' => 'LIMPIEZA DE COMPONENTES',
                        'descripcion' => 'Fecha: 29-11-25. H/H: 1.5. Tecnico: Tec. Hilario Gutierrez Hernandez.',
                        'orden' => 3,
                        'tipo' => 'LIMPIEZA',
                        'prioridad' => 'ALTA',
                        'tiempo_estimado_min' => 90,
                        'estado' => 'completada',
                        'tecnico' => 'Tec. Hilario Gutierrez Hernandez',
                    ],
                    [
                        'titulo' => 'APLICACION DE REMOVEDOR',
                        'descripcion' => 'Fecha: 01-12-25. H/H: 6. Tecnico: Tec. Hilario Gutierrez Hernandez.',
                        'orden' => 4,
                        'tipo' => 'PROCESO',
                        'prioridad' => 'ALTA',
                        'tiempo_estimado_min' => 360,
                        'estado' => 'completada',
                        'tecnico' => 'Tec. Hilario Gutierrez Hernandez',
                    ],
                    [
                        'titulo' => 'DETALLES DE PINTURA PARA INSPECCION',
                        'descripcion' => 'Fecha: 03-12-25. H/H: 1. Tecnico: Tec. Hilario Gutierrez Hernandez.',
                        'orden' => 5,
                        'tipo' => 'PREPARACION',
                        'prioridad' => 'MEDIA',
                        'tiempo_estimado_min' => 60,
                        'estado' => 'completada',
                        'tecnico' => 'Tec. Hilario Gutierrez Hernandez',
                    ],
                    [
                        'titulo' => 'SE ENVIA A INSPECCIONES NO DESTRUCTIVAS EL MATERIAL EN RELACION',
                        'descripcion' => 'Fecha: 04-12-25. H/H: 0.5. Tecnico: Tec. Hilario Gutierrez Hernandez.',
                        'orden' => 6,
                        'tipo' => 'NDT',
                        'prioridad' => 'ALTA',
                        'tiempo_estimado_min' => 30,
                        'estado' => 'completada',
                        'tecnico' => 'Tec. Hilario Gutierrez Hernandez',
                    ],
                    [
                        'titulo' => 'RESEPCION DE MATERIAL INSPECCIONADO',
                        'descripcion' => 'Fecha: 05-01-26. H/H: 0.5. Tecnico: Tec. Hilario Gutierrez Hernandez.',
                        'orden' => 7,
                        'tipo' => 'RECEPCION',
                        'prioridad' => 'MEDIA',
                        'tiempo_estimado_min' => 30,
                        'estado' => 'completada',
                        'tecnico' => 'Tec. Hilario Gutierrez Hernandez',
                    ],
                    [
                        'titulo' => 'APLICACION DE PRIMER',
                        'descripcion' => 'Fecha: 05-01-26. H/H: 4. Tecnico: Tec. Hilario Gutierrez Hernandez.',
                        'orden' => 8,
                        'tipo' => 'PINTURA',
                        'prioridad' => 'MEDIA',
                        'tiempo_estimado_min' => 240,
                        'estado' => 'completada',
                        'tecnico' => 'Tec. Hilario Gutierrez Hernandez',
                    ],
                    [
                        'titulo' => 'APLICACION DE PINTURA',
                        'descripcion' => 'Fecha: 06-01-26. H/H: 5. Tecnico: Tec. Hilario Gutierrez Hernandez.',
                        'orden' => 9,
                        'tipo' => 'PINTURA',
                        'prioridad' => 'MEDIA',
                        'tiempo_estimado_min' => 300,
                        'estado' => 'completada',
                        'tecnico' => 'Tec. Hilario Gutierrez Hernandez',
                    ],
                    [
                        'titulo' => 'RESEPCION DE REFACCIONES Y CONSUMIBLES',
                        'descripcion' => 'Fecha: 15-01-26. H/H: 0.5. Tecnico: Tec. Hilario Gutierrez Hernandez.',
                        'orden' => 10,
                        'tipo' => 'RECEPCION',
                        'prioridad' => 'MEDIA',
                        'tiempo_estimado_min' => 30,
                        'estado' => 'completada',
                        'tecnico' => 'Tec. Hilario Gutierrez Hernandez',
                    ],
                    [
                        'titulo' => 'ARMADO DE COMPONENTE',
                        'descripcion' => 'Fecha: 15-01-26. H/H: 8. Tecnico: Tec. Hilario Gutierrez Hernandez.',
                        'orden' => 11,
                        'tipo' => 'ARMADO',
                        'prioridad' => 'ALTA',
                        'tiempo_estimado_min' => 480,
                        'estado' => 'completada',
                        'tecnico' => 'Tec. Hilario Gutierrez Hernandez',
                    ],
                ],
                'discrepancias' => [
                    [
                        'item' => '01',
                        'descripcion' => 'Desensamble, Limpieza e inspeccion del MLG WHEEL RH',
                        'accion_correctiva' => 'SE REALIZA DESARMADO DE COMPONENTE, LIMPIEZA DE ACCESWORIOS, APLICACION DE REMOVEDOR, ENVIO Y RECEPCION DE MATERIAL INSPECCION DE NDT., APLICACION DE PRIMMER, APLICACION DE PINTURA, ARMADO DE CONJUNTO QUEDANDO PENDIENTE LA INSTALACION DE TORNILLOS Y TORQUE DE LOS MISMOS.',
                        'status' => 'cerrada',
                        'inspector' => null,
                        'fecha_inicio' => '2025-11-28',
                        'fecha_termino' => '2026-01-15',
                        'horas_hombre' => 30,
                    ],
                ],
                'refacciones' => [
                    ['item' => 'R1', 'solicitante_fecha' => '2025-11-28', 'nombre' => 'CONE, Bearing (ITEM 10)', 'descripcion' => 'REEMPLAZO MANDATORIO', 'cantidad' => 1, 'numero_parte' => 'L305649 / ALT. L305649-20629', 'status' => 'ENTREGADO ALMACEN', 'certificado_conformidad' => 'PENDIENTE', 'area_procedencia' => 'COMPRAS', 'recibe_fecha' => '2026-01-14'],
                    ['item' => 'R2', 'solicitante_fecha' => '2025-11-28', 'nombre' => 'SEAL, Bearing (ITEM 30)', 'descripcion' => 'REEMPLAZO MANDATORIO', 'cantidad' => 1, 'numero_parte' => '5010691', 'status' => 'ENTREGADO ALMACEN', 'certificado_conformidad' => null, 'area_procedencia' => 'COMPRAS', 'recibe_fecha' => '2026-01-14'],
                    ['item' => 'R3', 'solicitante_fecha' => '2025-11-28', 'nombre' => 'CONE, Bearing (ITEM 40)', 'descripcion' => 'REEMPLAZO MANDATORIO', 'cantidad' => 1, 'numero_parte' => 'L305649 / ALT. L30569-20629', 'status' => 'ENTREGADO ALMACEN', 'certificado_conformidad' => 'PENDIENTE', 'area_procedencia' => 'COMPRAS', 'recibe_fecha' => '2026-01-14'],
                    ['item' => 'R4', 'solicitante_fecha' => '2025-11-28', 'nombre' => 'SEAL, Bearin (ITEM 50)', 'descripcion' => 'REEMPLAZO MANDATORIO', 'cantidad' => 1, 'numero_parte' => '5011252', 'status' => 'ENTREGADO ALMACEN', 'certificado_conformidad' => null, 'area_procedencia' => 'COMPRAS', 'recibe_fecha' => '2026-01-14'],
                    ['item' => 'R5', 'solicitante_fecha' => '2025-11-28', 'nombre' => 'INFLATION VALVE / ASSEMBLY (ITEM 60)****', 'descripcion' => 'REEMPLAZO MANDATORIO', 'cantidad' => 1, 'numero_parte' => 'TR760-03', 'status' => 'CANCELADO', 'certificado_conformidad' => '-', 'area_procedencia' => null, 'recibe_fecha' => null],
                    ['item' => 'R6', 'solicitante_fecha' => '2025-11-28', 'nombre' => 'CAP, Valve (ITEM 70)', 'descripcion' => 'REEMPLAZO MANDATORIO', 'cantidad' => 1, 'numero_parte' => 'VC5 / ALT. MS20813-1', 'status' => 'ENTREGADO ALMACEN', 'certificado_conformidad' => null, 'area_procedencia' => 'COMPRAS', 'recibe_fecha' => '2026-01-14'],
                    ['item' => 'R7', 'solicitante_fecha' => '2025-11-28', 'nombre' => 'CORE, Valve (ITEM 80)', 'descripcion' => 'REEMPLAZO MANDATORIO', 'cantidad' => 1, 'numero_parte' => 'C4', 'status' => 'ENTREGADO ALMACEN', 'certificado_conformidad' => '-', 'area_procedencia' => 'ALMACEN HANGAR', 'recibe_fecha' => '2025-12-01'],
                    ['item' => 'R8', 'solicitante_fecha' => '2025-11-28', 'nombre' => 'PACKING, Preformed (ITEM 100)', 'descripcion' => 'REEMPLAZO MANDATORIO', 'cantidad' => 1, 'numero_parte' => 'RG30', 'status' => 'ENTREGADO ALMACEN', 'certificado_conformidad' => null, 'area_procedencia' => 'COMPRAS', 'recibe_fecha' => '2026-01-14'],
                    ['item' => 'R9', 'solicitante_fecha' => '2025-11-28', 'nombre' => 'NUT, Self-locking (ITEM 120)', 'descripcion' => 'REEMPLAZO MANDATORIO', 'cantidad' => 7, 'numero_parte' => 'GYN186', 'status' => 'COTIZADO TEXTRON', 'certificado_conformidad' => null, 'area_procedencia' => null, 'recibe_fecha' => null],
                    ['item' => 'R10', 'solicitante_fecha' => '2025-11-28', 'nombre' => 'PACKING, Preformed (ITEM 150)', 'descripcion' => 'REEMPLAZO MANDATORIO', 'cantidad' => 1, 'numero_parte' => 'MS28775-269', 'status' => 'ENTREGADO ALMACEN', 'certificado_conformidad' => null, 'area_procedencia' => 'COMPRAS', 'recibe_fecha' => '2026-01-14'],
                    ['item' => 'R11', 'solicitante_fecha' => '2025-11-28', 'nombre' => 'SPACER, Heat Shield (ITEM 190)', 'descripcion' => 'REEMPLAZO MANDATORIO', 'cantidad' => 7, 'numero_parte' => '5010891', 'status' => 'ENTREGADO ALMACEN', 'certificado_conformidad' => null, 'area_procedencia' => 'COMPRAS', 'recibe_fecha' => '2026-01-14'],
                    ['item' => 'R12', 'solicitante_fecha' => '2025-11-28', 'nombre' => 'CUP, Tapered Roller (ITEM 250)', 'descripcion' => 'REEMPLAZO MANDATORIO', 'cantidad' => 2, 'numero_parte' => 'L305610', 'status' => 'ENTREGADO ALMACEN', 'certificado_conformidad' => null, 'area_procedencia' => 'COMPRAS', 'recibe_fecha' => '2026-01-14'],
                    ['item' => 'R13', 'solicitante_fecha' => '2025-11-28', 'nombre' => 'BAFFLE, Grease Retainer (ITEM 260)', 'descripcion' => 'REEMPLAZO MANDATORIO', 'cantidad' => 2, 'numero_parte' => '5010890', 'status' => 'ENTREGADO ALMACEN', 'certificado_conformidad' => null, 'area_procedencia' => 'COMPRAS', 'recibe_fecha' => '2026-01-14'],
                    ['item' => 'R14', 'solicitante_fecha' => '2025-12-01', 'nombre' => 'BOLT', 'descripcion' => 'REMPALZO', 'cantidad' => 7, 'numero_parte' => 'GYS186C21', 'status' => 'ENTREGADO', 'certificado_conformidad' => null, 'area_procedencia' => 'ALMACEN GENERAL', 'recibe_fecha' => '2025-12-01'],
                    ['item' => 'R15', 'solicitante_fecha' => '2025-12-01', 'nombre' => 'PLATE INSTRUCTION (ITEM 290)', 'descripcion' => 'REEMPLAZO MANDATORIO', 'cantidad' => 1, 'numero_parte' => '5002742', 'status' => 'COTIZADO TEXTRON', 'certificado_conformidad' => null, 'area_procedencia' => 'COMPRAS', 'recibe_fecha' => '2026-01-14'],
                    ['item' => 'R16', 'solicitante_fecha' => '2025-12-01', 'nombre' => 'PLATE , CUSTUMER IDENTIFICATION DATE (ITEM 300B)', 'descripcion' => 'REEMPLAZO MANDATORIO', 'cantidad' => 1, 'numero_parte' => '5012449', 'status' => 'ENTREGADO ALMACEN', 'certificado_conformidad' => null, 'area_procedencia' => 'COMPRAS', 'recibe_fecha' => '2026-01-14'],
                ],
                'consumibles' => [
                    ['item' => 'C1', 'solicitante_fecha' => '2025-12-08', 'nombre' => 'EPOXY PRIMER', 'descripcion' => 'PINTURA', 'cantidad' => 1, 'numero_parte' => 'MIL-PRF-23377', 'status' => 'entregado', 'area_procedencia' => 'ALMACEN GENERAL', 'recibe_fecha' => '2025-12-09'],
                    ['item' => 'C2', 'solicitante_fecha' => '2025-12-08', 'nombre' => 'REMOVEDOR', 'descripcion' => 'PINTURA', 'cantidad' => 2, 'numero_parte' => 'EPO-GON', 'status' => 'entregado', 'area_procedencia' => 'ALMACEN GENERAL', 'recibe_fecha' => '2025-12-09'],
                    ['item' => 'C3', 'solicitante_fecha' => '2025-12-08', 'nombre' => 'M.E.K.', 'descripcion' => 'LIMPIEZA', 'cantidad' => 1, 'numero_parte' => 'UNK', 'status' => 'entregado', 'area_procedencia' => 'ALMACEN GENERAL', 'recibe_fecha' => '2025-12-09'],
                    ['item' => 'C4', 'solicitante_fecha' => '2025-12-08', 'nombre' => 'ALCOHOL', 'descripcion' => 'LIMPIEZA', 'cantidad' => 1, 'numero_parte' => 'UNK', 'status' => 'entregado', 'area_procedencia' => 'ALMACEN GENERAL', 'recibe_fecha' => '2025-12-09'],
                    ['item' => 'C5', 'solicitante_fecha' => '2025-12-08', 'nombre' => 'TRAPO LIMPIO', 'descripcion' => 'LIMPIEZA', 'cantidad' => 1, 'numero_parte' => 'UNK', 'status' => 'entregado', 'area_procedencia' => 'ALMACEN GENERAL', 'recibe_fecha' => '2025-12-09'],
                    ['item' => 'C6', 'solicitante_fecha' => '2025-12-08', 'nombre' => 'GUANTES DE NITRILO', 'descripcion' => 'TRABAJOS', 'cantidad' => 4, 'numero_parte' => 'UNK', 'status' => 'entregado', 'area_procedencia' => 'ALMACEN GENERAL', 'recibe_fecha' => '2025-12-09'],
                    ['item' => 'C7', 'solicitante_fecha' => '2025-12-08', 'nombre' => 'LOCTITE', 'descripcion' => 'ARMADO', 'cantidad' => 1, 'numero_parte' => 'GRADO H', 'status' => 'entregado', 'area_procedencia' => 'ALMACEN GENERAL', 'recibe_fecha' => '2025-12-09'],
                    ['item' => 'C8', 'solicitante_fecha' => '2025-12-08', 'nombre' => 'LUBRI BOND 220', 'descripcion' => 'ARMADO', 'cantidad' => 1, 'numero_parte' => 'MIL-L-23398', 'status' => 'entregado', 'area_procedencia' => 'ALMACEN GENERAL', 'recibe_fecha' => '2025-12-09'],
                    ['item' => 'C9', 'solicitante_fecha' => '2025-12-08', 'nombre' => 'GRASA', 'descripcion' => 'ENGRASE', 'cantidad' => 2, 'numero_parte' => 'MIL-PRF - 81322 (SHC 100 MOVIL)', 'status' => 'entregado', 'area_procedencia' => 'ALMACEN GENERAL', 'recibe_fecha' => '2025-12-09'],
                    ['item' => 'C10', 'solicitante_fecha' => '2025-12-08', 'nombre' => 'NITROGENO', 'descripcion' => 'CARGA DE NITROGENO', 'cantidad' => 250, 'numero_parte' => null, 'status' => 'entregado', 'area_procedencia' => 'ALMACEN GENERAL', 'recibe_fecha' => '2025-12-09'],
                    ['item' => 'C11', 'solicitante_fecha' => '2025-12-08', 'nombre' => 'KIT PINTURA BLANCA', 'descripcion' => 'PINTURA', 'cantidad' => 1, 'numero_parte' => 'NERVION', 'status' => 'entregado', 'area_procedencia' => 'ALMACEN GENERAL', 'recibe_fecha' => '2025-12-09'],
                    ['item' => 'C12', 'solicitante_fecha' => '2025-12-08', 'nombre' => 'ANTISIZE MOLIBDENO', 'descripcion' => 'ARMADO', 'cantidad' => 1, 'numero_parte' => 'MIL-PRF 83483', 'status' => 'entregado', 'area_procedencia' => 'ALMACEN GENERAL', 'recibe_fecha' => '2025-12-09'],
                    ['item' => 'C13', 'solicitante_fecha' => '2025-12-08', 'nombre' => 'HIELO SECO', 'descripcion' => 'ARMADO', 'cantidad' => 1, 'numero_parte' => 'UNK', 'status' => 'entregado', 'area_procedencia' => 'ALMACEN GENERAL', 'recibe_fecha' => '2025-12-09'],
                ],
                'ndt' => [
                    ['item' => '1', 'tipo_prueba' => 'LIQUIDOS PENETRANTES', 'cantidad' => 1, 'sub_componente' => 'WHEEL SUBASSEMBLY', 'numero_parte' => '5010714-1', 'numero_serie' => 'DEC02-1735', 'seccion_manual' => 'CMM 32-40-99 / Refer to AP-763 Nondestructive Testing Manual (32-42-07)', 'envio_a' => 'EXCEL'],
                    ['item' => '2', 'tipo_prueba' => 'LIQUIDOS PENETRANTES', 'cantidad' => 1, 'sub_componente' => 'FLANGE', 'numero_parte' => '5010703', 'numero_serie' => 'NOV08-2408', 'seccion_manual' => 'CMM 32-40-99 / Refer to AP-763 Nondestructive Testing Manual (32-42-07)', 'envio_a' => 'EXCEL'],
                    ['item' => '3', 'tipo_prueba' => 'PARTICULAS MAGNETICAS', 'cantidad' => 7, 'sub_componente' => 'BOLT', 'numero_parte' => 'GYS186C21', 'numero_serie' => '-', 'seccion_manual' => 'CMM 32-40-99 / Refer to AP-763 Nondestructive Testing Manual (32-42-04 or 32-42-06)', 'envio_a' => 'NAPSA'],
                    ['item' => '4', 'tipo_prueba' => 'PARTICULAS MAGNETICAS', 'cantidad' => 6, 'sub_componente' => 'BOLT', 'numero_parte' => 'GYS186C21', 'numero_serie' => '-', 'seccion_manual' => 'CMM 32-40-99 / Refer to AP-763 Nondestructive Testing Manual (32-42-04 or 32-42-06)', 'envio_a' => 'NAPSA'],
                    ['item' => '5', 'tipo_prueba' => 'ULTRASONIDO', 'cantidad' => 1, 'sub_componente' => 'Wheel bead seat / areas', 'numero_parte' => '5010714-1', 'numero_serie' => 'DEC02-1735', 'seccion_manual' => 'CMM 32-40-99 / Refer to AP-763 Nondestructive Testing Manual (32-42-04 or 32-42-05)', 'envio_a' => 'EXCEL'],
                ],
            ],
            [
                'folio' => 'CESA-HANG26-016',
                'area_codigo' => 'HANG',
                'anio' => 2026,
                'consecutivo' => 16,
                'fecha' => '2026-02-10',
                'matricula' => 'XA-MMN',
                'cliente' => 'RED AVIATION',
                'aeronave_modelo' => 'N/A',
                'aeronave_serie' => 'N/A',
                'descripcion' => 'Recarga de oxigeno.',
                'trabajo_descripcion' => 'Recarga de oxigeno a la aeronave y verificacion de presion final del sistema.',
                'componente_descripcion' => 'Sistema de oxigeno',
                'tipo_tarea' => 'SERVICIO',
                'intervalo' => 'BAJO DEMANDA',
                'accion_correctiva' => 'Recarga de oxigeno y verificacion de ausencia de fugas en conexiones accesibles.',
                'tecnico_responsable' => 'Kevin',
                'inspector' => 'Supervisor HANG',
                'estado' => 'cerrada',
                'tareas' => [
                    [
                        'titulo' => 'Recarga y comprobacion del sistema de oxigeno',
                        'descripcion' => 'Realizar recarga, registrar presión final y verificar condición general del sistema.',
                        'orden' => 1,
                        'tipo' => 'SERVICIO',
                        'prioridad' => 'MEDIA',
                        'tiempo_estimado_min' => 60,
                        'estado' => 'completada',
                        'tecnico' => 'Kevin',
                    ],
                ],
                'discrepancias' => [
                    [
                        'item' => '01',
                        'descripcion' => 'Presion del sistema por debajo del valor operativo.',
                        'accion_correctiva' => 'Se realizo recarga de oxigeno y verificacion de hermeticidad en conexiones accesibles.',
                        'status' => 'cerrada',
                        'inspector' => 'Supervisor HANG',
                        'fecha_inicio' => '2026-02-10',
                        'fecha_termino' => '2026-02-10',
                        'horas_hombre' => 1.00,
                    ],
                ],
                'refacciones' => [
                    [
                        'item' => 'R1',
                        'solicitante_fecha' => '2026-02-10',
                        'nombre' => 'Sello para puerto de carga',
                        'descripcion' => 'Sello de reemplazo preventivo para conexion de carga.',
                        'cantidad' => 1,
                        'numero_parte' => 'OXY-SEAL-01',
                        'status' => 'entregado',
                        'certificado_conformidad' => 'OK',
                        'area_procedencia' => 'ALMACEN HANGAR',
                        'recibe_fecha' => '2026-02-10',
                        'costo_total' => 420.00,
                        'precio_venta' => 650.00,
                    ],
                ],
                'consumibles' => [
                    [
                        'item' => 'C1',
                        'solicitante_fecha' => '2026-02-10',
                        'nombre' => 'Oxigeno aviacion',
                        'descripcion' => 'Carga de oxigeno gaseoso para sistema de aeronave.',
                        'cantidad' => 1,
                        'status' => 'entregado',
                        'area_procedencia' => 'ALMACEN HANGAR',
                        'recibe_fecha' => '2026-02-10',
                    ],
                ],
                'herramientas' => [
                    [
                        'item' => 'H1',
                        'solicitante_fecha' => '2026-02-10',
                        'nombre' => 'Carro de oxigeno',
                        'descripcion' => 'Unidad de servicio para recarga de sistema de oxigeno.',
                        'cantidad' => 1,
                        'status' => 'disponible',
                        'area_procedencia' => 'HANGAR',
                    ],
                ],
                'ndt' => [
                    [
                        'item' => '1',
                        'tipo_prueba' => 'Prueba de fugas',
                        'cantidad' => 1,
                        'sub_componente' => 'Linea de oxigeno',
                        'resultado' => 'Sin fugas detectadas en conexiones accesibles',
                    ],
                ],
                'talleres_externos' => [
                    [
                        'item' => '1',
                        'proveedor' => 'Laboratorio de Gases Aeronauticos',
                        'tarea' => 'Certificacion de pureza de oxigeno',
                        'cantidad' => 1,
                        'certificado' => 'CERT-OXY-260210',
                        'trabajo_realizado' => 'Suministro certificado de oxigeno para aviacion.',
                        'costo' => 1800.00,
                        'precio_venta' => 2300.00,
                    ],
                ],
                'mediciones' => [
                    [
                        'item' => 'M1',
                        'tecnico' => 'Kevin',
                        'descripcion' => 'Presion final del sistema de oxigeno',
                        'manual_od' => '1850 PSI',
                        'resultado_od' => '1850 PSI',
                        'parametro' => 'Presion',
                        'valor' => '1850',
                        'unidad' => 'PSI',
                    ],
                ],
            ],
        ];

        foreach ($examples as $example) {
            $area = Area::where('codigo', $example['area_codigo'])->firstOrFail();
            $tipo = TipoOrden::where('codigo', $example['area_codigo'])->firstOrFail();

            $orden = Orden::updateOrCreate(
                ['folio' => $example['folio']],
                [
                    'area_id' => $area->id,
                    'tipo_id' => $tipo->id,
                    'user_id' => $user->id,
                    'consecutivo' => $example['consecutivo'],
                    'anio' => $example['anio'],
                    'fecha' => $example['fecha'],
                    'cliente' => $example['cliente'],
                    'matricula' => $example['matricula'],
                    'aeronave_modelo' => $example['aeronave_modelo'],
                    'aeronave_serie' => $example['aeronave_serie'],
                    'descripcion' => $example['descripcion'],
                    'trabajo_descripcion' => $example['trabajo_descripcion'],
                    'componente_descripcion' => $example['componente_descripcion'],
                    'tipo_tarea' => $example['tipo_tarea'],
                    'intervalo' => $example['intervalo'],
                    'accion_correctiva' => $example['accion_correctiva'],
                    'tecnico_responsable' => $example['tecnico_responsable'],
                    'inspector' => $example['inspector'],
                    'fecha_inicio' => $example['fecha_inicio'] ?? $example['fecha'],
                    'fecha_termino' => $example['fecha_termino'] ?? $example['fecha'],
                    'estado' => $example['estado'],
                ]
            );

            $this->syncOrderDetails($orden, $area, $example);
        }
    }

    private function syncOrderDetails(Orden $orden, Area $area, array $example): void
    {
        $orden->tareas()->delete();
        foreach ($example['tareas'] ?? [] as $task) {
            Tarea::create([
                'orden_id' => $orden->id,
                'area_id' => $area->id,
                'titulo' => $task['titulo'],
                'descripcion' => $task['descripcion'],
                'orden' => $task['orden'],
                'tipo' => $task['tipo'],
                'prioridad' => $task['prioridad'],
                'tiempo_estimado_min' => $task['tiempo_estimado_min'],
                'estado' => $task['estado'],
                'tecnico' => $task['tecnico'],
            ]);
        }

        $this->syncSimpleRelation($orden, 'discrepancias', $example['discrepancias'] ?? []);
        $this->syncSimpleRelation($orden, 'refacciones', $example['refacciones'] ?? []);
        $this->syncSimpleRelation($orden, 'consumibles', $example['consumibles'] ?? []);
        $this->syncSimpleRelation($orden, 'herramientas', $example['herramientas'] ?? []);
        $this->syncSimpleRelation($orden, 'ndt', $example['ndt'] ?? []);
        $this->syncSimpleRelation($orden, 'talleresExternos', $example['talleres_externos'] ?? []);
        $this->syncSimpleRelation($orden, 'mediciones', $example['mediciones'] ?? []);
    }

    private function syncSimpleRelation(Orden $orden, string $relation, array $items): void
    {
        $orden->{$relation}()->delete();

        foreach ($items as $item) {
            $orden->{$relation}()->create($item);
        }
    }
}
