<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Motor extends Model
{
    protected $table = 'motores';

    protected $fillable = [
        'aeronave_id',
        'posicion',
        'fabricante',
        'modelo',
        'numero_parte',
        'numero_serie',
        'tiempo_total',
        'ciclos_totales',
        'estado',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'tiempo_total' => 'decimal:2',
            'ciclos_totales' => 'decimal:2',
        ];
    }

    public function aeronave()
    {
        return $this->belongsTo(Aeronave::class);
    }

    public function ordenes()
    {
        return $this->hasMany(Orden::class)->latest('fecha')->latest('id');
    }
}
