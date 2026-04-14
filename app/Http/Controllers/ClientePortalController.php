<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ClientePortalIncident;
use App\Models\ClientePortalInvoice;
use App\Models\ClientePortalPaymentMethod;
use App\Models\ClientePortalPaymentSelection;
use App\Models\Orden;
use Illuminate\Http\Request;

class ClientePortalController extends Controller
{
    public function dashboard(Request $request)
    {
        $cliente = $this->currentCliente($request);

        $orders = $cliente->relatedOrdersQuery()
            ->withCount('discrepancias')
            ->latest('fecha')
            ->latest('id')
            ->limit(20)
            ->get();

        $invoices = ClientePortalInvoice::query()
            ->with(['orden:id,folio', 'latestPaymentSelection.paymentMethod:id,name'])
            ->where('cliente_id', $cliente->id)
            ->latest('issued_at')
            ->latest('id')
            ->limit(20)
            ->get();

        $paymentMethods = ClientePortalPaymentMethod::query()
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $incidents = ClientePortalIncident::query()
            ->with('orden:id,folio')
            ->where('cliente_id', $cliente->id)
            ->latest('id')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'customer' => [
                    'id' => $cliente->id,
                    'name' => $cliente->contacto_nombre ?: $cliente->nombre_comercial,
                    'business_name' => $cliente->nombre_comercial,
                    'email' => $cliente->email,
                    'phone' => $cliente->telefono,
                    'city' => $cliente->ciudad,
                    'status' => $cliente->estatus ?: 'Activo',
                ],
                'metrics' => [
                    'orders_count' => $orders->count(),
                    'open_orders_count' => $orders->whereIn('estado', ['abierta', 'en proceso', 'proceso'])->count(),
                    'pending_invoices_count' => $invoices->whereNotIn('status', ['pagada', 'cancelada'])->count(),
                    'urgent_incidents_count' => $incidents->where('urgent', true)->count(),
                ],
                'orders' => $orders->map(fn (Orden $orden) => [
                    'id' => $orden->id,
                    'folio' => $orden->folio,
                    'estado' => $orden->estado,
                    'descripcion' => $orden->descripcion,
                    'trabajo_descripcion' => $orden->trabajo_descripcion,
                    'matricula' => $orden->matricula,
                    'aeronave_modelo' => $orden->aeronave_modelo,
                    'fecha_inicio' => optional($orden->fecha_inicio)->toDateString(),
                    'fecha_termino' => optional($orden->fecha_termino)->toDateString(),
                    'tiempo_entrega_estimado' => optional($orden->fecha_termino)->toDateString(),
                    'discrepancias_count' => $orden->discrepancias_count,
                    'area' => $orden->area ? [
                        'id' => $orden->area->id,
                        'codigo' => $orden->area->codigo,
                        'nombre' => $orden->area->nombre,
                    ] : null,
                ])->values(),
                'invoices' => $invoices->map(fn (ClientePortalInvoice $invoice) => [
                    'id' => $invoice->id,
                    'folio' => $invoice->folio,
                    'concepto' => $invoice->concepto,
                    'amount_total' => (float) $invoice->amount_total,
                    'currency' => $invoice->currency,
                    'status' => $invoice->status,
                    'issued_at' => optional($invoice->issued_at)->toDateString(),
                    'due_at' => optional($invoice->due_at)->toDateString(),
                    'pdf_url' => $invoice->pdf_url,
                    'notes' => $invoice->notes,
                    'orden' => $invoice->orden ? [
                        'id' => $invoice->orden->id,
                        'folio' => $invoice->orden->folio,
                    ] : null,
                    'selected_payment_method' => $invoice->latestPaymentSelection?->paymentMethod?->name,
                ])->values(),
                'payment_methods' => $paymentMethods->map(fn (ClientePortalPaymentMethod $method) => [
                    'id' => $method->id,
                    'code' => $method->code,
                    'name' => $method->name,
                    'description' => $method->description,
                    'instructions' => $method->instructions,
                ])->values(),
                'incidents' => $incidents->map(fn (ClientePortalIncident $incident) => [
                    'id' => $incident->id,
                    'type' => $incident->type,
                    'title' => $incident->title,
                    'description' => $incident->description,
                    'piece_name' => $incident->piece_name,
                    'part_number' => $incident->part_number,
                    'serial_number' => $incident->serial_number,
                    'priority' => $incident->priority,
                    'status' => $incident->status,
                    'urgent' => $incident->urgent,
                    'request_callback' => $incident->request_callback,
                    'created_at' => optional($incident->created_at)->toIso8601String(),
                    'orden' => $incident->orden ? [
                        'id' => $incident->orden->id,
                        'folio' => $incident->orden->folio,
                    ] : null,
                ])->values(),
            ],
        ]);
    }

    public function selectPaymentMethod(Request $request)
    {
        $cliente = $this->currentCliente($request);

        $data = $request->validate([
            'invoice_id' => 'nullable|integer|exists:cliente_portal_invoices,id',
            'orden_id' => 'nullable|integer|exists:ordenes,id',
            'payment_method_id' => 'required|integer|exists:cliente_portal_payment_methods,id',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        abort_unless(
            ! empty($data['invoice_id']) || ! empty($data['orden_id']),
            422,
            'Debes seleccionar una factura u orden.'
        );

        if (! empty($data['invoice_id'])) {
            $invoice = ClientePortalInvoice::query()
                ->where('cliente_id', $cliente->id)
                ->findOrFail($data['invoice_id']);
            $data['invoice_id'] = $invoice->id;
            $data['orden_id'] = $data['orden_id'] ?? $invoice->orden_id;
        }

        if (! empty($data['orden_id'])) {
            $orden = $cliente->relatedOrdersQuery()
                ->whereKey($data['orden_id'])
                ->firstOrFail();
            $data['orden_id'] = $orden->id;
        }

        $selection = ClientePortalPaymentSelection::create([
            'cliente_id' => $cliente->id,
            'invoice_id' => $data['invoice_id'] ?? null,
            'orden_id' => $data['orden_id'] ?? null,
            'payment_method_id' => $data['payment_method_id'],
            'reference' => $data['reference'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'pendiente',
        ]);

        $selection->load('paymentMethod:id,name');

        return response()->json([
            'success' => true,
            'message' => 'Forma de pago registrada correctamente.',
            'data' => [
                'id' => $selection->id,
                'status' => $selection->status,
                'payment_method' => $selection->paymentMethod?->name,
                'reference' => $selection->reference,
            ],
        ], 201);
    }

    public function reportIncident(Request $request)
    {
        $cliente = $this->currentCliente($request);

        $data = $request->validate([
            'orden_id' => 'nullable|integer|exists:ordenes,id',
            'type' => 'nullable|string|max:50',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'piece_name' => 'nullable|string|max:255',
            'part_number' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'priority' => 'nullable|string|max:50',
            'urgent' => 'nullable|boolean',
            'request_callback' => 'nullable|boolean',
        ]);

        if (! empty($data['orden_id'])) {
            $orden = $cliente->relatedOrdersQuery()
                ->whereKey($data['orden_id'])
                ->firstOrFail();
            $data['orden_id'] = $orden->id;
        }

        $incident = ClientePortalIncident::create([
            'cliente_id' => $cliente->id,
            'orden_id' => $data['orden_id'] ?? null,
            'type' => $data['type'] ?? (($data['urgent'] ?? false) ? 'urgencia' : 'falla'),
            'title' => $data['title'],
            'description' => $data['description'],
            'piece_name' => $data['piece_name'] ?? null,
            'part_number' => $data['part_number'] ?? null,
            'serial_number' => $data['serial_number'] ?? null,
            'priority' => $data['priority'] ?? (($data['urgent'] ?? false) ? 'alta' : 'media'),
            'urgent' => (bool) ($data['urgent'] ?? false),
            'request_callback' => (bool) ($data['request_callback'] ?? false),
            'status' => 'reportada',
        ]);

        return response()->json([
            'success' => true,
            'message' => $incident->urgent
                ? 'Urgencia enviada al equipo operativo.'
                : 'Falla reportada correctamente.',
            'data' => [
                'id' => $incident->id,
                'status' => $incident->status,
                'priority' => $incident->priority,
                'urgent' => $incident->urgent,
            ],
        ], 201);
    }

    private function currentCliente(Request $request): Cliente
    {
        $user = $request->user();

        abort_unless($user instanceof Cliente || $user?->rol === 'cliente', 403, 'Este modulo es solo para clientes.');

        return $user instanceof Cliente
            ? $user
            : Cliente::query()->findOrFail($user->id);
    }
}
