<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabourAccountTransaction extends Model
{
    protected $table = 'labour_account_transactions';

    protected $fillable = [
        'transactionDate',
        'labour_id',
        'labourCode',
        'labourName',
        'labourCatagory',
        'description',
        'bankAccount',
        'amount',
        'entryType',
        'marketBalance',
        'recovery',
        'recoveryCommPercent',
        'commissionPercent',
        'isAdvance',
    ];

    protected $casts = [
        'transactionDate' => 'date',
        'amount' => 'decimal:2',
        'marketBalance' => 'decimal:2',
        'recovery' => 'decimal:2',
        'recoveryCommPercent' => 'decimal:2',
        'commissionPercent' => 'decimal:2',
        'isAdvance' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (LabourAccountTransaction $model) {
            if ($model->description !== null && $model->description !== '') {
                $model->description = ucwords(strtolower(trim((string) $model->description)));
            }
        });
    }
}
