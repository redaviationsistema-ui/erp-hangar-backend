<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtaChapter extends Model
{
    protected $fillable = [
        'codigo',
        'descripcion',
    ];

    public function subchapters()
    {
        return $this->hasMany(AtaSubchapter::class);
    }

    public function ordenes()
    {
        return $this->hasMany(Orden::class);
    }

    public function manualChunks()
    {
        return $this->hasMany(ManualChunk::class, 'ata_chapter_id');
    }
}
