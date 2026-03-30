<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualChunkReferencia extends Model
{
    protected $table = 'manual_chunk_referencias';

    protected $fillable = [
        'manual_chunk_id',
        'tipo',
        'valor',
    ];

    public function chunk()
    {
        return $this->belongsTo(ManualChunk::class, 'manual_chunk_id');
    }
}
