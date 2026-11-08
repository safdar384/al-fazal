<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'orderNo',
        'productCode',
        'productName',
        'qty',
        'rate',
        'discount',
        'amount',
        'remarks',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'discount' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
