<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Default administrator (change password after first login in production).
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@tradegate.local'],
            [
                'name' => 'Administrator',
                'password' => 'password',
                'password_plain' => 'password',
                'role' => User::ROLE_ADMIN,
            ]
        );
    }
}
