<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder{
    public function run(): void
    {
        // 1. Super Admin
        User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'role' => User::ROLE_SUPER_ADMIN,
                'is_active' => true,
            ]
        );

        echo "✅ User seeder berhasil dijalankan!\n";
        echo "Super Admin: " . User::where('role', User::ROLE_SUPER_ADMIN)->count() . "\n";
    }
}
