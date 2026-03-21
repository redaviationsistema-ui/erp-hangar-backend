<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $nombre
 * @property string $codigo
 */

class Area extends Model
{
    protected $fillable = [
    'nombre',
    'codigo'
];
}
