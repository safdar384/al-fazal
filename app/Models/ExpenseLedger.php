<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseLedger extends Model
{
    protected $table = 'expense_ledger';

    protected $fillable = [
        'entryDate',
        'expenseCode',
        'expenseCategory',
        'description',
        'amount',
        'paymentMethod',
        'accountCode',
        'debit',
        'credit',
        'referenceNo',
        'category',
    ];

    protected $casts = [
        'entryDate' => 'date',
        'amount' => 'decimal:2',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];
}
