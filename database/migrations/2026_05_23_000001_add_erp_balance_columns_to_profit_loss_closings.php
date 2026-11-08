<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profit_loss_closings', function (Blueprint $table) {
            $table->decimal('gross_profit', 18, 2)->default(0)->after('cost_amount');
            $table->decimal('voucher_discount_amount', 18, 2)->default(0)->after('ledger_bilty_amount');
            $table->decimal('customer_receivable', 18, 2)->default(0)->after('net_profit');
            $table->decimal('customer_payable', 18, 2)->default(0)->after('customer_receivable');
            $table->decimal('bank_balance', 18, 2)->default(0)->after('customer_payable');
            $table->decimal('labour_balance', 18, 2)->default(0)->after('bank_balance');
            $table->decimal('expense_balance', 18, 2)->default(0)->after('labour_balance');
            $table->decimal('stock_balance', 18, 2)->default(0)->after('expense_balance');
            $table->decimal('purchase_amount', 18, 2)->default(0)->after('stock_balance');
        });
    }

    public function down(): void
    {
        Schema::table('profit_loss_closings', function (Blueprint $table) {
            $table->dropColumn([
                'gross_profit',
                'voucher_discount_amount',
                'customer_receivable',
                'customer_payable',
                'bank_balance',
                'labour_balance',
                'expense_balance',
                'stock_balance',
                'purchase_amount',
            ]);
        });
    }
};
