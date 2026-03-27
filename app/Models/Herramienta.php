<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Herramienta extends Model
{
    protected $fillable = [
        'orden_id',
        'item',
        'solicitante_fecha',
        'nombre',
        'descripcion',
        'cantidad',
        'numero_parte',
        'status',
        'certificado_conformidad',
        'area_procedencia',
        'recibe_fecha',
        'costo_total',
        'precio_venta',
    ];

    protected function casts(): array
    {
        return [
            'solicitante_fecha' => 'date',
            'recibe_fecha' => 'date',
            'costo_total' => 'decimal:2',
            'precio_venta' => 'decimal:2',
        ];
    }

    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }
}
