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
        'saleRate',
        'costRate',
        'lessQty',
        'stockQty',
        'catagoryName',
        
    ];
    public $timestamps = false;

    /**
     * Generate a unique product code
     */
    public static function generateUniqueCode(): string
    {
        do {
            $latestProduct = self::orderBy('id', 'desc')->first();
            
            if ($latestProduct && preg_match('/PRD(\d+)/', $latestProduct->productCode, $matches)) {
                $number = (int)$matches[1] + 1;
            } else {
                $number = 1;
            }
            
            $code = 'PRD' . str_pad($number, 4, '0', STR_PAD_LEFT);
            
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
            if (!empty($model->productCode)) {
                $model->productCode = strtoupper($model->productCode);
            }

            // Proper Case for all name fields
            foreach (['productName', 'companyName', 'colourName', 'pakingName', 'catagoryName'] as $field) {
                if (!empty($model->$field)) {
                    $model->$field = ucwords(strtolower($model->$field));
                }
            }
        });

        static::updating(function ($model) {
            // UPPERCASE productCode
            if (!empty($model->productCode)) {
                $model->productCode = strtoupper($model->productCode);
            }

            // Proper Case for all name fields
            foreach (['productName', 'companyName', 'colourName', 'pakingName', 'catagoryName'] as $field) {
                if (!empty($model->$field)) {
                    $model->$field = ucwords(strtolower($model->$field));
                }
            }
        });
    }
}
