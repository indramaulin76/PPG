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
        'tgl_lahir',
        'jenis_kelamin',
        'status_pernikahan',
        'pendidikan_aktivitas',
        'no_telepon',
        'role_dlm_keluarga',
    ];

    protected $casts = [
        'tgl_lahir' => 'date',
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
     * Accessor: Kategori usia otomatis
     * BALITA (0-5), ANAK (6-12), REMAJA (13-17), 
     * PEMUDA (18-40), DEWASA (41-60), LANSIA (60+)
     */
    protected function kategoriUsia(): Attribute
    {
        return Attribute::make(
            get: function () {
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
     * Query Scope: Filter by age range
     */
    public function scopeByUsia($query, $min, $max)
    {
        $minDate = Carbon::now()->subYears($max)->format('Y-m-d');
        $maxDate = Carbon::now()->subYears($min)->format('Y-m-d');
        
        return $query->whereBetween('tgl_lahir', [$minDate, $maxDate]);
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
        return $query->where('nama_lengkap', 'LIKE', "%{$keyword}%");
    }

    /**
     * Query Scope: Filter by gender
     */
    public function scopeByGender($query, $gender)
    {
        return $query->where('jenis_kelamin', $gender);
    }
}
