<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aeronave extends Model
{
    protected $fillable = [
        'cliente',
        'matricula',
        'fabricante',
        'modelo',
        'numero_serie',
        'estado',
        'notas',
    ];

    public function motores()
    {
        return $this->hasMany(Motor::class);
    }

    public function ordenes()
    {
        return $this->hasManyThrough(Orden::class, Motor::class, 'aeronave_id', 'motor_id');
    }

    public function manuales()
    {
        return $this->hasMany(Manual::class);
    }
}
