<?php

namespace App\Http\Resources;

use App\Models\PersonalTecnico;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PersonalTecnico */
class PersonalTecnicoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $estado = $this->estado ?: 'Activo';

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'usuario_id' => $this->user_id,
            'nombre' => $this->nombre,
            'name' => $this->nombre,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'puesto' => $this->puesto,
            'especialidad' => $this->especialidad,
            'tipo' => $this->tipo,
            'rol' => $this->tipo,
            'area_id' => $this->area_id,
            'area_codigo' => $this->area?->codigo ?? 'GENERAL',
            'area_nombre' => $this->area?->nombre ?? 'GENERAL',
            'estado' => $estado,
            'activo' => strcasecmp($estado, 'Activo') === 0,
            'notas' => $this->notas,
            'area' => [
                'id' => $this->area?->id ?? 0,
                'codigo' => $this->area?->codigo ?? 'GENERAL',
                'nombre' => $this->area?->nombre ?? 'GENERAL',
            ],
            'usuario' => $this->whenLoaded('usuario', fn () => [
                'id' => $this->usuario?->id,
                'nombre' => $this->usuario?->name,
                'email' => $this->usuario?->email,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
