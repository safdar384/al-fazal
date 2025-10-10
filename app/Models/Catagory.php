<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Catagory extends Model
{
     // Table name
    protected $table = 'product_catagory'; // change to 'colours' if that's your table

    // Fillable fields
    protected $fillable = ['catagoryName'];

    // Disable timestamps if table has no created_at/updated_at
    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // UPPER case for catagoryName
            if (!empty($model->catagoryName)) {
                $model->catagoryName = strtoupper($model->catagoryName);
            }
        });
    }
}
