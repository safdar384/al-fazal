<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleReturnItem extends Model
{
    public const CONDITION_GOOD = 'good';

    public const CONDITION_DAMAGE = 'damage';

    protected $fillable = [
        'sale_return_id',
        'returnNo',
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
        'condition',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'rate' => 'decimal:2',
        'cost_rate' => 'decimal:2',
        'discount' => 'decimal:2',
        'amount' => 'decimal:2',
        'variant_added_price' => 'decimal:2',
    ];

    public function saleReturn(): BelongsTo
    {
        return $this->belongsTo(SaleReturn::class, 'sale_return_id');
    }

    public function isGood(): bool
    {
        return strtolower((string) $this->condition) === self::CONDITION_GOOD;
    }

    public function isDamage(): bool
    {
        return strtolower((string) $this->condition) === self::CONDITION_DAMAGE;
    }
}
