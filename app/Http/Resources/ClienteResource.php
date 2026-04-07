<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClienteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
