<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NewCustomer;
use Faker\Factory as Faker;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        $areas = ['LHR', 'KHI', 'ISB', 'FSD', 'RWP', 'MLT', 'SKT', 'GWL', 'PSW', 'QTA'];
        $cities = ['Lahore', 'Karachi', 'Islamabad', 'Faisalabad', 'Rawalpindi', 'Multan', 'Sialkot', 'Gujranwala', 'Peshawar', 'Quetta'];
        $categories = ['Retail', 'Wholesale', 'VIP', 'Supplier'];
        
        $businessTypes = [
            'Traders', 'Store', 'Mart', 'Enterprises', 'Corporation', 'Industries',
            'Trading Co.', 'General Store', 'Super Store', 'Wholesale Market',
            'Electronics', 'Garments', 'Hardware', 'Fabrics', 'Textile Mills'
        ];

        $customers = [];

        for ($i = 1; $i <= 50; $i++) {
            $area = $faker->randomElement($areas);
            $city = $cities[array_search($area, $areas)];
            $businessType = $faker->randomElement($businessTypes);
            
            $customers[] = [
                'custCode' => 'CUST' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'custArea' => $area,
                'bussnessName' => $faker->company . ' ' . $businessType,
                'custName' => $faker->name,
                'custAddress' => $faker->streetAddress . ', ' . $faker->secondaryAddress,
                'custContect' => '03' . rand(10, 49) . '-' . rand(1000000, 9999999),
                'custCity' => $city,
                'custPage' => 'Page-' . rand(1, 100),
                'custdiscount' => $faker->randomFloat(2, 0, 25), // 0-25% discount
                'custPayTermDay' => $faker->randomElement([0, 7, 15, 30, 45, 60, 90]),
                'custCreditLimit' => $faker->randomFloat(2, 10000, 500000),
                'custOppDate' => $faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
                'custBalance' => $faker->randomFloat(2, -50000, 100000),
                'custCatagory' => $faker->randomElement($categories),
            ];
        }

        foreach ($customers as $customer) {
            NewCustomer::create($customer);
        }

        $this->command->info('✓ 50 customers have been created successfully!');
        $this->command->newLine();
        
        // Show summary
        $this->command->table(
            ['Area', 'Count', 'Total Balance'],
            collect($customers)->groupBy('custArea')->map(function ($group, $area) {
                return [
                    $area,
                    count($group),
                    'Rs. ' . number_format(collect($group)->sum('custBalance'), 2)
                ];
            })->toArray()
        );

        $this->command->newLine();
        $this->command->info('📊 Statistics:');
        $this->command->info('   Total Customers: 50');
        $this->command->info('   Total Balance: Rs. ' . number_format(collect($customers)->sum('custBalance'), 2));
        $this->command->info('   Total Credit Limit: Rs. ' . number_format(collect($customers)->sum('custCreditLimit'), 2));
        $this->command->info('   Average Discount: ' . number_format(collect($customers)->avg('custdiscount'), 2) . '%');
    }
}

