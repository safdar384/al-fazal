<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BomLabourWork extends Model
{
    protected $fillable = [
        'work_name',
        'rate',
    ];

    protected $casts = [
        'rate' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            if (! empty($model->work_name)) {
                $model->work_name = ucwords(strtolower(trim($model->work_name)));
            }
        });

        static::updating(function (self $model) {
            if ($model->isDirty('work_name') && ! empty($model->work_name)) {
                $model->work_name = ucwords(strtolower(trim($model->work_name)));
            }
        });
    }
}
