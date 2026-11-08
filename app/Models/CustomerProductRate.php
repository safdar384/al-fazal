<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerProductRate extends Model
{
    protected $table = 'customer_product_rates';

    public const TYPE_SALE = 'Sale';

    public const TYPE_PURCHASE = 'Purchase';

    protected $fillable = [
        'customerCode',
        'productCode',
        'work_name',
        'variant_id',
        'variant_name',
        'variant_value',
        'effective_from',
        'specialRate',
        'rateType',
        'discountPercent',
        'remarks',
        'status',
        'is_active',
    ];

    protected $casts = [
        'specialRate' => 'decimal:2',
        'discountPercent' => 'decimal:2',
        'effective_from' => 'date',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (self $model) {
            if ($model->work_name !== null && $model->work_name !== '') {
                $model->work_name = ucwords(strtolower(trim((string) $model->work_name)));
            } else {
                $model->work_name = null;
            }
        });
    }

    // Scope for active rates only
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Filter by Sale/Purchase rate type (legacy null rows match the requested type).
     */
    public function scopeForRateType($query, string $rateType)
    {
        $rateType = $rateType === self::TYPE_PURCHASE ? self::TYPE_PURCHASE : self::TYPE_SALE;

        return $query->where(function ($q) use ($rateType) {
            $q->where('rateType', $rateType)->orWhereNull('rateType');
        });
    }

    // Get current rate for a customer-product combination
    public static function getCurrentRate($customerCode, $productCode)
    {
        return self::where('customerCode', $customerCode)
            ->where('productCode', $productCode)
            ->where('is_active', true)
            ->where('effective_from', '<=', now())
            ->orderBy('effective_from', 'desc')
            ->first();
    }

    // Scope to get rate history
    public function scopeHistory($query, $customerCode, $productCode)
    {
        return $query->where('customerCode', $customerCode)
            ->where('productCode', $productCode)
            ->orderBy('effective_from', 'desc');
    }

    public function customer()
    {
        return $this->belongsTo(NewCustomer::class, 'customerCode', 'custCode');
    }

    public function product()
    {
        return $this->belongsTo(newProduct::class, 'productCode', 'productCode');
    }
}
