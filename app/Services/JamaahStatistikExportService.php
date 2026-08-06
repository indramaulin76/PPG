<?php

namespace App\Services;

use App\Models\Desa;
use App\Models\Jamaah;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class JamaahStatistikExportService
{
    public function __construct(private User $user)
    {
    }

    public function export(): Spreadsheet
    {
        $user = $this->user;

        $query = Jamaah::query();

        if ($user->isAdminDesa()) {
            $query->whereHas('kelompok', fn ($q) => $q->where('desa_id', $user->desa_id));
        } elseif ($user->isAdminKelompok()) {
            $query->where('kelompok_id', $user->kelompok_id);
        }

        $totalJamaah = $query->count();
        $jamaahLaki = (clone $query)->where('jenis_kelamin', 'L')->count();
        $jamaahPerempuan = (clone $query)->where('jenis_kelamin', 'P')->count();

        if ($user->isSuperAdmin()) {
            $desas = Desa::withCount('jamaahs', 'kelompoks')->get();
        } elseif ($user->isAdminDesa()) {
            $desas = Desa::where('id', $user->desa_id)->withCount('jamaahs', 'kelompoks')->get();
        } else {
            $desas = collect();
        }

        $perDesa = [];
        foreach ($desas as $desa) {
            $perDesa[] = [
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

        $spreadsheet = new Spreadsheet;
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

        if (count($perDesa) > 0) {
            $sheet->setCellValue('A'.$row, 'DATA PER DESA');
            $sheet->getStyle('A'.$row)->getFont()->setBold(true);
            $row++;

            $sheet->setCellValue('A'.$row, 'Desa');
            $sheet->setCellValue('B'.$row, 'Jumlah Jamaah');
            $sheet->setCellValue('C'.$row, 'Jumlah Kelompok');
            $sheet->getStyle('A'.$row.':C'.$row)->getFont()->setBold(true);
            $row++;

            foreach ($perDesa as $k) {
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

        return $spreadsheet;
    }

    public function generateFilename(): string
    {
        return 'statistik_jamaah_'.now()->format('Y-m-d_His').'.xlsx';
    }
}
