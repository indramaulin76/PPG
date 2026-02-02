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
        Schema::create('kelompoks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('desa_id')->constrained()->onDelete('cascade')->comment('Relasi ke tabel desas');
            $table->string('nama_kelompok')->comment('Nama kelompok dalam desa (contoh: JATI LAMA)');
            $table->timestamps();

            // Composite index untuk query filtering yang lebih cepat
            $table->index(['desa_id', 'nama_kelompok']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelompoks');
    }
};
