<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'orderNo',
        'orderDate',
        'customerCode',
        'customerName',
        'city',
        'gudsName',
        'noOfItems',
        'orderTotal',
        'orderDiscount',
        'grandTotal',
        'comments',
        'notes',
        'status',
        'converted_invoice_no',
    ];

    protected $casts = [
        'orderDate' => 'date:Y-m-d',
        'orderTotal' => 'decimal:2',
        'orderDiscount' => 'decimal:2',
        'grandTotal' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(NewCustomer::class, 'customerCode', 'custCode');
    }

    public function isConverted(): bool
    {
        return ! empty($this->converted_invoice_no);
    }

    public function canConvertToInvoice(): bool
    {
        return ! $this->isConverted()
            && $this->status !== 'Cancelled'
            && $this->items()->exists();
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'Pending' => 'bg-warning text-dark',
            'Confirmed' => 'bg-info',
            'In Process' => 'bg-primary',
            'Completed' => 'bg-success',
            'Cancelled' => 'bg-danger',
            default => 'bg-secondary',
        };
    }
}
