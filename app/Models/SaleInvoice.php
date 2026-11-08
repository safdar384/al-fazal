<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleInvoice extends Model
{
    protected $fillable = [
        'invoiceNo',
        'invoiceDate',
        'customerCode',
        'customerName',
        'orderNo',
        'city',
        'balance',
        'gudsName',
        'corten',
        'biltyNo',
        'biltyExpanse',
        'loaderExpanse',
        'biltyBalance',
        'delivered',
        'delivered_date',
        'comments',
        'noOfItems',
        'invoiceTotal',
        'invoiceDiscount',
        'grandTotal',
        'status',
        'sale_type',
        'payment_status',
        'paid_amount',
    ];

    public function isCashSale(): bool
    {
        return ($this->sale_type ?? 'credit') === 'cash';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid' || $this->isCashSale();
    }

    protected $casts = [
        'invoiceDate' => 'date:Y-m-d',
        'balance' => 'decimal:2',
        'biltyExpanse' => 'decimal:2',
        'loaderExpanse' => 'decimal:2',
        'biltyBalance' => 'decimal:2',
        'delivered' => 'boolean',
        'delivered_date' => 'datetime',
        'invoiceTotal' => 'decimal:2',
        'invoiceDiscount' => 'decimal:2',
        'grandTotal' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(NewCustomer::class, 'customerCode', 'custCode');
    }

    public function items()
    {
        return $this->hasMany(SaleInvoiceItem::class, 'sale_invoice_id', 'id');
    }

    public function bankPayments(): HasMany
    {
        return $this->hasMany(SaleInvoiceBankPayment::class);
    }
}
