<?php

namespace App\Services;

use App\Models\Area;
use App\Models\Orden;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class OrdenService
{
    private ?bool $hasAreaColumn = null;

    public function generarFolio(Area $area): array
    {
        $anioCompleto = Carbon::now()->year;
        $anioCorto = Carbon::now()->format('y');
        $folioPrefix = sprintf('CESA-%s%s-', strtoupper($area->codigo), $anioCorto);

        $query = Orden::query()->where('anio', $anioCompleto);

        if ($this->hasAreaColumn()) {
            $query->where('area_id', $area->id);
        } else {
            $query->where('folio', 'like', $folioPrefix . '%');
        }

        $ultimo = $query->max('consecutivo');

        $nuevo = ($ultimo ?? 0) + 1;

        return [
            'folio' => sprintf(
                'CESA-%s%s-%03d',
                strtoupper($area->codigo),
                $anioCorto,
                $nuevo
            ),
            'consecutivo' => $nuevo,
            'anio' => $anioCompleto,
        ];
    }

    private function hasAreaColumn(): bool
    {
        return $this->hasAreaColumn ??= Schema::hasColumn('ordenes', 'area_id');
    }
}
