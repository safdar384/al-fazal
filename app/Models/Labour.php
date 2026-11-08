<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Labour extends Model
{
    public const TYPE_SALARY = 'salary_labour';

    protected $fillable = [
        'labourCode',
        'labourName',
        'labourAddress',
        'labourCatagory',
        'labour_type',
        'salary',
        'advance',
        'balance',
        'commissionPercentage',
    ];

    protected $attributes = [
        'labour_type' => self::TYPE_SALARY,
    ];

    protected $casts = [
        'salary' => 'float',
        'advance' => 'float',
        'balance' => 'float',
        'commissionPercentage' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (Labour $model) {
            if (! empty($model->labourCode)) {
                $model->labourCode = strtoupper(trim((string) $model->labourCode));
            }

            foreach (['labourName', 'labourAddress'] as $field) {
                if (! empty($model->{$field})) {
                    $model->{$field} = ucwords(strtolower(trim((string) $model->{$field})));
                }
            }

            if (! empty($model->labourCatagory)) {
                $model->labourCatagory = strtolower(trim((string) $model->labourCatagory));
            }
        });
    }
}
