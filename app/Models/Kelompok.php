<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelompok extends Model
{
    protected $fillable = [
        'desa_id',
        'nama_kelompok',
    ];

    /**
     * Relasi N:1 ke Desa
     * Kelompok ini milik satu desa
     */
    public function desa()
    {
        return $this->belongsTo(Desa::class);
    }

    /**
     * Relasi 1:N ke Jamaah
     * Satu kelompok memiliki banyak jamaah
     */
    public function jamaahs()
    {
        return $this->hasMany(Jamaah::class);
    }

    /**
     * Get total jamaah in this kelompok
     */
    public function getTotalJamaahAttribute()
    {
        return $this->jamaahs()->count();
    }
}
