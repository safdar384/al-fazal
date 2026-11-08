<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleInvoiceItem extends Model
{
    protected $fillable = [
        'sale_invoice_id',
        'invoiceNo',
        'productCode',
        'productName',
        'variant_id',
        'variant_name',
        'variant_value',
        'variant_added_price',
        'qty',
        'rate',
        'cost_rate',
        'discount',
        'amount',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'cost_rate' => 'decimal:2',
        'discount' => 'decimal:2',
        'amount' => 'decimal:2',
        'variant_added_price' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(SaleInvoice::class, 'sale_invoice_id');
    }
}
