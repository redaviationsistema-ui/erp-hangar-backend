<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualChunk extends Model
{
    protected $fillable = [
        'manual_id',
        'ata_chapter_id',
        'ata_subchapter_id',
        'codigo_seccion',
        'titulo',
        'tipo_contenido',
        'pagina_inicio',
        'pagina_fin',
        'orden',
        'resumen',
        'keywords',
        'embedding',
        'texto',
    ];

    protected function casts(): array
    {
        return [
            'keywords' => 'array',
            'embedding' => 'array',
        ];
    }

    public function manual()
    {
        return $this->belongsTo(Manual::class);
    }

    public function ataChapter()
    {
        return $this->belongsTo(AtaChapter::class, 'ata_chapter_id');
    }

    public function ataSubchapter()
    {
        return $this->belongsTo(AtaSubchapter::class, 'ata_subchapter_id');
    }

    public function referencias()
    {
        return $this->hasMany(ManualChunkReferencia::class);
    }
}
