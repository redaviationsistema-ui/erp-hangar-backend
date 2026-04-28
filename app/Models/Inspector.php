<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $personal_tecnico_id
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
 * @property PersonalTecnico|null $personalTecnico
 * @property User|null $usuario
 */
class Inspector extends Model
{
    protected $table = 'inspectores';

    protected $fillable = [
        'personal_tecnico_id',
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

    public function personalTecnico(): BelongsTo
    {
        return $this->belongsTo(PersonalTecnico::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
