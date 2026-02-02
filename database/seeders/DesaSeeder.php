<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Desa;

class DesaSeeder extends Seeder
{
    public function run(): void
    {
        $desas = [
            ['nama_desa' => 'JATI', 'kode_desa' => 'DSA001'],
            ['nama_desa' => 'PONDOK BAHAR', 'kode_desa' => 'DSA002'],
            ['nama_desa' => 'KALI DERES', 'kode_desa' => 'DSA003'],
            ['nama_desa' => 'CENGKARENG', 'kode_desa' => 'DSA004'],
            ['nama_desa' => 'DURI KOSAMBI', 'kode_desa' => 'DSA005'],
            ['nama_desa' => 'RAWA BUAYA', 'kode_desa' => 'DSA006'],
            ['nama_desa' => 'KEDAUNG KALI ANGKE', 'kode_desa' => 'DSA007'],
            ['nama_desa' => 'KAPUK', 'kode_desa' => 'DSA008'],
            ['nama_desa' => 'CENGKARENG BARAT', 'kode_desa' => 'DSA009'],
            ['nama_desa' => 'CENGKARENG TIMUR', 'kode_desa' => 'DSA010'],
            ['nama_desa' => 'PEGADUNGAN', 'kode_desa' => 'DSA011'],
            ['nama_desa' => 'KAMAL', 'kode_desa' => 'DSA012'],
        ];

        foreach ($desas as $desa) {
            Desa::create($desa);
        }
    }
}
