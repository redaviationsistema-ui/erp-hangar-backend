<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientePortalPaymentMethod extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'instructions',
        'active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }
}
