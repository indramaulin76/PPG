<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Desa;
use App\Models\Kelompok;

class KelompokSeeder extends Seeder
{
    public function run(): void
    {
        $kelompoks = [
            'JATI' => ['JATI LAMA', 'JATI BARU', 'JATI TIMUR', 'JATI BARAT'],
            'PONDOK BAHAR' => ['PONDOK TIMUR', 'PONDOK BARAT', 'PONDOK SELATAN'],
            'KALI DERES' => ['KALI UTARA', 'KALI SELATAN', 'KALI TENGAH'],
            'CENGKARENG' => ['CENGKARENG PUSAT', 'CENGKARENG MAKMUR', 'CENGKARENG JAYA'],
            'DURI KOSAMBI' => ['DURI UTARA', 'DURI SELATAN'],
            'RAWA BUAYA' => ['RAWA SATU', 'RAWA DUA', 'RAWA TIGA'],
            'KEDAUNG KALI ANGKE' => ['KEDAUNG TIMUR', 'KEDAUNG BARAT'],
            'KAPUK' => ['KAPUK UTARA', 'KAPUK SELATAN', 'KAPUK TENGAH'],
            'CENGKARENG BARAT' => ['CB SATU', 'CB DUA'],
            'CENGKARENG TIMUR' => ['CT SATU', 'CT DUA', 'CT TIGA'],
            'PEGADUNGAN' => ['PEGADUNGAN SATU', 'PEGADUNGAN DUA'],
            'KAMAL' => ['KAMAL UTARA', 'KAMAL SELATAN', 'KAMAL TIMUR'],
        ];

        foreach ($kelompoks as $namaDesa => $namaKelompoks) {
            $desa = Desa::where('nama_desa', $namaDesa)->first();
            if ($desa) {
                foreach ($namaKelompoks as $namaKelompok) {
                    Kelompok::create([
                        'desa_id' => $desa->id,
                        'nama_kelompok' => $namaKelompok,
                    ]);
                }
            }
        }
    }
}
