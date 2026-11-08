<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FittingProductWork extends Model
{
    protected $table = 'fitting_product_works';

    protected $fillable = [
        'product_code',
        'product_name',
        'work_name',
        'rate',
        'status',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (self $model) {
            if (! empty($model->work_name)) {
                $model->work_name = ucwords(strtolower(trim((string) $model->work_name)));
            }
        });
    }
}
