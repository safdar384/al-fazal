<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['account_code' => 'CASH001', 'account_name' => 'Cash in Hand', 'account_type' => 'Bank', 'opening_balance' => 50000, 'status' => 'Active'],
            ['account_code' => 'BANK001', 'account_name' => 'HBL Main Account', 'account_type' => 'Bank', 'opening_balance' => 500000, 'status' => 'Active'],
            ['account_code' => 'BANK002', 'account_name' => 'UBL Business Account', 'account_type' => 'Bank', 'opening_balance' => 300000, 'status' => 'Active'],
            ['account_code' => 'EXP001', 'account_name' => 'Office Rent', 'account_type' => 'Expense', 'opening_balance' => 0, 'status' => 'Active'],
            ['account_code' => 'EXP002', 'account_name' => 'Utilities', 'account_type' => 'Expense', 'opening_balance' => 0, 'status' => 'Active'],
            ['account_code' => 'INC001', 'account_name' => 'Sales Revenue', 'account_type' => 'Income', 'opening_balance' => 0, 'status' => 'Active'],
            ['account_code' => 'ACC_AR', 'account_name' => 'Accounts Receivable', 'account_type' => 'Customer', 'opening_balance' => 0, 'status' => 'Active'],
            ['account_code' => 'ACC_AP', 'account_name' => 'Accounts Payable', 'account_type' => 'Supplier', 'opening_balance' => 0, 'status' => 'Active'],
            ['account_code' => 'ACC_PUR', 'account_name' => 'Purchases / Stock', 'account_type' => 'Expense', 'opening_balance' => 0, 'status' => 'Active'],
        ];

        foreach ($accounts as $row) {
            DB::table('accounts')->updateOrInsert(
                ['account_code' => $row['account_code']],
                $row
            );
        }
    }
}
