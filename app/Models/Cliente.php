<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Crypt;
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
        ];
    }

    protected function contrasenaPortal(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value): ?string {
                if ($value === null) {
                    return null;
                }

                $plain = trim((string) $value);
                if ($plain === '') {
                    return null;
                }

                try {
                    return Crypt::decryptString($plain);
                } catch (DecryptException) {
                    // Compatibilidad con registros legacy que guardaron texto plano.
                    return $plain;
                }
            },
            set: function (mixed $value): ?string {
                if ($value === null) {
                    return null;
                }

                $plain = trim((string) $value);
                if ($plain === '') {
                    return null;
                }

                return Crypt::encryptString($plain);
            },
        );
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
        $explicitOrderIds = DB::table('cliente_orden')
            ->where('cliente_id', $this->id)
            ->pluck('orden_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($this->ot_asignada_id) {
            $explicitOrderIds[] = (int) $this->ot_asignada_id;
        }

        $explicitOrderIds = array_values(array_unique($explicitOrderIds));

        if (! empty($explicitOrderIds)) {
            return Orden::query()
                ->with(['area:id,nombre,codigo'])
                ->whereIn('ordenes.id', $explicitOrderIds);
        }

        $names = $this->relatedOrderNames();

        return Orden::query()
            ->with(['area:id,nombre,codigo'])
            ->when(
                ! empty($names),
                fn (Builder $query) => $query->whereIn('ordenes.cliente', $names),
                fn (Builder $query) => $query->whereRaw('1 = 0'),
            );
    }
}
