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
        $imageUrl = $this->resolveImageUrl($this->imagen_path);

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
            'imagen_archivo' => PublicStoragePath::normalize($this->imagen_path),
            'imagen_path' => $imageUrl,
            'foto' => $imageUrl,
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

    private function resolveImageUrl(?string $path): ?string
    {
        $normalizedPath = PublicStoragePath::normalize($path);

        if ($normalizedPath === null) {
            return null;
        }

        if (PublicStoragePath::isExternalUrl($normalizedPath) || Str::startsWith($normalizedPath, ['http://', 'https://'])) {
            return $normalizedPath;
        }

        if (! Storage::disk('public')->exists($normalizedPath)) {
            return null;
        }

        return Storage::disk('public')->url($normalizedPath);
    }
}
