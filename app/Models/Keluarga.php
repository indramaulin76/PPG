<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keluarga extends Model
{
    protected $fillable = [
        'no_kk',
        'alamat_rumah',
        'kepala_keluarga_id',
    ];

    /**
     * Relasi 1:N ke Jamaah
     * Satu keluarga memiliki banyak anggota
     */
    public function anggota()
    {
        return $this->hasMany(Jamaah::class);
    }

    /**
     * Relasi N:1 ke Jamaah (Kepala Keluarga)
     * Mendapatkan kepala keluarga
     */
    public function kepalaKeluarga()
    {
        return $this->belongsTo(Jamaah::class, 'kepala_keluarga_id');
    }

    /**
     * Get total family members
     */
    public function getJumlahAnggotaAttribute()
    {
        return $this->anggota()->count();
    }
}
