<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refaccion extends Model
{
    protected $table = 'refacciones';

    protected $fillable = [
        'orden_id',
        'item',
        'solicitante_fecha',
        'solicitante_nombre',
        'nombre',
        'descripcion',
        'cantidad',
        'numero_parte',
        'status',
        'certificado_conformidad',
        'certificado_conformidad_imagen',
        'area_procedencia',
        'recibe_fecha',
        'recibe_nombre',
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
        return $this->belongsTo(Orden::class, 'orden_id');
    }
}
