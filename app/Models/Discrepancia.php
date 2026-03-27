<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discrepancia extends Model
{
    protected $appends = [
        'foto',
    ];

    protected $fillable = [
        'orden_id',
        'item',
        'descripcion',
        'accion_correctiva',
        'status',
        'inspector',
        'fecha_inicio',
        'fecha_termino',
        'horas_hombre',
        'imagen_path',
        'componente_numero_parte_off',
        'componente_numero_serie_off',
        'componente_numero_parte_on',
        'componente_numero_serie_on',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_termino' => 'date',
            'horas_hombre' => 'decimal:2',
        ];
    }

    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }

    public function getFotoAttribute(): ?string
    {
        return $this->imagen_path;
    }
}
