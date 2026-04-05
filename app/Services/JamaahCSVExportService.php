<?php

namespace App\Services;

use App\Models\Jamaah;
use Illuminate\Support\Collection;

class JamaahCSVExportService
{
    private $filters = [];

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function export(): Collection
    {
        $query = Jamaah::with(['kelompok.desa', 'keluarga'])
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

        // Filter by Paket (groups of kelas_generus)
        if (!empty($this->filters['paket'])) {
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

        // Filter by age category
        if (!empty($this->filters['kategori_usia'])) {
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
        
        // Filter by search text
        if (!empty($this->filters['search'])) {
            $query->search($this->filters['search']);
        }

        return $query->orderBy('nama_lengkap')->get();
    }

    public function getHeadings(): array
    {
        return [
            'ID',
            'Nama Lengkap',
            'Tempat Lahir',
            'Jenis Kelamin',
            'Tanggal Lahir',
            'Umur',
            'Kelas Generus',
            'Status Pernikahan',
            'Kategori Sodaqoh',
            'Dapukan',
            'Pekerjaan',
            'Status Mubaligh',
            'Pendidikan Terakhir',
            'Minat KBM',
            'Desa',
            'Kelompok',
            'No. Telepon',
            'Pendidikan Aktivitas',
            'Role Keluarga',
            'No. KK',
            'Alamat'
        ];
    }

    public function mapToCSV(Collection $jamaahs): string
    {
        $headers = implode(',', $this->getHeadings()) . "\n";
        $rows = '';

        foreach ($jamaahs as $jamaah) {
            $row = [
                $jamaah->id,
                $this->escapeCSV($jamaah->nama_lengkap),
                $this->escapeCSV($jamaah->tempat_lahir ?? ''),
                $jamaah->jenis_kelamin,
                $jamaah->tgl_lahir,
                $jamaah->age,
                $this->escapeCSV($jamaah->kelas_generus ?? ''),
                $jamaah->status_pernikahan,
                $this->escapeCSV($jamaah->kategori_sodaqoh ?? ''),
                $this->escapeCSV($jamaah->dapukan ?? ''),
                $this->escapeCSV($jamaah->pekerjaan ?? ''),
                $this->escapeCSV($jamaah->status_mubaligh ?? ''),
                $this->escapeCSV($jamaah->pendidikan_terakhir ?? ''),
                $this->escapeCSV($jamaah->minat_kbm ?? ''),
                $this->escapeCSV($jamaah->kelompok->desa->nama_desa ?? ''),
                $this->escapeCSV($jamaah->kelompok->nama_kelompok ?? ''),
                $this->escapeCSV($jamaah->no_telepon ?? ''),
                $this->escapeCSV($jamaah->pendidikan_aktivitas ?? ''),
                $jamaah->role_dlm_keluarga,
                $this->escapeCSV($jamaah->keluarga->no_kk ?? ''),
                $this->escapeCSV($jamaah->keluarga->alamat_rumah ?? ''),
            ];

            $rows .= implode(',', $row) . "\n";
        }

        return $headers . $rows;
    }

    private function escapeCSV(string $value): string
    {
        if (empty($value)) {
            return '';
        }

        $value = str_replace('"', '""', $value);

        if (str_contains($value, ',') || str_contains($value, '"') || str_contains($value, "\n")) {
            return '"' . $value . '"';
        }

        return $value;
    }

    public function generateFilename(): string
    {
        return 'data_jamaah_' . now()->format('Y-m-d_His') . '.csv';
    }
}