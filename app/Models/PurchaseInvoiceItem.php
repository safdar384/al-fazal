<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseInvoiceItem extends Model
{
    protected $table = 'purchase_invoice_items';

    protected $fillable = [
        'purchase_invoice_id', 'invoiceNo', 'productCode', 'productName', 'variant_id', 'variant_name', 'variant_value', 'variant_added_price', 'qty', 'rate', 'discount', 'amount', 'freightPerUnit',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }
}
