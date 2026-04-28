<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int|null $area_id
 * @property string $nombre
 * @property string|null $email
 * @property string|null $telefono
 * @property string|null $puesto
 * @property string|null $especialidad
 * @property string $tipo
 * @property string $estado
 * @property string|null $notas
 * @property Area|null $area
 * @property User|null $usuario
 */
class PersonalTecnico extends Model
{
    protected $table = 'personal_tecnico';

    protected $fillable = [
        'user_id',
        'area_id',
        'nombre',
        'email',
        'telefono',
        'puesto',
        'especialidad',
        'tipo',
        'estado',
        'notas',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
