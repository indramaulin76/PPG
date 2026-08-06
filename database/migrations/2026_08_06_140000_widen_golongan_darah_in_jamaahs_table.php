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
        // Real data includes non-blood-type placeholders like "BELUM TAHU" (not yet known),
        // which don't fit in the original varchar(5).
        Schema::table('jamaahs', function (Blueprint $table) {
            $table->string('golongan_darah', 20)->nullable()->comment('Golongan darah: A, B, AB, O, atau keterangan lain')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jamaahs', function (Blueprint $table) {
            $table->string('golongan_darah', 5)->nullable()->change();
        });
    }
};
