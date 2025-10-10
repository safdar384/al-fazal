<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Packing extends Model
{
     // Table name
    protected $table = 'packing'; // change to 'colours' if that's your table

    // Fillable fields
    protected $fillable = ['pakingName'];

    // Disable timestamps if table has no created_at/updated_at
    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Proper case for pakingName
            if (!empty($model->pakingName)) {
                $model->pakingName = strtoupper($model->pakingName);
            }
        });
    }

}
