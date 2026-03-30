<?php

namespace App\Http\Resources;

use App\Support\PublicStoragePath;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DiscrepanciaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $image = $this->resolveImage($this->imagen_path);

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
            'imagen_archivo' => $image['archivo'],
            'imagen_path' => $image['url'],
            'imagen_url' => $image['url'],
            'foto' => $image['url'],
            'foto_url' => $image['url'],
            'evidencia_url' => $image['url'],
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

    private function resolveImage(?string $path): array
    {
        $normalizedPath = PublicStoragePath::normalize($path);

        if ($normalizedPath === null) {
            return [
                'archivo' => null,
                'url' => null,
            ];
        }

        if (PublicStoragePath::isExternalUrl($normalizedPath) || Str::startsWith($normalizedPath, ['http://', 'https://'])) {
            return [
                'archivo' => $normalizedPath,
                'url' => $normalizedPath,
            ];
        }

        if (! Storage::disk('public')->exists($normalizedPath)) {
            return [
                'archivo' => null,
                'url' => null,
            ];
        }

        return [
            'archivo' => $normalizedPath,
            'url' => PublicStoragePath::url($normalizedPath),
        ];
    }
}

