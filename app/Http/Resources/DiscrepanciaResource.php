<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DiscrepanciaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'orden_id' => $this->orden_id,
            'item' => $this->item,
            'descripcion' => $this->descripcion,
            'accion_correctiva' => $this->accion_correctiva,
            'status' => $this->status,
            'inspector' => $this->inspector,
            'fecha_inicio' => $this->fecha_inicio?->toDateString(),
            'fecha_termino' => $this->fecha_termino?->toDateString(),
            'horas_hombre' => $this->horas_hombre,
            'imagen_archivo' => $this->imagen_path,
            'imagen_path' => $this->imagen_path
                ? Storage::disk('public')->url($this->imagen_path)
                : null,
            'foto' => $this->imagen_path
                ? Storage::disk('public')->url($this->imagen_path)
                : null,
            'componente_numero_parte_off' => $this->componente_numero_parte_off,
            'componente_numero_serie_off' => $this->componente_numero_serie_off,
            'componente_numero_parte_on' => $this->componente_numero_parte_on,
            'componente_numero_serie_on' => $this->componente_numero_serie_on,
            'observaciones' => $this->observaciones,
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
