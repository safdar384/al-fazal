<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            ['companyName' => 'Master Tiles'],
            ['companyName' => 'Elegant Ceramics'],
            ['companyName' => 'Royal Tiles'],
            ['companyName' => 'Premium Flooring'],
            ['companyName' => 'Diamond Ceramics'],
        ];

        DB::table('product_company')->insert($companies);
    }
}
