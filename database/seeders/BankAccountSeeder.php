<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $accounts = [
            [
                'bankAccount' => 'ACC001',
                'bankName' => 'National Bank',
                'bankAddress' => 'Head Office, Main St',
                'branchCode' => 'NBH01',
            ],
            [
                'bankAccount' => 'ACC002',
                'bankName' => 'First Trust Bank',
                'bankAddress' => 'Downtown Branch',
                'branchCode' => 'FTB02',
            ],
            [
                'bankAccount' => 'ACC003',
                'bankName' => 'People\'s Cooperative Bank',
                'bankAddress' => 'Market Road',
                'branchCode' => 'PCB03',
            ],
            [
                'bankAccount' => 'ACC004',
                'bankName' => 'Community Savings',
                'bankAddress' => 'Industrial Area',
                'branchCode' => 'CSB04',
            ],
        ];

        foreach ($accounts as $acc) {
            DB::table('bank_accounts')->updateOrInsert(
                ['bankAccount' => $acc['bankAccount']],
                $acc
            );
        }
    }
}
