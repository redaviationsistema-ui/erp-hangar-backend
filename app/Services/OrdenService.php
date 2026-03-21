<?php

namespace App\Services;

// 🔥 IMPORTS IMPORTANTES
use App\Models\Orden;
use Carbon\Carbon;

class OrdenService
{
    public function generarFolio($tipo)
    {
        // 📅 Año actual
        $anio = Carbon::now()->year;

        // 🔍 Obtener último consecutivo
        $ultimo = Orden::where('tipo_id', $tipo->id)
            ->where('anio', $anio)
            ->max('consecutivo');

        // 🧠 Si no hay registros, empieza en 1
        $nuevo = ($ultimo ?? 0) + 1;

        // 🧾 Generar folio
        $folio = "CESA-{$tipo->codigo}-{$anio}-" . str_pad($nuevo, 4, '0', STR_PAD_LEFT);

        return [
            'folio' => $folio,
            'consecutivo' => $nuevo,
            'anio' => $anio
        ];
    }
}