<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleInvoiceBankPayment extends Model
{
    protected $fillable = [
        'sale_invoice_id',
        'bank_account_id',
        'amount',
        'ledger_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function saleInvoice(): BelongsTo
    {
        return $this->belongsTo(SaleInvoice::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }
}
