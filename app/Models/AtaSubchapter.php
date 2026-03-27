<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtaSubchapter extends Model
{
    protected $fillable = [
        'ata_chapter_id',
        'codigo',
        'descripcion',
        'intervalo_horas',
        'intervalo_ciclos',
        'intervalo_dias',
        'tipo_mantenimiento',
    ];

    public function chapter()
    {
        return $this->belongsTo(AtaChapter::class, 'ata_chapter_id');
    }

    public function tasks()
    {
        return $this->hasMany(AtaTaskTemplate::class, 'ata_subchapter_id');
    }

    public function ordenes()
    {
        return $this->hasMany(Orden::class, 'ata_subchapter_id');
    }
}
