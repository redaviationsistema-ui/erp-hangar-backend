<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
