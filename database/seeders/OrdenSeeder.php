<?php

namespace Database\Seeders;

use App\Models\Aeronave;
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
use Illuminate\Support\Facades\Cache;

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
        $this->bustListingCaches();
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
            [
                'archivo_path' => 'A:\\RED AVIATION\\OT\\CESA-AVCS26-043 - XA-MMN - Atención a reporte.xlsx',
                'nombre' => 'CESA-AVCS26-043 - XA-MMN - Atención a reporte',
                'tipo_manual' => 'XLSX',
                'idioma' => 'es',
                'estado' => 'vigente',
                'descripcion' => 'Registro documental sembrado para base de datos.',
            ],
            [
                'archivo_path' => 'A:\\RED AVIATION\\OT\\CESA-AVCS26-048 - XA-VGZ - INSPECCION FUEL PUMP.xlsx',
                'nombre' => 'CESA-AVCS26-048 - XA-VGZ - INSPECCION FUEL PUMP',
                'tipo_manual' => 'XLSX',
                'idioma' => 'es',
                'estado' => 'vigente',
                'descripcion' => 'Registro documental sembrado para base de datos.',
            ],
            [
                'archivo_path' => 'A:\\RED AVIATION\\OT\\CESA-BATT26-001 - XA-VEE - TOPPING BATERIA.xlsx',
                'nombre' => 'CESA-BATT26-001 - XA-VEE - TOPPING BATERIA',
                'tipo_manual' => 'XLSX',
                'idioma' => 'es',
                'estado' => 'vigente',
                'descripcion' => 'Registro documental sembrado para base de datos.',
            ],
            [
                'archivo_path' => 'A:\\RED AVIATION\\OT\\CESA-ESTR26-004 - XA-TZA - REPARACION DE FAIRING RH ASSY.xlsx',
                'nombre' => 'CESA-ESTR26-004 - XA-TZA - REPARACION DE FAIRING RH ASSY',
                'tipo_manual' => 'XLSX',
                'idioma' => 'es',
                'estado' => 'vigente',
                'descripcion' => 'Registro documental sembrado para base de datos.',
            ],
            [
                'archivo_path' => 'A:\\RED AVIATION\\OT\\CESA-FREN26-001 - INSPECCION DE CONDICIÓN A BRAKE ASSY (1).xlsx',
                'nombre' => 'CESA-FREN26-001 - INSPECCION DE CONDICIÓN A BRAKE ASSY (1)',
                'tipo_manual' => 'XLSX',
                'idioma' => 'es',
                'estado' => 'vigente',
                'descripcion' => 'Registro documental sembrado para base de datos.',
            ],
            [
                'archivo_path' => 'A:\\RED AVIATION\\OT\\CESA-HANG26-014 - XA-MMN - ATENCIÓN A REPORTE LUZ SPOILER.xlsx',
                'nombre' => 'CESA-HANG26-014 - XA-MMN - ATENCION A REPORTE LUZ SPOILER',
                'tipo_manual' => 'XLSX',
                'idioma' => 'es',
                'estado' => 'vigente',
                'descripcion' => 'Registro documental sembrado para base de datos.',
            ],
            [
                'archivo_path' => 'A:\\RED AVIATION\\OT\\CESA-HELI25-002 - INSPECCIÓN VISUAL A TANQUE AUXILIAR DE COMBUSTIBLE - 94373007.xlsx',
                'nombre' => 'CESA-HELI25-002 - INSPECCION VISUAL A TANQUE AUXILIAR DE COMBUSTIBLE - 94373007',
                'tipo_manual' => 'XLSX',
                'idioma' => 'es',
                'estado' => 'vigente',
                'descripcion' => 'Registro documental sembrado para base de datos.',
            ],
            [
                'archivo_path' => 'A:\\RED AVIATION\\OT\\CESA-PROP25-001 - INSPECCION A PROPELLER LH.xlsx',
                'nombre' => 'CESA-PROP25-001 - INSPECCION A PROPELLER LH',
                'tipo_manual' => 'XLSX',
                'idioma' => 'es',
                'estado' => 'vigente',
                'descripcion' => 'Registro documental sembrado para base de datos.',
            ],
            [
                'archivo_path' => 'A:\\RED AVIATION\\OT\\CESA-SALV26-001 - XA-JUL - INVENTARIO DE COMPONENTES.xlsx',
                'nombre' => 'CESA-SALV26-001 - XA-JUL - INVENTARIO DE COMPONENTES',
                'tipo_manual' => 'XLSX',
                'idioma' => 'es',
                'estado' => 'vigente',
                'descripcion' => 'Registro documental sembrado para base de datos.',
            ],
            [
                'archivo_path' => 'A:\\RED AVIATION\\OT\\CESA-VEST25-001 - 1091 - TAPIZADO DE 2 ASIENTOS Y 2 RESPALDOS DE PILOTO Y COPILOTO.xlsx',
                'nombre' => 'CESA-VEST25-001 - 1091 - TAPIZADO DE 2 ASIENTOS Y 2 RESPALDOS DE PILOTO Y COPILOTO',
                'tipo_manual' => 'XLSX',
                'idioma' => 'es',
                'estado' => 'vigente',
                'descripcion' => 'Registro documental sembrado para base de datos.',
            ],
            [
                'archivo_path' => 'A:\\RED AVIATION\\OT\\GESA-TORN26-006 - BARRENADO DE 6 ORIFICIOS.xlsx',
                'nombre' => 'GESA-TORN26-006 - BARRENADO DE 6 ORIFICIOS',
                'tipo_manual' => 'XLSX',
                'idioma' => 'es',
                'estado' => 'vigente',
                'descripcion' => 'Registro documental sembrado para base de datos.',
            ],
            [
                'archivo_path' => 'A:\\RED AVIATION\\OT\\Nueva carpeta\\CESA-AVCS26-048 - XA-VGZ - INSPECCION FUEL PUMP (1).xlsx',
                'nombre' => 'CESA-AVCS26-048 - XA-VGZ - INSPECCION FUEL PUMP (1)',
                'tipo_manual' => 'XLSX',
                'idioma' => 'es',
                'estado' => 'vigente',
                'descripcion' => 'Registro documental sembrado para base de datos.',
            ],
            [
                'archivo_path' => 'A:\\RED AVIATION\\OT\\Nueva carpeta\\CESA-HANG25-161 - XB-SDS - PRUEBAS HIDROSTATICAS.xlsx',
                'nombre' => 'CESA-HANG25-161 - XB-SDS - PRUEBAS HIDROSTATICAS',
                'tipo_manual' => 'XLSX',
                'idioma' => 'es',
                'estado' => 'vigente',
                'descripcion' => 'Registro documental sembrado para base de datos.',
            ],
            [
                'archivo_path' => 'A:\\RED AVIATION\\OT\\Nueva carpeta\\CESA-HANG25-163 - XB-SDS - PHASE A1 - 300 HOUR INSPECTION_CHECKS.xlsx',
                'nombre' => 'CESA-HANG25-163 - XB-SDS - PHASE A1 - 300 HOUR INSPECTION_CHECKS',
                'tipo_manual' => 'XLSX',
                'idioma' => 'es',
                'estado' => 'vigente',
                'descripcion' => 'Registro documental sembrado para base de datos.',
            ],
            [
                'archivo_path' => 'A:\\RED AVIATION\\OT\\Nueva carpeta\\CESA-HANG26-026 - XB-SDS - INSPECCIÓN Y LIMPIEZA FWR MOUNT CASTING.xlsx',
                'nombre' => 'CESA-HANG26-026 - XB-SDS - INSPECCION Y LIMPIEZA FWR MOUNT CASTING',
                'tipo_manual' => 'XLSX',
                'idioma' => 'es',
                'estado' => 'vigente',
                'descripcion' => 'Registro documental sembrado para base de datos.',
            ],
            [
                'archivo_path' => 'A:\\RED AVIATION\\OT\\Nueva carpeta\\CESA-HANG24-151 - XA-VGZ - ATENCION A REPORTES.xlsx',
                'nombre' => 'CESA-HANG24-151 - XA-VGZ - ATENCION A REPORTES',
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
        $this->seedAvcs26043Example($user);
        $this->seedAdditionalOtExamples($user);
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

    // CESA-AVCS26-043
    private function seedAvcs26043Example(User $user): void
    {
        $area = Area::where('codigo', 'AVCS')->firstOrFail();
        $tipo = TipoOrden::where('codigo', 'AVCS')->firstOrFail();

        $orden = Orden::updateOrCreate(
            ['folio' => 'CESA-AVCS26-043'],
            [
                'area_id' => $area->id,
                'tipo_id' => $tipo->id,
                'user_id' => $user->id,
                'consecutivo' => 43,
                'anio' => 2026,
                'fecha' => '2026-02-07',
                'cliente' => 'SKY JETS INTERNATIONAL',
                'matricula' => 'XA-MMN',
                'aeronave_modelo' => 'LEARJET 35',
                'aeronave_serie' => '221',
                'descripcion' => 'ATENCION A REPORTE PILOTO AUTOMATICO',
                'trabajo_descripcion' => 'ATENCION A REPORTE PILOTO AUTOMATICO',
                'componente_descripcion' => 'PILOTO AUTOMATICO',
                'tipo_tarea' => 'ATENCION A REPORTE',
                'intervalo' => 'UNICA VEZ',
                'accion_correctiva' => 'LA FALLA DE LVL SE DETERMINA QUE LA FALLA ESTA EN LA COMPUTADORA DE PILOTO AUTOMATICO, SE REMPLAZA COMPLETAMENTE EL MODULO Y SE QUITA LA FALLA. EL MODULO ESTA COMPUESTO DE DOS TARJETAS LAS CUALES TAMBIEN SE AISLA Y SE DETERMINA QUE LA FALLA ESTA EN LA TARJETA DE CONTROL SE REMPLAZA QUEDANDO OPERATIVO Y LISTO PARA SU USO.',
                'tecnico_responsable' => 'Tec. Jesus Adrian Monroy Blanco',
                'inspector' => 'Insp. Ruben Damian Rodela 201325866',
                'fecha_inicio' => '2026-02-07',
                'fecha_termino' => '2026-02-07',
                'estado' => 'cerrada',
            ]
        );

        $this->syncOrderDetails($orden, $area, [
            'tareas' => [
                [
                    'titulo' => 'Diagnostico y correccion de falla de piloto automatico LVL',
                    'descripcion' => '07-02-26. Se diagnostica falla de piloto automatico LVL, se aisla la falla en la computadora de piloto automatico y se reemplaza la tarjeta de control del modulo para dejar el sistema operativo.',
                    'orden' => 1,
                    'tipo' => 'DIAGNOSTICO',
                    'prioridad' => 'ALTA',
                    'tiempo_estimado_min' => 180,
                    'estado' => 'completada',
                    'tecnico' => 'Tec. Jesus Adrian Monroy Blanco',
                ],
            ],
            'discrepancias' => [
                [
                    'item' => '01',
                    'descripcion' => 'Falla de piloto automatico LVL detectada en la computadora de piloto automatico.',
                    'accion_correctiva' => 'Se reemplaza completamente el modulo y se determina que la falla estaba en la tarjeta de control, quedando operativo y listo para su uso.',
                    'status' => 'cerrada',
                    'inspector' => 'Insp. Ruben Damian Rodela 201325866',
                    'fecha_inicio' => '2026-02-07',
                    'fecha_termino' => '2026-02-07',
                    'horas_hombre' => 3.00,
                    'componente_numero_parte_off' => '502-1078-04',
                    'componente_numero_serie_off' => '1771',
                    'componente_numero_parte_on' => '502-1078-04',
                    'componente_numero_serie_on' => '1779',
                    'observaciones' => 'SE TOMA LA TARJETA DE CONTROL DE ESTE MODULO Y SE REMPLAZA.',
                ],
            ],
            'refacciones' => [],
            'consumibles' => [],
            'herramientas' => [],
            'ndt' => [],
            'talleres_externos' => [],
            'mediciones' => [],
        ]);
    }

    private function seedAdditionalOtExamples(User $user): void
    {
        $examples = [
            [
                'folio' => 'CESA-AVCS26-048',
                'area_codigo' => 'AVCS',
                'anio' => 2026,
                'consecutivo' => 48,
                'fecha' => '2026-02-10',
                'fecha_inicio' => '2026-02-23',
                'fecha_termino' => '2026-02-27',
                'cliente' => 'CESA',
                'matricula' => 'XA-VGZ',
                'aeronave_modelo' => 'HAWKER 400',
                'aeronave_serie' => 'NA774 (25281)',
                'descripcion' => 'INGRESA A INSPECCION FUEL PUMP',
                'trabajo_descripcion' => 'INGRESA A INSPECCION FUEL PUMP',
                'componente_descripcion' => 'FUEL PUMP',
                'componente_modelo' => 'DUKES',
                'componente_numero_parte' => '1500-00-65',
                'componente_numero_serie' => '-',
                'tipo_tarea' => 'INSPECCION',
                'intervalo' => null,
                'accion_correctiva' => 'Se diagnostica falta de potencia, se desarma el equipo, se detectan carbones desgastados y baleros defectuosos, se adaptan carbones, se reemplazan baleros, se repara el cable de alimentacion y se realizan pruebas funcionales satisfactorias.',
                'tecnico_responsable' => 'Tec. Fernando Moreno',
                'inspector' => 'Insp. Eduardo Castaneda Barrera',
                'estado' => 'cerrada',
                'tareas' => [
                    ['titulo' => 'Diagnostico inicial de fuel pump', 'descripcion' => '23-02-26. Se conecta y energiza el equipo para diagnosticar la baja potencia de giro.', 'orden' => 1, 'tipo' => 'DIAGNOSTICO', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 120, 'estado' => 'completada', 'tecnico' => 'Tec. Alfredo Enrique Santillan Perez'],
                    ['titulo' => 'Inspeccion interna del equipo', 'descripcion' => '23-02-26. Se identifican carbones desgastados y baleros superior e inferior en mal estado.', 'orden' => 2, 'tipo' => 'INSPECCION', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 120, 'estado' => 'completada', 'tecnico' => 'Tec. Alfredo Enrique Santillan Perez'],
                    ['titulo' => 'Adaptacion de carbones', 'descripcion' => '25-02-26. Se adaptan carbones compatibles para el equipo.', 'orden' => 3, 'tipo' => 'REPARACION', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 120, 'estado' => 'completada', 'tecnico' => 'Tec. Alfredo Enrique Santillan Perez'],
                    ['titulo' => 'Cambio de baleros y armado del rotor', 'descripcion' => '25-02-26. Se realiza el cambio de baleros y se arma rotor con estator.', 'orden' => 4, 'tipo' => 'ARMADO', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 360, 'estado' => 'completada', 'tecnico' => 'Tec. Alfredo Enrique Santillan Perez'],
                    ['titulo' => 'Armado final y reparacion de cable', 'descripcion' => '26-02-26. Se colocan carbones, se repara cable de alimentacion y se completa el ensamble.', 'orden' => 5, 'tipo' => 'ARMADO', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 480, 'estado' => 'completada', 'tecnico' => 'Tec. Alfredo Enrique Santillan Perez'],
                    ['titulo' => 'Pruebas funcionales', 'descripcion' => '27-02-26. Se energiza el equipo y se valida mejor funcionamiento antes de enviarlo a pruebas.', 'orden' => 6, 'tipo' => 'PRUEBA', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 60, 'estado' => 'completada', 'tecnico' => 'Tec. Alfredo Enrique Santillan Perez'],
                ],
                'discrepancias' => [],
                'refacciones' => [],
                'consumibles' => [],
                'herramientas' => [],
                'ndt' => [],
                'talleres_externos' => [],
                'mediciones' => [],
            ],
            [
                'folio' => 'CESA-BATT26-001',
                'area_codigo' => 'BATT',
                'anio' => 2026,
                'consecutivo' => 1,
                'fecha' => '2026-01-13',
                'fecha_inicio' => '2026-01-13',
                'fecha_termino' => '2026-01-14',
                'cliente' => 'CESA',
                'matricula' => 'XA-VEE',
                'aeronave_modelo' => 'LEARJET 35',
                'aeronave_serie' => '129',
                'descripcion' => 'TOPPING BATERIA',
                'trabajo_descripcion' => 'TOPPING BATERIA',
                'componente_descripcion' => 'BATERIA',
                'componente_modelo' => 'CONCORDE',
                'componente_numero_parte' => 'RG-380E/44',
                'tipo_tarea' => 'TOPPING',
                'intervalo' => null,
                'accion_correctiva' => 'Se verifica el estado de la bateria, se pone a cargar, se deja reposar y se valida la tension final, quedando operativa.',
                'tecnico_responsable' => 'Tec. Jose Luis Reyes Ramirez',
                'inspector' => 'Insp. Eduardo Castaneda Barrera',
                'estado' => 'cerrada',
                'tareas' => [
                    ['titulo' => 'Carga de bateria', 'descripcion' => '13-01-26. Se verifica el estado de la bateria y se pone a cargar.', 'orden' => 1, 'tipo' => 'SERVICIO', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 180, 'estado' => 'completada', 'tecnico' => 'Tec. Jose Luis Reyes Ramirez'],
                    ['titulo' => 'Verificacion de tension final', 'descripcion' => '14-01-26. Se verifica la tension despues del reposo y la bateria queda operativa.', 'orden' => 2, 'tipo' => 'PRUEBA', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 30, 'estado' => 'completada', 'tecnico' => 'Tec. Jose Luis Reyes Ramirez'],
                ],
                'discrepancias' => [],
                'refacciones' => [],
                'consumibles' => [],
                'herramientas' => [],
                'ndt' => [],
                'talleres_externos' => [],
                'mediciones' => [],
            ],
            [
                'folio' => 'CESA-ESTR26-004',
                'area_codigo' => 'ESTR',
                'anio' => 2026,
                'consecutivo' => 4,
                'fecha' => '2026-02-13',
                'fecha_inicio' => '2026-02-13',
                'fecha_termino' => '2026-02-17',
                'cliente' => 'CESA',
                'matricula' => 'XA-TZA',
                'aeronave_modelo' => 'AGUSTA AW109SP',
                'aeronave_serie' => '22380',
                'descripcion' => 'REPARACION DE FAIRING RH ASSY. N/P 109-0329-61-202',
                'trabajo_descripcion' => 'REPARACION DE FAIRING RH ASSY. N/P 109-0329-61-202',
                'componente_descripcion' => 'FAIRING RH',
                'componente_modelo' => '-',
                'componente_numero_parte' => '109-0329-61-202',
                'componente_numero_serie' => '-',
                'tipo_tarea' => 'REPARACION',
                'intervalo' => null,
                'accion_correctiva' => 'Se endereza el dano, se retrabajan defectos, se aplica primer exterior e interior y se pinta interior en negro mate.',
                'tecnico_responsable' => 'Tec. Antonio Ernesto Martinez Ibanez',
                'inspector' => 'Pendiente',
                'estado' => 'cerrada',
                'tareas' => [
                    ['titulo' => 'Remocion de empaque', 'descripcion' => '13-02-26. Se remueve empaque del fairing.', 'orden' => 1, 'tipo' => 'DESENSAMBLE', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 60, 'estado' => 'completada', 'tecnico' => 'Tec. Antonio Ernesto Martinez Ibanez'],
                    ['titulo' => 'Inicio de enderezado', 'descripcion' => '13-02-26. Se inicia el enderezado del dano.', 'orden' => 2, 'tipo' => 'REPARACION', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 120, 'estado' => 'completada', 'tecnico' => 'Tec. Antonio Ernesto Martinez Ibanez'],
                    ['titulo' => 'Solicitud de material', 'descripcion' => '14-02-26. Se solicita y recoge material necesario para la reparacion.', 'orden' => 3, 'tipo' => 'ABASTO', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 120, 'estado' => 'completada', 'tecnico' => 'Tec. Antonio Ernesto Martinez Ibanez'],
                    ['titulo' => 'Fabricacion de apoyo para enderezado', 'descripcion' => '16-02-26. Se solicita al taller de vestiduras la elaboracion de un saco de arena.', 'orden' => 4, 'tipo' => 'SOPORTE', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 150, 'estado' => 'completada', 'tecnico' => 'Tec. Antonio Ernesto Martinez Ibanez'],
                    ['titulo' => 'Continuacion de enderezado', 'descripcion' => '16-02-26. Se continua el enderezado del dano.', 'orden' => 5, 'tipo' => 'REPARACION', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 180, 'estado' => 'completada', 'tecnico' => 'Tec. Antonio Ernesto Martinez Ibanez'],
                    ['titulo' => 'Aplicacion de pasta', 'descripcion' => '16-02-26. Se aplica pasta para relleno de defectos.', 'orden' => 6, 'tipo' => 'ACABADO', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 60, 'estado' => 'completada', 'tecnico' => 'Tec. Antonio Ernesto Martinez Ibanez'],
                    ['titulo' => 'Retrabajo y primer', 'descripcion' => '16-02-26. Se retrabaja la pasta y se aplica primer.', 'orden' => 7, 'tipo' => 'PINTURA', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 240, 'estado' => 'completada', 'tecnico' => 'Tec. Antonio Ernesto Martinez Ibanez'],
                    ['titulo' => 'Correccion final de defectos', 'descripcion' => '17-02-26. Se aplica plaste en defectos restantes y se reaplica primer.', 'orden' => 8, 'tipo' => 'PINTURA', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 180, 'estado' => 'completada', 'tecnico' => 'Tec. Antonio Ernesto Martinez Ibanez'],
                    ['titulo' => 'Acabado interior y reinstalacion', 'descripcion' => '17-02-26. Se aplica negro mate en el interior y se reinstala empaque.', 'orden' => 9, 'tipo' => 'INSTALACION', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 120, 'estado' => 'completada', 'tecnico' => 'Tec. Antonio Ernesto Martinez Ibanez'],
                ],
                'discrepancias' => [],
                'refacciones' => [],
                'consumibles' => [],
                'herramientas' => [],
                'ndt' => [],
                'talleres_externos' => [],
                'mediciones' => [],
            ],
            [
                'folio' => 'CESA-HANG26-014',
                'area_codigo' => 'HANG',
                'anio' => 2026,
                'consecutivo' => 14,
                'fecha' => '2026-01-13',
                'fecha_inicio' => '2026-01-13',
                'fecha_termino' => '2026-01-13',
                'cliente' => 'SKY JETS INTERNATIONAL',
                'matricula' => 'XA-MMN',
                'aeronave_modelo' => 'LEARJET 35',
                'aeronave_serie' => '221',
                'descripcion' => 'ATENCION A REPORTE LUZ SPOILER',
                'trabajo_descripcion' => 'ATENCION A REPORTE LUZ SPOILER. POR REPORTE DE LUZ ENCENDIDA DE SPOILER.',
                'componente_descripcion' => 'SISTEMA DE SPOILERS',
                'tipo_tarea' => 'ATENCION A REPORTE',
                'intervalo' => null,
                'accion_correctiva' => 'Se inspecciona el sistema de spoilers y se encuentra un switch desajustado respecto al lado derecho; se ajusta de acuerdo con el AMM y el sistema queda operativo.',
                'tecnico_responsable' => 'Tec. Omar Jair Montoya Landin 202504522',
                'inspector' => 'Tec. Omar Jair Montoya Landin 202504522',
                'estado' => 'cerrada',
                'tareas' => [
                    ['titulo' => 'Inspeccion inicial del sistema', 'descripcion' => '13-01-26. Se inspecciona el area de spoilers con apoyo de la mula.', 'orden' => 1, 'tipo' => 'INSPECCION', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 60, 'estado' => 'completada', 'tecnico' => 'Tec. Luis Manuel Huertas Garrido'],
                    ['titulo' => 'Ajuste de switch', 'descripcion' => '13-01-26. Se localiza switch con ajuste distinto y se corrige conforme al AMM.', 'orden' => 2, 'tipo' => 'AJUSTE', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 60, 'estado' => 'completada', 'tecnico' => 'Tec. Jose Alberto Flores Alcantara'],
                    ['titulo' => 'Prueba funcional de spoilers', 'descripcion' => '13-01-26. Se realiza test del sistema y queda operando correctamente.', 'orden' => 3, 'tipo' => 'PRUEBA', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 60, 'estado' => 'completada', 'tecnico' => 'Tec. Omar Jair Montoya Landin 202504522'],
                ],
                'discrepancias' => [],
                'refacciones' => [],
                'consumibles' => [],
                'herramientas' => [],
                'ndt' => [],
                'talleres_externos' => [],
                'mediciones' => [],
            ],
            [
                'folio' => 'CESA-HELI25-002',
                'area_codigo' => 'HELI',
                'anio' => 2025,
                'consecutivo' => 2,
                'fecha' => '2025-03-21',
                'fecha_inicio' => '2025-03-21',
                'fecha_termino' => '2025-04-25',
                'cliente' => 'FAM',
                'matricula' => '-',
                'aeronave_modelo' => 'UH-60L',
                'aeronave_serie' => '-',
                'descripcion' => 'INSPECCION Y REPARACION DE TANQUE AUXILIAR DE COMBUSTIBLE',
                'trabajo_descripcion' => 'INSPECCION Y REPARACION DE TANQUE AUXILIAR DE COMBUSTIBLE',
                'componente_descripcion' => 'TANQUE AUXILIAR DE COMBUSTIBLE',
                'componente_modelo' => 'SIKORSKY / UH-60L',
                'componente_numero_parte' => '235SFT001-501',
                'componente_numero_serie' => '94373007',
                'tipo_tarea' => 'INSPECCION Y REPARACION',
                'intervalo' => null,
                'accion_correctiva' => 'Se efectua inspeccion visual y pruebas operacionales, incluida verificacion por delaminacion en areas externas, quedando el tanque en condiciones aeronavegables de acuerdo con manual.',
                'tecnico_responsable' => 'Tec. Ernesto Antonio Martinez Ibanez',
                'inspector' => 'Pendiente',
                'estado' => 'cerrada',
                'tareas' => [
                    ['titulo' => 'Inspeccion preliminar general', 'descripcion' => '21-03-25. Inspeccion preliminar al tanque.', 'orden' => 1, 'tipo' => 'INSPECCION', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 120, 'estado' => 'completada', 'tecnico' => 'Tec. Jose Mauricio Jaime Bonilla 200700355'],
                    ['titulo' => 'Limpieza y drenado', 'descripcion' => '21-03-25. Limpieza y drenado del remanente de combustible.', 'orden' => 2, 'tipo' => 'LIMPIEZA', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 180, 'estado' => 'completada', 'tecnico' => 'Tec. Jose Mauricio Jaime Bonilla 200700355'],
                    ['titulo' => 'Remocion de registros y tapones', 'descripcion' => '24-03-25. Se remueven registros, tapones de seguridad y conos aerodinamicos para inspeccion.', 'orden' => 3, 'tipo' => 'DESENSAMBLE', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 180, 'estado' => 'completada', 'tecnico' => 'Tec. Jose Mauricio Jaime Bonilla 200700355'],
                    ['titulo' => 'Inspeccion visual interna y externa', 'descripcion' => '25-03-25. Inspeccion por delaminacion, roturas y areas criticas.', 'orden' => 4, 'tipo' => 'INSPECCION', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 360, 'estado' => 'completada', 'tecnico' => 'Tec. Jose Mauricio Jaime Bonilla 200700355'],
                    ['titulo' => 'Pruebas operacionales', 'descripcion' => '21-04-25. Se realizan pruebas de presion para verificar ausencia de fugas.', 'orden' => 5, 'tipo' => 'PRUEBA', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 120, 'estado' => 'completada', 'tecnico' => 'Tec. Jose Mauricio Jaime Bonilla 200700355'],
                    ['titulo' => 'Limpieza final y resguardo', 'descripcion' => '22-04-25. Limpieza del liquido utilizado y resguardo en contenedor apropiado.', 'orden' => 6, 'tipo' => 'LIMPIEZA', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 60, 'estado' => 'completada', 'tecnico' => 'Tec. Jose Mauricio Jaime Bonilla 200700355'],
                    ['titulo' => 'Reinstalacion de registros', 'descripcion' => '25-04-25. Se instalan registros con tornilleria apropiada para traslado.', 'orden' => 7, 'tipo' => 'INSTALACION', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 120, 'estado' => 'completada', 'tecnico' => 'Tec. Jose Mauricio Jaime Bonilla 200700355'],
                    ['titulo' => 'Cierre documental', 'descripcion' => '28-04-25. Se completa papeleria y cierre de ordenes de trabajo.', 'orden' => 8, 'tipo' => 'ADMINISTRATIVO', 'prioridad' => 'BAJA', 'tiempo_estimado_min' => 60, 'estado' => 'completada', 'tecnico' => 'Tec. Jose Mauricio Jaime Bonilla 200700355'],
                ],
                'discrepancias' => [],
                'refacciones' => [],
                'consumibles' => [],
                'herramientas' => [],
                'ndt' => [],
                'talleres_externos' => [],
                'mediciones' => [],
            ],
            [
                'folio' => 'CESA-PROP25-001',
                'area_codigo' => 'PROP',
                'anio' => 2025,
                'consecutivo' => 1,
                'fecha' => '2025-08-14',
                'fecha_inicio' => '2025-08-14',
                'fecha_termino' => '2025-08-14',
                'cliente' => 'CESA',
                'matricula' => '-',
                'aeronave_modelo' => 'CESSNA 414',
                'aeronave_serie' => '-',
                'descripcion' => 'INSPECCION A PROPELLER LH',
                'trabajo_descripcion' => 'INSPECCION A PROPELLER LH',
                'componente_descripcion' => 'PROPELLER',
                'componente_modelo' => 'MCCAULEY',
                'componente_numero_parte' => '3AF32C87-NR',
                'componente_numero_serie' => '786224',
                'tipo_tarea' => 'INSPECCION',
                'intervalo' => null,
                'accion_correctiva' => 'Se efectua inspeccion del propeller de acuerdo con el manual del fabricante, encontrandose en estado operativo.',
                'tecnico_responsable' => 'Tec. Jose Carlos Rojas Estrada 202273703',
                'inspector' => 'Pendiente',
                'estado' => 'cerrada',
                'tareas' => [
                    ['titulo' => 'Inspeccion de propeller LH', 'descripcion' => '14-08-25. Se inspecciona propeller P/N 3AF32C87-NR, S/N 786224, conforme al manual del fabricante.', 'orden' => 1, 'tipo' => 'INSPECCION', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 480, 'estado' => 'completada', 'tecnico' => 'Tec. Jose Carlos Rojas Estrada 202273703'],
                ],
                'discrepancias' => [],
                'refacciones' => [],
                'consumibles' => [],
                'herramientas' => [],
                'ndt' => [],
                'talleres_externos' => [],
                'mediciones' => [],
            ],
            [
                'folio' => 'CESA-SALV26-001',
                'area_codigo' => 'SALV',
                'anio' => 2026,
                'consecutivo' => 1,
                'fecha' => '2026-01-09',
                'fecha_inicio' => '2026-01-09',
                'fecha_termino' => '2026-01-09',
                'cliente' => 'CESA',
                'matricula' => 'XA-JUL',
                'aeronave_modelo' => 'HAWKER 800A',
                'aeronave_serie' => '258006',
                'descripcion' => 'INVENTARIO DE COMPONENTES',
                'trabajo_descripcion' => 'INVENTARIO DE COMPONENTES',
                'componente_descripcion' => 'COMPONENTES DIVERSOS',
                'tipo_tarea' => 'INVENTARIO',
                'intervalo' => null,
                'accion_correctiva' => 'Se realiza inventario fisico y documental de componentes recuperados para control y trazabilidad.',
                'tecnico_responsable' => 'Tec. Cesar Mora',
                'inspector' => 'Ing. Eduardo Trejo Perez',
                'estado' => 'cerrada',
                'tareas' => [
                    ['titulo' => 'Levantamiento de inventario', 'descripcion' => '09-01-26. Se realiza inventario de componentes recuperados de la aeronave.', 'orden' => 1, 'tipo' => 'INVENTARIO', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 240, 'estado' => 'completada', 'tecnico' => 'Tec. Cesar Mora'],
                ],
                'discrepancias' => [],
                'refacciones' => [
                    ['item' => 'R1', 'nombre' => 'COVER LH', 'descripcion' => 'Inventario. S/N removido: N/A. S/N instalado: N/A.', 'cantidad' => 1, 'numero_parte' => '25-6WS211', 'status' => 'OK'],
                    ['item' => 'R2', 'nombre' => 'VALVE REVERSING LH', 'descripcion' => 'Inventario. S/N removido: LK9007601. S/N instalado: N/A.', 'cantidad' => 1, 'numero_parte' => 'AIR 48542-1', 'status' => 'OK'],
                    ['item' => 'R3', 'nombre' => 'LAMP ASSY LH', 'descripcion' => 'Inventario. S/N removido: N/A. S/N instalado: N/A.', 'cantidad' => 2, 'numero_parte' => '22875-1-24', 'status' => 'OK'],
                    ['item' => 'R4', 'nombre' => 'LAMP ASSY RH', 'descripcion' => 'Inventario. S/N removido: N/A. S/N instalado: N/A.', 'cantidad' => 2, 'numero_parte' => '22875-2-24', 'status' => 'OK'],
                    ['item' => 'R5', 'nombre' => 'VALVE SELECTOR', 'descripcion' => 'Inventario. S/N removido: N/A. S/N instalado: N/A.', 'cantidad' => 2, 'numero_parte' => 'HTE1984-1', 'status' => 'UNIT EXCHANGE ITEM MOD 257544'],
                    ['item' => 'R6', 'nombre' => 'VALVE GRAVITY CROSFEED', 'descripcion' => 'Inventario. S/N removido: M-149 VRV52. S/N instalado: N/A.', 'cantidad' => 2, 'numero_parte' => 'HTE1984-1', 'status' => 'UNIT EXCHANGE ITEM MOD 257544'],
                    ['item' => 'R7', 'nombre' => 'DETECTOR FLUX', 'descripcion' => 'Inventario. S/N removido: 27113. S/N instalado: N/A.', 'cantidad' => 1, 'numero_parte' => '522-4945-001', 'status' => '3 BOLT (A169B1), 3 WASHER (SP124B)'],
                    ['item' => 'R8', 'nombre' => 'DETECTOR FLUX', 'descripcion' => 'Inventario. S/N removido: 28561. S/N instalado: N/A.', 'cantidad' => 1, 'numero_parte' => '522-4945-001', 'status' => '3 BOLT (A169B1), 3 WASHER (SP124B)'],
                    ['item' => 'R9', 'nombre' => 'BRACKET ASSY', 'descripcion' => 'Inventario. S/N removido: N/A. S/N instalado: N/A.', 'cantidad' => 1, 'numero_parte' => '25CW651A', 'status' => 'BOLT(A102-16D), BOLT (A102-12D), NUT(A110ES), RING-ABUTMET (25CW163)'],
                    ['item' => 'R10', 'nombre' => 'BRACKET ASSY - RUDDER PULLEY', 'descripcion' => 'Inventario. S/N removido: N/A. S/N instalado: N/A.', 'cantidad' => 1, 'numero_parte' => '25CF465AB', 'status' => 'OK'],
                ],
                'consumibles' => [],
                'herramientas' => [],
                'ndt' => [],
                'talleres_externos' => [],
                'mediciones' => [],
            ],
            [
                'folio' => 'CESA-VEST25-001',
                'area_codigo' => 'VEST',
                'anio' => 2025,
                'consecutivo' => 1,
                'fecha' => '2025-01-09',
                'fecha_inicio' => '2025-01-17',
                'fecha_termino' => '2025-01-24',
                'cliente' => 'FAM',
                'matricula' => '1091',
                'aeronave_modelo' => 'UH-60L',
                'aeronave_serie' => '702053',
                'descripcion' => 'TAPIZADO DE 2 ASIENTOS Y 2 RESPALDOS DE PILOTO Y COPILOTO',
                'trabajo_descripcion' => 'TAPIZADO DE 2 ASIENTOS Y 2 RESPALDOS DE PILOTO Y COPILOTO',
                'componente_descripcion' => 'ASIENTOS Y RESPALDOS DE PILOTO Y COPILOTO',
                'tipo_tarea' => 'TAPIZADO',
                'intervalo' => null,
                'accion_correctiva' => 'Se realiza moldeado de foam, plantillas, corte y costura de materiales, y montaje final de vestiduras sobre el acojinamiento.',
                'tecnico_responsable' => 'Tec. Adrian Reyes Trejo',
                'inspector' => 'Insp. Heriberto Garcia Acevedo 200204416',
                'estado' => 'cerrada',
                'tareas' => [
                    ['titulo' => 'Moldeado de foam', 'descripcion' => '17-01-25. Moldeado de foam de asientos de pilotos.', 'orden' => 1, 'tipo' => 'FABRICACION', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 480, 'estado' => 'completada', 'tecnico' => 'Tec. Adrian Reyes Trejo'],
                    ['titulo' => 'Plantillas de carton', 'descripcion' => '18-01-25. Elaboracion de plantillas en carton y hule cristal.', 'orden' => 2, 'tipo' => 'TRAZO', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 240, 'estado' => 'completada', 'tecnico' => 'Tec. Adrian Reyes Trejo'],
                    ['titulo' => 'Corte y costura', 'descripcion' => '20-01-25. Corte de piel y costura.', 'orden' => 3, 'tipo' => 'COSTURA', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 480, 'estado' => 'completada', 'tecnico' => 'Tec. Adrian Reyes Trejo'],
                    ['titulo' => 'Costura de partes', 'descripcion' => '21-01-25. Costura de partes del conjunto.', 'orden' => 4, 'tipo' => 'COSTURA', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 480, 'estado' => 'completada', 'tecnico' => 'Tec. Adrian Reyes Trejo'],
                    ['titulo' => 'Enfundado de acojinamiento', 'descripcion' => '22-01-25. Enfundado final del acojinamiento.', 'orden' => 5, 'tipo' => 'INSTALACION', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 240, 'estado' => 'completada', 'tecnico' => 'Tec. Adrian Reyes Trejo'],
                ],
                'discrepancias' => [],
                'refacciones' => [],
                'consumibles' => [],
                'herramientas' => [],
                'ndt' => [],
                'talleres_externos' => [],
                'mediciones' => [],
            ],
            [
                'folio' => 'GESA-TORN26-006',
                'area_codigo' => 'TORN',
                'anio' => 2026,
                'consecutivo' => 6,
                'fecha' => '2026-01-27',
                'fecha_inicio' => '2026-01-27',
                'fecha_termino' => '2026-02-06',
                'cliente' => 'NAVE',
                'matricula' => '-',
                'aeronave_modelo' => '-',
                'aeronave_serie' => '-',
                'descripcion' => 'BARRENADO DE 6 ORIFICIOS A 16 PLACAS PARA NAVE 2 EXPORTEC',
                'trabajo_descripcion' => 'BARRENADO DE 6 ORIFICIOS A 16 PLACAS PARA NAVE 2 EXPORTEC',
                'componente_descripcion' => 'PLACAS PARA NAVE 2',
                'tipo_tarea' => 'BARRENADO',
                'intervalo' => null,
                'accion_correctiva' => 'Se realiza trazado y barrenado de placas conforme a distancias indicadas y diametro requerido.',
                'tecnico_responsable' => 'Tec. Romero Guzman Victor',
                'inspector' => 'Pendiente',
                'estado' => 'cerrada',
                'tareas' => [
                    ['titulo' => 'Trazado y barrenado de placas', 'descripcion' => '27-01-26 a 06-02-26. Se trazan y barrenan placas con separaciones de 24 cm, 23 cm y 12.5 cm, con orificios de 1 1/4.', 'orden' => 1, 'tipo' => 'MAQUINADO', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 1440, 'estado' => 'completada', 'tecnico' => 'Tec. Romero Guzman Victor'],
                ],
                'discrepancias' => [],
                'refacciones' => [],
                'consumibles' => [],
                'herramientas' => [],
                'ndt' => [],
                'talleres_externos' => [],
                'mediciones' => [],
            ],
            [
                'folio' => 'CESA-HANG25-161',
                'area_codigo' => 'HANG',
                'anio' => 2025,
                'consecutivo' => 161,
                'fecha' => '2025-12-05',
                'fecha_inicio' => '2025-12-05',
                'fecha_termino' => '2025-12-05',
                'cliente' => 'CESA',
                'matricula' => 'XB-SDS',
                'aeronave_modelo' => 'LEARJET 35A',
                'aeronave_serie' => '35-492',
                'descripcion' => 'PRUEBAS HIDROSTATICAS',
                'trabajo_descripcion' => 'PRUEBAS HIDROSTATICAS',
                'componente_descripcion' => 'BOTELLAS Y ESFERAS DEL SISTEMA',
                'tipo_tarea' => 'SERVICIO',
                'intervalo' => null,
                'accion_correctiva' => 'Se remueven esferas extintoras LH y RH, se instalan botellas vigentes, se envia y recibe botella de oxigeno para inspeccion HYD, se instala esfera vigente de aire de emergencia y se recarga nitrogeno, dejando el sistema operativo.',
                'tecnico_responsable' => 'Tec. Jose Alberto Flores Alcantara',
                'inspector' => 'Tec. Omar Jair Montoya Landin 202504522',
                'estado' => 'cerrada',
                'tareas' => [
                    ['titulo' => 'Pruebas hidrostaticas e intercambio de botellas', 'descripcion' => '05-12-25. Se remueven botellas y esferas, se instalan componentes vigentes y se recargan niveles para dejar el sistema operativo.', 'orden' => 1, 'tipo' => 'SERVICIO', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 600, 'estado' => 'completada', 'tecnico' => 'Tec. Jose Alberto Flores Alcantara'],
                ],
                'discrepancias' => [],
                'refacciones' => [],
                'consumibles' => [],
                'herramientas' => [],
                'ndt' => [],
                'talleres_externos' => [],
                'mediciones' => [],
            ],
            [
                'folio' => 'CESA-HANG25-163',
                'area_codigo' => 'HANG',
                'anio' => 2025,
                'consecutivo' => 163,
                'fecha' => '2025-12-01',
                'fecha_inicio' => '2025-12-01',
                'fecha_termino' => '2025-12-01',
                'cliente' => 'GESA',
                'matricula' => 'XB-SDS',
                'aeronave_modelo' => 'LEARJET 35A',
                'aeronave_serie' => '35-492',
                'descripcion' => 'PHASE A1 - 300 HOUR INSPECTION/CHECKS',
                'trabajo_descripcion' => 'PHASE A1 - 300 HOUR INSPECTION/CHECKS',
                'componente_descripcion' => 'AERONAVE',
                'tipo_tarea' => 'INSPECCION',
                'intervalo' => '300 HOUR',
                'accion_correctiva' => 'Se realiza inspeccion fase A1 de 300 horas conforme al programa de mantenimiento.',
                'tecnico_responsable' => 'Pendiente',
                'inspector' => 'Tec. Omar Jair Montoya Landin 202504522',
                'estado' => 'cerrada',
                'tareas' => [
                    ['titulo' => 'Phase A1 - 300 Hour Inspection/Checks', 'descripcion' => 'Se registra inspeccion fase A1 de 300 horas para la aeronave XB-SDS.', 'orden' => 1, 'tipo' => 'INSPECCION', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 240, 'estado' => 'completada', 'tecnico' => 'Pendiente'],
                ],
                'discrepancias' => [],
                'refacciones' => [],
                'consumibles' => [],
                'herramientas' => [],
                'ndt' => [],
                'talleres_externos' => [],
                'mediciones' => [],
            ],
            [
                'folio' => 'CESA-HANG26-026',
                'area_codigo' => 'HANG',
                'anio' => 2026,
                'consecutivo' => 26,
                'fecha' => '2026-02-18',
                'fecha_inicio' => '2026-02-18',
                'fecha_termino' => '2026-02-19',
                'cliente' => 'CESA',
                'matricula' => 'XB-SDS',
                'aeronave_modelo' => 'LEARJET 35A',
                'aeronave_serie' => '492',
                'descripcion' => 'INSPECCION Y LIMPIEZA FWR MOUNT CASTING',
                'trabajo_descripcion' => 'INSPECCION Y LIMPIEZA PARA ENVIO A PRUEBAS NDT DE FWR MOUNT CASTING Y COMPONENTES ASOCIADOS',
                'componente_descripcion' => 'FWR MOUNT CASTING Y COMPONENTES ASOCIADOS',
                'tipo_tarea' => 'INSPECCION Y LIMPIEZA',
                'intervalo' => null,
                'accion_correctiva' => 'Se efectuan trabajos de limpieza de ferreteria, remocion de pintura para inspeccion NDT e inspeccion dimensional de los componentes.',
                'tecnico_responsable' => 'Tec. Hilario Gutierrez Hernandez',
                'inspector' => 'Pendiente',
                'estado' => 'cerrada',
                'tareas' => [
                    ['titulo' => 'Recepcion de componentes', 'descripcion' => '18-02-26. Recepcion de componentes.', 'orden' => 1, 'tipo' => 'RECEPCION', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 30, 'estado' => 'completada', 'tecnico' => 'Tec. Hilario Gutierrez Hernandez'],
                    ['titulo' => 'Aplicacion de removedor', 'descripcion' => '18-02-26. Aplicacion de removedor para limpieza.', 'orden' => 2, 'tipo' => 'LIMPIEZA', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 480, 'estado' => 'completada', 'tecnico' => 'Tec. Hilario Gutierrez Hernandez'],
                    ['titulo' => 'Envio de pernos a remocion de cadminizado', 'descripcion' => '19-02-26. Se envian pernos a remocion de cadminizado.', 'orden' => 3, 'tipo' => 'ABASTO', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 30, 'estado' => 'completada', 'tecnico' => 'Tec. Hilario Gutierrez Hernandez'],
                    ['titulo' => 'Inspeccion dimensional micrometrica', 'descripcion' => '19-02-26. Se realiza inspeccion dimensional micrometrica.', 'orden' => 4, 'tipo' => 'INSPECCION', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 240, 'estado' => 'completada', 'tecnico' => 'Tec. Hilario Gutierrez Hernandez'],
                    ['titulo' => 'Envio a inspeccion NDT', 'descripcion' => '19-02-26. Se envia a inspeccion NDT.', 'orden' => 5, 'tipo' => 'NDT', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 30, 'estado' => 'completada', 'tecnico' => 'Tec. Hilario Gutierrez Hernandez'],
                ],
                'discrepancias' => [],
                'refacciones' => [],
                'consumibles' => [],
                'herramientas' => [],
                'ndt' => [],
                'talleres_externos' => [],
                'mediciones' => [],
            ],
            [
                'folio' => 'CESA-HANG24-151',
                'area_codigo' => 'HANG',
                'anio' => 2024,
                'consecutivo' => 151,
                'fecha' => '2024-12-20',
                'fecha_inicio' => '2024-12-20',
                'fecha_termino' => '2025-02-08',
                'cliente' => 'CESA',
                'matricula' => 'XA-VGZ',
                'aeronave_modelo' => 'HAWKER 800',
                'aeronave_serie' => '-',
                'descripcion' => 'ATENCION A REPORTES',
                'trabajo_descripcion' => 'ATENCION A REPORTES',
                'componente_descripcion' => 'SISTEMAS DIVERSOS / TKS',
                'tipo_tarea' => 'ATENCION A REPORTES',
                'intervalo' => null,
                'accion_correctiva' => 'Se atienden reportes diversos, incluyendo reemplazo de bomba main fuel del motor LH y trabajos de servicio, desobstruccion, pruebas, sellado e instalacion de paneles TKS y bordes de ataque del estabilizador.',
                'tecnico_responsable' => 'Tec. Omar Jair Montoya Landin',
                'inspector' => 'Tec. Juan Martin Carrillo Trejo',
                'estado' => 'cerrada',
                'tareas' => [
                    ['titulo' => 'Reemplazo de bomba main fuel motor LH', 'descripcion' => '20-12-24. Reemplazo de bomba main fuel del motor LH.', 'orden' => 1, 'tipo' => 'REEMPLAZO', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 240, 'estado' => 'completada', 'tecnico' => 'Tec. Omar Jair Montoya Landin'],
                    ['titulo' => 'Desmontaje de paneles TKS', 'descripcion' => 'Se desmontan paneles de borde de ataque de estabilizador horizontal y vertical para servicio por obstruccion del sistema TKS.', 'orden' => 2, 'tipo' => 'DESENSAMBLE', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 1200, 'estado' => 'completada', 'tecnico' => 'Tec. Luis Miguel Romero Hernandez'],
                    ['titulo' => 'Desarmado y destape de measuring pipes', 'descripcion' => 'Se desarman paneles TKS, se destapan measuring pipes y se verifica salida de liquido.', 'orden' => 3, 'tipo' => 'REPARACION', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 4800, 'estado' => 'completada', 'tecnico' => 'Tec. Luis Miguel Romero Hernandez'],
                    ['titulo' => 'Armado de paneles TKS', 'descripcion' => '04-02-25. Limpieza de bordes del estabilizador horizontal y armado de paneles TKS.', 'orden' => 4, 'tipo' => 'ARMADO', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 960, 'estado' => 'completada', 'tecnico' => 'Tec. Luis Miguel Romero Hernandez'],
                    ['titulo' => 'Acondicionamiento e instalacion preliminar', 'descripcion' => '05-02-25. Se limpia estabilizador, se elimina PRC viejo y se instalan bordes de ataque con tornillos de resguardo.', 'orden' => 5, 'tipo' => 'INSTALACION', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 960, 'estado' => 'completada', 'tecnico' => 'Tec. Luis Miguel Romero Hernandez'],
                    ['titulo' => 'Prueba anti-ice y atencion de fugas', 'descripcion' => '06-02-25. Se realiza prueba anti-ice, se cambian o-rings, se verifica ausencia de fugas y se continua instalacion.', 'orden' => 6, 'tipo' => 'PRUEBA', 'prioridad' => 'ALTA', 'tiempo_estimado_min' => 960, 'estado' => 'completada', 'tecnico' => 'Tec. Luis Miguel Romero Hernandez'],
                    ['titulo' => 'Aplicacion de PRC', 'descripcion' => '07-02-25. Se enmascara y se inicia aplicacion de PRC en bordes de ataque.', 'orden' => 7, 'tipo' => 'SELLADO', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 960, 'estado' => 'completada', 'tecnico' => 'Tec. Luis Miguel Romero Hernandez'],
                    ['titulo' => 'Sellado final de paneles TKS', 'descripcion' => '08-02-25. Se continua preparacion y enmascarado de paneles TKS para sellado final.', 'orden' => 8, 'tipo' => 'SELLADO', 'prioridad' => 'MEDIA', 'tiempo_estimado_min' => 480, 'estado' => 'completada', 'tecnico' => 'Tec. Luis Miguel Romero Hernandez'],
                ],
                'discrepancias' => [],
                'refacciones' => [],
                'consumibles' => [],
                'herramientas' => [],
                'ndt' => [],
                'talleres_externos' => [],
                'mediciones' => [],
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
                    'componente_modelo' => $example['componente_modelo'] ?? null,
                    'componente_numero_parte' => $example['componente_numero_parte'] ?? null,
                    'componente_numero_serie' => $example['componente_numero_serie'] ?? null,
                    'tipo_tarea' => $example['tipo_tarea'],
                    'intervalo' => $example['intervalo'],
                    'accion_correctiva' => $example['accion_correctiva'],
                    'tecnico_responsable' => $example['tecnico_responsable'],
                    'inspector' => $example['inspector'],
                    'fecha_inicio' => $example['fecha_inicio'],
                    'fecha_termino' => $example['fecha_termino'],
                    'estado' => $example['estado'],
                ]
            );

            $this->syncOrderDetails($orden, $area, $example);
        }
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
        $this->syncAircraftFromOrder($orden);

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

    private function syncAircraftFromOrder(Orden $orden): void
    {
        $matricula = trim((string) ($orden->matricula ?? ''));

        if ($matricula === '' || $matricula === '-' || strtoupper($matricula) === 'N/A') {
            return;
        }

        [$fabricante, $modelo] = $this->splitAircraftModel((string) ($orden->aeronave_modelo ?? ''));

        Aeronave::updateOrCreate(
            ['matricula' => $matricula],
            [
                'cliente' => $orden->cliente ?: null,
                'fabricante' => $fabricante,
                'modelo' => $modelo,
                'numero_serie' => $this->nullableAircraftValue($orden->aeronave_serie),
                'estado' => 'activa',
                'notas' => 'Seed basado en orden ' . $orden->folio . '.',
            ]
        );
    }

    private function splitAircraftModel(string $rawModel): array
    {
        $rawModel = trim($rawModel);

        if ($rawModel === '' || $rawModel === '-' || strtoupper($rawModel) === 'N/A') {
            return [null, null];
        }

        $parts = preg_split('/\s+/', $rawModel) ?: [];

        if (count($parts) <= 1) {
            return [null, $rawModel];
        }

        $fabricante = ucfirst(strtolower((string) array_shift($parts)));
        $modelo = trim(implode(' ', $parts));

        return [$fabricante, $modelo !== '' ? $modelo : null];
    }

    private function nullableAircraftValue(mixed $value): ?string
    {
        $normalized = trim((string) ($value ?? ''));

        if ($normalized === '' || $normalized === '-' || strtoupper($normalized) === 'N/A') {
            return null;
        }

        return $normalized;
    }

    private function bustListingCaches(): void
    {
        Cache::forever('ordenes_cache_version', (int) Cache::get('ordenes_cache_version', 1) + 1);
        Cache::forever('motores_cache_version', (int) Cache::get('motores_cache_version', 1) + 1);
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
