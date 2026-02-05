<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Desa;
use App\Models\Kelompok;
use App\Models\Keluarga;
use App\Models\Jamaah;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database. 
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
        ]);
        
        // Buat data dummy untuk testing
        
        // 1. Buat Desa
        $desas = [
            ['nama_desa' => 'JATI', 'kode_desa' => 'JT01'],
            ['nama_desa' => 'PONDOK BM', 'kode_desa' => 'PB01'],
            ['nama_desa' => 'KALI DERES', 'kode_desa' => 'KD01'],
        ];
        
        foreach ($desas as $desaData) {
            $desa = Desa::create($desaData);
            
            // 2. Buat Kelompok untuk setiap desa
            $kelompoks = [
                $desa->kelompoks()->create(['nama_kelompok' => $desaData['nama_desa'] . ' TIMUR']),
                $desa->kelompoks()->create(['nama_kelompok' => $desaData['nama_desa'] . ' BARAT']),
            ];
            
            // 3. Buat Keluarga dan Jamaah untuk testing
            foreach ($kelompoks as $kelompok) {
                for ($i = 1; $i <= 3; $i++) {
                    $keluarga = Keluarga::create([
                        'no_kk' => fake()->numerify('33########'),
                        'alamat_rumah' => fake()->address(),
                    ]);
                    
                    // Buat Kepala Keluarga
                    $kepala = Jamaah::create([
                        'kelompok_id' => $kelompok->id,
                        'keluarga_id' => $keluarga->id,
                        'nama_lengkap' => fake()->name('male'),
                        'tgl_lahir' => fake()->dateTimeBetween('-65 years', '-25 years')->format('Y-m-d'),
                        'jenis_kelamin' => 'L',
                        'status_pernikahan' => 'MENIKAH',
                        'pendidikan_aktivitas' => fake()->randomElement(['SD', 'SMP', 'SMA', 'S1', 'Bekerja']),
                        'no_telepon' => fake()->phoneNumber(),
                        'role_dlm_keluarga' => 'KEPALA',
                    ]);
                    
                    // Update kepala keluarga
                    $keluarga->update(['kepala_keluarga_id' => $kepala->id]);
                    
                    // Buat Istri
                    Jamaah::create([
                        'kelompok_id' => $kelompok->id,
                        'keluarga_id' => $keluarga->id,
                        'nama_lengkap' => fake()->name('female'),
                        'tgl_lahir' => fake()->dateTimeBetween('-60 years', '-20 years')->format('Y-m-d'),
                        'jenis_kelamin' => 'P',
                        'status_pernikahan' => 'MENIKAH',
                        'pendidikan_aktivitas' => fake()->randomElement(['SD', 'SMP', 'SMA', 'S1', 'Ibu Rumah Tangga']),
                        'no_telepon' => fake()->phoneNumber(),
                        'role_dlm_keluarga' => 'ISTRI',
                    ]);
                    
                    // Buat Anak-anak (2-4 anak)
                    $jumlahAnak = rand(2, 4);
                    for ($j = 0; $j < $jumlahAnak; $j++) {
                        Jamaah::create([
                            'kelompok_id' => $kelompok->id,
                            'keluarga_id' => $keluarga->id,
                            'nama_lengkap' => fake()->name(fake()->randomElement(['male', 'female'])),
                            'tgl_lahir' => fake()->dateTimeBetween('-20 years', '-1 years')->format('Y-m-d'),
                            'jenis_kelamin' => fake()->randomElement(['L', 'P']),
                            'status_pernikahan' => 'BELUM',
                            'pendidikan_aktivitas' => fake()->randomElement(['BALITA', 'TK', 'SD', 'SMP', 'SMA', 'KULIAH']),
                            'no_telepon' => null,
                            'role_dlm_keluarga' => 'ANAK',
                        ]);
                    }
                }
            }
        }
        
        $this->command->info('✅ Dummy data berhasil dibuat!');
        $this->command->info('Total Desa: ' . Desa::count());
        $this->command->info('Total Kelompok: ' . Kelompok::count());
        $this->command->info('Total Keluarga: ' . Keluarga::count());
        $this->command->info('Total Jamaah: ' . Jamaah::count());
    }
}
