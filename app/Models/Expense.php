<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'expenseCode',
        'expenseName',
        'expenseCategory',
        'description',
        'amount',
        'expenseDate',
        'paymentMethod',
        'status',
    ];

    protected $casts = [
        'expenseDate' => 'date',
        'amount' => 'decimal:2',
    ];
}
