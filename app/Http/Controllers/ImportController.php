<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\Jamaah;
use App\Services\JamaahCSVExportService;
use App\Services\JamaahCSVImportService;
use App\Services\JamaahExcelExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImportController extends Controller
{
    public function index()
    {
        return inertia('Import/Index');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ]);

        try {
            $file = $request->file('file');

            if (! $file->isValid()) {
                throw new \Exception('File upload tidak valid');
            }

            $originalName = $file->getClientOriginalName();
            $sanitizedName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $originalName);
            $fileName = time().'_'.$sanitizedName;
            $filePath = $file->storeAs('imports', $fileName);

            $fullPath = Storage::disk('local')->path($filePath);
            $importService = new JamaahCSVImportService;
            $result = $importService->import($fullPath);

            Storage::delete($filePath);

            if ($result['success_count'] == 0 && $result['error_count'] > 0) {
                session()->flash('error', 'Import gagal! Tidak ada data yang berhasil diimport.');
                session()->flash('result', $result);
            } elseif ($result['success_count'] == 0 && $result['skipped_count'] > 0) {
                session()->flash('error', 'Import gagal! Semua baris dilewati karena format header tidak sesuai. Pastikan kolom "NAMA LENGKAP" ada di file.');
                session()->flash('result', $result);
            } else {
                $message = 'Import berhasil! '.$result['success_count'].' data berhasil diimport.';
                if ($result['skipped_count'] > 0) {
                    $message .= ' ('.$result['skipped_count'].' baris dilewati karena data kosong)';
                }
                if ($result['error_count'] > 0) {
                    $message .= ' ('.$result['error_count'].' baris gagal)';
                }
                session()->flash('success', $message);
                session()->flash('result', $result);
            }

            return back();
        } catch (\Exception $e) {
            if (isset($filePath)) {
                Storage::delete($filePath);
            }

            session()->flash('error', 'Import gagal: '.$e->getMessage());

            return back();
        }
    }

    public function downloadTemplate(Request $request)
    {
        $delimiter = $request->get('delimiter', ';');
        $delimiter = ($delimiter === ',') ? ',' : ';';

        $importService = new JamaahCSVImportService;
        $content = $importService->generateCSVTemplate($delimiter);

        $delimiterName = $delimiter === ';' ? 'semicolon' : 'comma';
        $fileName = 'template_import_jamaah_'.$delimiterName.'.csv';

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="'.$fileName.'"');
    }

    public function downloadTemplateExcel()
    {
        $importService = new JamaahCSVImportService;
        $templateData = $importService->getCSVTemplate();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import Jamaah');

        $headers = $templateData['headers'];
        $columnLetter = 'A';

        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E3F2FD'],
            ],
        ];

        foreach ($headers as $header) {
            $sheet->setCellValue($columnLetter.'1', $header);
            $sheet->getStyle($columnLetter.'1')->applyFromArray($headerStyle);
            $columnLetter++;
        }

        $row = 2;
        foreach ([$templateData['sample1'], $templateData['sample2']] as $sample) {
            $col = 'A';
            foreach ($sample as $value) {
                $sheet->setCellValue($col.$row, $value);
                $col++;
            }
            $row++;
        }

        foreach (range('A', chr(64 + count($headers))) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'template_import_jamaah.xlsx';
        $response = response()->streamDownload(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$fileName.'"');

        return $response;
    }

    public function export(Request $request)
    {
        $delimiter = $request->get('delimiter', ';');
        $delimiter = ($delimiter === ',') ? ',' : ';';

        $filters = $request->only([
            'desa_id', 'kelompok_id', 'jenis_kelamin', 'status_pernikahan',
            'kategori_usia', 'paket', 'kategori_sodaqoh', 'status_mubaligh', 'search',
        ]);

        try {
            $exportService = new JamaahCSVExportService($filters, $delimiter);
            $jamaahs = $exportService->export();
            $csvContent = $exportService->mapToCSV($jamaahs);
            $fileName = $exportService->generateFilename();

            return response($csvContent)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="'.$fileName.'"');
        } catch (\Exception $e) {
            session()->flash('error', 'Export gagal: '.$e->getMessage());

            return back();
        }
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only([
            'desa_id', 'kelompok_id', 'jenis_kelamin', 'status_pernikahan',
            'kategori_usia', 'paket', 'kategori_sodaqoh', 'status_mubaligh', 'search',
        ]);

        try {
            $exportService = new JamaahExcelExportService($filters);
            $spreadsheet = $exportService->export();
            $fileName = $exportService->generateFilename();

            $response = response()->streamDownload(function () use ($spreadsheet) {
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                $writer->save('php://output');
            });

            $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $response->headers->set('Content-Disposition', 'attachment; filename="'.$fileName.'"');

            return $response;
        } catch (\Exception $e) {
            session()->flash('error', 'Export Excel gagal: '.$e->getMessage());

            return back();
        }
    }

    public function exportStatistik()
    {
        $user = auth()->user();

        $query = Jamaah::query();

        if ($user->isAdminDesa()) {
            $query->whereHas('kelompok', fn ($q) => $q->where('desa_id', $user->desa_id));
        } elseif ($user->isAdminKelompok()) {
            $query->where('kelompok_id', $user->kelompok_id);
        }

        $totalJamaah = $query->count();
        $jamaahLaki = (clone $query)->where('jenis_kelamin', 'L')->count();
        $jamaahPerempuan = (clone $query)->where('jenis_kelamin', 'P')->count();

        $kelompoks = [];
        if ($user->isSuperAdmin()) {
            $desas = Desa::withCount('jamaahs', 'kelompoks')->get();
        } elseif ($user->isAdminDesa()) {
            $desas = Desa::where('id', $user->desa_id)->withCount('jamaahs', 'kelompoks')->get();
        } else {
            $desas = collect();
        }

        foreach ($desas as $desa) {
            $kelompoks[] = [
                'desa' => $desa->nama_desa,
                'jumlah_jamaah' => $desa->jamaahs_count,
                'jumlah_kelompok' => $desa->kelompoks_count,
            ];
        }

        $kelasCounts = (clone $query)->selectRaw('kelas_generus, COUNT(*) as total')
            ->groupBy('kelas_generus')
            ->pluck('total', 'kelas_generus')
            ->toArray();

        $statusCounts = (clone $query)->selectRaw('status_pernikahan, COUNT(*) as total')
            ->groupBy('status_pernikahan')
            ->pluck('total', 'status_pernikahan')
            ->toArray();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Statistik Jamaah');

        $sheet->setCellValue('A1', 'STATISTIK DATA JAMAAH');
        $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
        $sheet->mergeCells('A1:D1');

        $scopeLabel = $user->isSuperAdmin() ? 'Seluruh Sistem' :
                      ($user->isAdminDesa() ? 'Desa '.$user->desa?->nama_desa :
                      'Kelompok '.$user->kelompok?->nama_kelompok);

        $sheet->setCellValue('A2', 'Scope: '.$scopeLabel);
        $sheet->setCellValue('A3', 'Tanggal: '.now()->format('d/m/Y H:i'));

        $row = 5;
        $sheet->setCellValue('A'.$row, 'RINGKASAN TOTAL');
        $sheet->getStyle('A'.$row)->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue('A'.$row, 'Total Jamaah');
        $sheet->setCellValue('B'.$row, $totalJamaah);
        $row++;

        $sheet->setCellValue('A'.$row, 'Jamaah Laki-laki');
        $sheet->setCellValue('B'.$row, $jamaahLaki);
        $row++;

        $sheet->setCellValue('A'.$row, 'Jamaah Perempuan');
        $sheet->setCellValue('B'.$row, $jamaahPerempuan);
        $row += 2;

        if (count($kelompoks) > 0) {
            $sheet->setCellValue('A'.$row, 'DATA PER DESA');
            $sheet->getStyle('A'.$row)->getFont()->setBold(true);
            $row++;

            $sheet->setCellValue('A'.$row, 'Desa');
            $sheet->setCellValue('B'.$row, 'Jumlah Jamaah');
            $sheet->setCellValue('C'.$row, 'Jumlah Kelompok');
            $sheet->getStyle('A'.$row.':C'.$row)->getFont()->setBold(true);
            $row++;

            foreach ($kelompoks as $k) {
                $sheet->setCellValue('A'.$row, $k['desa']);
                $sheet->setCellValue('B'.$row, $k['jumlah_jamaah']);
                $sheet->setCellValue('C'.$row, $k['jumlah_kelompok']);
                $row++;
            }
            $row++;
        }

        if (count($kelasCounts) > 0) {
            $sheet->setCellValue('A'.$row, 'DATA PER KELAS GENERUS');
            $sheet->getStyle('A'.$row)->getFont()->setBold(true);
            $row++;

            $sheet->setCellValue('A'.$row, 'Kelas');
            $sheet->setCellValue('B'.$row, 'Jumlah');
            $sheet->getStyle('A'.$row.':B'.$row)->getFont()->setBold(true);
            $row++;

            foreach ($kelasCounts as $kelas => $jumlah) {
                $sheet->setCellValue('A'.$row, $kelas ?: 'Tidak Diketahui');
                $sheet->setCellValue('B'.$row, $jumlah);
                $row++;
            }
            $row++;
        }

        if (count($statusCounts) > 0) {
            $sheet->setCellValue('A'.$row, 'DATA PER STATUS PERNIKAHAN');
            $sheet->getStyle('A'.$row)->getFont()->setBold(true);
            $row++;

            $sheet->setCellValue('A'.$row, 'Status');
            $sheet->setCellValue('B'.$row, 'Jumlah');
            $sheet->getStyle('A'.$row.':B'.$row)->getFont()->setBold(true);
            $row++;

            foreach ($statusCounts as $status => $jumlah) {
                $sheet->setCellValue('A'.$row, $status);
                $sheet->setCellValue('B'.$row, $jumlah);
                $row++;
            }
        }

        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'statistik_jamaah_'.now()->format('Y-m-d_His').'.xlsx';
        $response = response()->streamDownload(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$fileName.'"');

        return $response;
    }
}
