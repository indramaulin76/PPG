<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\JamaahCSVImportService;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportLegacyJamaahSheets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jamaah:import-legacy-sheets {files?* : Specific relative file path(s) to import; omit to import the full curated list}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import legacy jamaah data from the DATABASE JAMAAH/sheets Excel files, applying known desa/kelompok name corrections first';

    private string $sheetsBase = 'C:\Users\indra\Documents\DATABASE JAMAAH\sheets';

    /**
     * Full curated file list (52 source files minus the 2 excluded rollup/duplicate files).
     *
     * @var array<string>
     */
    private array $fileList = [
        'DESA CIKUPA\1.klp cikupa.xlsx',
        'DESA CIKUPA\2.klp citra raya.xlsx',
        'DESA CIKUPA\3.klp bitung.xlsx',
        'DESA CIKUPA\4.klp graha citra.xlsx',
        'DESA CIKUPA\5.klp pasir gadung.xlsx',
        'Desa Jati\1. Bumimas.xlsx',
        'Desa Jati\2. Jati Baru.xlsx',
        'Desa Jati\3. Jati Lama.xlsx',
        'Desa Jati\4. Rawacana.xlsx',
        'DESA JAYANTI\1.klp Sempur 1.xlsx',
        'DESA JAYANTI\2.klp sempur 2.xlsx',
        'DESA JAYANTI\3.klp sumur bandung.xlsx',
        'DESA JAYANTI\4.klp balaraja.xlsx',
        'DESA JAYANTI\5.klp cangkudu.xlsx',
        'DESA JAYANTI\6.klp adiyasa.xlsx',
        'DESA JAYANTI\7.klp bukit gading.xlsx',
        'DESA JAYANTI\8.klp sukamulya.xlsx',
        'Desa Kutajaya\1. Bermis.xlsx',
        'Desa Kutajaya\2. Elok.xlsx',
        'Desa Kutajaya\3. Gelam.xlsx',
        'Desa Kutajaya\4. Puri Indah.xlsx',
        'Desa Kutajaya\5. Wisma Mas.xlsx',
        'Desa Periuk\1. Periuk Jaya 1.xlsx',
        'Desa Periuk\2. Periuk Jaya 2.xlsx',
        'Desa Periuk\3. Kotabaru Permai.xlsx',
        'Desa Periuk\4. Kotabaru Sejahtera.xlsx',
        'Desa Periuk\5. Kotabumi.xlsx',
        'Desa Periuk\6. Pondok Arum.xlsx',
        'Desa Periuk\7. Sangiang.xlsx',
        'Desa Periuk\8. Sarakan Selatan.xlsx',
        'Desa Periuk\9. Sarakan Utama.xlsx',
        'Desa Pondok Alam Permai\1. Keroncong.xlsx',
        'Desa Pondok Alam Permai\2. Pondok Makmur.xlsx',
        'Desa Pondok Alam Permai\3. Purati.xlsx',
        'Desa Pondok Alam Permai\4. Taman Kota.xlsx',
        'DESA RAJEG RAYA\1.klp rajeg raya.xlsx',
        'DESA RAJEG RAYA\2.klp rajeg asri.xlsx',
        'DESA RAJEG RAYA\3.klp taman walet.xlsx',
        'DESA RAJEG RAYA\4.klp sukatani.xlsx',
        'DESA RAJEG RAYA\5.klp tanjakan mekar.xlsx',
        'DESA TIGARAKSA\1.klp tigaraksa 1.xlsx',
        'DESA TIGARAKSA\2.klp tigaraksa 2.xlsx',
        'DESA TIGARAKSA\3.klp rancagede 1.xlsx',
        'DESA TIGARAKSA\4.klp rancagede 2.xlsx',
        'DESA TIGARAKSA\5.klp rancagede 3.xlsx',
        'DESA TIGARAKSA\6.klp sudirman.xlsx',
        'DESA TIGARAKSA\7.klp munjul.xlsx',
        'DESA TIGARAKSA\8.klp mustika.xlsx',
        'DESA TIGARAKSA\9.klp bidara.xlsx',
        'klp PONDOK BM\klp PONDOK BM.xlsx',
        // Excluded on purpose (see plan): 'Desa Periuk\10. Total.xlsx' (rollup duplicate),
        // 'Desa Jati\Lainnya.xlsx' (exact duplicate of Rajeg Raya/Tanjakan Mekar).
    ];

    /** Desa cell corrections: raw sheet value (uppercased) => DB value. */
    private array $desaCorrections = [
        'KUTAJAYA' => 'KUTA JAYA',
    ];

    /** Kelompok cell corrections: raw sheet value (uppercased) => DB value. */
    private array $kelompokCorrections = [
        'KUTABARU PERMAI' => 'KOTA BARU PERMAI',
        'KUTABARU SEJAHTERA' => 'KOTA BARU SEJAHTERA',
        'KUTABUMI' => 'KOTA BUMI',
        'PERIUK JAYA 1' => 'PERIUK JAYA',
        'TAMAN WALET' => 'WALET',
        'SEMPUR1' => 'SEMPUR 1',
        'BUKIT GADING' => 'GADING',
        'RANCAGEDE 1' => 'RANCA GEDE 1',
        'RANCAGEDE 2' => 'RANCA GEDE 2',
        'RANCAGEDE 3' => 'RANCA GEDE 3',
        'SUB KELOMPOK KIRANA' => 'ADIYASA',
        'PONDOK BM' => 'PONDOK',
    ];

    /** Kategori Sodaqoh spelling corrections. */
    private array $sodaqohCorrections = [
        'CALON AGHNIA' => 'CALON AGNIYA',
        'CALON AGNHIA' => 'CALON AGNIYA',
        'CALON AGHNIYA' => 'CALON AGNIYA',
    ];

    /** Pendidikan Terakhir synonym corrections. */
    private array $pendidikanCorrections = [
        'SLTA' => 'SMA/SMK',
        'SMA' => 'SMA/SMK',
        'SMK' => 'SMA/SMK',
        'STM' => 'SMA/SMK',
        'SLTP' => 'SMP',
        'D1' => 'DIPLOMA',
        'D3' => 'DIPLOMA',
        'DIII' => 'DIPLOMA',
    ];

    /** Files whose DESA column is blank for every row and must be forced to a specific desa. */
    private array $forceDesa = [
        'klp PONDOK BM\klp PONDOK BM.xlsx' => 'PONDOK',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $files = $this->argument('files');
        $files = ! empty($files) ? $files : $this->fileList;

        $user = User::where('role', User::ROLE_DEVELOPER)->first()
            ?? User::where('role', User::ROLE_SUPER_ADMIN)->first();

        if (! $user) {
            $this->error('Tidak ada akun Developer/Super Admin ditemukan untuk menjalankan import.');

            return 1;
        }

        $this->info("Menjalankan import sebagai: {$user->name} ({$user->username})");

        $tmpDir = storage_path('app/tmp-legacy-import');
        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $grandTotal = ['success' => 0, 'error' => 0, 'skipped' => 0];
        $allErrors = [];

        foreach ($files as $relativePath) {
            $sourcePath = $this->sheetsBase.'\\'.$relativePath;
            $this->line('');
            $this->info("=== {$relativePath} ===");

            if (! file_exists($sourcePath)) {
                $this->error("File tidak ditemukan: {$sourcePath}");

                continue;
            }

            try {
                $correctedCsvPath = $this->buildCorrectedCsv($sourcePath, $relativePath, $tmpDir);
            } catch (\Throwable $e) {
                $this->error('Gagal memproses file: '.$e->getMessage());

                continue;
            }

            $service = new JamaahCSVImportService($user);
            $result = $service->import($correctedCsvPath);

            @unlink($correctedCsvPath);

            $this->line("Berhasil: {$result['success_count']} | Gagal: {$result['error_count']} | Dilewati: {$result['skipped_count']}");

            $grandTotal['success'] += $result['success_count'];
            $grandTotal['error'] += $result['error_count'];
            $grandTotal['skipped'] += $result['skipped_count'];

            foreach ($result['errors'] as $err) {
                $allErrors[] = "[{$relativePath}] {$err}";
            }
        }

        $this->line('');
        $this->info('===== RINGKASAN TOTAL =====');
        $this->line("Total Berhasil : {$grandTotal['success']}");
        $this->line("Total Gagal    : {$grandTotal['error']}");
        $this->line("Total Dilewati : {$grandTotal['skipped']}");

        if (! empty($allErrors)) {
            $this->line('');
            $this->warn('Daftar error:');
            foreach (array_slice($allErrors, 0, 50) as $err) {
                $this->line('  - '.$err);
            }
            if (count($allErrors) > 50) {
                $this->line('  ... dan '.(count($allErrors) - 50).' error lainnya.');
            }
        }

        return 0;
    }

    /**
     * Read the source xlsx, apply desa/kelompok/sodaqoh/pendidikan corrections,
     * and write a corrected semicolon-delimited CSV that JamaahCSVImportService can consume.
     */
    private function buildCorrectedCsv(string $sourcePath, string $relativePath, string $tmpDir): string
    {
        $spreadsheet = IOFactory::load($sourcePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $headerRowIdx = null;
        foreach ($rows as $idx => $row) {
            $upper = array_map(fn ($c) => strtoupper(trim((string) ($c ?? ''))), $row);
            if (in_array('NAMA LENGKAP', $upper)) {
                $headerRowIdx = $idx;
                break;
            }
        }

        if ($headerRowIdx === null) {
            throw new \RuntimeException('Header (NAMA LENGKAP) tidak ditemukan di file.');
        }

        $header = array_map(fn ($c) => strtoupper(trim((string) ($c ?? ''))), $rows[$headerRowIdx]);
        $colIdx = array_flip($header);
        $forcedDesa = $this->forceDesa[$relativePath] ?? null;

        $outPath = $tmpDir.DIRECTORY_SEPARATOR.'corrected_'.md5($relativePath).'.csv';
        $handle = fopen($outPath, 'w');

        fputcsv($handle, $header, ';');

        for ($i = $headerRowIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $nama = trim((string) ($row[$colIdx['NAMA LENGKAP']] ?? ''));
            if ($nama === '') {
                continue;
            }
            $firstCell = trim((string) ($row[0] ?? ''));
            if (preg_match('/^(DATABASE|DATA|MASTER|TOTAL|SEDESA|SEDERAH)/i', $firstCell)) {
                continue;
            }

            $out = [];
            foreach ($header as $colName) {
                $val = trim((string) ($row[$colIdx[$colName]] ?? ''));

                if ($colName === 'DESA') {
                    $val = $forcedDesa ?? $this->cleanAndCorrect($val, $this->desaCorrections);
                } elseif ($colName === 'KELOMPOK') {
                    $val = $this->cleanAndCorrect($val, $this->kelompokCorrections);
                } elseif ($colName === 'KATAGORI SODAQOH') {
                    $val = $this->cleanAndCorrect($val, $this->sodaqohCorrections);
                } elseif ($colName === 'PENDIDIKAN TERAKHIR') {
                    $val = $this->cleanAndCorrect($val, $this->pendidikanCorrections);
                } elseif (in_array($colName, ['KELAS GENERUS', 'DAPUKAN', 'PEKERJAAN'])) {
                    $val = $val === '-' ? '' : $val;
                }

                $out[] = $val;
            }

            fputcsv($handle, $out, ';');
        }

        fclose($handle);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $outPath;
    }

    /**
     * Strip stray punctuation, uppercase, then apply an exact-match correction table.
     */
    private function cleanAndCorrect(string $val, array $corrections): string
    {
        if ($val === '' || $val === '-') {
            return '';
        }

        $clean = strtoupper(trim(preg_replace('/[^A-Za-z0-9\/\-\s]/', '', $val)));
        $clean = preg_replace('/\s+/', ' ', $clean);

        return $corrections[$clean] ?? $clean;
    }
}
