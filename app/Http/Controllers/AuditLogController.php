<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $perPage = max(1, min($request->integer('per_page', 25), 100));

        $logs = AuditLog::query()
            ->with([
                'user:id,name,email',
                'orden:id,folio,estado',
            ])
            ->when($request->filled('entity_type'), fn ($query) => $query->where('entity_type', $request->string('entity_type')))
            ->when($request->filled('entity_id'), fn ($query) => $query->where('entity_id', $request->integer('entity_id')))
            ->when($request->filled('order_id'), fn ($query) => $query->where('order_id', $request->integer('order_id')))
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
            ->when($request->filled('action'), fn ($query) => $query->where('action', $request->string('action')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = trim((string) $request->string('search'));

                $query->where(function ($nested) use ($term) {
                    $nested
                        ->where('description', 'like', "%{$term}%")
                        ->orWhere('entity_label', 'like', "%{$term}%")
                        ->orWhere('entity_type', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Bitacora de auditoria obtenida correctamente.',
            'data' => $logs->getCollection()->map(function (AuditLog $log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'entity_type' => $log->entity_type,
                    'entity_id' => $log->entity_id,
                    'entity_label' => $log->entity_label,
                    'order_id' => $log->order_id,
                    'description' => $log->description,
                    'old_values' => $log->old_values,
                    'new_values' => $log->new_values,
                    'context' => $log->context,
                    'ip_address' => $log->ip_address,
                    'user_agent' => $log->user_agent,
                    'occurred_at' => optional($log->occurred_at)->toIso8601String(),
                    'user' => $log->user ? [
                        'id' => $log->user->id,
                        'name' => $log->user->name,
                        'email' => $log->user->email,
                    ] : null,
                    'orden' => $log->orden ? [
                        'id' => $log->orden->id,
                        'folio' => $log->orden->folio,
                        'estado' => $log->orden->estado,
                    ] : null,
                ];
            })->values()->all(),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
                'last_page' => $logs->lastPage(),
            ],
        ]);
    }
}
