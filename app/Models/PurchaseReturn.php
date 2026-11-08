<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseReturn extends Model
{
    protected $fillable = [
        'returnNo',
        'returnDate',
        'supplierCode',
        'supplierName',
        'city',
        'balance',
        'originalInvoiceNo',
        'comments',
        'noOfItems',
        'invoiceTotal',
        'invoiceDiscount',
        'grandTotal',
        'goodQtyTotal',
        'damageQtyTotal',
        'status',
    ];

    protected $casts = [
        'returnDate' => 'date:Y-m-d',
        'balance' => 'decimal:2',
        'invoiceTotal' => 'decimal:2',
        'invoiceDiscount' => 'decimal:2',
        'grandTotal' => 'decimal:2',
        'goodQtyTotal' => 'decimal:2',
        'damageQtyTotal' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class, 'purchase_return_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(NewCustomer::class, 'supplierCode', 'custCode');
    }
}
