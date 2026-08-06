<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Jamaah extends Model
{
    protected $fillable = [
        'kelompok_id',
        'keluarga_id',
        'nama_lengkap',
        'tempat_lahir',
        'tgl_lahir',
        'jenis_kelamin',
        'golongan_darah',
        'kelas_generus',
        'status_pernikahan',
        'kategori_sodaqoh',
        'dapukan',
        'pekerjaan',
        'status_mubaligh',
        'pendidikan_terakhir',
        'minat_kbm',
        'pendidikan_aktivitas',
        'no_telepon',
        'role_dlm_keluarga',
    ];

    protected $casts = [
        'tgl_lahir' => 'date',
    ];

    // DEFINISI OPSI DROPDOWN (Single Source of Truth)
    const STATUS_PERNIKAHAN = ['BELUM', 'MENIKAH', 'JANDA', 'DUDA'];
    
    const KELAS_GENERUS = [
        'PRA-PAUD', 'CABERAWIT', 'PAUD',
        'KELAS 1', 'KELAS 2', 'KELAS 3', 'KELAS 4', 'KELAS 5', 'KELAS 6',
        'KELAS 7', 'KELAS 8', 'KELAS 9', 'KELAS 10', 'KELAS 11', 'KELAS 12',
        'PRA REMAJA', 'UMUM', 'PELAJAR', 'MUDA-MUDI', 'USIA NIKAH', 'SENIOR',
    ];
    
    const KATEGORI_SODAQOH = ['AGNIYA SUPER PLUS', 'AGNIYA SUPER', 'AGNIYA', 'CALON AGNIYA', 'MAHASISWA', 'PELAJAR'];
    
    const DAPUKAN = ['KI', 'WAKIL KI', 'KU', 'PKU', 'PENEROBOS', 'MT', 'RUKYAH'];

    const STATUS_MUBALIGH = ['MT', 'MS', 'ASISTEN'];

    const PENDIDIKAN = ['SD', 'SMP', 'SMA/SMK', 'DIPLOMA', 'S1', 'S2', 'S3'];

    const MINAT_KBM = ['PRAMUKA', 'PERSINAS', 'TAHFID', 'FORSGI'];

    // Age-category ranges [min, max] used across index/exports
    const USIA_RANGES = [
        'BALITA' => [0, 5],
        'ANAK' => [6, 12],
        'REMAJA' => [13, 17],
        'PEMUDA' => [18, 40],
        'DEWASA' => [41, 60],
        'LANSIA' => [61, 150],
    ];

    // Kelas Generus yang secara eksplisit menunjukkan kategori usia, dipakai
    // sebagai acuan utama (mengalahkan hitungan umur dari tanggal lahir) di
    // scopeByKategoriUsia()/kategoriUsia(). Kelas yang tidak spesifik usia
    // (mis. UMUM, PELAJAR) sengaja tidak dimasukkan dan jatuh ke fallback umur.
    const KELAS_GENERUS_USIA_MAP = [
        'PRA-PAUD' => 'BALITA',
        'CABERAWIT' => 'BALITA',
        'PAUD' => 'BALITA',
        'KELAS 1' => 'ANAK',
        'KELAS 2' => 'ANAK',
        'KELAS 3' => 'ANAK',
        'KELAS 4' => 'ANAK',
        'KELAS 5' => 'ANAK',
        'KELAS 6' => 'ANAK',
        'PRA REMAJA' => 'REMAJA',
        'KELAS 7' => 'REMAJA',
        'KELAS 8' => 'REMAJA',
        'KELAS 9' => 'REMAJA',
        'KELAS 10' => 'REMAJA',
        'KELAS 11' => 'REMAJA',
        'KELAS 12' => 'REMAJA',
        'MUDA-MUDI' => 'PEMUDA',
        'USIA NIKAH' => 'PEMUDA',
        'SENIOR' => 'LANSIA',
    ];

    /**
     * Relasi N:1 ke Kelompok
     */
    public function kelompok()
    {
        return $this->belongsTo(Kelompok::class);
    }

    /**
     * Relasi N:1 ke Keluarga
     */
    public function keluarga()
    {
        return $this->belongsTo(Keluarga::class);
    }

    /**
     * Accessor: Hitung umur otomatis dari tanggal lahir
     * Laravel 12 style menggunakan Attribute class
     */
    protected function age(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->tgl_lahir 
                ? Carbon::parse($this->tgl_lahir)->age 
                : null,
        );
    }

    /**
     * Accessor: Kategori usia otomatis.
     * Kelas Generus yang punya makna usia eksplisit (lihat
     * KELAS_GENERUS_USIA_MAP, mis. MUDA-MUDI/USIA NIKAH = PEMUDA,
     * SENIOR = LANSIA) diutamakan; kalau kosong atau tidak spesifik
     * (UMUM, PELAJAR, dst), baru dihitung dari umur tanggal lahir:
     * BALITA (0-5), ANAK (6-12), REMAJA (13-17),
     * PEMUDA (18-40), DEWASA (41-60), LANSIA (60+).
     */
    protected function kategoriUsia(): Attribute
    {
        return Attribute::make(
            get: function () {
                $kelas = $this->kelas_generus ? strtoupper(trim($this->kelas_generus)) : null;
                if ($kelas && isset(self::KELAS_GENERUS_USIA_MAP[$kelas])) {
                    return self::KELAS_GENERUS_USIA_MAP[$kelas];
                }

                $umur = $this->age;
                if ($umur === null) return 'TIDAK DIKETAHUI';

                return match (true) {
                    $umur <= 5 => 'BALITA',
                    $umur <= 12 => 'ANAK',
                    $umur <= 17 => 'REMAJA',
                    $umur <= 40 => 'PEMUDA',
                    $umur <= 60 => 'DEWASA',
                    default => 'LANSIA',
                };
            }
        );
    }

    /**
     * Query Scope: Filter by Desa
     */
    public function scopeByDesa($query, $desaId)
    {
        return $query->whereHas('kelompok', function ($q) use ($desaId) {
            $q->where('desa_id', $desaId);
        });
    }

    /**
     * Query Scope: Filter by Kelompok
     */
    public function scopeByKelompok($query, $kelompokId)
    {
        return $query->where('kelompok_id', $kelompokId);
    }

    /**
     * Query Scope: Filter by kategori usia (BALITA/ANAK/REMAJA/PEMUDA/DEWASA/LANSIA).
     * Mengikuti prioritas yang sama dengan accessor kategoriUsia(): kelas_generus
     * yang termasuk KELAS_GENERUS_USIA_MAP diutamakan; jamaah dengan kelas_generus
     * kosong atau tidak spesifik usia (mis. UMUM, PELAJAR) dicocokkan lewat umur
     * dari tanggal lahir sebagai fallback.
     */
    public function scopeByKategoriUsia($query, $kategori)
    {
        if (! isset(self::USIA_RANGES[$kategori])) {
            return $query;
        }

        [$min, $max] = self::USIA_RANGES[$kategori];
        $minDate = Carbon::now()->subYears($max)->format('Y-m-d');
        $maxDate = Carbon::now()->subYears($min)->format('Y-m-d');
        $mappedKelas = array_keys(self::KELAS_GENERUS_USIA_MAP);
        $kelasForKategori = array_keys(array_filter(
            self::KELAS_GENERUS_USIA_MAP,
            fn ($v) => $v === $kategori
        ));

        return $query->where(function ($q) use ($kelasForKategori, $mappedKelas, $minDate, $maxDate) {
            if ($kelasForKategori) {
                $q->whereIn('kelas_generus', $kelasForKategori);
            }

            $q->orWhere(function ($fallback) use ($mappedKelas, $minDate, $maxDate) {
                $fallback->where(function ($w) use ($mappedKelas) {
                    $w->whereNull('kelas_generus')
                        ->orWhere('kelas_generus', '')
                        ->orWhereNotIn('kelas_generus', $mappedKelas);
                })->whereBetween('tgl_lahir', [$minDate, $maxDate]);
            });
        });
    }

    /**
     * Query Scope: Filter by marital status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status_pernikahan', $status);
    }

    /**
     * Query Scope: Search by name
     */
    public function scopeSearch($query, $keyword)
    {
        // Safe parameter binding to prevent SQL injection
        $searchTerm = '%' . $keyword . '%';
        return $query->where('nama_lengkap', 'LIKE', $searchTerm);
    }

    /**
     * Query Scope: Filter by gender
     */
    public function scopeByGender($query, $gender)
    {
        return $query->where('jenis_kelamin', $gender);
    }
}
