<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tarea extends Model
{
    protected $appends = [
        'foto',
    ];

    protected $fillable = [
        'orden_id',
        'area_id',
        'ata_task_template_id',
        'titulo',
        'descripcion',
        'orden',
        'tipo',
        'prioridad',
        'tiempo_estimado_min',
        'estado',
        'tecnico',
        'foto_path',
    ];

    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function plantillaAta()
    {
        return $this->belongsTo(AtaTaskTemplate::class, 'ata_task_template_id');
    }

    public function getFotoAttribute(): ?string
    {
        return $this->foto_path;
    }
}
