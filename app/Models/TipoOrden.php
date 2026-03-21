<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoOrden extends Model
{
    protected $table = 'tipo_ordenes'; // 🔥 SOLUCIÓN

    protected $fillable = [
        'nombre',
        'codigo'
    ];
}