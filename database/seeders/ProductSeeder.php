<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\newProduct;
use App\Models\productCompany;
use App\Models\Colour;
use App\Models\packing;
use App\Models\Catagory;
use Faker\Factory as Faker;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Seed Companies First
        $this->command->info('🏢 Seeding Companies...');
        $companies = ['Samsung', 'LG', 'Sony', 'Panasonic', 'Haier', 'Dawlance', 'Orient', 'PEL', 'Canon', 'HP', 'Dell', 'Lenovo', 'Toshiba', 'Philips', 'Bosch'];
        foreach ($companies as $companyName) {
            productCompany::create(['companyName' => $companyName]);
        }
        $this->command->info('   ✓ ' . count($companies) . ' companies created');
        
        // Seed Colours
        $this->command->info('🎨 Seeding Colours...');
        $colours = ['Black', 'White', 'Silver', 'Grey', 'Blue', 'Red', 'Gold', 'Brown', 'Green', 'Multi', 'Beige', 'Navy', 'Maroon', 'Orange'];
        foreach ($colours as $colourName) {
            Colour::create(['colourName' => $colourName]);
        }
        $this->command->info('   ✓ ' . count($colours) . ' colours created');
        
        // Seed Packings
        $this->command->info('📦 Seeding Packings...');
        $packings = ['Box', 'Carton', 'Pack', 'Piece', 'Set', 'Unit', 'Bundle', 'Case', 'Bag', 'Crate'];
        foreach ($packings as $pakingName) {
            packing::create(['pakingName' => $pakingName]);
        }
        $this->command->info('   ✓ ' . count($packings) . ' packing types created');
        
        // Seed Categories
        $this->command->info('🏷️  Seeding Categories...');
        $categories = ['Electronics', 'Appliances', 'Hardware', 'Textile', 'Stationery', 'Furniture', 'Tools', 'Machinery', 'Kitchenware'];
        foreach ($categories as $catagoryName) {
            Catagory::create(['catagoryName' => $catagoryName]);
        }
        $this->command->info('   ✓ ' . count($categories) . ' categories created');
        
        $this->command->newLine();
        $this->command->info('📦 Seeding Products...');
        
        $productTypes = [
            'LED TV', 'Refrigerator', 'Washing Machine', 'Air Conditioner', 'Microwave Oven',
            'Water Dispenser', 'Deep Freezer', 'Fan', 'Iron', 'Toaster',
            'Blender', 'Mixer', 'Printer', 'Scanner', 'Monitor',
            'Keyboard', 'Mouse', 'Speaker', 'Headphone', 'Camera',
            'Laptop', 'Tablet', 'Mobile Phone', 'Charger', 'Power Bank',
            'Cable', 'Adapter', 'Router', 'Modem', 'Switch',
            'Drill Machine', 'Grinder', 'Cutter', 'Pliers', 'Wrench',
            'Hammer', 'Screwdriver', 'Measuring Tape', 'Level Tool', 'Saw',
            'Paint Brush', 'Roller', 'Spray Gun', 'Ladder', 'Tool Box',
            'Office Chair', 'Desk', 'Cabinet', 'Shelf', 'Sofa'
        ];

        $products = [];

        for ($i = 1; $i <= 50; $i++) {
            $productType = $productTypes[$i - 1];
            $company = $faker->randomElement($companies);
            $colour = $faker->randomElement($colours);
            $packing = $faker->randomElement($packings);
            $category = $faker->randomElement($categories);
            
            $costRate = $faker->randomFloat(2, 1000, 50000);
            $saleRate = $costRate * $faker->randomFloat(2, 1.15, 1.50); // 15-50% markup
            
            $products[] = [
                'productCode' => 'PRD' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'productName' => $company . ' ' . $productType,
                'companyName' => $company,
                'colourName' => $colour,
                'pakingName' => $packing,
                'saleRate' => round($saleRate, 2),
                'costRate' => round($costRate, 2),
                'lessQty' => $faker->randomElement([5, 10, 15, 20, 25]),
                'stockQty' => $faker->numberBetween(0, 200),
                'catagoryName' => $category,
            ];
        }

        foreach ($products as $product) {
            newProduct::create($product);
        }

        $this->command->info('   ✓ 50 products created successfully!');
        $this->command->newLine();
        
        // Show statistics by category
        $this->command->table(
            ['Category', 'Products', 'Total Stock', 'Stock Value'],
            collect($products)->groupBy('catagoryName')->map(function ($group, $category) {
                $totalStock = collect($group)->sum('stockQty');
                $stockValue = collect($group)->sum(function($p) {
                    return $p['costRate'] * $p['stockQty'];
                });
                return [
                    $category,
                    count($group),
                    number_format($totalStock),
                    'Rs. ' . number_format($stockValue, 2)
                ];
            })->toArray()
        );

        $this->command->newLine();
        $this->command->info('📊 Overall Statistics:');
        $this->command->info('   Companies: ' . count($companies));
        $this->command->info('   Colours: ' . count($colours));
        $this->command->info('   Packings: ' . count($packings));
        $this->command->info('   Categories: ' . count($categories));
        $this->command->info('   Products: 50');
        $this->command->info('   Total Stock: ' . number_format(collect($products)->sum('stockQty')));
        $totalValue = collect($products)->sum(function($p) {
            return $p['costRate'] * $p['stockQty'];
        });
        $this->command->info('   Total Stock Value: Rs. ' . number_format($totalValue, 2));
        $lowStock = collect($products)->filter(function($p) {
            return $p['stockQty'] <= $p['lessQty'];
        })->count();
        $this->command->info('   Low Stock Items: ' . $lowStock);
    }
}
