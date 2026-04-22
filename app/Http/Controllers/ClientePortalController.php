<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ClientePortalIncident;
use App\Models\ClientePortalInvoice;
use App\Models\ClientePortalPaymentMethod;
use App\Models\ClientePortalPaymentSelection;
use App\Models\Orden;
use App\Models\Refaccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClientePortalController extends Controller
{
    public function dashboard(Request $request)
    {
        $cliente = $this->currentCliente($request);

        return response()->json([
            'success' => true,
            'data' => $this->cachedDashboardPayload($cliente),
        ]);
    }

    public function adminDashboard(Request $request, Cliente $cliente)
    {
        $this->authorizeAdminManagement($request);

        return response()->json([
            'success' => true,
            'data' => $this->cachedDashboardPayload($cliente),
        ]);
    }

    public function selectPaymentMethod(Request $request)
    {
        return $this->storePaymentSelection($request, $this->currentCliente($request));
    }

    public function adminSelectPaymentMethod(Request $request, Cliente $cliente)
    {
        $this->authorizeAdminManagement($request);

        return $this->storePaymentSelection($request, $cliente);
    }

    public function adminStoreInvoice(Request $request, Cliente $cliente)
    {
        $this->authorizeAdminManagement($request);

        $invoice = ClientePortalInvoice::create($this->invoicePayload($request, $cliente));
        $this->ensureInvoiceDocument($invoice, $cliente);
        $invoice->load(['orden:id,folio', 'latestPaymentSelection.paymentMethod:id,name']);
        $this->bustDashboardCache();

        return response()->json([
            'success' => true,
            'message' => 'Factura registrada correctamente en el portal del cliente.',
            'data' => $this->transformInvoice($invoice),
        ], 201);
    }

    public function adminUpdateInvoice(Request $request, Cliente $cliente, ClientePortalInvoice $invoice)
    {
        $this->authorizeAdminManagement($request);
        abort_unless($invoice->cliente_id === $cliente->id, 404, 'No se encontro la factura del cliente.');

        $invoice->fill($this->invoicePayload($request, $cliente, updating: true));
        $invoice->save();
        $this->ensureInvoiceDocument($invoice, $cliente, force: ! $request->filled('pdf_url'));
        $invoice->load(['orden:id,folio', 'latestPaymentSelection.paymentMethod:id,name']);
        $this->bustDashboardCache();

        return response()->json([
            'success' => true,
            'message' => 'Factura actualizada correctamente.',
            'data' => $this->transformInvoice($invoice),
        ]);
    }

    public function adminDeleteInvoice(Request $request, Cliente $cliente, ClientePortalInvoice $invoice)
    {
        $this->authorizeAdminManagement($request);
        abort_unless($invoice->cliente_id === $cliente->id, 404, 'No se encontro la factura del cliente.');

        $invoice->delete();
        $this->bustDashboardCache();

        return response()->json([
            'success' => true,
            'message' => 'Factura eliminada correctamente.',
        ]);
    }

    public function reportIncident(Request $request)
    {
        return $this->storeIncident($request, $this->currentCliente($request));
    }

    public function adminReportIncident(Request $request, Cliente $cliente)
    {
        $this->authorizeAdminManagement($request);

        return $this->storeIncident($request, $cliente);
    }

    private function currentCliente(Request $request): Cliente
    {
        $user = $request->user();

        abort_unless($user instanceof Cliente || $user?->rol === 'cliente', 403, 'Este modulo es solo para clientes.');

        return $user instanceof Cliente
            ? $user
            : Cliente::query()->findOrFail($user->id);
    }

    private function buildDashboardPayload(Cliente $cliente): array
    {
        $cliente->loadMissing([
            'ordenesAsignadas:id',
            'otAsignadaOrden:id,area_id,folio,estado,descripcion,trabajo_descripcion,matricula,aeronave_modelo,fecha_termino',
            'otAsignadaOrden.area:id,codigo,nombre',
        ]);

        $ordersBaseQuery = $cliente->relatedOrdersQuery();
        $ordersCount = (clone $ordersBaseQuery)->count();
        $openOrdersCount = (clone $ordersBaseQuery)
            ->whereIn('ordenes.estado', ['abierta', 'en proceso', 'proceso'])
            ->count();
        $pendingInvoicesCount = ClientePortalInvoice::query()
            ->where('cliente_id', $cliente->id)
            ->whereNotIn('status', ['pagada', 'cancelada'])
            ->count();
        $totalIncidentsCount = ClientePortalIncident::query()
            ->where('cliente_id', $cliente->id)
            ->count();
        $urgentIncidentsCount = ClientePortalIncident::query()
            ->where('cliente_id', $cliente->id)
            ->where('urgent', true)
            ->count();

        $orders = (clone $ordersBaseQuery)
            ->select([
                'ordenes.id',
                'ordenes.area_id',
                'ordenes.folio',
                'ordenes.estado',
                'ordenes.descripcion',
                'ordenes.trabajo_descripcion',
                'ordenes.matricula',
                'ordenes.aeronave_modelo',
                'ordenes.fecha_inicio',
                'ordenes.fecha_termino',
                'ordenes.fecha',
            ])
            ->withCount('discrepancias')
            ->latest('fecha')
            ->latest('id')
            ->limit(25)
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

        $pendingParts = $this->pendingPartsForCustomer($cliente);

        $otAsignada = $cliente->otAsignadaOrden;

        return [
            'customer' => [
                'id' => $cliente->id,
                'name' => $cliente->contacto_nombre ?: $cliente->nombre_comercial,
                'business_name' => $cliente->nombre_comercial,
                'email' => $cliente->email,
                'phone' => $cliente->telefono,
                'city' => $cliente->ciudad,
                'status' => $cliente->estatus ?: 'Activo',
                'ot_asignadas_ids' => $cliente->ordenesAsignadas->pluck('id')->values(),
            ],
            'metrics' => [
                'orders_count' => $ordersCount,
                'open_orders_count' => $openOrdersCount,
                'pending_invoices_count' => $pendingInvoicesCount,
                'incidents_count' => $totalIncidentsCount,
                'urgent_incidents_count' => $urgentIncidentsCount,
            ],
            'meta' => [
                'orders_preview_limit' => 25,
                'orders_loaded' => $orders->count(),
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
            'assigned_order' => $otAsignada ? [
                'id' => $otAsignada->id,
                'folio' => $otAsignada->folio,
                'estado' => $otAsignada->estado ?: 'Asignada',
                'descripcion' => $otAsignada->descripcion,
                'trabajo_descripcion' => $otAsignada->trabajo_descripcion,
                'matricula' => $otAsignada->matricula,
                'aeronave_modelo' => $otAsignada->aeronave_modelo,
                'tiempo_entrega_estimado' => optional($otAsignada->fecha_termino)->toDateString(),
                'area' => $otAsignada->area ? [
                    'id' => $otAsignada->area->id,
                    'codigo' => $otAsignada->area->codigo,
                    'nombre' => $otAsignada->area->nombre,
                ] : null,
            ] : null,
            'invoices' => $invoices->map(fn (ClientePortalInvoice $invoice) => [
                ...$this->transformInvoice($invoice),
            ])->values(),
            'pending_parts' => $pendingParts,
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
        ];
    }

    private function pendingPartsForCustomer(Cliente $cliente): array
    {
        $orderIds = $cliente->relatedOrdersQuery()
            ->select('ordenes.id')
            ->pluck('ordenes.id');

        if ($orderIds->isEmpty()) {
            return [];
        }

        return Refaccion::query()
            ->with('orden:id,folio')
            ->whereIn('orden_id', $orderIds)
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '')
                    ->orWhereRaw("lower(status) like '%pendiente%'")
                    ->orWhereRaw("lower(status) like '%autorizar%'")
                    ->orWhereRaw("lower(status) like '%cotiz%'");
            })
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '')
                    ->orWhere(function ($statusQuery) {
                        $statusQuery
                            ->whereRaw("lower(status) not like '%autorizado comprado%'")
                            ->whereRaw("lower(status) not like '%entregado%'")
                            ->whereRaw("lower(status) not like '%cancel%'")
                            ->whereRaw("lower(status) not like '%rechaz%'");
                    });
            })
            ->latest('id')
            ->limit(30)
            ->get()
            ->map(fn (Refaccion $item) => [
                'id' => $item->id,
                'orden_id' => $item->orden_id,
                'orden_folio' => $item->orden?->folio,
                'descripcion' => $item->nombre ?: $item->descripcion,
                'razon' => $item->descripcion,
                'cantidad' => (int) ($item->cantidad ?: 0),
                'numero_parte' => $item->numero_parte,
                'precio' => (float) ($item->precio_venta ?: $item->costo_total ?: 0),
                'currency' => 'MXN',
                'status' => $item->status ?: 'Pendiente por autorizar',
                'fecha_entrega' => optional($item->fecha_entrega)->toDateString(),
            ])
            ->values()
            ->all();
    }

    private function cachedDashboardPayload(Cliente $cliente): array
    {
        return $this->cacheOrFetch(
            $this->dashboardCacheKey($cliente),
            now()->addMinutes(5),
            fn () => $this->buildDashboardPayload($cliente->fresh())
        );
    }

    private function storePaymentSelection(Request $request, Cliente $cliente)
    {
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
        $this->bustDashboardCache();

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

    private function invoicePayload(Request $request, Cliente $cliente, bool $updating = false): array
    {
        $rules = [
            'orden_id' => 'nullable|integer|exists:ordenes,id',
            'folio' => ($updating ? 'sometimes|' : 'required|') . 'string|max:255',
            'concepto' => ($updating ? 'sometimes|' : 'required|') . 'string|max:255',
            'amount_total' => ($updating ? 'sometimes|' : 'required|') . 'numeric|min:0',
            'currency' => 'nullable|string|max:8',
            'status' => 'nullable|string|max:50',
            'issued_at' => 'nullable|date',
            'due_at' => 'nullable|date',
            'pdf_url' => 'nullable|string|max:2048',
            'notes' => 'nullable|string',
        ];

        $data = $request->validate($rules);

        if (array_key_exists('orden_id', $data) && ! empty($data['orden_id'])) {
            $orden = $cliente->relatedOrdersQuery()
                ->whereKey($data['orden_id'])
                ->firstOrFail();
            $data['orden_id'] = $orden->id;
        }

        if (! $updating) {
            $data['cliente_id'] = $cliente->id;
        }

        if (array_key_exists('currency', $data)) {
            $data['currency'] = strtoupper(trim((string) ($data['currency'] ?: 'MXN')));
        } elseif (! $updating) {
            $data['currency'] = 'MXN';
        }

        if (array_key_exists('status', $data)) {
            $data['status'] = trim((string) ($data['status'] ?: 'pendiente'));
        } elseif (! $updating) {
            $data['status'] = 'pendiente';
        }

        return $data;
    }

    private function transformInvoice(ClientePortalInvoice $invoice): array
    {
        return [
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
            'orden_id' => $invoice->orden_id,
            'orden' => $invoice->orden ? [
                'id' => $invoice->orden->id,
                'folio' => $invoice->orden->folio,
            ] : null,
            'selected_payment_method' => $invoice->latestPaymentSelection?->paymentMethod?->name,
        ];
    }

    private function ensureInvoiceDocument(
        ClientePortalInvoice $invoice,
        Cliente $cliente,
        bool $force = false
    ): void {
        if (! $force && filled($invoice->pdf_url)) {
            return;
        }

        $path = 'facturas-clientes/factura-' . $invoice->id . '.pdf';
        Storage::disk('public')->put($path, $this->buildInvoicePdf($invoice, $cliente));

        $invoice->forceFill([
            'pdf_url' => Storage::disk('public')->url($path),
        ])->save();
    }

    private function buildInvoicePdf(ClientePortalInvoice $invoice, Cliente $cliente): string
    {
        $lines = [
            'RED AVIATION',
            'Factura / Documento de cobro',
            '',
            'Folio: ' . $invoice->folio,
            'Cliente: ' . ($cliente->razon_social ?: $cliente->nombre_comercial ?: $cliente->contacto_nombre),
            'RFC: ' . ($cliente->rfc ?: '-'),
            'Contacto: ' . ($cliente->contacto_nombre ?: '-'),
            'Correo: ' . ($cliente->email ?: '-'),
            'Concepto: ' . $invoice->concepto,
            'Total: ' . $invoice->currency . ' ' . number_format((float) $invoice->amount_total, 2),
            'Estatus: ' . ($invoice->status ?: 'pendiente'),
            'Emision: ' . (optional($invoice->issued_at)->toDateString() ?: now()->toDateString()),
            'Vencimiento: ' . (optional($invoice->due_at)->toDateString() ?: '-'),
            'Orden: ' . ($invoice->orden?->folio ?: ($invoice->orden_id ? '#' . $invoice->orden_id : '-')),
            '',
            'Notas: ' . ($invoice->notes ?: '-'),
            '',
            'Este documento se genero desde el portal administrativo.',
        ];

        return $this->minimalPdf($lines);
    }

    private function minimalPdf(array $lines): string
    {
        $content = "BT\n/F1 18 Tf\n72 760 Td\n(RED AVIATION) Tj\n";
        $content .= "/F1 11 Tf\n0 -28 Td\n";

        foreach (array_slice($lines, 1) as $line) {
            $content .= '(' . $this->pdfText($line) . ") Tj\n0 -18 Td\n";
        }

        $content .= "ET\n";
        $objects = [
            "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n",
            "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n",
            "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n",
            "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n",
            "5 0 obj\n<< /Length " . strlen($content) . " >>\nstream\n$content\nendstream\nendobj\n",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        foreach (array_slice($offsets, 1) as $offset) {
            $pdf .= str_pad((string) $offset, 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n$xrefOffset\n%%EOF\n";

        return $pdf;
    }

    private function pdfText(string $text): string
    {
        $text = Str::ascii($text);

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    private function storeIncident(Request $request, Cliente $cliente)
    {
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
        $this->bustDashboardCache();

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

    private function authorizeAdminManagement(Request $request): void
    {
        $user = $request->user();
        $permissions = $this->normalizePermissions($user?->permisos ?? []);

        $allowed = in_array($user?->rol, ['admin', 'supervisor'], true)
            || strcasecmp((string) $user?->email, 'ing@redaviation.com') === 0
            || in_array(strtolower((string) $user?->rol_nombre), ['ingenieria', 'ingeniero', 'engineering', 'engineer', 'ing'], true)
            || in_array('clientes_crud', $permissions, true)
            || in_array('clientes.manage', $permissions, true)
            || in_array('manage_clients', $permissions, true)
            || in_array('usuarios_crud', $permissions, true)
            || in_array('manage_users', $permissions, true);

        abort_unless($allowed, 403, 'No tienes permisos para gestionar clientes.');
    }

    private function normalizePermissions(mixed $permissions): array
    {
        if (is_string($permissions)) {
            $decoded = json_decode($permissions, true);
            $permissions = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($permissions)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn ($permission) => trim((string) $permission),
            $permissions
        )));
    }

    private function dashboardCacheKey(Cliente $cliente): string
    {
        return 'cliente_portal_dashboard:'
            . Cache::get('cliente_portal_dashboard_version', 2)
            . ':dashboard:' . Cache::get('dashboard_cache_version', 1)
            . ':cliente:' . $cliente->id;
    }

    private function bustDashboardCache(): void
    {
        Cache::forever(
            'cliente_portal_dashboard_version',
            (int) Cache::get('cliente_portal_dashboard_version', 1) + 1
        );
    }
}
