<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Colour extends Model
{
    // Table name
    protected $table = 'colour'; // change to 'colours' if that's your table

    // Fillable fields
    protected $fillable = ['colourName'];

    // Disable timestamps if table has no created_at/updated_at
    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // upper case for colourName
            if (!empty($model->colourName)) {
                $model->colourName = strtoupper($model->colourName);
            }
        });
    }
}
