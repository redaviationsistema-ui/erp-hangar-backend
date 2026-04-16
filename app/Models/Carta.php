<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carta extends Model
{
    protected $fillable = [
        'orden_id',
        'item',
        'tarea',
        'titulo',
        'remanente',
        'completado',
        'siguiente',
        'notas',
        'accion_correctiva',
        'descripcion_componente',
        'cantidad',
        'numero_parte',
        'numero_serie_removido',
        'numero_serie_instalado',
        'observaciones',
        'fecha_termino',
        'horas_labor',
        'auxiliar',
        'tecnico',
        'inspector',
    ];

    protected function casts(): array
    {
        return [
            'fecha_termino' => 'date',
            'horas_labor' => 'decimal:2',
        ];
    }

    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }
}

