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
        // Flexible column mapping (support both formats)
        $namaLengkap = $row['NAMA LENGKAP'] ?? $row['nama_jamaah'] ?? null;
        
        if (empty($namaLengkap)) {
            return;
        }

        try {
            $desaId = $this->handleDesa($row);
            $kelompokId = $this->handleKelompok($desaId, $row);
            $keluargaId = $this->handleKeluarga($row);

            Jamaah::create([
                'kelompok_id' => $kelompokId,
                'keluarga_id' => $keluargaId,
                'nama_lengkap' => $namaLengkap,
                'tempat_lahir' => $row['TEMPAT LAHIR'] ?? $row['tempat_lahir'] ?? null,
                'jenis_kelamin' => $this->parseGender($row['JENIS KELAMIN'] ?? $row['jenis_kelamin'] ?? 'L'),
                'tgl_lahir' => $this->parseDate($row['TANGGAL LAHIR'] ?? $row['tgl_lahir'] ?? null),
                'kelas_generus' => $row['KELAS GENERUS'] ?? $row['kelas_generus'] ?? null,
                'status_pernikahan' => $this->parseMaritalStatus($row['STATUS PERNIKAHAN'] ?? $row['status_pernikahan'] ?? 'BELUM'),
                'kategori_sodaqoh' => $row['KATAGORI SODAQOH'] ?? $row['kategori_sodaqoh'] ?? null,
                'dapukan' => $row['DAPUKAN'] ?? $row['dapukan'] ?? null,
                'pekerjaan' => $row['PEKERJAAN'] ?? $row['pekerjaan'] ?? null,
                'status_mubaligh' => $row['DEWAN GURU'] ?? $row['status_mubaligh'] ?? null,
                'pendidikan_terakhir' => $row['PENDIDIKAN TERAKHIR'] ?? $row['pendidikan_terakhir'] ?? null,
                'minat_kbm' => $row['KBM YANG DIMINATI'] ?? $row['minat_kbm'] ?? null,
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
        $namaDesa = strtoupper(trim($row['DESA'] ?? $row['desa'] ?? 'LAINNYA'));
        if (!isset($this->desas[$namaDesa])) {
            $desa = Desa::create(['nama_desa' => $namaDesa]);
            $this->desas[$namaDesa] = $desa->id;
        }
        return $this->desas[$namaDesa];
    }

    private function handleKelompok($desaId, $row)
    {
        $namaKelompok =strtoupper(trim($row['KELOMPOK'] ?? $row['kelompok'] ?? 'UMUM'));
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
            // Try d/m/Y format (Indonesian)
            return Carbon::createFromFormat('d/m/Y', trim($val))->format('Y-m-d');
        } catch (\Exception $e) {
            try {
                // Try m/d/Y format (US)
                return Carbon::createFromFormat('m/d/Y', trim($val))->format('Y-m-d');
            } catch (\Exception $e2) {
                try {
                    // Try standard Carbon parse
                    $parsed = Carbon::parse(trim($val));
                    // Validate year (reject obviously wrong years)
                    if ($parsed->year < 1900 || $parsed->year > date('Y')) {
                        return null;
                    }
                    return $parsed->format('Y-m-d');
                } catch (\Exception $e3) {
                    return null;
                }
            }
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
            'DESA,KELOMPOK,NAMA LENGKAP,TEMPAT LAHIR,TANGGAL LAHIR,JENIS KELAMIN,KELAS GENERUS,STATUS PERNIKAHAN,KATAGORI SODAQOH,DAPUKAN,PEKERJAAN,DEWAN GURU,PENDIDIKAN TERAKHIR,KBM YANG DIMINATI',
            'JATI,JATI LAMA,Ahmad Budiman,Jakarta,15/05/1990,L,PEMUDA,MENIKAH,AGNIYA,Ketua RT,Wiraswasta,MT,S1,Tahfidz',
            'JATI,JATI BARU,Siti Nurhaliza,Bandung,22/08/1992,P,PRA NIKAH,BELUM MENIKAH,CALON AGNIYA,Sekretaris,Guru,MS,S1,Tahsin',
        ];
    }
}