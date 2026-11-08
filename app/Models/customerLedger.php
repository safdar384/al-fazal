<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerLedger extends Model
{
    protected $table = 'customerledgers'; // 👈 add this line

    protected $fillable = [
        'customerDate',
        'invoiceNumber',
        'customerArea',
        'customerCode',
        'description',
        'credit',
        'debit',
        'category',
        'accountType',
        'statuse',
    ];

    protected $attributes = [
        'statuse' => '0',
    ];

    protected $casts = [
        'voucher_date' => 'date',
    ];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (!empty($model->description)) {
                $model->description = ucwords(strtolower($model->description));
            }
        });
    }
}

