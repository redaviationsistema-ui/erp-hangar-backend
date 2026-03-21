<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ndt extends Model
{
    protected $table = 'ndt';
    protected $fillable = [
        'orden_id',
        'tipo_prueba',
        'resultado'
    ];

    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }
}