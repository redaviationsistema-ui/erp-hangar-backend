<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $nombre
 * @property string $codigo
 * @property string $numero
 */
class Area extends Model
{
    protected $fillable = [
        'nombre',
        'codigo',
        'numero',
    ];

    public function ordenes(): HasMany
    {
        return $this->hasMany(Orden::class);
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function personalTecnico(): HasMany
    {
        return $this->hasMany(PersonalTecnico::class);
    }

    public function ataTaskTemplates(): HasMany
    {
        return $this->hasMany(AtaTaskTemplate::class);
    }
}
