<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// 👇 IMPORTANTE: importar el modelo Area
use App\Models\Area;

class User extends Authenticatable
{
    use HasApiTokens;
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Campos que se pueden llenar masivamente
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'area_id', // 🔥 AGREGA ESTO
    ];

    /**
     * Campos ocultos
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts de atributos
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * 🔥 RELACIÓN: Usuario pertenece a un área
     */
    public function area()
    {
        return $this->belongsTo(Area::class);
    }
}