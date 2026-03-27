<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $fillable = [
        'nombre',
        'codigo',
        'numero',
    ];

    public function ordenes()
    {
        return $this->hasMany(Orden::class);
    }

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }

    public function ataTaskTemplates()
    {
        return $this->hasMany(AtaTaskTemplate::class);
    }
}
