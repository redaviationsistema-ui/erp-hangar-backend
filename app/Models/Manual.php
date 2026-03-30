<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manual extends Model
{
    protected $table = 'manuales';

    protected $fillable = [
        'aeronave_id',
        'nombre',
        'tipo_manual',
        'fabricante',
        'aeronave_modelo',
        'revision',
        'idioma',
        'estado',
        'archivo_path',
        'vigente_desde',
        'vigente_hasta',
        'descripcion',
    ];

    protected function casts(): array
    {
        return [
            'vigente_desde' => 'date',
            'vigente_hasta' => 'date',
        ];
    }

    public function aeronave()
    {
        return $this->belongsTo(Aeronave::class);
    }

    public function chunks()
    {
        return $this->hasMany(ManualChunk::class);
    }
}
