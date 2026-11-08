<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseInvoice extends Model
{
    protected $table = 'purchase_invoices';

    protected $fillable = [
        'invoiceNo', 'invoiceDate', 'supplierCode', 'supplierName', 'city', 'balance',
        'corten', 'biltyExpanse', 'loaderExpanse', 'biltyBalance',
        'comments', 'transportCode', 'transportName', 'vehicleNumber', 'dispatchNo', 'weight',
        'transportRate', 'transportAmount',
        'noOfItems', 'invoiceTotal', 'invoiceDiscount', 'grandTotal',
        'paid_amount', 'payment_status', 'status',
    ];

    protected $casts = [
        'invoiceDate' => 'date:Y-m-d',
        'invoiceTotal' => 'decimal:2',
        'invoiceDiscount' => 'decimal:2',
        'grandTotal' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'biltyExpanse' => 'decimal:2',
        'loaderExpanse' => 'decimal:2',
        'biltyBalance' => 'decimal:2',
        'weight' => 'decimal:2',
        'transportRate' => 'decimal:2',
        'transportAmount' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseInvoiceItem::class, 'purchase_invoice_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PurchaseInvoicePayment::class, 'purchase_invoice_id');
    }

    public function supplier()
    {
        return $this->belongsTo(NewCustomer::class, 'supplierCode', 'custCode');
    }

    public function balanceDue(): float
    {
        return max(0, round((float) $this->grandTotal - (float) $this->paid_amount, 2));
    }

    public function paymentStatusBadgeClass(): string
    {
        return match ($this->payment_status) {
            'paid' => 'bg-success',
            'partial' => 'bg-warning text-dark',
            default => 'bg-danger',
        };
    }
}
