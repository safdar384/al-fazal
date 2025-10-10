<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewAjent extends Model
{
    protected $table = 'new_ajensts'; // explicitly set table name

    protected $fillable = [
        'ajentCode',
        'ajentName',
        'AjentAdvance',
        'ajentContact',
        'ajentAdderss',
        'ajentCommission',
        'ajentBalance',
    ];
    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // 🔹 Uppercase fields
            if (!empty($model->ajentCode)) {
                $model->ajentCode = strtoupper($model->ajentCode);
            }

            // 🔹 Proper case fields
            foreach (['ajentName', 'ajentAdderss'] as $field) {
                if (!empty($model->$field)) {
                    $model->$field = ucwords(strtolower($model->$field));
                }
            }
        });
    }
}
