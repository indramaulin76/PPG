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
        return Jamaah::with(['kelompok.desa', 'keluarga'])
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
            ->orderBy('id')
            ->get();
    }

    public function getHeadings(): array
    {
        return [
            'ID',
            'Nama Lengkap',
            'Jenis Kelamin',
            'Tanggal Lahir',
            'Umur',
            'Status Pernikahan',
            'Desa',
            'Kelompok',
            'No. Telepon',
            'Pendidikan',
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
                $jamaah->jenis_kelamin,
                $jamaah->tgl_lahir,
                $jamaah->age,
                $jamaah->status_pernikahan,
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