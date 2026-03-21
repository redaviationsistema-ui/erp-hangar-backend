<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrdenResource extends JsonResource
{
    /**
     * Transformar la orden a JSON limpio
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'folio' => $this->folio,
            'descripcion' => $this->descripcion,
            'estado' => $this->estado,

            'tipo' => [
                'id' => $this->tipo?->id,
                'nombre' => $this->tipo?->nombre,
            ],

            'usuario' => [
                'id' => $this->usuario?->id,
                'nombre' => $this->usuario?->name,
            ],

            // 🔥 RELACIONES
            'tareas' => $this->tareas,
            'discrepancias' => $this->discrepancias,
            'refacciones' => $this->refacciones,
            'consumibles' => $this->consumibles,
            'herramientas' => $this->herramientas,
            'ndt' => $this->ndt,
            'talleres_externos' => $this->talleresExternos,
            'mediciones' => $this->mediciones,

            'created_at' => $this->created_at,
        ];
    }
}