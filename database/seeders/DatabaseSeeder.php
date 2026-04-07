<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Desa;
use App\Models\Kelompok;
use App\Models\Keluarga;
use App\Models\Jamaah;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database. 
     */
    public function run(): void
    {
        // 1. Buat User Super Admin
        $this->call([
            UserSeeder::class,
        ]);

        // 2. Buat User Developer (Indra)
        User::updateOrCreate(
            ['username' => 'indra'],
            [
                'name' => 'Indra Developer',
                'password' => bcrypt('indra123'),
                'role' => User::ROLE_DEVELOPER,
                'is_active' => true
            ]
        );
        $this->command->info('✅ User Developer Indra berhasil disiapkan.');

        // 3. Masukkan Data Master Desa & Kelompok
        $this->call([
            DesaKelompokSeeder::class,
        ]);
        $this->command->info('✅ Master Data Desa & Kelompok berhasil dimasukkan.');

        // 4. (Opsional) Buat Akun Admin untuk setiap Desa & Kelompok
        $desas = Desa::all();
        foreach ($desas as $desa) {
            // Admin Desa
            User::updateOrCreate(
                ['username' => 'admindesa_' . strtolower(str_replace(' ', '', $desa->nama_desa))],
                [
                    'name' => 'Admin Desa ' . $desa->nama_desa,
                    'password' => bcrypt('password'),
                    'role' => User::ROLE_ADMIN_DESA,
                    'desa_id' => $desa->id,
                    'is_active' => true,
                ]
            );

            // Admin Kelompok
            foreach ($desa->kelompoks as $kelompok) {
                User::updateOrCreate(
                    ['username' => 'adminklp_' . strtolower(str_replace(' ', '', $kelompok->nama_kelompok))],
                    [
                        'name' => 'Admin Kelompok ' . $kelompok->nama_kelompok,
                        'password' => bcrypt('password'),
                        'role' => User::ROLE_ADMIN_KELOMPOK,
                        'desa_id' => $desa->id,
                        'kelompok_id' => $kelompok->id,
                        'is_active' => true,
                    ]
                );
            }
        }
        $this->command->info('✅ Akun Admin Desa & Kelompok berhasil disiapkan.');

        // Ringkasan
        $this->command->info('--- RINGKASAN DATABASE ---');
        $this->command->info('Total User    : ' . User::count());
        $this->command->info('Total Desa    : ' . Desa::count());
        $this->command->info('Total Kelompok: ' . Kelompok::count());
        $this->command->info('Total Jamaah  : ' . Jamaah::count());
        $this->command->info('--------------------------');
    }
}
