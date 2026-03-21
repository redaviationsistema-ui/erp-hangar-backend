<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Herramienta extends Model
{
    protected $fillable = [
        'orden_id',
        'nombre',
        'descripcion'
    ];

    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }
}