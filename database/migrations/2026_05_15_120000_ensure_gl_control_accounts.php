<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            ['account_code' => 'INC001', 'account_name' => 'Sales Revenue', 'account_type' => 'Income', 'opening_balance' => 0, 'status' => 'Active'],
            ['account_code' => 'ACC_AR', 'account_name' => 'Accounts Receivable', 'account_type' => 'Customer', 'opening_balance' => 0, 'status' => 'Active'],
            ['account_code' => 'ACC_AP', 'account_name' => 'Accounts Payable', 'account_type' => 'Supplier', 'opening_balance' => 0, 'status' => 'Active'],
            ['account_code' => 'ACC_PUR', 'account_name' => 'Purchases / Stock', 'account_type' => 'Expense', 'opening_balance' => 0, 'status' => 'Active'],
        ];

        foreach ($rows as $row) {
            DB::table('accounts')->updateOrInsert(
                ['account_code' => $row['account_code']],
                $row
            );
        }
    }

    public function down(): void
    {
        // Control accounts are shared; leave in place on rollback.
    }
};
