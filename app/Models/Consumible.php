<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consumible extends Model
{
    protected $fillable = [
        'orden_id',
        'nombre',
        'cantidad'
    ];

    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }
}