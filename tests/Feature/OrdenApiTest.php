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

        $response = $this->getJson('/api/v1/ordenes');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.area.codigo', 'AVCS')
            ->assertJsonPath('data.0.tareas_count', 1)
            ->assertJsonPath('data.0.discrepancias_count', 1);

        $response->assertJsonMissingPath('data.0.tareas.0');
        $response->assertJsonMissingPath('data.0.discrepancias.0');
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
