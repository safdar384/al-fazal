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

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // UPPERCASE only productCode
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
