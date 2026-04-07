<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AtaChapter;
use App\Models\Discrepancia;
use App\Models\TipoOrden;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_manual_with_chunks_and_references(): void
    {
        $this->seed();

        $chapter = AtaChapter::where('codigo', '32')->firstOrFail();

        $response = $this->postJson('/api/v1/manuales', [
            'nombre' => 'Hawker 800XP AMM',
            'tipo_manual' => 'AMM',
            'aeronave_modelo' => 'Hawker 800XP',
            'revision' => 'A7',
            'chunks' => [
                [
                    'ata_chapter_id' => $chapter->id,
                    'codigo_seccion' => '32-40-00',
                    'titulo' => 'Brake inspection',
                    'tipo_contenido' => 'procedimiento',
                    'pagina_inicio' => 12,
                    'pagina_fin' => 18,
                    'texto' => 'Inspect brakes for vibration and wear.',
                    'keywords' => ['brakes', 'vibration'],
                    'referencias' => [
                        ['tipo' => 'keyword', 'valor' => 'brakes'],
                    ],
                ],
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.nombre', 'Hawker 800XP AMM')
            ->assertJsonPath('data.chunks_count', 1);
    }

    public function test_it_searches_manual_chunks_using_context_filters(): void
    {
        $this->seed();

        $response = $this->getJson('/api/v1/manuales-busqueda?query=vibracion%20en%20frenos%20al%20aterrizar&aeronave_modelo=Learjet%2035A');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.ata_candidates.0', 32)
            ->assertJsonPath('data.chunks.0.manual.aeronave_modelo', 'Learjet 35A');

        $this->assertNotEmpty($response->json('data.chunks'));
    }

    public function test_it_lists_source_pdf_files(): void
    {
        $this->seed();

        $this->getJson('/api/v1/manuales/source-files')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_it_builds_manual_context_for_a_discrepancy_from_order_data(): void
    {
        $this->seed();

        $area = Area::where('codigo', 'HANG')->firstOrFail();
        $tipo = TipoOrden::where('codigo', 'HANG')->firstOrFail();
        $user = User::firstOrFail();
        $chapter = AtaChapter::where('codigo', '79')->firstOrFail();

        $ordenResponse = $this->postJson('/api/v1/ordenes', [
            'area_id' => $area->id,
            'tipo_id' => $tipo->id,
            'user_id' => $user->id,
            'aeronave_modelo' => 'Learjet 35A',
            'descripcion' => 'OT para fuga de aceite',
            'estado' => 'abierta',
            'ata_chapter_id' => $chapter->id,
            'generar_tareas_ata' => false,
        ])->assertCreated();

        $discrepancia = Discrepancia::create([
            'orden_id' => $ordenResponse->json('data.id'),
            'item' => '01',
            'descripcion' => 'Fuga de aceite en motor izquierdo',
        ]);

        $response = $this->getJson('/api/v1/discrepancias/' . $discrepancia->id . '/contexto-manual');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.orden.aeronave_modelo', 'Learjet 35A');

        $manuales = $response->json('data.manuales_relacionados');
        $this->assertContains('Learjet 35A AMM Rev A7', $manuales);
        $this->assertNotEmpty($response->json('data.chunks'));
    }
}
