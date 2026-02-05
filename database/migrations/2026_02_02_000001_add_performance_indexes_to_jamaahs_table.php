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
            // Composite index for frequently used combinations
            $table->index(['kelompok_id', 'jenis_kelamin'], 'idx_kelompok_gender');
            $table->index(['kelompok_id', 'tgl_lahir'], 'idx_kelompok_age');
            
            // Index for CSV import performance
            $table->index(['nama_lengkap', 'tgl_lahir'], 'idx_name_dob');
            
            // Index for filtering operations
            $table->index('keluarga_id'); // Family-based queries
            $table->index('status_pernikahan'); // Marital status filtering
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jamaahs', function (Blueprint $table) {
            $table->dropIndex('idx_kelompok_gender');
            $table->dropIndex('idx_kelompok_age');
            $table->dropIndex('idx_name_dob');
            $table->dropIndex('jamaahs_keluarga_id_index');
            $table->dropIndex('jamaahs_status_pernikahan_index');
        });
    }
};