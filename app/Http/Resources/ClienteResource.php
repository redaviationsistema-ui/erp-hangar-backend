<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class ClienteResource extends JsonResource
{
    public function __construct(mixed $resource)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $otAsignada = $this->whenLoaded('otAsignadaOrden');
        $ordenesAsignadasPreview = $this->relationLoaded('ordenesAsignadasPreview')
            ? $this->getRelation('ordenesAsignadasPreview')
            : null;
        $ordenesAsignadas = $ordenesAsignadasPreview ?? ($this->whenLoaded('ordenesAsignadas')
            ? $this->ordenesAsignadas
            : new Collection());
        $relatedOrders = $ordenesAsignadas
            ->sortByDesc('id')
            ->take(10)
            ->values();

        if ($relatedOrders->isEmpty() && $otAsignada) {
            $relatedOrders = collect([$otAsignada]);
        }

        if ($relatedOrders->isEmpty() && $request->boolean('include_order_preview')) {
            $relatedOrders = $this->relatedOrdersQuery()
                ->select(['ordenes.id', 'ordenes.area_id', 'ordenes.folio', 'ordenes.estado', 'ordenes.descripcion', 'ordenes.matricula'])
                ->latest('id')
                ->limit(10)
                ->get();
        }

        $ordersCount = $this->whenCounted('ordenesAsignadas');
        $ordersCount = is_numeric($ordersCount)
            ? (int) $ordersCount
            : $ordenesAsignadas->count();

        if ($ordersCount === 0 && $request->boolean('include_order_preview')) {
            $ordersCount = (int) $this->relatedOrdersQuery()->count();
        }

        return [
            'id' => $this->id,
            'nombre_comercial' => $this->nombre_comercial,
            'nombre' => $this->nombre_comercial,
            'razon_social' => $this->razon_social,
            'rfc' => $this->rfc,
            'contacto_nombre' => $this->contacto_nombre,
            'contacto' => [
                'nombre' => $this->contacto_nombre,
                'email' => $this->email,
                'telefono' => $this->telefono,
            ],
            'email' => $this->email,
            'telefono' => $this->telefono,
            'ciudad' => $this->ciudad,
            'estatus' => $this->estatus ?: 'Activo',
            'notas' => $this->notas,
            'ot_asignada_id' => $this->ot_asignada_id,
            'ot_asignada' => $otAsignada?->folio,
            'ot_asignadas_ids' => $ordenesAsignadas->pluck('id')->values(),
            'contrasena' => $this->contrasena_portal,
            'ordenes_trabajo_count' => $ordersCount,
            'ordenes_trabajo' => $relatedOrders->map(fn ($orden) => [
                'id' => $orden->id,
                'folio' => $orden->folio,
                'estado' => $orden->estado,
                'descripcion' => $orden->descripcion,
                'matricula' => $orden->matricula,
                'area_nombre' => $orden->area?->nombre,
                'area_codigo' => $orden->area?->codigo,
            ])->values(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
