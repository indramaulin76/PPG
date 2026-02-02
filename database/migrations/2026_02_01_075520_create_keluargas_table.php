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
        Schema::create('keluargas', function (Blueprint $table) {
            $table->id();
            $table->string('no_kk')->unique()->comment('Nomor Kartu Keluarga');
            $table->text('alamat_rumah')->nullable()->comment('Alamat lengkap keluarga');
            $table->unsignedBigInteger('kepala_keluarga_id')->nullable()->comment('ID jamaah yang menjadi kepala keluarga');
            $table->timestamps();

            // Foreign key akan ditambahkan setelah tabel jamaahs dibuat
            // Untuk menghindari circular dependency
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keluargas');
    }
};
