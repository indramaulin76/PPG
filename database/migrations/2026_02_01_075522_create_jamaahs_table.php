<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jamaahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelompok_id')->constrained()->onDelete('restrict')->comment('Relasi ke tabel kelompoks');
            $table->foreignId('keluarga_id')->nullable()->constrained()->onDelete('cascade')->comment('Relasi ke tabel keluargas');
            $table->string('nama_lengkap')->index()->comment('Nama lengkap jamaah - indexed untuk pencarian');
            $table->date('tgl_lahir')->nullable()->comment('Tanggal lahir - umur dihitung otomatis via accessor');
            $table->enum('jenis_kelamin', ['L', 'P'])->comment('L = Laki-laki, P = Perempuan');
            $table->enum('status_pernikahan', ['BELUM', 'MENIKAH', 'JANDA', 'DUDA'])->nullable()->comment('Status pernikahan');
            $table->string('pendidikan_aktivitas')->nullable()->comment('Pendidikan/Aktivitas: SD, SMP, Kuliah, Bekerja, dll');
            $table->string('no_telepon', 20)->nullable()->comment('Nomor telepon/HP');
            $table->enum('role_dlm_keluarga', ['KEPALA', 'ISTRI', 'ANAK', 'LAINNYA'])->nullable()->comment('Peran dalam keluarga');
            $table->timestamps();

            // Indexes untuk performa query
            $table->index(['jenis_kelamin', 'status_pernikahan'], 'idx_gender_marital'); // Untuk statistik
            $table->index('tgl_lahir'); // Untuk filtering berdasarkan umur
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jamaahs');
    }
};
