<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerLedgerSeeder extends Seeder
{
    public function run(): void
    {
        $records = [];
        $today = Carbon::today();

        $customers = [
            // Lahore
            ['area' => 'LHR', 'customerCode' => 'CUST001', 'name' => 'Muhammad Ali Khan'],
            ['area' => 'LHR', 'customerCode' => 'CUST002', 'name' => 'Ahsan Raza'],
            ['area' => 'LHR', 'customerCode' => 'CUST003', 'name' => 'Bilal Tariq'],
            ['area' => 'LHR', 'customerCode' => 'CUST004', 'name' => 'Hassan Javed'],

            // Karachi
            ['area' => 'KHI', 'customerCode' => 'CUST005', 'name' => 'Ahmed Hassan'],
            ['area' => 'KHI', 'customerCode' => 'CUST006', 'name' => 'Saad Ali'],
            ['area' => 'KHI', 'customerCode' => 'CUST007', 'name' => 'Hassan Ahmed'],
            ['area' => 'KHI', 'customerCode' => 'CUST008', 'name' => 'Imran Khan'],
        ];

        foreach ($customers as $cust) {
            // Add 5 random Sale invoices for each customer
            for ($i = 0; $i < 5; $i++) {
                $invoiceAmount = rand(1000, 5000);
                $records[] = [
                    'customerDate' => $today->subDays(rand(0, 30))->format('Y-m-d'),
                    'invoiceNumber' => strtoupper(Str::random(6)),
                    'customerArea' => $cust['area'],
                    'customerCode' => $cust['customerCode'],
                    'description' => "Sale Invoice for {$cust['name']}",
                    'credit' => 0,
                    'debit' => $invoiceAmount,  // Sale amount
                    'category' => 'SaleInvoice',
                ];

                // Add one random Recovery record
                $records[] = [
                    'customerDate' => $today->subDays(rand(0, 30))->format('Y-m-d'),
                    'invoiceNumber' => null,
                    'customerArea' => $cust['area'],
                    'customerCode' => $cust['customerCode'],
                    'description' => "Recovery from {$cust['name']}",
                    'credit' => rand(500, 2000),  // Payment received
                    'debit' => 0,
                    'category' => 'Recovery',
                ];
            }
        }

        DB::table('customerledgers')->insert($records);
    }
}
