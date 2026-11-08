<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class newProduct extends Model
{
    protected $table = 'product_info'; // optional if table name matches

    protected $fillable = [
        'productCode',
        'productName',
        'companyName',
        'colourName',
        'pakingName',
        'unitPerPackName',
        'piecesPerUnit',
        'saleRate',
        'costRate',
        'lessQty',
        'stockQty',
        'catagoryName',
        'image',
    ];

    public $timestamps = false;

    /**
     * Get the variants for the product.
     */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'productCode', 'productCode');
    }

    public function getImageUrlAttribute(): ?string
    {
        return \App\Support\ProductImageStorage::url(
            \App\Support\ProductImageStorage::normalizeRelativePath((string) ($this->image ?? ''))
        );
    }

    /**
     * Kilograms in one quantity of this product. Packs such as Bag / 50 Kg use piecesPerUnit.
     */
    public function kgEach(): float
    {
        $unit = strtolower(trim((string) $this->unitPerPackName));
        $pack = strtolower(trim((string) $this->pakingName));
        $kgUnits = ['kg', 'kgs', 'kilogram', 'kilograms'];
        if (! in_array($unit, $kgUnits, true) && ! in_array($pack, $kgUnits, true)) {
            return 0;
        }

        $per = (float) $this->piecesPerUnit;

        return $per > 0 ? $per : 1;
    }

    /**
     * Generate a unique product code
     */
    public static function generateUniqueCode(): string
    {
        do {
            $latestProduct = self::orderBy('id', 'desc')->first();

            if ($latestProduct && preg_match('/PRD(\d+)/', $latestProduct->productCode, $matches)) {
                $number = (int) $matches[1] + 1;
            } else {
                $number = 1;
            }

            $code = 'PRD'.str_pad($number, 4, '0', STR_PAD_LEFT);

        } while (self::where('productCode', $code)->exists());

        return $code;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Auto-generate code if not provided
            if (empty($model->productCode)) {
                $model->productCode = self::generateUniqueCode();
            }

            // UPPERCASE productCode
            if (! empty($model->productCode)) {
                $model->productCode = strtoupper($model->productCode);
            }

            // Proper Case for all name fields
            foreach (['productName', 'companyName', 'colourName', 'pakingName', 'unitPerPackName', 'catagoryName'] as $field) {
                if (! empty($model->$field)) {
                    $model->$field = ucwords(strtolower($model->$field));
                }
            }
        });

        static::updating(function ($model) {
            // UPPERCASE productCode
            if (! empty($model->productCode)) {
                $model->productCode = strtoupper($model->productCode);
            }

            // Proper Case for all name fields
            foreach (['productName', 'companyName', 'colourName', 'pakingName', 'unitPerPackName', 'catagoryName'] as $field) {
                if (! empty($model->$field)) {
                    $model->$field = ucwords(strtolower($model->$field));
                }
            }
        });
    }
}
