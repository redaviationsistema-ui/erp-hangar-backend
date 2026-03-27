<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtaTaskTemplate extends Model
{
    protected $fillable = [
        'ata_subchapter_id',
        'area_id',
        'titulo',
        'descripcion',
        'tipo',
        'tiempo_estimado_min',
        'prioridad',
    ];

    public function subchapter()
    {
        return $this->belongsTo(AtaSubchapter::class, 'ata_subchapter_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
}
