<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $cliente
 * @property string $matricula
 * @property string|null $fabricante
 * @property string|null $modelo
 * @property string|null $numero_serie
 * @property string|null $estado
 * @property string|null $notas
 * @property-read int|null $motores_count
 */
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
