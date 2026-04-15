<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $cliente_id
 * @property int $orden_id
 * @property string $folio
 * @property string $concepto
 * @property string $amount_total
 * @property string $currency
 * @property string $status
 * @property string $issued_at
 * @property string $due_at
 * @property string $pdf_url
 * @property string $notes
 * @property Cliente $cliente
 * @property Orden $orden
 * @property ClientePortalPaymentSelection $latestPaymentSelection
 */
class ClientePortalInvoice extends Model
{
    protected $fillable = [
        'cliente_id',
        'orden_id',
        'folio',
        'concepto',
        'amount_total',
        'currency',
        'status',
        'issued_at',
        'due_at',
        'pdf_url',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount_total' => 'decimal:2',
            'issued_at' => 'date',
            'due_at' => 'date',
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

    public function latestPaymentSelection()
    {
        return $this->hasOne(ClientePortalPaymentSelection::class, 'invoice_id')->latestOfMany();
    }
}
