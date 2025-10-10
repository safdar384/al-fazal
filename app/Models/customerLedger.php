<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class customerLedger extends Model
{
    protected $fillable = [
        'customerDate',
        'invoiceNumber',
        'customerArea',
        'customerCode',
        'description',
        'credit',
        'debit',
        'category',
        
    ];

    protected $casts = [
        'voucher_date' => 'date',
    ];
    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Proper case for description
            if (!empty($model->description)) {
                $model->description = ucwords(strtolower($model->description));
            }
        });
    }
}
