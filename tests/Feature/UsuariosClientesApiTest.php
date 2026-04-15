<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Cliente;
use App\Models\ClientePortalIncident;
use App\Models\ClientePortalInvoice;
use App\Models\ClientePortalPaymentMethod;
use App\Models\Orden;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsuariosClientesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_internal_users_with_pagination_meta(): void
    {
        $this->seed();

        $response = $this->getJson('/api/v1/usuarios?per_page=5');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 5);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_engineering_user_can_crud_system_users(): void
    {
        $this->seed();
        $this->authenticateAsUser('ing@redaviation.com');

        $response = $this->postJson('/api/v1/usuarios', [
            'nombre' => 'Usuario QA',
            'email' => 'usuario-qa@redaviation.com',
            'telefono' => '555-777-9999',
            'puesto' => 'Inspector QA',
            'rol' => 'jefe_area',
            'area_codigo' => 'AVCS',
            'estado' => 'Activo',
            'permisos' => ['clientes_crud'],
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.nombre', 'Usuario QA')
            ->assertJsonPath('data.rol', 'jefe_area')
            ->assertJsonPath('data.area_codigo', 'AVCS');

        $userId = $response->json('data.id');

        $this->putJson("/api/v1/usuarios/{$userId}", [
            'puesto' => 'Coordinador QA',
            'rol' => 'calidad',
            'permisos' => ['clientes_crud', 'usuarios_crud'],
        ])
            ->assertOk()
            ->assertJsonPath('data.puesto', 'Coordinador QA')
            ->assertJsonPath('data.rol', 'calidad');

        $this->deleteJson("/api/v1/usuarios/{$userId}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('users', [
            'id' => $userId,
            'email' => 'usuario-qa@redaviation.com',
        ]);
    }

    public function test_engineering_user_can_crud_clients(): void
    {
        $this->seed();
        $this->authenticateAsUser('ing@redaviation.com');

        $create = $this->postJson('/api/v1/clientes', [
            'nombre_comercial' => 'Cliente QA',
            'razon_social' => 'Cliente QA SA de CV',
            'rfc' => 'CQA260406CC3',
            'contacto_nombre' => 'Maria QA',
            'email' => 'maria@clienteqa.com',
            'telefono' => '555-222-3333',
            'ciudad' => 'Queretaro',
            'estatus' => 'Activo',
            'notas' => 'Alta de prueba.',
        ]);

        $create
            ->assertCreated()
            ->assertJsonPath('data.nombre_comercial', 'Cliente QA')
            ->assertJsonPath('data.contacto_nombre', 'Maria QA');

        $clienteId = $create->json('data.id');

        $this->putJson("/api/v1/clientes/{$clienteId}", [
            'estatus' => 'Prospecto',
            'ciudad' => 'Monterrey',
        ])
            ->assertOk()
            ->assertJsonPath('data.estatus', 'Prospecto')
            ->assertJsonPath('data.ciudad', 'Monterrey');

        $this->deleteJson("/api/v1/clientes/{$clienteId}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('clientes', [
            'id' => $clienteId,
        ]);
    }

    public function test_me_endpoint_returns_permissions_and_role_name(): void
    {
        $this->seed();
        $user = User::query()->where('email', 'ing@redaviation.com')->firstOrFail();
        $this->authenticateAsUser($user);

        $response = $this->getJson('/api/v1/me');

        $response
            ->assertOk()
            ->assertJsonPath('user.rol_nombre', 'ingenieria')
            ->assertJsonPath('user.permisos.0', 'aeronaves_crud');
    }

    public function test_engineering_user_can_consult_client_portal_dashboard_and_register_actions(): void
    {
        $this->seed();
        $this->authenticateAsUser('ing@redaviation.com');

        $cliente = Cliente::query()->where('email', 'cliente.demo@redaviation.com')->firstOrFail();
        $orden = Orden::query()->firstOrFail();

        $cliente->update([
            'ot_asignada_id' => $orden->id,
        ]);
        $cliente->ordenesAsignadas()->sync([$orden->id]);

        $paymentMethod = ClientePortalPaymentMethod::query()->firstOrCreate(
            ['code' => 'wire-transfer-qa'],
            [
                'name' => 'Transferencia QA',
                'description' => 'Metodo de prueba para QA.',
                'instructions' => 'Compartir comprobante.',
                'active' => true,
                'sort_order' => 1,
            ]
        );

        $invoice = ClientePortalInvoice::query()->create([
            'cliente_id' => $cliente->id,
            'orden_id' => $orden->id,
            'folio' => 'FAC-QA-001',
            'concepto' => 'Prueba portal cliente',
            'amount_total' => 12500.50,
            'currency' => 'MXN',
            'status' => 'pendiente',
            'issued_at' => now()->toDateString(),
            'due_at' => now()->addDays(10)->toDateString(),
            'pdf_url' => 'https://example.test/fac-qa-001.pdf',
            'notes' => 'Factura de prueba.',
        ]);

        ClientePortalIncident::query()->create([
            'cliente_id' => $cliente->id,
            'orden_id' => $orden->id,
            'type' => 'falla',
            'title' => 'Incidencia existente',
            'description' => 'Incidencia cargada para validar dashboard.',
            'priority' => 'media',
            'status' => 'reportada',
            'urgent' => false,
            'request_callback' => true,
        ]);

        $dashboard = $this->getJson("/api/v1/clientes/{$cliente->id}/portal-dashboard");

        $dashboard
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.customer.id', $cliente->id)
            ->assertJsonPath('data.invoices.0.folio', 'FAC-QA-001')
            ->assertJsonPath('data.incidents.0.title', 'Incidencia existente');

        $paymentSelection = $this->postJson("/api/v1/clientes/{$cliente->id}/portal-selecciones-pago", [
            'invoice_id' => $invoice->id,
            'payment_method_id' => $paymentMethod->id,
            'reference' => 'REF-QA-7788',
        ]);

        $paymentSelection
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.payment_method', 'Transferencia QA')
            ->assertJsonPath('data.reference', 'REF-QA-7788');

        $incident = $this->postJson("/api/v1/clientes/{$cliente->id}/portal-incidencias", [
            'orden_id' => $orden->id,
            'title' => 'Urgencia capturada desde ERP',
            'description' => 'Se replica el flujo del portal desde escritorio.',
            'urgent' => true,
            'request_callback' => true,
        ]);

        $incident
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.urgent', true)
            ->assertJsonPath('data.priority', 'alta');
    }
}
