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
        $this->seedHangarMotorExample($user);
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
