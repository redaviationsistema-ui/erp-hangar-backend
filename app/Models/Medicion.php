<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medicion extends Model
{
    protected $table = 'mediciones'; // 👈 SOLUCIÓN

    protected $fillable = [
        'orden_id',
        'parametro',
        'valor',
        'unidad'
    ];

    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }
}