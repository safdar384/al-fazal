<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerArea extends Model
{
    // Tell Laravel the exact table name
    protected $table = 'customerareas';

    protected $fillable = ['areaCode', 'areaName', 'ajentName'];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::saving(function (CustomerArea $model) {
            if (! empty($model->areaCode)) {
                $model->areaCode = strtoupper(trim((string) $model->areaCode));
            }

            foreach (['areaName', 'ajentName'] as $field) {
                if (! empty($model->{$field})) {
                    $model->{$field} = ucwords(strtolower(trim((string) $model->{$field})));
                }
            }
        });
    }
}
