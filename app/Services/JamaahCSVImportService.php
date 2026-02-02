<?php

namespace App\Services;

use App\Models\Desa;
use App\Models\Jamaah;
use App\Models\Kelompok;
use App\Models\Keluarga;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JamaahCSVImportService
{
    private $desas = [];
    private $kelompoks = [];
    private $successCount = 0;
    private $errorCount = 0;
    private $errors = [];

    public function __construct()
    {
        $this->desas = Desa::pluck('id', 'nama_desa')->toArray();
        $allKelompoks = Kelompok::all();
        foreach ($allKelompoks as $k) {
            $this->kelompoks[$k->desa_id . '-' . $k->nama_kelompok] = $k->id;
        }
    }

    public function import($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception('File tidak ditemukan');
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new \Exception('Gagal membuka file');
        }

        $header = fgetcsv($handle);
        $rowNumber = 2;

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $data = array_combine($header, $row);
                $this->processRow($data, $rowNumber);
                $rowNumber++;
            }

            fclose($handle);
            DB::commit();

            return [
                'success' => true,
                'total_processed' => $this->successCount + $this->errorCount,
                'success_count' => $this->successCount,
                'error_count' => $this->errorCount,
                'errors' => $this->errors
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            Log::error('CSV Import Error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function processRow($row, $rowNumber)
    {
        if (empty($row['nama_jamaah'])) {
            return;
        }

        try {
            $desaId = $this->handleDesa($row);
            $kelompokId = $this->handleKelompok($desaId, $row);
            $keluargaId = $this->handleKeluarga($row);

            Jamaah::create([
                'kelompok_id' => $kelompokId,
                'keluarga_id' => $keluargaId,
                'nama_lengkap' => $row['nama_jamaah'],
                'jenis_kelamin' => $this->parseGender($row['jenis_kelamin'] ?? 'L'),
                'tgl_lahir' => $this->parseDate($row['tgl_lahir'] ?? null),
                'status_pernikahan' => $this->parseMaritalStatus($row['status_pernikahan'] ?? 'BELUM'),
                'pendidikan_aktivitas' => $row['pendidikan'] ?? null,
                'no_telepon' => $row['no_hp'] ?? null,
                'role_dlm_keluarga' => $this->parseFamilyRole($row['status_keluarga'] ?? 'LAINNYA'),
            ]);

            $this->successCount++;
        } catch (\Exception $e) {
            $this->errorCount++;
            $this->errors[] = "Baris {$rowNumber}: " . $e->getMessage();
            Log::error("CSV Import Error Row {$rowNumber}: " . $e->getMessage());
        }
    }

    private function handleDesa($row)
    {
        $namaDesa = strtoupper(trim($row['desa'] ?? 'LAINNYA'));
        if (!isset($this->desas[$namaDesa])) {
            $desa = Desa::create(['nama_desa' => $namaDesa]);
            $this->desas[$namaDesa] = $desa->id;
        }
        return $this->desas[$namaDesa];
    }

    private function handleKelompok($desaId, $row)
    {
        $namaKelompok = strtoupper(trim($row['kelompok'] ?? 'UMUM'));
        $kelompokKey = $desaId . '-' . $namaKelompok;

        if (!isset($this->kelompoks[$kelompokKey])) {
            $kelompok = Kelompok::create([
                'desa_id' => $desaId,
                'nama_kelompok' => $namaKelompok
            ]);
            $this->kelompoks[$kelompokKey] = $kelompok->id;
        }
        return $this->kelompoks[$kelompokKey];
    }

    private function handleKeluarga($row)
    {
        if (empty($row['no_kk'])) {
            return null;
        }

        $noKk = trim($row['no_kk']);
        $keluarga = Keluarga::firstOrCreate(
            ['no_kk' => $noKk],
            ['alamat_rumah' => $row['alamat'] ?? null]
        );

        return $keluarga->id;
    }

    private function parseGender($val)
    {
        $val = strtoupper(substr($val, 0, 1));
        return in_array($val, ['L', 'P']) ? $val : 'L';
    }

    private function parseDate($val)
    {
        if (empty($val)) return null;
        try {
            return Carbon::parse($val)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseMaritalStatus($val)
    {
        $val = strtoupper($val);
        if (str_contains($val, 'NIKAH')) return 'MENIKAH';
        if (str_contains($val, 'JANDA')) return 'JANDA';
        if (str_contains($val, 'DUDA')) return 'DUDA';
        return 'BELUM';
    }

    private function parseFamilyRole($val)
    {
        $val = strtoupper($val);
        if (str_contains($val, 'KEPALA')) return 'KEPALA';
        if (str_contains($val, 'ISTRI')) return 'ISTRI';
        if (str_contains($val, 'ANAK')) return 'ANAK';
        return 'LAINNYA';
    }

    public function getCSVTemplate()
    {
        return [
            'nama_jamaah,desa,kelompok,jenis_kelamin,tgl_lahir,status_pernikahan,no_kk,no_hp,status_keluarga,alamat,pendidikan',
            'Ahmad Budiman,SUKAMAJU,TARBIYAH,L,1990-05-15,MENIKAH,3201011234567890,08123456789,KEPALA,Jl. Merdeka No. 1,S1',
            'Siti Nurhaliza,SUKAMAJU,MUSLIMAH,P,1992-08-22,MENIKAH,3201011234567890,08234567890,ISTRI,Jl. Merdeka No. 1,S1',
        ];
    }
}