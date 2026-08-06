<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Normalize dropdown values to uppercase so they match the app constants.
     * Idempotent: safe to run even if already applied.
     */
    public function up(): void
    {
        $columns = ['kategori_sodaqoh', 'kelas_generus', 'status_mubaligh', 'pendidikan_terakhir', 'minat_kbm'];

        foreach ($columns as $column) {
            DB::table('jamaahs')
                ->whereNotNull($column)
                ->where($column, '!=', '')
                ->update([
                    $column => DB::raw('UPPER(TRIM('.$column.'))'),
                ]);
        }
    }

    public function down(): void
    {
        // No-op: normalization is not reversible
    }
};
