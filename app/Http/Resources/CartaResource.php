<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'orden_id' => $this->orden_id,
            'item' => $this->item,
            'tarea' => $this->tarea,
            'titulo' => $this->titulo,
            'remanente' => $this->remanente,
            'completado' => $this->completado,
            'siguiente' => $this->siguiente,
            'notas' => $this->notas,
            'accion_correctiva' => $this->accion_correctiva,
            'descripcion_componente' => $this->descripcion_componente,
            'cantidad' => $this->cantidad,
            'numero_parte' => $this->numero_parte,
            'numero_serie_removido' => $this->numero_serie_removido,
            'numero_serie_instalado' => $this->numero_serie_instalado,
            'observaciones' => $this->observaciones,
            'fecha_termino' => $this->fecha_termino?->toDateString(),
            'horas_labor' => $this->horas_labor,
            'auxiliar' => $this->auxiliar,
            'tecnico' => $this->tecnico,
            'inspector' => $this->inspector,
            'orden' => $this->whenLoaded('orden', fn () => [
                'id' => $this->orden?->id,
                'folio' => $this->orden?->folio,
                'fecha' => $this->orden?->fecha?->toDateString(),
                'estado' => $this->orden?->estado,
                'cliente' => $this->orden?->cliente,
                'matricula' => $this->orden?->matricula,
                'descripcion' => $this->orden?->descripcion,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

