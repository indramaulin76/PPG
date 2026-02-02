<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Desa extends Model
{
    protected $fillable = [
        'nama_desa',
        'kode_desa',
    ];

    /**
     * Relasi 1:N ke Kelompok
     * Satu desa memiliki banyak kelompok
     */
    public function kelompoks()
    {
        return $this->hasMany(Kelompok::class);
    }

    /**
     * Relasi through ke Jamaah
     * Mendapatkan semua jamaah dalam desa ini melalui kelompok
     */
    public function jamaahs()
    {
        return $this->hasManyThrough(Jamaah::class, Kelompok::class);
    }

    /**
     * Get total jamaah in this desa
     */
    public function getTotalJamaahAttribute()
    {
        return $this->jamaahs()->count();
    }
}
