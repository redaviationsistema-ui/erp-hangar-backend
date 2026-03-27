<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medicion extends Model
{
    protected $table = 'mediciones';

    protected $fillable = [
        'orden_id',
        'item',
        'tecnico',
        'descripcion',
        'manual_od',
        'manual_id',
        'resultado_od',
        'resultado_id',
        'imagen_path',
        'observaciones',
        'parametro',
        'valor',
        'unidad',
    ];

    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }
}
