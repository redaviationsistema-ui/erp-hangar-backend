<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AtaChapter extends Model
{
    protected $fillable = [
        'codigo',
        'descripcion',
    ];

    public function subchapters(): HasMany
    {
        return $this->hasMany(AtaSubchapter::class);
    }

    public function ordenes(): HasMany
    {
        return $this->hasMany(Orden::class);
    }

    public function manualChunks(): HasMany
    {
        return $this->hasMany(ManualChunk::class, 'ata_chapter_id');
    }
}
