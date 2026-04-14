<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientePortalPaymentSelection extends Model
{
    protected $fillable = [
        'cliente_id',
        'invoice_id',
        'orden_id',
        'payment_method_id',
        'status',
        'reference',
        'notes',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function invoice()
    {
        return $this->belongsTo(ClientePortalInvoice::class, 'invoice_id');
    }

    public function orden()
    {
        return $this->belongsTo(Orden::class, 'orden_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(ClientePortalPaymentMethod::class, 'payment_method_id');
    }
}
