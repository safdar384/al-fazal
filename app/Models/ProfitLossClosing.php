<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfitLossClosing extends Model
{
    protected $table = 'profit_loss_closings';

    protected $fillable = [
        'from_date',
        'to_date',
        'closing_date',
        'sale_amount',
        'cost_amount',
        'gross_profit',
        'sale_bilty_amount',
        'expense_amount',
        'labor_amount',
        'ledger_discount_amount',
        'ledger_bilty_amount',
        'voucher_discount_amount',
        'net_profit',
        'customer_receivable',
        'customer_payable',
        'bank_balance',
        'labour_balance',
        'expense_balance',
        'stock_balance',
        'purchase_amount',
        'closed_at',
        'notes',
    ];

    protected $casts = [
        'from_date' => 'date:Y-m-d',
        'to_date' => 'date:Y-m-d',
        'closing_date' => 'date:Y-m-d',
        'sale_amount' => 'decimal:2',
        'cost_amount' => 'decimal:2',
        'gross_profit' => 'decimal:2',
        'sale_bilty_amount' => 'decimal:2',
        'expense_amount' => 'decimal:2',
        'labor_amount' => 'decimal:2',
        'ledger_discount_amount' => 'decimal:2',
        'ledger_bilty_amount' => 'decimal:2',
        'voucher_discount_amount' => 'decimal:2',
        'net_profit' => 'decimal:2',
        'customer_receivable' => 'decimal:2',
        'customer_payable' => 'decimal:2',
        'bank_balance' => 'decimal:2',
        'labour_balance' => 'decimal:2',
        'expense_balance' => 'decimal:2',
        'stock_balance' => 'decimal:2',
        'purchase_amount' => 'decimal:2',
        'closed_at' => 'datetime',
    ];
}
