<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $description
 * @property string $instructions
 * @property bool $active
 * @property int $sort_order
 */
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
