<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClienteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $otAsignada = $this->whenLoaded('otAsignadaOrden');
        $relatedOrders = $this->relatedOrdersQuery()
            ->latest('id')
            ->limit(10)
            ->get();

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
            'contrasena' => $this->contrasena_portal,
            'ordenes_trabajo_count' => $this->relatedOrdersQuery()->count(),
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
