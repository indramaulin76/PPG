<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jamaah;
use App\Models\Kelompok;
use Carbon\Carbon;

class JamaahSeeder extends Seeder
{
    public function run(): void
    {
        $kelompoks = Kelompok::all();
        
        $firstNames = [
            'L' => ['Ahmad', 'Muhammad', 'Budi', 'Rahmat', 'Fajar', 'Dedi', 'Andi', 'Rudi', 'Hendra', 'Joko', 'Wahyu', 'Agus', 'Bambang', 'Cecep', 'Dadang', 'Eko', 'Faisal', 'Galih', 'Hasan', 'Irfan'],
            'P' => ['Siti', 'Dewi', 'Aminah', 'Fatimah', 'Nur', 'Sri', 'Rina', 'Wati', 'Yuni', 'Ani', 'Lestari', 'Ratna', 'Putri', 'Indah', 'Maya', 'Sari', 'Fitri', 'Ningsih', 'Rahayu', 'Kartini'],
        ];
        
        $lastNames = ['Hidayat', 'Santoso', 'Wijaya', 'Pratama', 'Saputra', 'Ramadhan', 'Nugroho', 'Siregar', 'Harahap', 'Situmorang', 'Panjaitan', 'Simbolon', 'Nasution', 'Lubis', 'Hutasoit'];
        
        $statuses = ['BELUM', 'MENIKAH', 'JANDA', 'DUDA'];
        $pendidikan = ['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'KULIAH', 'BEKERJA', 'WIRASWASTA', 'PELAJAR', 'PNS', 'IRT'];
        $roles = ['KEPALA', 'ISTRI', 'ANAK', 'LAINNYA'];

        $count = 0;
        $targetCount = 500; // Sample 500 data

        while ($count < $targetCount) {
            foreach ($kelompoks as $kelompok) {
                if ($count >= $targetCount) break;

                $jamaahPerKelompok = rand(10, 20);

                for ($i = 0; $i < $jamaahPerKelompok && $count < $targetCount; $i++) {
                    $gender = rand(0, 1) ? 'L' : 'P';
                    $firstName = $firstNames[$gender][array_rand($firstNames[$gender])];
                    $lastName = $lastNames[array_rand($lastNames)];
                    
                    // Random age between 1 and 80
                    $age = rand(1, 80);
                    $birthYear = Carbon::now()->subYears($age)->year;
                    $birthMonth = rand(1, 12);
                    $birthDay = rand(1, 28);
                    
                    // Status based on age
                    $status = null;
                    if ($age >= 17) {
                        $status = $statuses[array_rand($statuses)];
                        // Adjust: young people more likely to be BELUM
                        if ($age < 25 && rand(0, 10) > 3) {
                            $status = 'BELUM';
                        }
                        // Janda/Duda more common in older ages
                        if ($age < 35 && in_array($status, ['JANDA', 'DUDA'])) {
                            $status = rand(0, 1) ? 'MENIKAH' : 'BELUM';
                        }
                    }

                    // Role based on gender and status
                    $role = null;
                    if ($age >= 17) {
                        if ($status === 'MENIKAH') {
                            $role = $gender === 'L' ? 'KEPALA' : 'ISTRI';
                        } elseif (in_array($status, ['JANDA', 'DUDA'])) {
                            $role = 'KEPALA';
                        } else {
                            $role = 'ANAK';
                        }
                    } else {
                        $role = 'ANAK';
                    }

                    // Pendidikan based on age
                    $pend = null;
                    if ($age <= 6) {
                        $pend = 'PAUD';
                    } elseif ($age <= 12) {
                        $pend = 'SD';
                    } elseif ($age <= 15) {
                        $pend = 'SMP';
                    } elseif ($age <= 18) {
                        $pend = 'SMA';
                    } elseif ($age <= 24) {
                        $pend = rand(0, 1) ? 'KULIAH' : 'BEKERJA';
                    } else {
                        $pend = $pendidikan[array_rand($pendidikan)];
                    }

                    Jamaah::create([
                        'kelompok_id' => $kelompok->id,
                        'nama_lengkap' => $firstName . ' ' . $lastName,
                        'tgl_lahir' => Carbon::create($birthYear, $birthMonth, $birthDay),
                        'jenis_kelamin' => $gender,
                        'status_pernikahan' => $status,
                        'pendidikan_aktivitas' => $pend,
                        'no_telepon' => '08' . rand(100000000, 999999999),
                        'role_dlm_keluarga' => $role,
                    ]);

                    $count++;
                }
            }
        }
    }
}
