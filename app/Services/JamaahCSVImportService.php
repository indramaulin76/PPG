<?php

namespace App\Services;

use App\Models\Desa;
use App\Models\Jamaah;
use App\Models\Kelompok;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JamaahCSVImportService
{
    private $desas = [];

    private $kelompoks = [];

    private $successCount = 0;

    private $errorCount = 0;

    private $skippedCount = 0;

    private $errors = [];

    private $delimiter = ';';

    private $headerMapping = [];

    private $rawHeader = [];

    public function __construct()
    {
        $this->initializeReferenceData();
    }

    private function initializeReferenceData()
    {
        $this->desas = Desa::pluck('id', 'nama_desa')->toArray();
        $allKelompoks = Kelompok::all();
        foreach ($allKelompoks as $k) {
            $this->kelompoks[$k->desa_id.'-'.$k->nama_kelompok] = $k->id;
        }
    }

    public function import($filePath)
    {
        if (! file_exists($filePath)) {
            throw new \Exception('File tidak ditemukan');
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        DB::beginTransaction();

        try {
            if (in_array($extension, ['xlsx', 'xls'])) {
                $this->parseExcel($filePath);
            } else {
                $this->parseCSV($filePath);
            }

            DB::commit();

            return [
                'success' => true,
                'total_processed' => $this->successCount + $this->errorCount + $this->skippedCount,
                'success_count' => $this->successCount,
                'error_count' => $this->errorCount,
                'skipped_count' => $this->skippedCount,
                'errors' => $this->errors,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import Error: '.$e->getMessage());
            throw $e;
        }
    }

    private function detectDelimiter($filePath): string
    {
        $handle = fopen($filePath, 'r');
        $firstLine = fgets($handle);
        fclose($handle);

        $semicolonCount = substr_count($firstLine, ';');
        $commaCount = substr_count($firstLine, ',');

        return ($semicolonCount > $commaCount) ? ';' : ',';
    }

    private function parseCSV($filePath)
    {
        $this->delimiter = $this->detectDelimiter($filePath);

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new \Exception('Gagal membuka file');
        }

        $this->initializeReferenceData();
        $rowNumber = 0;
        $headerFound = false;

        while (($row = fgetcsv($handle, 0, $this->delimiter)) !== false) {
            $rowNumber++;

            if (! $this->isValidDataRow($row)) {
                continue;
            }

            if (! $headerFound) {
                $this->parseHeader($row);
                $headerFound = true;

                continue;
            }

            $data = array_combine($this->rawHeader, $row);
            $this->processRow($data, $rowNumber);
        }

        fclose($handle);
    }

    private function parseExcel($filePath)
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        if (count($rows) < 2) {
            throw new \Exception('File Excel kosong atau tidak terdapat data');
        }

        $this->initializeReferenceData();
        $headerFound = false;
        $rowNumber = 1;

        foreach ($rows as $row) {
            $rowNumber++;

            if (! $this->isValidDataRow($row)) {
                continue;
            }

            if (! $headerFound) {
                $this->parseHeader($row);
                $headerFound = true;

                continue;
            }

            if (count($row) < count($this->rawHeader)) {
                $row = array_pad($row, count($this->rawHeader), null);
            } elseif (count($row) > count($this->rawHeader)) {
                $row = array_slice($row, 0, count($this->rawHeader));
            }

            $data = array_combine($this->rawHeader, $row);
            $this->processRow($data, $rowNumber);
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
    }

    private function isValidDataRow($row): bool
    {
        if (count($row) < 2) {
            return false;
        }

        $firstCell = trim($row[0] ?? '');

        if (empty($firstCell)) {
            return false;
        }

        if (str_starts_with($firstCell, ';')) {
            return false;
        }

        if (preg_match('/^(DATABASE|DATA|MASTER|TOTAL|SEDESA|SEDERAH)/i', $firstCell)) {
            return false;
        }

        if (preg_match('/^[;\s]+$/', $firstCell)) {
            return false;
        }

        return true;
    }

    private function parseHeader($row): void
    {
        $this->rawHeader = array_map(function ($cell) {
            return trim($cell ?? '');
        }, $row);

        $this->headerMapping = [
            'nama_lengkap' => $this->findHeaderFlexible(['NAMA LENGKAP', 'NAMA JAMAAH', 'NAMA', 'NAMALENGKAP']),
            'tempat_lahir' => $this->findHeaderFlexible(['TEMPAT LAHIR', 'TEMPAT', 'KOTA LAHIR', 'LOKASI']),
            'tgl_lahir' => $this->findHeaderFlexible(['TANGGAL LAHIR', 'TGL LAHIR', 'TGLLAHIR', 'TANGGAL', 'BORN']),
            'jenis_kelamin' => $this->findHeaderFlexible(['JENIS KELAMIN', 'JENIS KEL', 'L/P', 'JK', 'GENDER', 'SEX']),
            'kelas_generus' => $this->findHeaderFlexible(['KELAS GENERUS', 'KELAS', 'PAKET', 'GENERUS', 'TINGKAT']),
            'status_pernikahan' => $this->findHeaderFlexible(['STATUS PERNIKAHAN', 'STATUS NIKAH', 'NIKAH', 'STATUS']),
            'kategori_sodaqoh' => $this->findHeaderFlexible(['KATAGORI SODAQOH', 'KATEGORI SODAQOH', 'SODAQOH', 'KAT']),
            'dapukan' => $this->findHeaderFlexible(['DAPUKAN', 'JABATAN', 'STATUS DLM', 'ROLE']),
            'pekerjaan' => $this->findHeaderFlexible(['PEKERJAAN', 'JOB', 'PEKERJA']),
            'status_mubaligh' => $this->findHeaderFlexible(['DEWAN GURU', 'STATUS MUBALIGH', 'MUBALIGH', 'GURU', 'DAFTAR GURU']),
            'pendidikan_terakhir' => $this->findHeaderFlexible(['PENDIDIKAN TERAKHIR', 'PENDIDIKAN', 'IJAZAH', 'EDU']),
            'minat_kbm' => $this->findHeaderFlexible(['KBM YANG DIMINATI', 'KBM', 'MINAT KBM', 'MINAT', 'KEAHLIAN']),
            'no_telepon' => $this->findHeaderFlexible(['NO TELEPON', 'NO HP', 'TELP', 'HP', 'PHONE', 'NOMOR']),
            'desa' => $this->findHeaderStrict(['DESA']),
            'kelompok' => $this->findHeaderStrict(['KELOMPOK']),
        ];
    }

    private function findHeaderFlexible(array $aliases): ?string
    {
        foreach ($aliases as $alias) {
            $aliasUpper = strtoupper($alias);
            foreach ($this->rawHeader as $header) {
                $headerUpper = strtoupper(trim($header));
                if ($headerUpper === $aliasUpper || str_contains($headerUpper, $aliasUpper)) {
                    return $header;
                }
            }
        }

        return null;
    }

    private function findHeaderStrict(array $aliases): ?string
    {
        foreach ($aliases as $alias) {
            $aliasUpper = strtoupper($alias);
            foreach ($this->rawHeader as $header) {
                $headerUpper = strtoupper(trim($header));
                if ($headerUpper === $aliasUpper) {
                    return $header;
                }
            }
        }

        return null;
    }

    private function processRow($row, $rowNumber)
    {
        $namaLengkap = $this->getMappedValue($row, 'nama_lengkap');

        if (empty($namaLengkap)) {
            $this->skippedCount++;

            return;
        }

        try {
            $desaId = $this->handleDesa($row);
            $kelompokId = $this->handleKelompok($desaId, $row);

            $jamaahData = [
                'kelompok_id' => $kelompokId,
                'nama_lengkap' => trim($namaLengkap),
                'tempat_lahir' => $this->getMappedValue($row, 'tempat_lahir'),
                'jenis_kelamin' => $this->parseGender($this->getMappedValue($row, 'jenis_kelamin') ?? 'L'),
                'tgl_lahir' => $this->parseDate($this->getMappedValue($row, 'tgl_lahir')),
                'kelas_generus' => $this->getMappedValue($row, 'kelas_generus'),
                'status_pernikahan' => $this->parseMaritalStatus($this->getMappedValue($row, 'status_pernikahan') ?? 'BELUM'),
                'kategori_sodaqoh' => $this->getMappedValue($row, 'kategori_sodaqoh'),
                'dapukan' => $this->getMappedValue($row, 'dapukan'),
                'pekerjaan' => $this->getMappedValue($row, 'pekerjaan'),
                'status_mubaligh' => $this->getMappedValue($row, 'status_mubaligh'),
                'pendidikan_terakhir' => $this->getMappedValue($row, 'pendidikan_terakhir'),
                'minat_kbm' => $this->getMappedValue($row, 'minat_kbm'),
                'no_telepon' => $this->getMappedValue($row, 'no_telepon'),
                'role_dlm_keluarga' => 'LAINNYA',
            ];

            Jamaah::create($jamaahData);
            $this->successCount++;
        } catch (\Exception $e) {
            $this->errorCount++;
            $this->errors[] = "Baris {$rowNumber}: ".$e->getMessage();
            Log::error("Import Error Row {$rowNumber}: ".$e->getMessage());
        }
    }

    private function getMappedValue($row, $field): ?string
    {
        $header = $this->headerMapping[$field ?? ''] ?? null;
        if (! $header) {
            return null;
        }
        $value = $row[$header] ?? null;

        return ! empty(trim($value ?? '')) ? trim($value) : null;
    }

    private function handleDesa($row)
    {
        $namaDesa = strtoupper(trim($this->getMappedValue($row, 'desa') ?? 'LAINNYA'));
        if (! isset($this->desas[$namaDesa])) {
            $desa = Desa::create(['nama_desa' => $namaDesa]);
            $this->desas[$namaDesa] = $desa->id;
        }

        return $this->desas[$namaDesa];
    }

    private function handleKelompok($desaId, $row)
    {
        $namaKelompok = strtoupper(trim($this->getMappedValue($row, 'kelompok') ?? 'UMUM'));
        $kelompokKey = $desaId.'-'.$namaKelompok;

        if (! isset($this->kelompoks[$kelompokKey])) {
            $kelompok = Kelompok::create([
                'desa_id' => $desaId,
                'nama_kelompok' => $namaKelompok,
            ]);
            $this->kelompoks[$kelompokKey] = $kelompok->id;
        }

        return $this->kelompoks[$kelompokKey];
    }

    private function parseGender($val)
    {
        $val = strtoupper(substr(trim($val ?? 'L'), 0, 1));

        return in_array($val, ['L', 'P']) ? $val : 'L';
    }

    private function parseDate($val)
    {
        if (empty($val)) {
            return null;
        }

        $val = trim($val);

        if (is_numeric($val) && $val > 10000 && $val < 100000) {
            $unixDate = ($val - 25569) * 86400;

            return gmdate('Y-m-d', $unixDate);
        }

        $formats = ['d/m/Y', 'd-m-Y', 'm/d/Y', 'm-d-Y', 'd/m/y', 'd-m-y'];
        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $val)->format('Y-m-d');
            } catch (\Exception $e) {
                continue;
            }
        }

        try {
            $parsed = Carbon::parse($val);
            if ($parsed->year < 1900 || $parsed->year > date('Y') + 1) {
                return null;
            }

            return $parsed->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseMaritalStatus($val)
    {
        $val = strtoupper(trim($val ?? 'BELUM'));
        if (str_contains($val, 'JANDA') || str_contains($val, 'DUDA')) {
            return $val;
        }
        if (str_contains($val, 'NIKAH') || str_contains($val, 'MENIKAH')) {
            return 'MENIKAH';
        }

        return 'BELUM';
    }

    public function getCSVTemplate()
    {
        return [
            'headers' => [
                'DESA',
                'KELOMPOK',
                'NAMA LENGKAP',
                'TEMPAT LAHIR',
                'TANGGAL LAHIR',
                'JENIS KELAMIN',
                'PAKET',
                'STATUS PERNIKAHAN',
                'KATAGORI SODAQOH',
                'DAPUKAN',
                'PEKERJAAN',
                'DEWAN GURU',
                'PENDIDIKAN TERAKHIR',
                'KBM YANG DIMINATI',
                'NO TELEPON',
            ],
            'sample1' => [
                'JATI',
                'JATI LAMA',
                'Ahmad Budiman',
                'Jakarta',
                '15/05/1990',
                'L',
                'Umum',
                'MENIKAH',
                'AGNIYA',
                'Ketua RT',
                'Wiraswasta',
                'MT',
                'S1',
                'Tahfidz',
                '081234567890',
            ],
            'sample2' => [
                'JATI',
                'JATI BARU',
                'Siti Nurhaliza',
                'Bandung',
                '22/08/1992',
                'P',
                'Pra-Nikah',
                'BELUM',
                'CALON AGNIYA',
                'Sekretaris',
                'Guru',
                'MS',
                'S1',
                'Tahsin',
                '089876543210',
            ],
        ];
    }

    public function generateCSVTemplate(string $delimiter = ';'): string
    {
        $template = $this->getCSVTemplate();
        $lines = [];
        $lines[] = implode($delimiter, $template['headers']);
        $lines[] = $this->escapeCSVRow($template['sample1'], $delimiter);
        $lines[] = $this->escapeCSVRow($template['sample2'], $delimiter);

        return implode("\n", $lines);
    }

    private function escapeCSVRow(array $row, string $delimiter): string
    {
        return implode($delimiter, array_map(function ($value) use ($delimiter) {
            $value = trim($value);
            if ($delimiter === ';' || str_contains($value, ',') || str_contains($value, '"') || str_contains($value, "\n")) {
                return '"'.str_replace('"', '""', $value).'"';
            }

            return $value;
        }, $row));
    }
}
