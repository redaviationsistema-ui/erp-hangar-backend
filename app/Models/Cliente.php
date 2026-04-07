<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'nombre_comercial',
        'razon_social',
        'rfc',
        'contacto_nombre',
        'email',
        'telefono',
        'ciudad',
        'estatus',
        'notas',
    ];
}
