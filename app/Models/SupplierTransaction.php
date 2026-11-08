<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierTransaction extends Model
{
    public const TYPE_SALE = 'Sale';

    public const TYPE_PURCHASE = 'Purchase';

    public const TYPE_CASH = 'Cash';

    protected $table = 'supplier_transactions';

    protected $fillable = [
        'txn_no',
        'supplier_code',
        'supplier_name',
        'txn_type',
        'entry_type',
        'bank_account',
        'txn_date',
        'total_amount',
        'remarks',
        'status',
    ];

    protected $casts = [
        'txn_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SupplierTransactionItem::class, 'supplier_transaction_id');
    }

    public function supplier()
    {
        return $this->belongsTo(NewCustomer::class, 'supplier_code', 'custCode');
    }
}
