<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@jemaah.com'],
            [
                'name' => 'Admin Jemaah',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        echo "✅ User seeder berhasil dijalankan!\n";
        echo "Admin: " . User::where('role', 'admin')->count() . "\n";
        echo "Operator: " . User::where('role', 'operator')->count() . "\n";
    }
}
