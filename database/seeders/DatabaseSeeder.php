<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed areas first, then agents, customers and products
        $this->call([
            AreaSeeder::class,
            AgentSeeder::class,
            CustomerSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
