<?php

namespace Database\Seeders;

use App\Models\CustomerCatagory;
use App\Models\NewCustomer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CustomerCatagorySeeder extends Seeder
{
    public function run(): void
    {
        $defaults = ['Retail', 'Wholesale', 'VIP', 'Supplier'];

        foreach ($defaults as $name) {
            $exists = CustomerCatagory::query()
                ->whereRaw('LOWER(catagoryName) = ?', [strtolower($name)])
                ->first();

            if ($exists) {
                // Prefer canonical default casing (e.g. VIP over Vip)
                if ($exists->catagoryName !== $name) {
                    $exists->update(['catagoryName' => $name]);
                }
            } else {
                CustomerCatagory::create(['catagoryName' => $name]);
            }
        }

        // Preserve any custom categories already used on customers
        $table = null;
        if (Schema::hasTable('newcustomers')) {
            $table = 'newcustomers';
        } elseif (Schema::hasTable('newCustomers')) {
            $table = 'newCustomers';
        }

        if ($table) {
            $existing = DB::table($table)
                ->whereNotNull('custCatagory')
                ->where('custCatagory', '!=', '')
                ->distinct()
                ->pluck('custCatagory');

            foreach ($existing as $name) {
                $trimmed = trim((string) $name);
                if ($trimmed === '') {
                    continue;
                }

                $exists = CustomerCatagory::query()
                    ->whereRaw('LOWER(catagoryName) = ?', [strtolower($trimmed)])
                    ->exists();

                if (! $exists) {
                    CustomerCatagory::create(['catagoryName' => $trimmed]);
                }
            }
        }

        $this->command->info('✓ Customer categories seeded successfully!');
    }
}
