<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $cliente_id
 * @property int $orden_id
 * @property string $type
 * @property string $title
 * @property string $description
 * @property string $piece_name
 * @property string $part_number
 * @property string $serial_number
 * @property string $priority
 * @property string $status
 * @property bool $urgent
 * @property bool $request_callback
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property Cliente $cliente
 * @property Orden $orden
 */
class ClientePortalIncident extends Model
{
    protected $fillable = [
        'cliente_id',
        'orden_id',
        'type',
        'title',
        'description',
        'piece_name',
        'part_number',
        'serial_number',
        'priority',
        'status',
        'urgent',
        'request_callback',
    ];

    protected function casts(): array
    {
        return [
            'urgent' => 'boolean',
            'request_callback' => 'boolean',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function orden()
    {
        return $this->belongsTo(Orden::class, 'orden_id');
    }
}
