<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TallerExterno extends Model
{
    protected $fillable = [
        'orden_id',
        'item',
        'proveedor',
        'tarea',
        'cantidad',
        'sub_componente',
        'numero_parte',
        'numero_serie',
        'foto_path',
        'observaciones',
        'certificado',
        'envio_a',
        'recepcion',
        'trabajo_realizado',
        'costo',
        'precio_venta',
    ];

    protected function casts(): array
    {
        return [
            'costo' => 'decimal:2',
            'precio_venta' => 'decimal:2',
        ];
    }

    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }
}
