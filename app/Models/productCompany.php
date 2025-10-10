<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCompany extends Model
{
    // Table name
    protected $table = 'product_company';

    // Fillable fields
    protected $fillable = ['companyName'];

    // Disable timestamps if your table doesn't have created_at/updated_at
    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Proper case for companyName
            if (!empty($model->companyName)) {
                $model->companyName = strtoupper($model->companyName);
            }
        });
    }
}

