<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $table = 'product_variants';

    protected $fillable = [
        'productCode',
        'variantName',
        'variantValue',
        'additionalCost',
        'additionalPrice',
        'stockQty',
        'isActive',
        'image',
    ];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::saving(function (ProductVariant $model) {
            if (! empty($model->productCode)) {
                $model->productCode = strtoupper(trim((string) $model->productCode));
            }
            foreach (['variantName', 'variantValue'] as $field) {
                if (! empty($model->{$field})) {
                    $model->{$field} = ucwords(strtolower(trim((string) $model->{$field})));
                }
            }
        });
    }

    // Relationship with product
    public function product()
    {
        return $this->belongsTo(newProduct::class, 'productCode', 'productCode');
    }

    public function getImageUrlAttribute(): ?string
    {
        return \App\Support\ProductImageStorage::url($this->image);
    }
}
