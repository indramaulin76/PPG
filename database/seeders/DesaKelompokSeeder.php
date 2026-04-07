<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\Kelompok;
use Illuminate\Database\Seeder;

class DesaKelompokSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'CIKUPA' => ['BITUNG', 'CIKUPA', 'CITRA RAYA', 'GRAHA CITRA', 'PASIR GADUNG'],
            'JATI' => ['BUMIMAS', 'JATI BARU', 'JATI LAMA', 'RAWACANA'],
            'JAYANTI' => ['ADIYASA', 'BALARAJA', 'CANGKUDU', 'GADING', 'SEMPUR 1', 'SEMPUR 2', 'SUKAMULYA', 'SUMUR BANDUNG'],
            'KUTA JAYA' => ['BERMIS', 'ELOK', 'GELAM', 'PURI INDAH', 'WISMA MAS'],
            'PAP' => ['KERONCONG', 'PONDOK MAKMUR', 'PURATI', 'TAMAN KOTA'],
            'PRIUK JAYA' => ['KOTA BARU PERMAI', 'KOTA BARU SEJAHTERA', 'KOTA BUMI', 'PONDOK ARUM', 'PRIUK JAYA', 'PRIUK JAYA 2', 'SANGIANG', 'SARAKAN SELATAN', 'SARAKAN UTAMA'],
            'RAJEG RAYA' => ['RAJEG ASRI', 'TAMAN RAYA', 'SUKATANI', 'TANJAKAN MEKAR', 'WALET'],
            'TIGARAKSA' => ['BIDARA', 'MUNJUL', 'MUSTIKA', 'RANCA GEDE 1', 'RANCA GEDE 2', 'RANCA GEDE 3', 'SUDIRMAN', 'TIGARAKSA 1', 'TIGARAKSA 2'],
        ];

        foreach ($data as $namaDesa => $kelompoks) {
            $desa = Desa::create(['nama_desa' => $namaDesa]);
            foreach ($kelompoks as $namaKelompok) {
                Kelompok::create([
                    'desa_id' => $desa->id,
                    'nama_kelompok' => $namaKelompok,
                ]);
            }
        }
    }
}
