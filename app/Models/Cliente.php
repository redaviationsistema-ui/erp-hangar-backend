<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Cliente extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'nombre_comercial',
        'razon_social',
        'rfc',
        'contacto_nombre',
        'email',
        'password',
        'contrasena_portal',
        'ot_asignada_id',
        'telefono',
        'ciudad',
        'estatus',
        'notas',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'contrasena_portal' => 'encrypted',
        ];
    }

    public function otAsignadaOrden()
    {
        return $this->belongsTo(Orden::class, 'ot_asignada_id');
    }

    public function scopeWithClienteOrders(Builder $query): Builder
    {
        return $query->with(['otAsignadaOrden.area']);
    }

    public function relatedOrderNames(): array
    {
        return array_values(array_unique(array_filter([
            trim((string) $this->nombre_comercial),
            trim((string) $this->razon_social),
        ])));
    }

    public function relatedOrdersQuery(): Builder
    {
        $names = $this->relatedOrderNames();

        return Orden::query()
            ->with(['area:id,nombre,codigo'])
            ->when(! empty($names), fn (Builder $query) => $query->whereIn('cliente', $names), fn (Builder $query) => $query->whereRaw('1 = 0'));
    }
}
