<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerCatagory extends Model
{
    protected $table = 'customer_catagory';

    protected $fillable = ['catagoryName'];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (! empty($model->catagoryName)) {
                // Keep user/default casing (e.g. VIP); only trim whitespace
                $model->catagoryName = trim($model->catagoryName);
            }
        });
    }
}
