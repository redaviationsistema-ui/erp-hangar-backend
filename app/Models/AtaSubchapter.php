<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(AtaChapter::class, 'ata_chapter_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(AtaTaskTemplate::class, 'ata_subchapter_id');
    }

    public function ordenes(): HasMany
    {
        return $this->hasMany(Orden::class, 'ata_subchapter_id');
    }

    public function manualChunks(): HasMany
    {
        return $this->hasMany(ManualChunk::class, 'ata_subchapter_id');
    }
}
