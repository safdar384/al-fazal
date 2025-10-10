<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $table = 'bank_accounts'; // explicitly set table name

    protected $fillable = [
        'bankAccount',
        'bankName',
        'bankAddress',
        'branchCode',
    ];
    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // 🔹 Uppercase fields
            foreach (['bankAccount', 'branchCode'] as $field) {
                if (!empty($model->$field)) {
                    $model->$field = strtoupper($model->$field);
                }
            }

            // 🔹 Proper case fields
            foreach (['bankName', 'bankAddress'] as $field) {
                if (!empty($model->$field)) {
                    $model->$field = ucwords(strtolower($model->$field));
                }
            }
        });
    }
}
