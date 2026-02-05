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
        Schema::table('jamaahs', function (Blueprint $table) {
            $table->string('tempat_lahir')->nullable()->after('nama_lengkap');
            $table->string('kelas_generus')->nullable()->after('jenis_kelamin');
            $table->string('kategori_sodaqoh')->nullable()->after('status_pernikahan');
            $table->string('dapukan')->nullable()->after('kategori_sodaqoh');
            $table->string('pekerjaan')->nullable()->after('dapukan');
            $table->string('status_mubaligh')->nullable()->comment('MT, MS, Asisten')->after('pekerjaan');
            $table->string('pendidikan_terakhir')->nullable()->after('status_mubaligh');
            $table->string('minat_kbm')->nullable()->after('pendidikan_terakhir');
            
            // Indexing untuk performa filter dashboard
            $table->index(['kelas_generus', 'kategori_sodaqoh', 'status_mubaligh'], 'idx_jamaah_filters');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jamaahs', function (Blueprint $table) {
            $table->dropIndex('idx_jamaah_filters');
            $table->dropColumn([
                'tempat_lahir', 'kelas_generus', 'kategori_sodaqoh',
                'dapukan', 'pekerjaan', 'status_mubaligh',
                'pendidikan_terakhir', 'minat_kbm'
            ]);
        });
    }
};
