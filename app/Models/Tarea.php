<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tarea extends Model
{
    protected $fillable = [
        'orden_id',
        'titulo',
        'descripcion',
        'orden'
    ];

    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }
}