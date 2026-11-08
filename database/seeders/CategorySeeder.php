<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['catagoryName' => 'Floor Tiles'],
            ['catagoryName' => 'Wall Tiles'],
            ['catagoryName' => 'Bathroom Tiles'],
            ['catagoryName' => 'Kitchen Tiles'],
            ['catagoryName' => 'Outdoor Tiles'],
        ];

        DB::table('product_catagory')->insert($categories);
    }
}
