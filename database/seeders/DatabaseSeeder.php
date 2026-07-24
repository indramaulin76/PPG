<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Desa;
use App\Models\Kelompok;
use App\Models\Keluarga;
use App\Models\Jamaah;
use App\Models\User;
use Illuminate\Support\Str;

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
        $devPassword = env('DEVELOPER_PASSWORD', Str::random(16));
        $dev = User::updateOrCreate(
            ['username' => 'indra'],
            [
                'name' => 'Indra Developer',
                'password' => bcrypt($devPassword),
            ]
        );
        $dev->setRole(User::ROLE_DEVELOPER);
        $dev->setIsActive(true);
        $dev->save();
        $this->command->info('✅ User Developer Indra berhasil disiapkan.');
        $this->command->warn('   Password: ' . $devPassword . ' (simpan di tempat aman!)');

        // 3. Masukkan Data Master Desa & Kelompok
        $this->call([
            DesaKelompokSeeder::class,
        ]);
        $this->command->info('✅ Master Data Desa & Kelompok berhasil dimasukkan.');

        // 4. (Opsional) Buat Akun Admin untuk setiap Desa & Kelompok
        $desas = Desa::all();
        $adminPasswords = [];
        foreach ($desas as $desa) {
            // Admin Desa
            $adminDesaPassword = Str::random(12);
            $adminDesa = User::updateOrCreate(
                ['username' => 'admindesa_' . strtolower(str_replace(' ', '', $desa->nama_desa))],
                [
                    'name' => 'Admin Desa ' . $desa->nama_desa,
                    'password' => bcrypt($adminDesaPassword),
                    'desa_id' => $desa->id,
                ]
            );
            $adminDesa->setRole(User::ROLE_ADMIN_DESA);
            $adminDesa->setIsActive(true);
            $adminDesa->save();
            $adminPasswords[] = "Admin Desa {$desa->nama_desa}: {$adminDesaPassword}";

            // Admin Kelompok
            foreach ($desa->kelompoks as $kelompok) {
                $adminKlpPassword = Str::random(12);
                $adminKlp = User::updateOrCreate(
                    ['username' => 'adminklp_' . strtolower(str_replace(' ', '', $kelompok->nama_kelompok))],
                    [
                        'name' => 'Admin Kelompok ' . $kelompok->nama_kelompok,
                        'password' => bcrypt($adminKlpPassword),
                        'desa_id' => $desa->id,
                        'kelompok_id' => $kelompok->id,
                    ]
                );
                $adminKlp->setRole(User::ROLE_ADMIN_KELOMPOK);
                $adminKlp->setIsActive(true);
                $adminKlp->save();
                $adminPasswords[] = "Admin Kelompok {$kelompok->nama_kelompok}: {$adminKlpPassword}";
            }
        }
        $this->command->info('✅ Akun Admin Desa & Kelompok berhasil disiapkan.');
        $this->command->warn('--- PASSWORD AKUN (simpan di tempat aman!) ---');
        foreach ($adminPasswords as $line) {
            $this->command->warn("  {$line}");
        }
        $this->command->warn('---------------------------------------------');

        // Ringkasan
        $this->command->info('--- RINGKASAN DATABASE ---');
        $this->command->info('Total User    : ' . User::count());
        $this->command->info('Total Desa    : ' . Desa::count());
        $this->command->info('Total Kelompok: ' . Kelompok::count());
        $this->command->info('Total Jamaah  : ' . Jamaah::count());
        $this->command->info('--------------------------');
    }
}
