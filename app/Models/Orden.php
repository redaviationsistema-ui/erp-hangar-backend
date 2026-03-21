<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// 👇 IMPORTAR MODELOS
use App\Models\TipoOrden;
use App\Models\OrdenDetalle;
use App\Models\User;
use App\Models\Tarea;
use App\Models\Discrepancia;
use App\Models\Refaccion;
use App\Models\Consumible;
use App\Models\Herramienta;
use App\Models\Ndt;
use App\Models\TallerExterno;
use App\Models\Medicion;

class Orden extends Model
{
    // 🔥 IMPORTANTE (ARREGLA TU ERROR)
    protected $table = 'ordenes';

    protected $fillable = [
        'tipo_id',
        'user_id',
        'folio',
        'descripcion',
        'estado',
    ];

    // 🔥 RELACIÓN: tipo
    public function tipo()
    {
        return $this->belongsTo(TipoOrden::class, 'tipo_id');
    }

    // 🔥 RELACIÓN: detalles
    public function detalles()
    {
        return $this->hasMany(OrdenDetalle::class, 'orden_id');
    }

    // 🔥 RELACIÓN: usuario (TE FALTABA)
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // 🔥 RELACIONES
    public function tareas()
    {
        return $this->hasMany(Tarea::class, 'orden_id');
    }

    public function discrepancias()
    {
        return $this->hasMany(Discrepancia::class, 'orden_id');
    }

    public function refacciones()
    {
        return $this->hasMany(Refaccion::class, 'orden_id');
    }

    public function consumibles()
    {
        return $this->hasMany(Consumible::class, 'orden_id');
    }

    public function herramientas()
    {
        return $this->hasMany(Herramienta::class, 'orden_id');
    }

    public function ndt()
    {
        return $this->hasMany(Ndt::class, 'orden_id');
    }

    public function talleresExternos()
    {
        return $this->hasMany(TallerExterno::class, 'orden_id');
    }

    public function mediciones()
    {
        return $this->hasMany(Medicion::class, 'orden_id');
    }
    // FUNCION PARA GENERAR FOLIOS
    public static function generarFolio($area_id)
    {
        $area = Area::find($area_id);

        $anio = now()->year;

        $ultimo = self::where('area_id', $area_id)
            ->where('anio', $anio)
            ->max('consecutivo');

        $nuevoConsecutivo = $ultimo ? $ultimo + 1 : 1;

        $folio = sprintf(
            "CESA-%s-%s-%03d",
            $area->codigo,
            $anio,
            $nuevoConsecutivo
        );

        return [
            'folio' => $folio,
            'anio' => $anio,
            'consecutivo' => $nuevoConsecutivo
        ];
    }
}