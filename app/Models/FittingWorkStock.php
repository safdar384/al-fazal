<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FittingWorkStock extends Model
{
    protected $table = 'fitting_work_stocks';

    protected $fillable = [
        'product_code',
        'product_name',
        'work_name',
        'unit_name',
        'qty',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
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
