<?php

namespace Database\Seeders;

use App\Models\ClientUrlRestriction;
use Illuminate\Database\Seeder;

class ClientUrlRestrictionSeeder extends Seeder
{
    public function run(): void
    {
        if (ClientUrlRestriction::query()->exists()) {
            return;
        }

        foreach (config('client_access.admin_only_patterns', []) as $pattern) {
            ClientUrlRestriction::create([
                'pattern' => $pattern,
                'is_active' => true,
            ]);
        }
    }
}
