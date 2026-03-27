<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ndt extends Model
{
    protected $table = 'ndt';

    protected $fillable = [
        'orden_id',
        'item',
        'tipo_prueba',
        'cantidad',
        'sub_componente',
        'numero_parte',
        'numero_serie',
        'evidencia_path',
        'seccion_manual',
        'certificado',
        'envio_a',
        'recepcion',
        'costo_total',
        'precio_venta',
        'resultado',
    ];

    protected function casts(): array
    {
        return [
            'costo_total' => 'decimal:2',
            'precio_venta' => 'decimal:2',
        ];
    }

    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }
}
