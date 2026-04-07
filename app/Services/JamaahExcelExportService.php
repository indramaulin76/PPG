<?php

namespace App\Services;

use App\Models\Jamaah;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class JamaahExcelExportService
{
    private $filters = [];

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function export()
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Jamaah');

        $this->setPageSetup($sheet);
        $this->setHeaders($sheet);
        $this->setColumnWidths($sheet);
        $this->fillData($sheet);

        return $spreadsheet;
    }

    private function setPageSetup($sheet)
    {
        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setFitToPage(true)
            ->setFitToWidth(1)
            ->setFitToHeight(0);

        $sheet->getPageMargins()
            ->setTop(0.75)
            ->setRight(0.5)
            ->setBottom(0.75)
            ->setLeft(0.5);
    }

    private function setHeaders($sheet)
    {
        $headers = $this->getHeadings();
        $columnLetter = 'A';

        $headerStyle = [
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2563EB'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
            ],
        ];

        foreach ($headers as $header) {
            $sheet->setCellValue($columnLetter.'1', $header);
            $sheet->getStyle($columnLetter.'1')->applyFromArray($headerStyle);
            $columnLetter++;
        }

        $sheet->getRowDimension('1')->setRowHeight(30);
    }

    private function setColumnWidths($sheet)
    {
        $widths = [
            'A' => 15,    // DESA
            'B' => 18,    // KELOMPOK
            'C' => 25,    // NAMA LENGKAP
            'D' => 18,    // TEMPAT LAHIR
            'E' => 12,    // TANGGAL LAHIR
            'F' => 10,    // JENIS KELAMIN
            'G' => 8,     // UMUR
            'H' => 15,    // PAKET
            'I' => 15,    // STATUS PERNIKAHAN
            'J' => 18,    // KATAGORI SODAQOH
            'K' => 15,    // DAPUKAN
            'L' => 18,    // PEKERJAAN
            'M' => 15,    // DEWAN GURU
            'N' => 18,    // PENDIDIKAN TERAKHIR
            'O' => 18,    // KBM YANG DIMINATI
            'P' => 15,    // NO TELEPON
        ];

        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
    }

    private function fillData($sheet)
    {
        $jamaahs = $this->getData();
        $row = 2;

        $dataStyle = [
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E5E7EB'],
                ],
            ],
        ];

        $alternateRowStyle = array_merge($dataStyle, [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F9FAFB'],
            ],
        ]);

        foreach ($jamaahs as $index => $jamaah) {
            $data = $this->mapToRow($jamaah);
            $column = 'A';

            foreach ($data as $value) {
                $sheet->setCellValue($column.$row, $value);
                $column++;
            }

            $style = ($index % 2 === 0) ? $dataStyle : $alternateRowStyle;
            $endColumn = chr(ord('A') + count($data) - 1);
            $sheet->getStyle("A{$row}:{$endColumn}{$row}")->applyFromArray($style);

            $row++;
        }

        $sheet->freezePane('A2');
    }

    private function getData()
    {
        $user = auth()->user();
        $query = Jamaah::with(['kelompok.desa', 'keluarga']);

        if ($user->isAdminDesa()) {
            $query->whereHas('kelompok', fn ($q) => $q->where('desa_id', $user->desa_id));
        } elseif ($user->isAdminKelompok()) {
            $query->where('kelompok_id', $user->kelompok_id);
        }

        $query
            ->when($this->filters['desa_id'] ?? null, function ($query, $desaId) {
                return $query->whereHas('kelompok', function ($q) use ($desaId) {
                    $q->where('desa_id', $desaId);
                });
            })
            ->when($this->filters['kelompok_id'] ?? null, function ($query, $kelompokId) {
                return $query->where('kelompok_id', $kelompokId);
            })
            ->when($this->filters['jenis_kelamin'] ?? null, function ($query, $gender) {
                return $query->where('jenis_kelamin', $gender);
            })
            ->when($this->filters['status_pernikahan'] ?? null, function ($query, $status) {
                return $query->where('status_pernikahan', $status);
            })
            ->when($this->filters['kategori_sodaqoh'] ?? null, function ($query, $sodaqoh) {
                return $query->where('kategori_sodaqoh', $sodaqoh);
            })
            ->when($this->filters['status_mubaligh'] ?? null, function ($query, $mubaligh) {
                return $query->where('status_mubaligh', $mubaligh);
            });

        if (! empty($this->filters['paket'])) {
            $paket = $this->filters['paket'];
            $paketMapping = [
                'PAUD' => ['PAUD'],
                'A' => ['KELAS 1', 'KELAS 2', 'KELAS 3'],
                'B' => ['KELAS 4', 'KELAS 5', 'KELAS 6'],
                'C' => ['KELAS 7', 'KELAS 8', 'KELAS 9'],
                'D' => ['KELAS 10', 'KELAS 11', 'KELAS 12'],
                'PRA_NIKAH' => ['MUDA-MUDI'],
            ];

            if ($paket === 'UMUM') {
                $query->whereIn('status_pernikahan', ['MENIKAH', 'JANDA', 'DUDA']);
            } elseif (isset($paketMapping[$paket])) {
                $query->whereIn('kelas_generus', $paketMapping[$paket]);
            }
        }

        if (! empty($this->filters['kategori_usia'])) {
            $kategori = $this->filters['kategori_usia'];
            $ranges = [
                'BALITA' => [0, 5],
                'ANAK' => [6, 12],
                'REMAJA' => [13, 17],
                'PEMUDA' => [18, 40],
                'DEWASA' => [41, 60],
                'LANSIA' => [61, 150],
            ];
            if (isset($ranges[$kategori])) {
                $query->byUsia($ranges[$kategori][0], $ranges[$kategori][1]);
            }
        }

        if (! empty($this->filters['search'])) {
            $query->search($this->filters['search']);
        }

        return $query->orderBy('nama_lengkap')->get();
    }

    private function mapToRow($jamaah)
    {
        return [
            $jamaah->kelompok->desa->nama_desa ?? '',
            $jamaah->kelompok->nama_kelompok ?? '',
            $jamaah->nama_lengkap,
            $jamaah->tempat_lahir ?? '',
            $jamaah->tgl_lahir ?? '',
            $jamaah->jenis_kelamin,
            $jamaah->age ?? '',
            $jamaah->kelas_generus ?? '',
            $jamaah->status_pernikahan,
            $jamaah->kategori_sodaqoh ?? '',
            $jamaah->dapukan ?? '',
            $jamaah->pekerjaan ?? '',
            $jamaah->status_mubaligh ?? '',
            $jamaah->pendidikan_terakhir ?? '',
            $jamaah->minat_kbm ?? '',
            $jamaah->no_telepon ?? '',
        ];
    }

    public function getHeadings()
    {
        return [
            'DESA',
            'KELOMPOK',
            'NAMA LENGKAP',
            'TEMPAT LAHIR',
            'TANGGAL LAHIR',
            'JENIS KELAMIN',
            'UMUR',
            'PAKET',
            'STATUS PERNIKAHAN',
            'KATAGORI SODAQOH',
            'DAPUKAN',
            'PEKERJAAN',
            'DEWAN GURU',
            'PENDIDIKAN TERAKHIR',
            'KBM YANG DIMINATI',
            'NO TELEPON',
        ];
    }

    public function generateFilename()
    {
        return 'data_jamaah_'.now()->format('Y-m-d_His').'.xlsx';
    }
}
