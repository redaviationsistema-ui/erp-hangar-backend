<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $displayRole = $this->displayRole();
        $estado = $this->estado ?: 'Activo';

        return [
            'id' => $this->id,
            'name' => $this->name,
            'nombre' => $this->name,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'puesto' => $this->puesto,
            'rol' => $displayRole,
            'rol_nombre' => $displayRole,
            'rol_base' => $this->rol,
            'area_id' => $this->area_id,
            'area_codigo' => $this->area?->codigo ?? 'GENERAL',
            'area_nombre' => $this->area?->nombre ?? 'GENERAL',
            'estado' => $estado,
            'activo' => strcasecmp($estado, 'Activo') === 0,
            'permisos' => $this->normalizedPermissions(),
            'area' => [
                'id' => $this->area?->id ?? 0,
                'codigo' => $this->area?->codigo ?? 'GENERAL',
                'nombre' => $this->area?->nombre ?? 'GENERAL',
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function displayRole(): string
    {
        $original = strtolower(trim((string) ($this->rol_nombre ?: '')));
        if ($original !== '') {
            return $original;
        }

        return match (strtolower(trim((string) $this->rol))) {
            'admin' => 'admin',
            'supervisor' => 'supervisor',
            'administracion' => 'administracion',
            default => 'tecnico_area',
        };
    }

    private function normalizedPermissions(): array
    {
        $permissions = $this->permisos;

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
}
