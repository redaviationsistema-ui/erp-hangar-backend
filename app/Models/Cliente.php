<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $nombre_comercial
 * @property string $razon_social
 * @property string $rfc
 * @property string $contacto_nombre
 * @property string $email
 * @property string $password
 * @property string $contrasena_portal
 * @property int $ot_asignada_id
 * @property string $telefono
 * @property string $ciudad
 * @property string $estatus
 * @property string $notas
 * @property Orden $otAsignadaOrden
 * @method static Builder query()
 * @method static Builder withClienteOrders()
 * @method $this fill(array $attributes)
 * @method bool save(array $options = [])
 * @method $this load(mixed ...$relations)
 * @method bool delete()
 */
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

    public function ordenesAsignadas()
    {
        return $this->belongsToMany(Orden::class, 'cliente_orden', 'cliente_id', 'orden_id');
    }

    public function scopeWithClienteOrders(Builder $query): Builder
    {
        return $query->with([
            'otAsignadaOrden:id,area_id,folio,estado,descripcion,matricula',
            'otAsignadaOrden.area:id,nombre,codigo',
            'ordenesAsignadas:id',
        ]);
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
        $orderIdsQuery = DB::table('cliente_orden')
            ->select('orden_id')
            ->where('cliente_id', $this->id);

        if (! empty($names)) {
            $orderIdsQuery = $orderIdsQuery->union(
                DB::table('ordenes')
                    ->selectRaw('id as orden_id')
                    ->whereIn('cliente', $names)
            );
        }

        return Orden::query()
            ->with(['area:id,nombre,codigo'])
            ->whereIn('ordenes.id', $orderIdsQuery);
    }
}
