<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierTransactionItem extends Model
{
    protected $table = 'supplier_transaction_items';

    protected $fillable = [
        'supplier_transaction_id',
        'txn_no',
        'product_code',
        'product_name',
        'work_name',
        'variant_id',
        'variant_name',
        'variant_value',
        'qty',
        'rate',
        'amount',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'rate' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(SupplierTransaction::class, 'supplier_transaction_id');
    }
}
