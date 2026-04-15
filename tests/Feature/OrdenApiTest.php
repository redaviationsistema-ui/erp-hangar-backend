<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AtaSubchapter;
use App\Models\Motor;
use App\Models\TipoOrden;
use App\Models\User;
use Database\Seeders\AreaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdenApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_aeronautical_order_and_generates_ata_tasks(): void
    {
        $this->seed();

        $area = Area::where('codigo', 'AVCS')->firstOrFail();
        $tipo = TipoOrden::where('codigo', 'AVCS')->firstOrFail();
        $user = User::firstOrFail();
        $subchapter = AtaSubchapter::where('codigo', '24-10')->firstOrFail();

        $response = $this->postJson('/api/v1/ordenes', [
            'area_id' => $area->id,
            'tipo_id' => $tipo->id,
            'user_id' => $user->id,
            'ata_subchapter_id' => $subchapter->id,
            'descripcion' => 'Inspeccion mayor sistema electrico',
            'cliente' => 'Cliente Demo',
            'matricula' => 'XA-TEST',
            'estado' => 'abierta',
            'discrepancias' => [
                [
                    'item' => '01',
                    'descripcion' => 'Arnes con desgaste visible',
                    'accion_correctiva' => 'Reemplazo parcial',
                ],
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.area.codigo', 'AVCS')
            ->assertJsonPath('data.ata.subchapter.codigo', '24-10');

        $this->assertStringStartsWith('CESA-AVCS', $response->json('data.folio'));
        $this->assertNotEmpty($response->json('data.tareas'));
        $this->assertCount(1, $response->json('data.discrepancias'));
    }

    public function test_it_lists_ata_catalog_with_templates(): void
    {
        $this->seed();

        $response = $this->getJson('/api/v1/ata');

        $response
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_it_lists_orders_with_lightweight_relations_and_counts(): void
    {
        $this->seed();

        $area = Area::where('codigo', 'AVCS')->firstOrFail();
        $tipo = TipoOrden::where('codigo', 'AVCS')->firstOrFail();
        $user = User::firstOrFail();
        $subchapter = AtaSubchapter::where('codigo', '24-10')->firstOrFail();

        $this->postJson('/api/v1/ordenes', [
            'area_id' => $area->id,
            'tipo_id' => $tipo->id,
            'user_id' => $user->id,
            'ata_subchapter_id' => $subchapter->id,
            'generar_tareas_ata' => false,
            'descripcion' => 'Orden para validar rendimiento de listado',
            'cliente' => 'Cliente Demo',
            'matricula' => 'XA-LIST',
            'tareas' => [
                [
                    'titulo' => 'Tarea manual',
                    'descripcion' => 'Solo para validar contadores',
                    'orden' => 1,
                ],
            ],
            'discrepancias' => [
                [
                    'item' => '01',
                    'descripcion' => 'Discrepancia de prueba',
                ],
            ],
        ])->assertCreated();

        $response = $this->getJson('/api/v1/ordenes?include_counts=1');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.area.codigo', 'AVCS')
            ->assertJsonPath('data.0.tareas_count', 1)
            ->assertJsonPath('data.0.discrepancias_count', 1);

        $response->assertJsonMissingPath('data.0.tareas.0');
        $response->assertJsonMissingPath('data.0.discrepancias.0');
    }

    public function test_it_returns_admin_dashboard_summary_from_backend(): void
    {
        $this->seed();

        $area = Area::create([
            'nombre' => 'QA Dashboard',
            'codigo' => 'QADM',
            'numero' => '99',
        ]);
        $tipo = TipoOrden::create([
            'nombre' => 'QA Dashboard',
            'codigo' => 'QADM',
        ]);
        $user = User::create([
            'name' => 'Tecnico QA',
            'email' => 'qa-dashboard@redaviation.com',
            'password' => 'secret123',
            'area_id' => $area->id,
            'rol' => 'tecnico',
        ]);

        $this->authenticateAsUser($user);

        $ordenResponse = $this->postJson('/api/v1/ordenes', [
            'area_id' => $area->id,
            'tipo_id' => $tipo->id,
            'user_id' => $user->id,
            'descripcion' => 'Orden para tablero administrativo',
            'cliente' => 'Cliente Resumen',
            'matricula' => 'XA-ADM',
            'estado' => 'cerrada',
            'generar_tareas_ata' => false,
            'discrepancias' => [
                [
                    'item' => '01',
                    'descripcion' => 'Discrepancia con mano de obra',
                    'accion_correctiva' => 'Correccion documentada',
                    'status' => 'resuelta',
                    'horas_hombre' => 3.5,
                ],
            ],
            'talleres_externos' => [
                [
                    'item' => '01',
                    'proveedor' => 'Proveedor QA',
                    'foto_path' => 'talleres-externos/evidencia.jpg',
                    'recepcion' => now()->toDateString(),
                    'trabajo_realizado' => 'Revision externa documentada',
                ],
            ],
        ])->assertCreated();

        $ordenId = $ordenResponse->json('data.id');
        $tallerId = $ordenResponse->json('data.talleres_externos.0.id');

        $refaccionId = $this->postJson('/api/v1/refacciones', [
            'orden_id' => $ordenId,
            'item' => '01',
            'nombre' => 'Arnes',
            'descripcion' => 'Arnes principal de prueba',
            'cantidad' => 2,
            'solicitante_fecha' => now()->toDateString(),
            'area_procedencia' => 'ALM',
            'recibe_fecha' => now()->toDateString(),
        ])->assertCreated()->json('data.id');

        $consumibleId = $this->postJson('/api/v1/consumibles', [
            'orden_id' => $ordenId,
            'item' => '01',
            'nombre' => 'Termofit',
            'descripcion' => 'Consumible de prueba',
            'cantidad' => 1,
            'solicitante_fecha' => now()->toDateString(),
            'area_procedencia' => 'ALM',
            'recibe_fecha' => now()->toDateString(),
        ])->assertCreated()->json('data.id');

        $this->authenticateAsUser('administracion@redaviation.com');

        $this->putJson('/api/v1/refacciones/' . $refaccionId, [
            'costo_total' => 1250,
        ])->assertOk();

        $this->putJson('/api/v1/consumibles/' . $consumibleId, [
            'costo_total' => 300,
        ])->assertOk();

        $this->putJson('/api/v1/ordenes/' . $ordenId, [
            'miscelanea_costo_total' => 150,
        ])->assertOk();

        $this->putJson('/api/v1/talleres/' . $tallerId, [
            'costo' => 500,
        ])->assertOk();

        $this->authenticateAsUser('administradoror@redaviation.com');

        $this->putJson('/api/v1/refacciones/' . $refaccionId, [
            'precio_venta' => 1600,
        ])->assertOk();

        $this->putJson('/api/v1/consumibles/' . $consumibleId, [
            'precio_venta' => 450,
        ])->assertOk();

        $this->authenticateAsUser('administracion@redaviation.com');

        $this->putJson('/api/v1/ordenes/' . $ordenId, [
            'miscelanea_precio_venta' => 240,
        ])->assertOk();

        $this->authenticateAsUser('administradoror@redaviation.com');

        $this->putJson('/api/v1/talleres/' . $tallerId, [
            'precio_venta' => 700,
        ])->assertOk();

        $this->authenticateAsUser($user);

        $response = $this->getJson('/api/v1/admin/dashboard/resumen');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_ordenes', 1)
            ->assertJsonPath('data.refacciones_registros', 1)
            ->assertJsonPath('data.consumibles_registros', 1)
            ->assertJsonPath('data.talleres_registros', 1)
            ->assertJsonPath('data.ndt_registros', 0)
            ->assertJsonPath('data.horas_hombre', 3.5)
            ->assertJsonPath('data.costo_refacciones', 1250)
            ->assertJsonPath('data.costo_consumibles', 300)
            ->assertJsonPath('data.costo_talleres', 500)
            ->assertJsonPath('data.costo_miscelanea', 150)
            ->assertJsonPath('data.costo_total', 2200)
            ->assertJsonPath('data.venta_total', 2990)
            ->assertJsonPath('data.por_facturar', 1)
            ->assertJsonPath('data.por_cobrar_monto', 2990)
            ->assertJsonPath('data.proveedores', 1)
            ->assertJsonPath('data.top_cliente', 'Cliente Resumen')
            ->assertJsonPath('data.top_matricula', 'XA-ADM')
            ->assertJsonPath('data.top_area', 'QADM')
            ->assertJsonPath('data.top_ots.0.folio', $ordenResponse->json('data.folio'))
            ->assertJsonPath('data.top_ots.0.costo', 2200)
            ->assertJsonPath('data.top_ots.0.venta', 2990)
            ->assertJsonPath('data.top_ots.0.margen', 790);
    }

    public function test_it_returns_task_hours_summary_and_restricts_miscelanea_admin_fields(): void
    {
        $this->seed();

        $area = Area::query()->firstOrCreate(
            ['codigo' => 'QADM'],
            ['nombre' => 'QA Horas', 'numero' => '99']
        );
        $tipo = TipoOrden::query()->firstOrCreate(
            ['codigo' => 'QADM'],
            ['nombre' => 'QA Horas']
        );
        $user = User::create([
            'name' => 'Tecnico HH',
            'email' => 'tecnico-hh@redaviation.com',
            'password' => 'secret123',
            'area_id' => $area->id,
            'rol' => 'tecnico',
        ]);

        $this->authenticateAsUser($user);

        $ordenId = $this->postJson('/api/v1/ordenes', [
            'area_id' => $area->id,
            'tipo_id' => $tipo->id,
            'user_id' => $user->id,
            'descripcion' => 'OT con resumen HH para miscelanea',
            'estado' => 'abierta',
            'generar_tareas_ata' => false,
            'tecnico_responsable' => 'Tec. Responsable Base',
            'tareas' => [
                [
                    'titulo' => 'Inspeccion inicial',
                    'tecnico' => 'Tec. Omar',
                    'tiempo_estimado_min' => 90,
                ],
                [
                    'titulo' => 'Ajuste final',
                    'tecnico' => 'Tec. Omar',
                    'tiempo_estimado_min' => 30,
                ],
                [
                    'titulo' => 'Prueba funcional',
                    'tecnico' => 'Tec. Kevin',
                    'tiempo_estimado_min' => 60,
                ],
            ],
        ])->assertCreated()->json('data.id');

        $this->getJson('/api/v1/ordenes/' . $ordenId)
            ->assertOk()
            ->assertJsonPath('data.hh_tareas_total', 3)
            ->assertJsonPath('data.hh_tecnico_resumen', 'Tec. Omar (2.0 h) | Tec. Kevin (1.0 h)')
            ->assertJsonPath('data.hh_tecnicos.0.tecnico', 'Tec. Omar')
            ->assertJsonPath('data.hh_tecnicos.0.horas', 2);

        $this->putJson('/api/v1/ordenes/' . $ordenId, [
            'miscelanea_costo_total' => 1500,
            'miscelanea_observaciones_admin' => 'Intento sin permiso',
        ])->assertForbidden();

        $this->authenticateAsUser('administracion@redaviation.com');

        $this->putJson('/api/v1/ordenes/' . $ordenId, [
            'miscelanea_costo_total' => 1500,
            'miscelanea_precio_venta' => 2100,
            'miscelanea_observaciones_admin' => 'Captura autorizada',
        ])->assertOk()
            ->assertJsonPath('data.miscelanea_costo_total', '1500.00')
            ->assertJsonPath('data.miscelanea_precio_venta', '2100.00')
            ->assertJsonPath('data.miscelanea_observaciones_admin', 'Captura autorizada');
    }

    public function test_it_creates_an_order_linked_to_a_motor(): void
    {
        $this->seed();

        $area = Area::where('codigo', 'AVCS')->firstOrFail();
        $tipo = TipoOrden::where('codigo', 'AVCS')->firstOrFail();
        $user = User::firstOrFail();
        $motor = Motor::where('numero_serie', 'SN-GEN-2026')->firstOrFail();

        $response = $this->postJson('/api/v1/ordenes', [
            'area_id' => $area->id,
            'tipo_id' => $tipo->id,
            'user_id' => $user->id,
            'motor_id' => $motor->id,
            'descripcion' => 'OT ligada a motor para historial tecnico',
            'estado' => 'abierta',
            'generar_tareas_ata' => false,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.motor.id', $motor->id)
            ->assertJsonPath('data.motor.numero_serie', 'SN-GEN-2026')
            ->assertJsonPath('data.motor.aeronave.matricula', 'XA-ABC')
            ->assertJsonPath('data.matricula', 'XA-ABC');
    }

    public function test_it_returns_motor_history_with_multiple_orders(): void
    {
        $this->seed();

        $motor = Motor::where('numero_serie', 'SN-GEN-2026')->firstOrFail();

        $response = $this->getJson('/api/v1/motores/' . $motor->id);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.numero_serie', 'SN-GEN-2026')
            ->assertJsonPath('data.aeronave.matricula', 'XA-ABC');

        $this->assertNotEmpty($response->json('data.ordenes'));
    }

    public function test_all_catalog_areas_can_be_related_to_work_orders(): void
    {
        $this->seed();

        $user = User::firstOrFail();

        foreach (AreaSeeder::catalog() as $areaData) {
            $area = Area::where('codigo', $areaData['codigo'])->firstOrFail();
            $tipo = TipoOrden::where('codigo', $areaData['codigo'])->firstOrFail();

            $response = $this->postJson('/api/v1/ordenes', [
                'area_id' => $area->id,
                'tipo_id' => $tipo->id,
                'user_id' => $user->id,
                'descripcion' => 'OT de prueba para area ' . $area->codigo,
                'estado' => 'abierta',
                'generar_tareas_ata' => false,
            ]);

            $response
                ->assertCreated()
                ->assertJsonPath('data.area.codigo', $area->codigo);

            $this->assertStringStartsWith('CESA-' . $area->codigo, $response->json('data.folio'));
        }

        $areasResponse = $this->getJson('/api/v1/areas');

        $areasResponse
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(13, 'data')
            ->assertJsonPath('data.0.ot_form.tabs.0.key', 'partes')
            ->assertJsonPath('data.0.ot_form.tabs.0.collection', 'refacciones')
            ->assertJsonPath('data.0.ot_form.tabs.1.key', 'materiales')
            ->assertJsonPath('data.0.ot_form.tabs.1.collection', 'consumibles');
    }

    public function test_it_returns_ot_form_schema_per_area(): void
    {
        $this->seed();

        $area = Area::where('codigo', 'AVCS')->firstOrFail();

        $response = $this->getJson('/api/v1/areas/' . $area->id);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.codigo', 'AVCS')
            ->assertJsonPath('data.ot_form.tabs.0.key', 'partes')
            ->assertJsonPath('data.ot_form.tabs.0.collection', 'refacciones')
            ->assertJsonPath('data.ot_form.tabs.0.presets.0.nombre', 'Arnes electrico')
            ->assertJsonPath('data.ot_form.tabs.1.key', 'materiales')
            ->assertJsonPath('data.ot_form.tabs.1.collection', 'consumibles')
            ->assertJsonPath('data.ot_form.tabs.1.presets.0.nombre', 'Termofit');
    }

    public function test_it_supports_full_crud_for_aeronaves(): void
    {
        $this->seed();

        $createResponse = $this->postJson('/api/v1/aeronaves', [
            'cliente' => 'Cliente CRUD',
            'matricula' => 'XA-CRUD',
            'fabricante' => 'Beechcraft',
            'modelo' => 'King Air',
            'numero_serie' => 'KA-001',
            'estado' => 'activa',
        ]);

        $createResponse
            ->assertCreated()
            ->assertJsonPath('data.matricula', 'XA-CRUD');

        $aeronaveId = $createResponse->json('data.id');

        $this->putJson('/api/v1/aeronaves/' . $aeronaveId, [
            'estado' => 'en_mantenimiento',
        ])->assertOk()->assertJsonPath('data.estado', 'en_mantenimiento');

        $this->deleteJson('/api/v1/aeronaves/' . $aeronaveId)
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_it_supports_full_crud_for_motores(): void
    {
        $this->seed();

        $aeronave = $this->postJson('/api/v1/aeronaves', [
            'cliente' => 'Cliente Motor',
            'matricula' => 'XA-MTR',
            'fabricante' => 'Cessna',
            'modelo' => '172',
            'numero_serie' => 'CESS-172-MTR',
            'estado' => 'activa',
        ])->assertCreated()->json('data');

        $createResponse = $this->postJson('/api/v1/motores', [
            'aeronave_id' => $aeronave['id'],
            'posicion' => 'MOTOR 1',
            'fabricante' => 'Lycoming',
            'modelo' => 'O-360',
            'numero_parte' => 'LYC-360',
            'numero_serie' => 'MTR-CRUD-001',
            'estado' => 'instalado',
        ]);

        $createResponse
            ->assertCreated()
            ->assertJsonPath('data.numero_serie', 'MTR-CRUD-001');

        $motorId = $createResponse->json('data.id');

        $this->putJson('/api/v1/motores/' . $motorId, [
            'estado' => 'overhaul',
        ])->assertOk()->assertJsonPath('data.estado', 'overhaul');

        $this->deleteJson('/api/v1/motores/' . $motorId)
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}
