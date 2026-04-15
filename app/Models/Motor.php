<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $aeronave_id
 * @property string|null $posicion
 * @property string|null $fabricante
 * @property string|null $modelo
 * @property string|null $numero_parte
 * @property string $numero_serie
 * @property float|null $tiempo_total
 * @property float|null $ciclos_totales
 * @property string|null $estado
 * @property string|null $notas
 * @property int|null $ordenes_count
 * @property Aeronave|null $aeronave
 */
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

    public function aeronave(): BelongsTo
    {
        return $this->belongsTo(Aeronave::class);
    }

    public function ordenes(): HasMany
    {
        return $this->hasMany(Orden::class)->latest('fecha')->latest('id');
    }
}
