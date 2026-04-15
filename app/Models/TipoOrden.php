<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoOrden extends Model
{
    protected $table = 'tipo_ordenes';

    protected $fillable = [
        'nombre',
        'codigo',
    ];

    public function ordenes(): HasMany
    {
        return $this->hasMany(Orden::class, 'tipo_id');
    }
}
