<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discrepancia extends Model
{
    protected $fillable = [
        'orden_id',
        'descripcion',
        'accion_correctiva',
        'status'
    ];

    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }
}