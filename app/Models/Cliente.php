<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Cliente extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'nombre_comercial',
        'razon_social',
        'rfc',
        'contacto_nombre',
        'email',
        'password',
        'telefono',
        'ciudad',
        'estatus',
        'notas',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
