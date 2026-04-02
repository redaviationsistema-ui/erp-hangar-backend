<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\AtaSubchapter;
use App\Models\Manual;
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
        $this->seedManualRecords();
        $this->seedAvionicsExample($user);
        $this->seedHangarMotorExample($user);
        $this->seedRequestedExamples($user);
        $this->seedSharedExcelExamples($user);
    }

    private function seedManualRecords(): void
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
            [
                'archivo_path' => 'A:\\RED AVIATION\\ALFA\\CESA-FREN26-001 - INSPECCION DE CONDICIÓN A BRAKE ASSY.xlsx',
                'nombre' => 'CESA-FREN26-001 - INSPECCION DE CONDICIÓN A BRAKE ASSY',
                'tipo_manual' => 'XLSX',
                'idioma' => 'es',
                'estado' => 'vigente',
                'descripcion' => 'Registro documental sembrado para base de datos.',
            ],
            [
                'archivo_path' => 'A:\\RED AVIATION\\ALFA\\CESA-HANG26-016 - XA-MMN - Recarga de oxígeno.xlsx',
                'nombre' => 'CESA-HANG26-016 - XA-MMN - Recarga de oxígeno',
                'tipo_manual' => 'XLSX',
                'idioma' => 'es',
                'estado' => 'vigente',
                'descripcion' => 'Registro documental sembrado para base de datos.',
            ],
            [
                'archivo_path' => 'A:\\RED AVIATION\\ALFA\\CESA-TREN25-007 - LV-FVT - INSPECCION Y ARMADO DE WHEEL ASSY.xlsx',
                'nombre' => 'CESA-TREN25-007 - LV-FVT - INSPECCION Y ARMADO DE WHEEL ASSY',
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
    }

    private function seedSharedExcelExamples(User $user): void
    {
        $this->seedBatt25004Example($user);
        $this->seedEstr25030Example($user);
        $this->seedFren26001Example($user);
        $this->seedHang26016ExcelExample($user);
        $this->seedTren25007Example($user);
    }

    // CESA-BATT25-004
    private function seedBatt25004Example(User $user): void
    {
        $area = Area::where('codigo', 'BATT')->firstOrFail();
        $tipo = TipoOrden::where('codigo', 'BATT')->firstOrFail();

        $orden = Orden::updateOrCreate(
            ['folio' => 'CESA-BATT25-004'],
            [
                'area_id' => $area->id,
                'tipo_id' => $tipo->id,
                'user_id' => $user->id,
                'consecutivo' => 4,
                'anio' => 2025,
                'fecha' => '2025-01-01',
                'cliente' => 'CESA',
                'matricula' => 'XB-SBG',
                'aeronave_modelo' => 'CESSNA 650',
                'aeronave_serie' => '650-0071',
                'descripcion' => 'INGRESAN A DEEP CYCLE BATERIA',
                'trabajo_descripcion' => 'INGRESAN A DEEP CYCLE BATERIA',
                'componente_descripcion' => 'INGRESAN A DEEP CYCLE BATERIA',
                'componente_numero_parte' => '025008-000',
                'componente_numero_serie' => '052014',
                'tipo_tarea' => 'SERVICIO',
                'intervalo' => null,
                'accion_correctiva' => 'Ingreso de bateria a proceso deep cycle.',
                'tecnico_responsable' => 'Pendiente',
                'inspector' => 'Pendiente',
                'fecha_inicio' => '2025-01-01',
                'fecha_termino' => '2025-01-01',
                'estado' => 'cerrada',
            ]
        );

        $this->syncOrderDetails($orden, $area, [
            'tareas' => [
                [
                    'titulo' => 'Ingreso a deep cycle',
                    'descripcion' => 'Ingreso de bateria a proceso deep cycle. Componente P/N 025008-000, S/N 052014.',
                    'orden' => 1,
                    'tipo' => 'SERVICIO',
                    'prioridad' => 'MEDIA',
                    'tiempo_estimado_min' => 60,
                    'estado' => 'completada',
                    'tecnico' => 'Pendiente',
                ],
            ],
            'discrepancias' => [],
            'refacciones' => [],
            'consumibles' => [],
            'herramientas' => [],
            'ndt' => [],
            'talleres_externos' => [],
            'mediciones' => [],
        ]);
    }

    // CESA-ESTR25-030
    private function seedEstr25030Example(User $user): void
    {
        $area = Area::where('codigo', 'ESTR')->firstOrFail();
        $tipo = TipoOrden::where('codigo', 'ESTR')->firstOrFail();

        $orden = Orden::updateOrCreate(
            ['folio' => 'CESA-ESTR25-030'],
            [
                'area_id' => $area->id,
                'tipo_id' => $tipo->id,
                'user_id' => $user->id,
                'consecutivo' => 30,
                'anio' => 2025,
                'fecha' => '2025-04-25',
                'cliente' => 'CESA',
                'matricula' => 'XA-ZYZ',
                'aeronave_modelo' => 'LEARJET 31',
                'aeronave_serie' => 'UNK',
                'descripcion' => 'APLICACION DE PRIMER A AIR INLET ASSY ENGINE NACELLE RH Y LH',
                'trabajo_descripcion' => 'APLICACION DE PRIMER A AIR INLET ASSY ENGINE NACELLE RH NP:2652010-096 Y LH NP:2652010-81',
                'componente_descripcion' => 'AIR INLET ASSY ENGINE NACELLE RH Y LH',
                'componente_numero_parte' => '2652010-096 / 2652010-81',
                'tipo_tarea' => 'REPARACION',
                'intervalo' => null,
                'accion_correctiva' => 'Se realizo reparacion con tela fibra de vidrio y resina Loctite EA9396, lijado y aplicacion de primer.',
                'tecnico_responsable' => 'JORGE GARCIA',
                'inspector' => 'Pendiente',
                'fecha_inicio' => '2025-04-25',
                'fecha_termino' => '2025-04-25',
                'estado' => 'cerrada',
            ]
        );

        $this->syncOrderDetails($orden, $area, [
            'tareas' => [
                ['titulo' => 'Lijado y desengrasado', 'descripcion' => 'Lijado y desengrasado del componente previo a reparacion y primer.', 'orden' => 1, 'tipo' => 'PREPARACION', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 120, 'estado' => 'completada', 'tecnico' => 'JORGE GARCIA'],
                ['titulo' => 'Reparacion con fibra de vidrio', 'descripcion' => 'Pegado de piel de fibra de vidrio de 8\"x2\" con resina Loctite 9396.', 'orden' => 2, 'tipo' => 'REPARACION', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 180, 'estado' => 'completada', 'tecnico' => 'JORGE GARCIA'],
                ['titulo' => 'Aplicacion de primer', 'descripcion' => 'Lijado y aplicacion de primer cromato de zinc en nacela RH y LH.', 'orden' => 3, 'tipo' => 'PINTURA', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 120, 'estado' => 'completada', 'tecnico' => 'JORGE GARCIA'],
            ],
            'discrepancias' => [
                ['item' => '01', 'descripcion' => 'Durante la preparacion de la nacela para aplicacion de primer se encontro daño en la estructura.', 'accion_correctiva' => 'Se realizo reparacion con tela fibra de vidrio y resina Loctite EA9396. Se dejo en condiciones operativas y se aplico primer.', 'status' => 'cerrada', 'fecha_inicio' => '2025-04-25', 'fecha_termino' => '2025-04-25'],
                ['item' => '02', 'descripcion' => 'Durante la preparacion de la nacela para aplicacion de primer se encontro daño en la estructura.', 'accion_correctiva' => 'Se realizo reparacion con tela fibra de vidrio y resina Loctite EA9396. Se dejo en condiciones operativas y se aplico primer.', 'status' => 'cerrada', 'fecha_inicio' => '2025-04-25', 'fecha_termino' => '2025-04-25'],
            ],
            'refacciones' => [],
            'consumibles' => [
                ['item' => 'C1', 'solicitante_fecha' => '2025-04-25', 'nombre' => 'RESINA LOCTITE EA 9396', 'descripcion' => 'REPARACION', 'cantidad' => '150G', 'status' => 'ENTREGADO', 'area_procedencia' => 'ALMACEN GENERAL'],
                ['item' => 'C2', 'solicitante_fecha' => '2025-04-25', 'nombre' => 'LIJA NO.320', 'descripcion' => 'REPARACION', 'cantidad' => '320', 'status' => 'ENTREGADO', 'area_procedencia' => 'ALMACEN GENERAL'],
                ['item' => 'C3', 'solicitante_fecha' => '2025-04-25', 'nombre' => 'LIJA NO.220', 'descripcion' => 'REPARACION', 'cantidad' => '220', 'status' => 'ENTREGADO', 'area_procedencia' => 'ALMACEN GENERAL'],
                ['item' => 'C4', 'solicitante_fecha' => '2025-04-25', 'nombre' => 'LIJA NO.80', 'descripcion' => 'REPARACION', 'cantidad' => '4', 'status' => 'ENTREGADO', 'area_procedencia' => 'ALMACEN GENERAL'],
                ['item' => 'C5', 'solicitante_fecha' => '2025-04-25', 'nombre' => 'PIEL FIBRA DE VIDRIO', 'descripcion' => 'REPARACION', 'cantidad' => '12\"X3\"', 'status' => 'ENTREGADO', 'area_procedencia' => 'ALMACEN GENERAL'],
                ['item' => 'C6', 'solicitante_fecha' => '2025-04-25', 'nombre' => 'PELICULA DE NAILON', 'descripcion' => 'REPARACION', 'cantidad' => '20\"X2\"', 'status' => 'ENTREGADO', 'area_procedencia' => 'ALMACEN GENERAL'],
                ['item' => 'C7', 'solicitante_fecha' => '2025-04-25', 'nombre' => 'CINTA PARA DUCTOS', 'descripcion' => 'REPARACION', 'cantidad' => '40CM', 'status' => 'ENTREGADO', 'area_procedencia' => 'ALMACEN GENERAL'],
                ['item' => 'C8', 'solicitante_fecha' => '2025-04-25', 'nombre' => 'TRAPO', 'descripcion' => 'REPARACION', 'cantidad' => '.5 KG', 'status' => 'ENTREGADO', 'area_procedencia' => 'ALMACEN GENERAL'],
                ['item' => 'C9', 'solicitante_fecha' => '2025-04-25', 'nombre' => 'MEK', 'descripcion' => 'REPARACION', 'cantidad' => '.5 LTS', 'status' => 'ENTREGADO', 'area_procedencia' => 'ALMACEN GENERAL'],
                ['item' => 'C10', 'solicitante_fecha' => '2025-04-25', 'nombre' => 'VASOS DESECHABLE', 'descripcion' => 'REPARACION', 'cantidad' => '10 PZA', 'status' => 'ENTREGADO', 'area_procedencia' => 'ALMACEN GENERAL'],
                ['item' => 'C11', 'solicitante_fecha' => '2025-04-25', 'nombre' => 'CROMATO DE ZINC', 'descripcion' => 'REPARACION', 'cantidad' => '0.5 LTS', 'status' => 'ENTREGADO', 'area_procedencia' => 'ALMACEN GENERAL'],
                ['item' => 'C12', 'solicitante_fecha' => '2025-04-25', 'nombre' => 'PAÑO RESPIRADOR PURGADOR', 'descripcion' => 'REPARACION', 'cantidad' => '15\"X5\"', 'status' => 'ENTREGADO', 'area_procedencia' => 'ALMACEN GENERAL'],
            ],
            'herramientas' => [],
            'ndt' => [],
            'talleres_externos' => [],
            'mediciones' => [],
        ]);
    }

    // CESA-FREN26-001
    private function seedFren26001Example(User $user): void
    {
        $area = Area::where('codigo', 'FREN')->firstOrFail();
        $tipo = TipoOrden::where('codigo', 'FREN')->firstOrFail();

        $orden = Orden::updateOrCreate(
            ['folio' => 'CESA-FREN26-001'],
            [
                'area_id' => $area->id,
                'tipo_id' => $tipo->id,
                'user_id' => $user->id,
                'consecutivo' => 1,
                'anio' => 2026,
                'fecha' => '2026-01-03',
                'cliente' => 'CORE DE VICTOR GAMBOA (CESA)',
                'matricula' => '-',
                'aeronave_modelo' => 'CESSNA 650',
                'aeronave_serie' => '-',
                'descripcion' => 'INSPECCION DE CONDICION A BRAKE ASSY',
                'trabajo_descripcion' => 'INSPECCION DE CONDICION A BRAKE ASSY',
                'componente_descripcion' => 'BRAKE ASSY',
                'componente_numero_parte' => '2-1502-3',
                'componente_numero_serie' => '1129',
                'tipo_tarea' => 'INSPECCION',
                'intervalo' => null,
                'accion_correctiva' => 'Inspeccion, tratamiento de oxido, armado, aplicacion de torque y pruebas para detectar fugas de hidraulico.',
                'tecnico_responsable' => 'Tec. Hilario Gutierrez Hernandez',
                'inspector' => 'Pendiente',
                'fecha_inicio' => '2026-01-03',
                'fecha_termino' => '2026-01-08',
                'estado' => 'cerrada',
            ]
        );

        $this->syncOrderDetails($orden, $area, [
            'tareas' => [
                ['titulo' => 'Recepcion de componente', 'descripcion' => '03-01-26. Se recepciona componente.', 'orden' => 1, 'tipo' => 'RECEPCION', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 30, 'estado' => 'completada', 'tecnico' => 'Tec. Hilario Gutierrez Hernandez'],
                ['titulo' => 'Desarmado de componente', 'descripcion' => '03-01-26. Se realiza desarmado de componente.', 'orden' => 2, 'tipo' => 'DESENSAMBLE', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 60, 'estado' => 'completada', 'tecnico' => 'Tec. Hilario Gutierrez Hernandez'],
                ['titulo' => 'Limpieza de discos y accesorios', 'descripcion' => '03-01-26. Limpieza de discos y accesorios.', 'orden' => 3, 'tipo' => 'LIMPIEZA', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 60, 'estado' => 'completada', 'tecnico' => 'Tec. Hilario Gutierrez Hernandez'],
                ['titulo' => 'Inspeccion visual', 'descripcion' => '03-01-26. Inspeccion visual del componente.', 'orden' => 4, 'tipo' => 'INSPECCION', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 60, 'estado' => 'completada', 'tecnico' => 'Tec. Hilario Gutierrez Hernandez'],
                ['titulo' => 'Tratamiento a oxido de discos', 'descripcion' => '06-01-25. Tratamiento a oxido de discos.', 'orden' => 5, 'tipo' => 'REPARACION', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 240, 'estado' => 'completada', 'tecnico' => 'Tec. Hilario Gutierrez Hernandez'],
                ['titulo' => 'Armado de componente', 'descripcion' => '07-01-26. Armado de componente.', 'orden' => 6, 'tipo' => 'ARMADO', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 120, 'estado' => 'completada', 'tecnico' => 'Tec. Hilario Gutierrez Hernandez'],
                ['titulo' => 'Aplicacion de torque', 'descripcion' => '07-01-26. Aplicacion de torque indicado en manual.', 'orden' => 7, 'tipo' => 'AJUSTE', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 30, 'estado' => 'completada', 'tecnico' => 'Tec. Hilario Gutierrez Hernandez'],
                ['titulo' => 'Prueba de fugas', 'descripcion' => '07-01-26. Pruebas para detectar fugas de hidraulico.', 'orden' => 8, 'tipo' => 'PRUEBA', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 60, 'estado' => 'completada', 'tecnico' => 'Tec. Hilario Gutierrez Hernandez'],
                ['titulo' => 'Desarmado y armado final', 'descripcion' => '08-01-26. Desarmado y armado total para aplicacion de Loctite, anti-seize, torque seal y frenado de tapones.', 'orden' => 9, 'tipo' => 'ARMADO', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 360, 'estado' => 'completada', 'tecnico' => 'Tec. Hilario Gutierrez Hernandez'],
            ],
            'discrepancias' => [],
            'refacciones' => [
                ['item' => 'R1', 'solicitante_fecha' => '2026-01-07', 'nombre' => 'ITEM. 35, RETAINER PACKING', 'descripcion' => 'ARMADO', 'cantidad' => 1, 'numero_parte' => '56-627', 'status' => 'MINISTRADO E INSTALADO', 'area_procedencia' => 'ALMACEN GENERAL'],
                ['item' => 'R2', 'solicitante_fecha' => '2026-01-07', 'nombre' => 'ITEM. 30, PREFORMED PACKING', 'descripcion' => 'ARMADO', 'cantidad' => 1, 'numero_parte' => 'MS 28775-012', 'status' => 'MINISTRADO E INSTALADO', 'area_procedencia' => 'ALMACEN GENERAL'],
                ['item' => 'R3', 'solicitante_fecha' => '2026-01-07', 'nombre' => 'ITEM. 225, PREFORMED PACKING', 'descripcion' => 'ARMADO', 'cantidad' => 4, 'numero_parte' => 'MS 28778-4', 'status' => 'MINISTRADO E INSTALADO', 'area_procedencia' => 'ALMACEN GENERAL'],
                ['item' => 'R4', 'solicitante_fecha' => '2026-01-07', 'nombre' => 'ITEM. 25, SHUTTLE VALVE', 'descripcion' => 'ARMADO', 'cantidad' => 1, 'numero_parte' => '195-171', 'status' => 'NO MINISTRADO'],
                ['item' => 'R5', 'solicitante_fecha' => '2026-01-07', 'nombre' => 'ITEM. 15, BOLT MACHINE', 'descripcion' => 'ARMADO', 'cantidad' => 2, 'numero_parte' => 'AN4H5A', 'status' => 'MINISTRADO E INSTALADO', 'area_procedencia' => 'ALMACEN GENERAL'],
                ['item' => 'R6', 'solicitante_fecha' => '2026-01-07', 'nombre' => 'ITEM. 20, WASHER FLAT', 'descripcion' => 'ARMADO', 'cantidad' => 2, 'numero_parte' => 'AN960-416', 'status' => 'MINISTRADO E INSTALADO', 'area_procedencia' => 'ALMACEN GENERAL'],
            ],
            'consumibles' => [
                ['item' => 'C1', 'solicitante_fecha' => '2026-01-07', 'nombre' => 'M.E.K.', 'descripcion' => 'LIMPIEZA', 'cantidad' => '1 LITRO', 'numero_parte' => 'UNK', 'status' => 'MINISTRADO Y USADO', 'area_procedencia' => 'ALMACEN GENERAL'],
                ['item' => 'C2', 'solicitante_fecha' => '2026-01-07', 'nombre' => 'ALCOHOL', 'descripcion' => 'LIMPIEZA', 'cantidad' => '1 LITRO', 'numero_parte' => 'UNK', 'status' => 'MINISTRADO Y USADO', 'area_procedencia' => 'ALMACEN GENERAL'],
                ['item' => 'C3', 'solicitante_fecha' => '2026-01-07', 'nombre' => 'TRAPO LIMPIO', 'descripcion' => 'LIMPIEZA', 'cantidad' => '1/2 KILO', 'numero_parte' => 'UNK', 'status' => 'MINISTRADO Y USADO', 'area_procedencia' => 'ALMACEN GENERAL'],
                ['item' => 'C4', 'solicitante_fecha' => '2026-01-07', 'nombre' => 'LIQUIDO HIDRAULICO', 'descripcion' => 'PRUEBAS', 'cantidad' => '1/2 QUARTO', 'numero_parte' => 'MIL-PRF-83282', 'status' => 'MINISTRADO Y USADO', 'area_procedencia' => 'ALMACEN GENERAL'],
                ['item' => 'C5', 'solicitante_fecha' => '2026-01-07', 'nombre' => 'COMPOUND THREAD-LOCK', 'descripcion' => 'ARMADO', 'cantidad' => '10 ML', 'numero_parte' => 'LOCTITE 262', 'status' => 'SE USO UN COMPATIBLE', 'area_procedencia' => 'ALMACEN GENERAL'],
                ['item' => 'C6', 'solicitante_fecha' => '2026-01-07', 'nombre' => 'ANTI-SEIZE COMPOUND', 'descripcion' => 'ARMADO', 'cantidad' => '1/4 LB', 'numero_parte' => 'SAE AMS 2518 (MIL-T5544)', 'status' => 'SE USO UN COMPATIBLE', 'area_procedencia' => 'ALMACEN GENERAL'],
            ],
            'herramientas' => [],
            'ndt' => [],
            'talleres_externos' => [],
            'mediciones' => [],
        ]);
    }

    // CESA-HANG26-016
    private function seedHang26016ExcelExample(User $user): void
    {
        $area = Area::where('codigo', 'HANG')->firstOrFail();
        $tipo = TipoOrden::where('codigo', 'HANG')->firstOrFail();

        $orden = Orden::updateOrCreate(
            ['folio' => 'CESA-HANG26-016'],
            [
                'area_id' => $area->id,
                'tipo_id' => $tipo->id,
                'user_id' => $user->id,
                'consecutivo' => 16,
                'anio' => 2026,
                'fecha' => '2026-01-15',
                'cliente' => 'SKY JETS INTERNATIONAL',
                'matricula' => 'XA-MMN',
                'aeronave_modelo' => 'LEARJET 35',
                'aeronave_serie' => '221',
                'descripcion' => 'Recarga de oxigeno a camilla de XA-MMN',
                'trabajo_descripcion' => 'Recarga de oxigeno a camilla de XA-MMN',
                'componente_descripcion' => 'Botella de oxigeno instalada en camilla',
                'tipo_tarea' => 'SERVICIO',
                'intervalo' => null,
                'accion_correctiva' => 'Se realizo recarga de oxigeno en botella de oxigeno instalada en camilla de aeronave.',
                'tecnico_responsable' => 'Tec. Omar Jair Montoya Landin 202504522',
                'inspector' => 'Tec. Omar Jair Montoya Landin 202504522',
                'fecha_inicio' => '2026-01-15',
                'fecha_termino' => '2026-01-15',
                'estado' => 'cerrada',
            ]
        );

        $this->syncOrderDetails($orden, $area, [
            'tareas' => [
                ['titulo' => 'Recarga de oxigeno', 'descripcion' => '15/01/26. Se realizo recarga de oxigeno en botella de oxigeno instalada en camilla de aeronave.', 'orden' => 1, 'tipo' => 'SERVICIO', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 60, 'estado' => 'completada', 'tecnico' => 'Tec. Omar Jair Montoya Landin 202504522'],
            ],
            'discrepancias' => [],
            'refacciones' => [],
            'consumibles' => [
                ['item' => 'C1', 'solicitante_fecha' => '2026-01-15', 'nombre' => 'Oxigeno', 'descripcion' => 'Recarga de botella de oxigeno', 'cantidad' => '2000 PSI', 'numero_parte' => '-', 'status' => 'ENTREGADO', 'certificado_conformidad' => '-', 'area_procedencia' => 'ALMACEN HANGAR'],
            ],
            'herramientas' => [],
            'ndt' => [],
            'talleres_externos' => [],
            'mediciones' => [],
        ]);
    }

    // CESA-TREN25-007
    private function seedTren25007Example(User $user): void
    {
        $area = Area::where('codigo', 'TREN')->firstOrFail();
        $tipo = TipoOrden::where('codigo', 'TREN')->firstOrFail();

        $orden = Orden::updateOrCreate(
            ['folio' => 'CESA-TREN25-007'],
            [
                'area_id' => $area->id,
                'tipo_id' => $tipo->id,
                'user_id' => $user->id,
                'consecutivo' => 7,
                'anio' => 2025,
                'fecha' => '2025-03-03',
                'cliente' => 'CESA',
                'matricula' => 'LV-FVT',
                'aeronave_modelo' => 'CESSNA 650',
                'aeronave_serie' => '650-0004',
                'descripcion' => 'INSPECCION Y ARMADO DE WHEEL ASSY',
                'trabajo_descripcion' => 'INSPECCION Y ARMADO DE WHEEL ASSY',
                'componente_descripcion' => 'WHEEEL ASSY',
                'componente_modelo' => 'GOODRICH',
                'componente_numero_parte' => '9914136-8',
                'tipo_tarea' => 'INSPECCION Y ARMADO',
                'intervalo' => null,
                'accion_correctiva' => 'Desarmado, despintado, inspeccion NDT, preparacion para pintura y armado parcial del componente.',
                'tecnico_responsable' => 'Hilario Gutierrez Hernandez',
                'inspector' => 'Pendiente',
                'fecha_inicio' => '2025-03-07',
                'fecha_termino' => '2025-03-25',
                'estado' => 'cerrada',
            ]
        );

        $this->syncOrderDetails($orden, $area, [
            'tareas' => [
                ['titulo' => 'Desarmado e inicio de despintado', 'descripcion' => '07-03-25. Se realiza desarmado y se inicia despintado de componentes.', 'orden' => 1, 'tipo' => 'DESENSAMBLE', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 480, 'estado' => 'completada', 'tecnico' => 'Hilario Gutierrez Hernandez'],
                ['titulo' => 'Conclusion de despintado', 'descripcion' => '18-03-25. Se concluye despintado y detallado de pintura.', 'orden' => 2, 'tipo' => 'PINTURA', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 480, 'estado' => 'completada', 'tecnico' => 'Hilario Gutierrez Hernandez'],
                ['titulo' => 'Envio a inspeccion NDI', 'descripcion' => '19-03-25. Se envia a inspeccion por NDI (liquidos penetrantes).', 'orden' => 3, 'tipo' => 'NDT', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 30, 'estado' => 'completada', 'tecnico' => 'Hilario Gutierrez Hernandez'],
                ['titulo' => 'Requisiciones de material', 'descripcion' => '20-03-25. Se efectuan requisiciones de material.', 'orden' => 4, 'tipo' => 'ABASTO', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 30, 'estado' => 'completada', 'tecnico' => 'Hilario Gutierrez Hernandez'],
                ['titulo' => 'Preparacion para pintura', 'descripcion' => '21-03-25. Se limpia y prepara para pintura.', 'orden' => 5, 'tipo' => 'PREPARACION', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 120, 'estado' => 'completada', 'tecnico' => 'Hilario Gutierrez Hernandez'],
                ['titulo' => 'Armado parcial de componente', 'descripcion' => '25-03-25. Se realiza armado parcial de componente.', 'orden' => 6, 'tipo' => 'ARMADO', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 180, 'estado' => 'completada', 'tecnico' => 'Hilario Gutierrez Hernandez'],
            ],
            'discrepancias' => [
                ['item' => '01', 'descripcion' => 'Se realiza inspeccion a masas por NDT.', 'accion_correctiva' => 'Se reemplaza masa por presentar fisura.', 'status' => 'cerrada', 'fecha_inicio' => '2025-03-19', 'fecha_termino' => '2025-03-19', 'componente_numero_parte_off' => '300-549-1', 'componente_numero_serie_off' => '2142', 'componente_numero_parte_on' => '300-549-1', 'componente_numero_serie_on' => '1735'],
            ],
            'refacciones' => [
                ['item' => 'R1', 'solicitante_fecha' => '2025-03-03', 'nombre' => 'ITEM 10A. TIRE MAIN LANDING GEAR', 'descripcion' => 'REEMPLAZO', 'cantidad' => 1, 'numero_parte' => '226K08-4', 'status' => 'PENDIENTE COTIZACION', 'area_procedencia' => 'COMPRAS'],
                ['item' => 'R2', 'solicitante_fecha' => '2025-03-03', 'nombre' => 'ITEM. 65. PACKING PREFORMED', 'descripcion' => 'REEMPLAZO', 'cantidad' => 1, 'numero_parte' => '68-559', 'status' => 'PENDIENTE COTIZACION', 'area_procedencia' => 'COMPRAS'],
                ['item' => 'R3', 'solicitante_fecha' => '2025-03-03', 'nombre' => 'ITEM 45 GROMMET', 'descripcion' => 'REEMPLAZO', 'cantidad' => 1, 'numero_parte' => 'TRRG30', 'status' => 'PENDIENTE COTIZACION', 'area_procedencia' => 'COMPRAS'],
                ['item' => 'R4', 'solicitante_fecha' => '2025-03-03', 'nombre' => 'ITEM 5. SEAL ASSEMBLY OUTER', 'descripcion' => 'REEMPLAZO', 'cantidad' => 1, 'numero_parte' => '68-1055', 'status' => 'PENDIENTE COTIZACION', 'area_procedencia' => 'COMPRAS'],
                ['item' => 'R5', 'solicitante_fecha' => '2025-03-03', 'nombre' => 'ITEM 10, CONE BEARING', 'descripcion' => 'REEMPLAZO', 'cantidad' => 1, 'numero_parte' => 'LL205449', 'status' => 'RESGUARDO EN ALMACEN', 'area_procedencia' => 'COMPRAS'],
                ['item' => 'R6', 'solicitante_fecha' => '2025-03-03', 'nombre' => 'ITEM 15, SEAL ASSEMBLY INNER', 'descripcion' => 'REEMPLAZO', 'cantidad' => 1, 'numero_parte' => '68-1054', 'status' => 'PENDIENTE COTIZACION', 'area_procedencia' => 'COMPRAS'],
                ['item' => 'R7', 'solicitante_fecha' => '2025-03-03', 'nombre' => 'ITEM 20, CONE, BEARING', 'descripcion' => 'REEMPLAZO', 'cantidad' => 1, 'numero_parte' => 'LM806649', 'status' => 'PENDIENTE COTIZACION', 'area_procedencia' => 'COMPRAS'],
                ['item' => 'R8', 'solicitante_fecha' => '2025-03-03', 'nombre' => 'ITEM 30, CAP, VALVE', 'descripcion' => 'REEMPLAZO', 'cantidad' => 1, 'numero_parte' => 'TRVC5', 'status' => 'ENTREGADO', 'area_procedencia' => 'COMPRAS'],
                ['item' => 'R9', 'solicitante_fecha' => '2025-03-03', 'nombre' => 'ITEM 35, VALVE, INSIDE', 'descripcion' => 'REEMPLAZO', 'cantidad' => 1, 'numero_parte' => 'TRC4', 'status' => 'ENTREGADO', 'area_procedencia' => 'COMPRAS'],
                ['item' => 'R10', 'solicitante_fecha' => '2025-03-03', 'nombre' => 'ITEM 2, SCREW', 'descripcion' => 'REEMPLAZO', 'cantidad' => 2, 'numero_parte' => 'AN503-8-12', 'status' => 'PENDIENTE COTIZACION', 'area_procedencia' => 'COMPRAS'],
                ['item' => 'R11', 'solicitante_fecha' => '2025-03-03', 'nombre' => 'ITEM 3, WASHER', 'descripcion' => 'REEMPLAZO', 'cantidad' => 1, 'numero_parte' => '6241108-4', 'status' => 'PENDIENTE COTIZACION', 'area_procedencia' => 'COMPRAS'],
                ['item' => 'R12', 'solicitante_fecha' => '2025-03-03', 'nombre' => 'ITEM 4, NUT', 'descripcion' => 'REEMPLAZO', 'cantidad' => 1, 'numero_parte' => 'MS21025-32', 'status' => 'PENDIENTE COTIZACION', 'area_procedencia' => 'COMPRAS'],
                ['item' => 'R13', 'solicitante_fecha' => '2025-03-06', 'nombre' => 'HUB CAP ASSEMBLY', 'descripcion' => 'INSTALACION', 'cantidad' => 1, 'numero_parte' => '9914078-7', 'status' => 'PENDIENTE COTIZACION', 'area_procedencia' => 'COMPRAS'],
                ['item' => 'R14', 'solicitante_fecha' => '2025-03-06', 'nombre' => 'SCREW', 'descripcion' => 'INSTALACION', 'cantidad' => 3, 'numero_parte' => 'AN502-10-8', 'status' => 'PENDIENTE COTIZACION', 'area_procedencia' => 'COMPRAS'],
                ['item' => 'R15', 'solicitante_fecha' => '2025-03-06', 'nombre' => 'WASHER', 'descripcion' => 'INSTALACION', 'cantidad' => 3, 'numero_parte' => 'NAS1149F0332P', 'status' => 'RESGUARDO EN ALMACEN', 'area_procedencia' => 'COMPRAS'],
            ],
            'consumibles' => [
                ['item' => 'C1', 'solicitante_fecha' => '2025-03-06', 'nombre' => 'ALCOHOL ISOPROPILICO', 'descripcion' => 'USO GENERAL', 'cantidad' => '2 LTS', 'status' => '1 LITRO MINISTRADO', 'area_procedencia' => 'ALMACEN GENERAL'],
                ['item' => 'C2', 'solicitante_fecha' => '2025-03-06', 'nombre' => 'REMOVEDOR DE PINTURA', 'descripcion' => 'USO GENERAL', 'cantidad' => '1 LT', 'status' => 'MINISTRADO', 'area_procedencia' => 'ALMACEN GENERAL'],
                ['item' => 'C3', 'solicitante_fecha' => '2025-03-06', 'nombre' => 'EPOXY PRIMER TYPE 1 CLASS C OR N', 'descripcion' => 'USO GENERAL', 'cantidad' => '1 LT', 'numero_parte' => 'MIL-P-23377', 'status' => 'MINISTRADO', 'area_procedencia' => 'ALMACEN GENERAL'],
                ['item' => 'C4', 'solicitante_fecha' => '2025-03-06', 'nombre' => 'LINEAR POLYURETHANE TOPCOAT', 'descripcion' => 'USO GENERAL', 'cantidad' => '1 LT', 'numero_parte' => 'ALUMINUM COLOR G1053', 'status' => 'MINISTRADO', 'area_procedencia' => 'ALMACEN GENERAL'],
                ['item' => 'C5', 'solicitante_fecha' => '2025-03-06', 'nombre' => 'STANDARD TOPCOAT CONVERTER', 'descripcion' => 'USO GENERAL', 'cantidad' => '250 ML', 'numero_parte' => 'AWL-CAT #2 G3010', 'status' => 'MINISTRADO', 'area_procedencia' => 'ALMACEN GENERAL'],
                ['item' => 'C6', 'solicitante_fecha' => '2025-03-06', 'nombre' => 'URETHANE TOPCOAT REDUCER', 'descripcion' => 'USO GENERAL', 'cantidad' => '250 ML', 'numero_parte' => 'T0003', 'status' => 'MINISTRADO', 'area_procedencia' => 'ALMACEN GENERAL'],
                ['item' => 'C7', 'solicitante_fecha' => '2025-03-06', 'nombre' => 'TRAPO LIMPIO', 'descripcion' => 'USO GENERAL', 'cantidad' => '2 KG', 'numero_parte' => 'N/A', 'status' => '1 KILO MINISTRADO', 'area_procedencia' => 'ALMACEN GENERAL'],
                ['item' => 'C8', 'solicitante_fecha' => '2025-03-06', 'nombre' => 'THINNER STANDARD', 'descripcion' => 'USO GENERAL', 'cantidad' => '2 LTS', 'status' => '1 LITRO MINISTRADO', 'area_procedencia' => 'ALMACEN GENERAL'],
                ['item' => 'C9', 'solicitante_fecha' => '2025-03-06', 'nombre' => 'NITROGENO', 'descripcion' => 'USO GENERAL', 'cantidad' => '200 LBS', 'status' => 'PENDIENTE AUTORIZACION'],
                ['item' => 'C10', 'solicitante_fecha' => '2025-03-06', 'nombre' => 'GUANTES DE NITRILO M', 'descripcion' => 'USO GENERAL', 'cantidad' => '10 PARES', 'status' => '5 PARES MINISTRADO', 'area_procedencia' => 'ALMACEN GENERAL'],
                ['item' => 'C11', 'solicitante_fecha' => '2025-03-06', 'nombre' => 'FIBRAS SCOTCH BRITE', 'descripcion' => 'USO GENERAL', 'cantidad' => '2', 'numero_parte' => '3M', 'status' => '1 PIEZA MINISTRADO', 'area_procedencia' => 'ALMACEN GENERAL'],
            ],
            'herramientas' => [],
            'ndt' => [
                ['item' => '1.0', 'tipo_prueba' => 'LIQUIDOS PENETRANTES', 'cantidad' => 1, 'sub_componente' => 'WHEEL HALF ASSEMBLY OUTER', 'numero_parte' => '300-549-1', 'numero_serie' => '2142', 'resultado' => 'COMPROBACION COMPONENTE', 'certificado' => 'No cuenta con certificado'],
            ],
            'talleres_externos' => [
                ['item' => '1.0', 'tarea' => 'PINTURA', 'cantidad' => 1, 'sub_componente' => 'WHEEL HALF ASSEMBLY INNER', 'numero_parte' => '300-550-6', 'numero_serie' => '1946', 'trabajo_realizado' => 'Aplicacion de primer base y pintura poliuretano color aluminio', 'proveedor' => 'TALLER DE PINTURA'],
                ['item' => '2.0', 'tarea' => 'PINTURA', 'cantidad' => 1, 'sub_componente' => 'WHEEL HALF ASSEMBLY OUTER', 'numero_parte' => '300-549-1', 'numero_serie' => '1735', 'trabajo_realizado' => 'Aplicacion de primer base y pintura poliuretano color aluminio', 'proveedor' => 'TALLER DE PINTURA'],
            ],
            'mediciones' => [],
        ]);
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


    // CESA-HANG25-097
    private function seedHangarMotorExample(User $user): void
    {
        $area = Area::where('codigo', 'HANG')->firstOrFail();
        $tipo = TipoOrden::where('codigo', 'HANG')->firstOrFail();
        $motor = Motor::where('numero_serie', 'P-73548')->first();

        $orden = Orden::updateOrCreate(
            ['folio' => 'CESA-HANG25-097'],
            [
                'area_id' => $area->id,
                'tipo_id' => $tipo->id,
                'user_id' => $user->id,
                'motor_id' => $motor?->id,
                'consecutivo' => 97,
                'anio' => 2025,
                'fecha' => '2025-07-21',
                'cliente' => '-',
                'matricula' => 'XA-MMN',
                'aeronave_modelo' => 'LEARJET 35A',
                'aeronave_serie' => '221',
                'descripcion' => 'Remocion e instalacion del motor #2',
                'trabajo_descripcion' => 'Remocion e instalacion del motor #2.',
                'componente_descripcion' => 'Motor',
                'componente_modelo' => 'TFE731-2',
                'componente_numero_serie' => 'P-73548',
                'tipo_tarea' => 'Remocion / Instalacion',
                'intervalo' => null,
                'accion_correctiva' => 'Se realizo remocion, configuracion e instalacion del motor #2, atendiendo discrepancias detectadas hasta dejar el sistema operativo.',
                'tecnico_responsable' => 'Tec. Juan Martin Carrillo Trejo',
                'inspector' => 'Tec. Juan Martin Carrillo Trejo',
                'fecha_inicio' => '2025-07-21',
                'fecha_termino' => '2025-08-02',
                'estado' => 'cerrada',
            ]
        );

        $this->syncOrderDetails($orden, $area, [
            'tareas' => [
                ['titulo' => 'Ingreso y desmontaje inicial', 'descripcion' => 'Fecha: 21/07/25. Ingreso de aeronave 12:10, remocion de cowling, air intake, aft body y desmontaje del motor.', 'orden' => 1, 'tipo' => 'REMOCION', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 1140, 'estado' => 'completada', 'tecnico' => 'Tec. Juan Martin Carrillo Trejo'],
                ['titulo' => 'Configuracion de accesorios', 'descripcion' => 'Fecha: 22/07/25. Se remueven accesorios para configuracion en nuevo motor.', 'orden' => 2, 'tipo' => 'CONFIGURACION', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 1500, 'estado' => 'completada', 'tecnico' => 'Tec. Juan Martin Carrillo Trejo'],
                ['titulo' => 'Cambio de gearbox y remocion adicional', 'descripcion' => 'Fecha: 23/07/25. Se termina de remover accesorios y se continua con componentes asociados al cambio de gearbox.', 'orden' => 3, 'tipo' => 'CONFIGURACION', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 1080, 'estado' => 'completada', 'tecnico' => 'Tec. Juan Martin Carrillo Trejo'],
                ['titulo' => 'Limpieza exterior del motor', 'descripcion' => 'Fecha: 24/07/25. Identificacion de componentes para personal de Excel y limpieza exterior para preservacion.', 'orden' => 4, 'tipo' => 'LIMPIEZA', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 990, 'estado' => 'completada', 'tecnico' => 'Tec. Omar Jair Montoya Landin'],
                ['titulo' => 'Proteccion de arnes', 'descripcion' => 'Fecha: 25/07/25. Limpieza de motor y mantenimiento del arnes con cinta de fibra de vidrio.', 'orden' => 5, 'tipo' => 'MANTENIMIENTO', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 720, 'estado' => 'completada', 'tecnico' => 'Tec. Luis Manuel Huertas Garrido'],
                ['titulo' => 'Conclusion de configuracion', 'descripcion' => 'Fecha: 30/07/25. Se concluye la configuracion del motor por personal de Excel con apoyo de mantenimiento.', 'orden' => 6, 'tipo' => 'CONFIGURACION', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 540, 'estado' => 'completada', 'tecnico' => 'Tec. Carlos Rodolfo Gonzales Rojas 202501321'],
                ['titulo' => 'Instalacion de forward mount', 'descripcion' => 'Fecha: 31/07/25. Instalacion de forward mount en aeronave y after mount en motor.', 'orden' => 7, 'tipo' => 'INSTALACION', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 540, 'estado' => 'completada', 'tecnico' => 'Tec. Carlos Rodolfo Gonzales Rojas 202501321'],
                ['titulo' => 'Instalacion de montante aft', 'descripcion' => 'Fecha: 31/07/25. Se realiza instalacion de montante aft.', 'orden' => 8, 'tipo' => 'INSTALACION', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 120, 'estado' => 'completada', 'tecnico' => 'Tec. Luis Manuel Huertas Garrido'],
                ['titulo' => 'Recarga de aceite', 'descripcion' => 'Fecha: 31/07/25. Se recarga aceite.', 'orden' => 9, 'tipo' => 'SERVICIO', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 15, 'estado' => 'completada', 'tecnico' => 'Tec. Luis Manuel Huertas Garrido'],
                ['titulo' => 'Montaje del motor en aeronave', 'descripcion' => 'Fecha: 31/07/25. Se monta el motor, se alinean pernos de montantes y se inicia conexion de arnes y lineas.', 'orden' => 10, 'tipo' => 'INSTALACION', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 525, 'estado' => 'completada', 'tecnico' => 'Tec. Juan Martin Carrillo Trejo'],
                ['titulo' => 'Conexion y corrida inicial', 'descripcion' => 'Fecha: 31/07/25. Conexion de accesorios, armado final y arranque sin encender para circular aceite y combustible.', 'orden' => 11, 'tipo' => 'PRUEBA', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 1050, 'estado' => 'completada', 'tecnico' => 'Tec. Juan Martin Carrillo Trejo'],
                ['titulo' => 'Diagnostico de falla ITT', 'descripcion' => 'Fecha: 31/07/25. Se detecta falla en indicacion ITT y se identifica terminal chromel en mal estado.', 'orden' => 12, 'tipo' => 'DIAGNOSTICO', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 120, 'estado' => 'completada', 'tecnico' => 'Tec. Omar Jair Montoya Landin'],
                ['titulo' => 'Reemplazo de terminal y aislamiento', 'descripcion' => 'Fecha: 02/08/25. Reemplazo de terminal y aislamiento de placa de terminal.', 'orden' => 13, 'tipo' => 'REPARACION', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 120, 'estado' => 'completada', 'tecnico' => 'Tec. Omar Jair Montoya Landin'],
                ['titulo' => 'Intercambio de valvula shutoff', 'descripcion' => 'Fecha: 02/08/25. Valvula shutoff intercambiada por una brindada por cliente.', 'orden' => 14, 'tipo' => 'REEMPLAZO', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 90, 'estado' => 'completada', 'tecnico' => 'Tec. Carlos Rodolfo Gonzales Rojas 202501321'],
                ['titulo' => 'Reemplazo de conector flow control', 'descripcion' => 'Fecha: 02/08/25. Se toma cannon de XA-VEE y se realiza la conexion.', 'orden' => 15, 'tipo' => 'REEMPLAZO', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 120, 'estado' => 'completada', 'tecnico' => 'Tec. Jesus Adrian Monroy Blanco'],
                ['titulo' => 'Cambio de generador', 'descripcion' => 'Fecha: 02/08/25. Se retira generador prestado de XA-VEE y se instala uno entregado por cliente.', 'orden' => 16, 'tipo' => 'REEMPLAZO', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 360, 'estado' => 'completada', 'tecnico' => 'Tec. Jesus Adrian Monroy Blanco'],
                ['titulo' => 'Analisis de oscilacion de fuel flow', 'descripcion' => 'Fecha: 02/08/25. Se detecta oscilacion en indicacion de flujo de combustible.', 'orden' => 17, 'tipo' => 'DIAGNOSTICO', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 60, 'estado' => 'completada', 'tecnico' => 'Tec. Omar Jair Montoya Landin'],
            ],
            'discrepancias' => [
                ['item' => '01', 'descripcion' => 'Al remover componentes se detectan ambas bujias en mal estado.', 'accion_correctiva' => 'Se notifica al cliente; entrega una bujia el 29/07/25 y se instala junto con la mejor bujia disponible del motor removido.', 'status' => 'cerrada', 'inspector' => 'Tec. Juan Martin Carrillo Trejo', 'fecha_inicio' => '2025-07-22', 'fecha_termino' => '2025-07-30', 'horas_hombre' => 0.33, 'componente_numero_parte_off' => '3074541-4', 'componente_numero_serie_off' => 'UNK', 'componente_numero_parte_on' => '3074541-4', 'componente_numero_serie_on' => 'CH3162', 'observaciones' => 'PLUG IGNITER proporcionado por cliente.'],
                ['item' => '02', 'descripcion' => 'Al momento de remover bleed air valve se encuentran faltantes gasket y seal C.', 'accion_correctiva' => 'Se solicitan refacciones y se instala la valvula con empaques nuevos, dejando el sistema operativo.', 'status' => 'cerrada', 'inspector' => 'Tec. Juan Martin Carrillo Trejo', 'fecha_inicio' => '2025-07-25', 'fecha_termino' => '2025-07-29', 'horas_hombre' => 2.00],
                ['item' => '03', 'descripcion' => 'Para la instalacion del arnes electrico fue necesaria la aplicacion de cinta protectora de fibra de vidrio.', 'accion_correctiva' => 'Se termina de enrutar el arnes electrico con cinta de fibra de vidrio.', 'status' => 'abierta', 'inspector' => 'Tec. Juan Martin Carrillo Trejo', 'fecha_inicio' => '2025-07-26', 'horas_hombre' => 2.00, 'observaciones' => 'Tecnico: Tec. Omar Jair Montoya Landin.'],
                ['item' => '04', 'descripcion' => 'Se indica remocion de FWD MOUNT y AFT MOUNT para inspeccion NDT por taller externo.', 'accion_correctiva' => 'Se envian a inspeccion NDT y se reinstalan despues de recibir componentes.', 'status' => 'cerrada', 'inspector' => 'Tec. Juan Martin Carrillo Trejo', 'fecha_inicio' => '2025-07-29', 'fecha_termino' => '2025-07-31', 'horas_hombre' => 5.00, 'observaciones' => 'Tecnico: Tec. Jose Alberto Flores Alcantara.'],
                ['item' => '05', 'descripcion' => 'Para instalacion de tanque de aceite se requiere reemplazo de o-ring en tubo.', 'accion_correctiva' => 'Se obtienen o-rings M83248-1-019 y se realiza la instalacion por personal de Excel.', 'status' => 'cerrada', 'inspector' => 'Tec. Juan Martin Carrillo Trejo', 'fecha_inicio' => '2025-07-29', 'fecha_termino' => '2025-07-29', 'horas_hombre' => 0.50, 'observaciones' => 'Tecnico: Personal de Excel.'],
                ['item' => '06', 'descripcion' => 'Para realizar instalacion de FWD MOUNT se requiere aplicacion de sellante.', 'accion_correctiva' => 'Se genera requisicion; al no contar con el sellante inicial se aplica PR1422 A 1/2.', 'status' => 'cerrada', 'inspector' => 'Tec. Juan Martin Carrillo Trejo', 'fecha_inicio' => '2025-07-30', 'fecha_termino' => '2025-07-31', 'horas_hombre' => 1.50],
                ['item' => '07', 'descripcion' => 'Se requiere reemplazo de filtro de combustible.', 'accion_correctiva' => 'Se reemplaza por filtro brindado por almacen hangar.', 'status' => 'cerrada', 'inspector' => 'Tec. Juan Martin Carrillo Trejo', 'fecha_inicio' => '2025-07-30', 'fecha_termino' => '2025-07-30', 'horas_hombre' => 1.50, 'componente_numero_parte_off' => '897513-1 (038493-12)', 'componente_numero_serie_off' => 'UNK', 'componente_numero_parte_on' => '897513-1 (038493-12)', 'componente_numero_serie_on' => 'UNK', 'observaciones' => 'FILTER ELEMENT brindado por almacen hangar.'],
                ['item' => '08', 'descripcion' => 'Para instalacion de bomba hidraulica fue necesario reemplazo de gasket por roturas.', 'accion_correctiva' => 'Se instala nuevo gasket AN4044-1 brindado por almacen hangar.', 'status' => 'cerrada', 'inspector' => 'Tec. Juan Martin Carrillo Trejo', 'fecha_inicio' => '2025-07-30', 'fecha_termino' => '2025-07-30', 'horas_hombre' => 0.50, 'componente_numero_parte_off' => '-', 'componente_numero_serie_off' => '-', 'componente_numero_parte_on' => 'AN4044-1', 'componente_numero_serie_on' => 'NA', 'observaciones' => 'Gasket obtenido de almacen general.'],
                ['item' => '09', 'descripcion' => 'Arreglo de conexion terminal ITT Chromel.', 'accion_correctiva' => 'Se realiza aislamiento de placa de conexion y se reemplaza terminal Chromel en mal estado.', 'status' => 'cerrada', 'inspector' => 'Tec. Juan Martin Carrillo Trejo', 'fecha_inicio' => '2025-07-31', 'fecha_termino' => '2025-08-01', 'horas_hombre' => 2.00, 'componente_numero_parte_off' => '54368-2', 'componente_numero_serie_off' => 'NA', 'componente_numero_parte_on' => '54368-2', 'componente_numero_serie_on' => 'NA', 'observaciones' => 'Terminal tomada de motor removido XA-MMN.'],
                ['item' => '10', 'descripcion' => 'Se detecta oscilacion en indicacion de fuel flow y se requiere reemplazo de switch.', 'accion_correctiva' => 'Se reemplaza el switch obtenido de almacen general.', 'status' => 'cerrada', 'inspector' => 'Tec. Juan Martin Carrillo Trejo', 'fecha_inicio' => '2025-08-01', 'fecha_termino' => '2025-08-01', 'horas_hombre' => 1.00, 'componente_numero_parte_off' => '6600097-4', 'componente_numero_serie_off' => '2380', 'componente_numero_parte_on' => '6600097-4', 'componente_numero_serie_on' => '794', 'observaciones' => 'FLOWMETER obtenido de almacen general.'],
                ['item' => '11', 'descripcion' => 'Durante pruebas de motor el generador RH prestado no entra en linea.', 'accion_correctiva' => 'Cliente entrega generador, se realizan pruebas y el sistema queda operativo.', 'status' => 'cerrada', 'inspector' => 'Tec. Juan Martin Carrillo Trejo', 'fecha_inicio' => '2025-08-01', 'fecha_termino' => '2025-08-01', 'horas_hombre' => 5.00, 'componente_numero_parte_off' => '30B107-19-A (6608201-9)', 'componente_numero_serie_off' => '1592', 'componente_numero_parte_on' => '30B107-19-A (6608201-9)', 'componente_numero_serie_on' => '2187', 'observaciones' => 'Generador entregado por cliente.'],
                ['item' => '12', 'descripcion' => 'Durante corrida de motor 02 se detecta que la valvula shut off bleed air no se mantiene cerrada y genera humo en cabina.', 'accion_correctiva' => 'Se reemplaza valvula proporcionada por cliente y se realiza corrida confirmando operacion correcta.', 'status' => 'cerrada', 'inspector' => 'Tec. Omar Jair Montoya Landin', 'fecha_inicio' => '2025-08-02', 'fecha_termino' => '2025-08-02', 'horas_hombre' => 1.50, 'componente_numero_parte_off' => '6600201-1', 'componente_numero_serie_off' => '2145', 'componente_numero_parte_on' => '6600201-1', 'componente_numero_serie_on' => '622', 'observaciones' => 'Valvula brindada por cliente.'],
                ['item' => '13', 'descripcion' => 'Durante instalacion de valvula shut off se observa cable de cannon abierto de valvula flow.', 'accion_correctiva' => 'Se toma cannon de XA-VEE y se realiza conexion quedando operativa la valvula.', 'status' => 'cerrada', 'inspector' => 'Insp. Ruben Damian Rodela 201325866', 'fecha_inicio' => '2025-08-02', 'fecha_termino' => '2025-08-02', 'horas_hombre' => 2.00, 'componente_numero_parte_off' => 'PT06E-8-4S', 'componente_numero_serie_off' => 'UNK', 'componente_numero_parte_on' => 'PT06E-8-4S', 'componente_numero_serie_on' => 'UNK', 'observaciones' => 'Cannon flow control tomado de XA-VEE.'],
            ],
            'refacciones' => [
                ['item' => 'R1', 'solicitante_fecha' => '2025-07-22', 'nombre' => 'PLUG IGNITOR', 'descripcion' => 'CAMBIO POR CONDICION', 'cantidad' => 2, 'numero_parte' => '3074541-4', 'status' => 'ESPERA / ENTREGADO 01', 'certificado_conformidad' => '-', 'area_procedencia' => 'CLIENTE', 'recibe_fecha' => '2025-07-30'],
                ['item' => 'R2', 'solicitante_fecha' => '2025-07-25', 'nombre' => 'GASKET', 'descripcion' => 'INSTALACION DE VALVULA BLEED AIR NACELLE', 'cantidad' => 1, 'numero_parte' => '67186', 'status' => 'ENTREGADO', 'certificado_conformidad' => '-', 'area_procedencia' => 'ALMACEN EXCEL', 'recibe_fecha' => '2025-07-29'],
                ['item' => 'R3', 'solicitante_fecha' => '2025-07-25', 'nombre' => 'SEAL C', 'descripcion' => 'INSTALACION DE VALVULA BLEED AIR NACELLE', 'cantidad' => 1, 'numero_parte' => '612A51-0038-2', 'status' => 'ENTREGADO', 'certificado_conformidad' => '-', 'area_procedencia' => 'ALMACEN GENERAL', 'recibe_fecha' => '2025-07-25'],
                ['item' => 'R4', 'solicitante_fecha' => '2025-07-25', 'nombre' => 'GASKET', 'descripcion' => 'INSTALACION DE VALVULA BLEED AIR NACELLE', 'cantidad' => 1, 'numero_parte' => '2655118-1', 'status' => 'ENTREGADO', 'certificado_conformidad' => '-', 'area_procedencia' => 'ALMACEN GENERAL', 'recibe_fecha' => '2025-07-25'],
                ['item' => 'R5', 'solicitante_fecha' => '2025-07-25', 'nombre' => 'GASKET', 'descripcion' => 'INSTALACION DE STARTER', 'cantidad' => 1, 'numero_parte' => 'AN4047-1', 'status' => 'SE ENTREGA JUNTO CON ENSAMBLE POR PARTE DE EXCEL', 'certificado_conformidad' => '-', 'area_procedencia' => 'ALMACEN EXCEL', 'recibe_fecha' => '2025-07-28'],
                ['item' => 'R6', 'solicitante_fecha' => '2025-07-25', 'nombre' => 'GASKET', 'descripcion' => 'INSTALACION DE GENERATOR', 'cantidad' => 1, 'numero_parte' => 'AN4047-1', 'status' => 'SE ENTREGA JUNTO CON ENSAMBLE POR PARTE DE EXCEL', 'certificado_conformidad' => '-', 'area_procedencia' => 'ALMACEN EXCEL', 'recibe_fecha' => '2025-07-28'],
                ['item' => 'R7', 'solicitante_fecha' => '2025-07-29', 'nombre' => 'GASKET', 'descripcion' => 'INSTALACION DE BOMBA DE HYD', 'cantidad' => 1, 'numero_parte' => 'AN4044-1', 'status' => 'ENTREGADO', 'certificado_conformidad' => '-', 'area_procedencia' => 'ALMACEN HANGAR', 'recibe_fecha' => '2025-07-30'],
                ['item' => 'R8', 'solicitante_fecha' => '2025-07-29', 'nombre' => 'ORING', 'descripcion' => 'INSTALACION DE TANQUE DE ACEITE', 'cantidad' => 2, 'numero_parte' => 'M83248-1-019', 'status' => 'ENTREGADO', 'certificado_conformidad' => '-', 'area_procedencia' => 'ALMACEN HANGAR', 'recibe_fecha' => '2025-07-29'],
                ['item' => 'R9', 'solicitante_fecha' => '2025-07-30', 'nombre' => 'FILTRO - ELEMENT', 'descripcion' => 'INSTALACION BOMBA Y FCU', 'cantidad' => 1, 'numero_parte' => '897513-1 (038493-12)', 'status' => 'ENTREGADO', 'certificado_conformidad' => '-', 'area_procedencia' => 'ALMACEN HANGAR', 'recibe_fecha' => '2025-07-30'],
                ['item' => 'R10', 'solicitante_fecha' => '2025-08-01', 'nombre' => 'FLOWMETER', 'descripcion' => 'FLUCTUACION INDICACION DE FUEL FLOW', 'cantidad' => 1, 'numero_parte' => '6600097-4', 'status' => 'ENTREGADO', 'certificado_conformidad' => null, 'area_procedencia' => 'ALMACEN GENERAL', 'recibe_fecha' => '2025-08-01'],
                ['item' => 'R11', 'solicitante_fecha' => '2025-08-01', 'nombre' => 'TERMINAL CHROMEL PARA CONEXION DE INDICACION DE ITT', 'descripcion' => 'TERMINAL EN MAL ESTADO', 'cantidad' => 1, 'numero_parte' => '54368-2', 'status' => 'ENTREGADO', 'certificado_conformidad' => '-', 'area_procedencia' => 'TOMADO DE XA-MMN MOTOR REMOVIDO', 'recibe_fecha' => '2025-08-01'],
            ],
            'consumibles' => [
                ['item' => 'C1', 'solicitante_fecha' => '2025-07-23', 'nombre' => 'ACEITE 254', 'descripcion' => 'REMOCION DE MOTOR', 'cantidad' => 10, 'numero_parte' => 'MIL-PRF-23699', 'status' => 'ENTREGADO', 'area_procedencia' => 'ALMACEN GENERAL', 'recibe_fecha' => '2025-07-23'],
                ['item' => 'C2', 'solicitante_fecha' => '2025-07-23', 'nombre' => 'CINTA AISLANTE FIBRA DE VIDRIO 1/2 IN O 1 IN', 'descripcion' => 'ARNES ELECTRICO', 'cantidad' => 1, 'numero_parte' => 'SCOTCH 27 3M', 'status' => 'ENTREGADO', 'area_procedencia' => 'ALMACEN GENERAL', 'recibe_fecha' => '2025-07-28'],
                ['item' => 'C3', 'solicitante_fecha' => '2025-07-30', 'nombre' => 'SELLANTE (TUBO DE 6 ONZAS)', 'descripcion' => 'INSTALACION FWD MOUNT (BANANA)', 'cantidad' => 2, 'numero_parte' => 'DAPCO 2100 / DAPCO 2200', 'status' => 'NO ENTREGADO', 'area_procedencia' => 'COMPRAS'],
                ['item' => 'C4', 'solicitante_fecha' => '2025-07-31', 'nombre' => 'SELLANTE', 'descripcion' => 'INSTALACION FWD MOUNT (BANANA)', 'cantidad' => 200, 'numero_parte' => 'PR1422 A 1/2', 'status' => 'ENTREGADO', 'area_procedencia' => 'ALMACEN HANGAR', 'recibe_fecha' => '2025-07-31'],
            ],
            'ndt' => [
                ['item' => '1', 'tipo_prueba' => 'PARTICULAS MAGNETICAS', 'cantidad' => 1, 'sub_componente' => 'FWD MOUNT', 'numero_parte' => '2651013', 'numero_serie' => '5981', 'envio_a' => 'NAPSA', 'recepcion' => '03/02/2026 MGMC', 'resultado' => 'Recepcion MGMC'],
                ['item' => '2', 'tipo_prueba' => 'PARTICULAS MAGNETICAS', 'cantidad' => 1, 'sub_componente' => 'BOLT FWD MOUNT UPPER (TO BEAM)', 'numero_parte' => '2651026-3 / 70315-10', 'numero_serie' => 'UNK', 'envio_a' => 'NAPSA', 'recepcion' => '03/02/2026 MGMC', 'resultado' => 'Recepcion MGMC'],
                ['item' => '3', 'tipo_prueba' => 'PARTICULAS MAGNETICAS', 'cantidad' => 1, 'sub_componente' => 'BOLT FWD MOUNT LOWER (TO BEAM)', 'numero_parte' => '2651026-3 / 70315-10', 'numero_serie' => 'UNK', 'envio_a' => 'NAPSA', 'recepcion' => '03/02/2026 MGMC', 'resultado' => 'Recepcion MGMC'],
                ['item' => '4', 'tipo_prueba' => 'LIQUIDOS PENETRANTES', 'cantidad' => 1, 'sub_componente' => 'FWD MOUNT SHOCK UPPER', 'numero_parte' => 'LM-833-3', 'numero_serie' => '0-105'],
                ['item' => '5', 'tipo_prueba' => 'LIQUIDOS PENETRANTES', 'cantidad' => 1, 'sub_componente' => 'FWD MOUNT SHOCK LOWER', 'numero_parte' => 'LM-833-3', 'numero_serie' => '0-104'],
                ['item' => '6', 'tipo_prueba' => 'PARTICULAS MAGNETICAS', 'cantidad' => 2, 'sub_componente' => 'BOLT FWD MOUNT SHOCK', 'numero_parte' => 'NAS1307-3H', 'numero_serie' => 'UNK', 'envio_a' => 'NAPSA', 'recepcion' => '03/02/2026 MGMC', 'resultado' => 'Recepcion MGMC'],
                ['item' => '7', 'tipo_prueba' => 'PARTICULAS MAGNETICAS', 'cantidad' => 1, 'sub_componente' => 'AFT MOUNT', 'numero_parte' => '2651031-2', 'numero_serie' => '777', 'envio_a' => 'NAPSA', 'recepcion' => '03/02/2026 MGMC', 'resultado' => 'Recepcion MGMC'],
                ['item' => '8', 'tipo_prueba' => 'PARTICULAS MAGNETICAS', 'cantidad' => 1, 'sub_componente' => 'BOLT AFT MOUNT', 'numero_parte' => '2651027-1', 'numero_serie' => 'UNK'],
            ],
        ]);
    }

    // CESA-TREN25-033
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
            $orden->{$relation}()->create($this->normalizeRelationItem($relation, $item));
        }
    }

    private function normalizeRelationItem(string $relation, array $item): array
    {
        if (! array_key_exists('cantidad', $item) || $item['cantidad'] === null || $item['cantidad'] === '') {
            return $item;
        }

        if (is_int($item['cantidad'])) {
            return $item;
        }

        $rawQuantity = trim((string) $item['cantidad']);

        if ($rawQuantity === '') {
            $item['cantidad'] = null;

            return $item;
        }

        if (preg_match('/-?\d+(?:[.,]\d+)?/', $rawQuantity, $matches) !== 1) {
            $item['cantidad'] = 1;
            $this->appendQuantityNote($relation, $item, $rawQuantity);

            return $item;
        }

        $normalized = (int) floor((float) str_replace(',', '.', $matches[0]));
        $item['cantidad'] = max(1, $normalized);

        if ((string) $item['cantidad'] !== $rawQuantity) {
            $this->appendQuantityNote($relation, $item, $rawQuantity);
        }

        return $item;
    }

    private function appendQuantityNote(string $relation, array &$item, string $rawQuantity): void
    {
        $note = 'Cantidad original: ' . $rawQuantity;

        if (in_array($relation, ['ndt', 'talleresExternos'], true)) {
            $field = $relation === 'ndt' ? 'resultado' : 'observaciones';
            $item[$field] = trim(implode('. ', array_filter([
                $item[$field] ?? null,
                $note,
            ])));

            return;
        }

        $item['descripcion'] = trim(implode('. ', array_filter([
            $item['descripcion'] ?? null,
            $note,
        ])));
    }
}
