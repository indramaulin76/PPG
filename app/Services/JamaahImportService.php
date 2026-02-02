<?php

namespace App\Services;

use App\Models\Desa;
use App\Models\Jamaah;
use App\Models\Kelompok;
use App\Models\Keluarga;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class JamaahImportService implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts
{
    private $desas = [];
    private $kelompoks = [];
    
    public function __construct()
    {
        // Cache existing data to minimize queries
        $this->desas = Desa::pluck('id', 'nama_desa')->toArray();
        // For kelompoks we need composite key desa_id-nama_kelompok
        $allKelompoks = Kelompok::all();
        foreach ($allKelompoks as $k) {
            $this->kelompoks[$k->desa_id . '-' . $k->nama_kelompok] = $k->id;
        }
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Skip empty rows
        if (!isset($row['nama_jamaah']) || empty($row['nama_jamaah'])) {
            return null;
        }

        try {
            return DB::transaction(function () use ($row) {
                // 1. Handle Desa
                $namaDesa = strtoupper(trim($row['desa'] ?? 'LAINNYA'));
                if (!isset($this->desas[$namaDesa])) {
                    $desa = Desa::create(['nama_desa' => $namaDesa]);
                    $this->desas[$namaDesa] = $desa->id;
                }
                $desaId = $this->desas[$namaDesa];

                // 2. Handle Kelompok
                $namaKelompok = strtoupper(trim($row['kelompok'] ?? 'UMUM'));
                $kelompokKey = $desaId . '-' . $namaKelompok;
                
                if (!isset($this->kelompoks[$kelompokKey])) {
                    $kelompok = Kelompok::create([
                        'desa_id' => $desaId,
                        'nama_kelompok' => $namaKelompok
                    ]);
                    $this->kelompoks[$kelompokKey] = $kelompok->id;
                }
                $kelompokId = $this->kelompoks[$kelompokKey];

                // 3. Handle Keluarga (Optional for now, simple implementation)
                // If NO_KK provided, link it, otherwise create new or leave null
                $keluargaId = null;
                if (!empty($row['no_kk'])) {
                    $noKk = trim($row['no_kk']);
                    $keluarga = Keluarga::firstOrCreate(
                        ['no_kk' => $noKk],
                        ['alamat_rumah' => $row['alamat'] ?? null]
                    );
                    $keluargaId = $keluarga->id;
                }

                // 4. Create Jamaah
                return new Jamaah([
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
            });
        } catch (\Exception $e) {
            Log::error('Import Error Row: ' . json_encode($row) . ' Error: ' . $e->getMessage());
            return null;
        }
    }
    
    private function parseGender($val) {
        $val = strtoupper(substr($val, 0, 1));
        return in_array($val, ['L', 'P']) ? $val : 'L';
    }
    
    private function parseDate($val) {
        if (empty($val)) return null;
        try {
            // Try standard formats
            return Carbon::parse($val)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
    
    private function parseMaritalStatus($val) {
        $val = strtoupper($val);
        if (str_contains($val, 'NIKAH')) return 'MENIKAH';
        if (str_contains($val, 'JANDA')) return 'JANDA';
        if (str_contains($val, 'DUDA')) return 'DUDA';
        return 'BELUM';
    }
    
    private function parseFamilyRole($val) {
        $val = strtoupper($val);
        if (str_contains($val, 'KEPALA')) return 'KEPALA';
        if (str_contains($val, 'ISTRI')) return 'ISTRI';
        if (str_contains($val, 'ANAK')) return 'ANAK';
        return 'LAINNYA';
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
