<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refaccion extends Model
{
    protected $table = 'refacciones'; // ✅ CORRECTO

    protected $fillable = [
        'orden_id',
        'nombre',
        'cantidad'
    ];

    public function orden()
    {
        return $this->belongsTo(Orden::class, 'orden_id');
    }
}