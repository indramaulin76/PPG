<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder{
    public function run(): void
    {
        // 1. Super Admin
        $saPassword = env('SUPER_ADMIN_PASSWORD', Str::random(16));
        $superAdmin = User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt($saPassword),
            ]
        );
        $superAdmin->setRole(User::ROLE_SUPER_ADMIN);
        $superAdmin->setIsActive(true);
        $superAdmin->save();

        echo "✅ User seeder berhasil dijalankan!\n";
        echo "Super Admin: " . User::where('role', User::ROLE_SUPER_ADMIN)->count() . "\n";
        echo "⚠️  Password Super Admin: {$saPassword} (simpan di tempat aman!)\n";
    }
}
