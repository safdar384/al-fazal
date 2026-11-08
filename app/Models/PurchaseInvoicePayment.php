<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseInvoicePayment extends Model
{
    protected $fillable = [
        'purchase_invoice_id',
        'invoiceNo',
        'payment_date',
        'amount',
        'payment_method',
        'reference_no',
        'ledger_id',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date:Y-m-d',
        'amount' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }
}
