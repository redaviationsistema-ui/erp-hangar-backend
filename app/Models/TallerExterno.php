<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TallerExterno extends Model
{
    protected $fillable = [
        'orden_id',
        'proveedor',
        'trabajo_realizado',
        'costo'
    ];

    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }
}
