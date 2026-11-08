<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitPerPack extends Model
{
    protected $table = 'unit_per_pack';

    protected $fillable = ['unitName', 'piecesPerUnit'];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (! empty($model->unitName)) {
                $model->unitName = strtoupper($model->unitName);
            }
        });
    }
}
