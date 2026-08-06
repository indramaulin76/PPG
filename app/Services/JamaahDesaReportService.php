<?php

namespace App\Services;

use App\Models\Desa;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class JamaahDesaReportService
{
    public function __construct(private User $user)
    {
    }

    public function export(): Spreadsheet
    {
        $user = $this->user;

        $desaQuery = Desa::withCount(['kelompoks', 'jamaahs']);

        if ($user->isAdminDesa()) {
            $desaQuery->where('id', $user->desa_id);
        }

        $desas = $desaQuery->orderBy('nama_desa')->get()->map(function (Desa $desa) {
            return [
                'nama_desa' => $desa->nama_desa,
                'jumlah_kelompok' => $desa->kelompoks_count,
                'jumlah_jamaah' => $desa->jamaahs_count,
                'laki_laki' => $desa->jamaahs()->where('jenis_kelamin', 'L')->count(),
                'perempuan' => $desa->jamaahs()->where('jenis_kelamin', 'P')->count(),
            ];
        });

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Per Desa');

        $sheet->setCellValue('A1', 'LAPORAN RINGKASAN PER DESA');
        $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
        $sheet->mergeCells('A1:E1');

        $sheet->setCellValue('A2', 'Tanggal: '.now()->format('d/m/Y H:i'));

        $headerRow = 4;
        $headers = ['Desa', 'Jumlah Kelompok', 'Total Jamaah', 'Laki-laki', 'Perempuan'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col.$headerRow, $header);
            $col++;
        }
        $sheet->getStyle('A'.$headerRow.':E'.$headerRow)->getFont()->setBold(true);

        $row = $headerRow + 1;
        foreach ($desas as $desa) {
            $sheet->setCellValue('A'.$row, $desa['nama_desa']);
            $sheet->setCellValue('B'.$row, $desa['jumlah_kelompok']);
            $sheet->setCellValue('C'.$row, $desa['jumlah_jamaah']);
            $sheet->setCellValue('D'.$row, $desa['laki_laki']);
            $sheet->setCellValue('E'.$row, $desa['perempuan']);
            $row++;
        }

        if ($desas->isEmpty()) {
            $sheet->setCellValue('A'.$row, 'Tidak ada data desa.');
        }

        foreach (range('A', 'E') as $letter) {
            $sheet->getColumnDimension($letter)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    public function generateFilename(): string
    {
        return 'laporan_per_desa_'.now()->format('Y-m-d_His').'.xlsx';
    }
}
