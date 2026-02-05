<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ===== DEFENSIVE CLEANUP =====
        // Drop existing constraints and columns if they exist from previous failed attempts
        
        // Check and drop existing foreign keys
        $foreignKeys = DB::select("SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'users' 
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            AND CONSTRAINT_NAME IN ('users_desa_id_foreign', 'users_kelompok_id_foreign')");
        
        foreach ($foreignKeys as $fk) {
            DB::statement("ALTER TABLE users DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
        }
        
        // Check and drop existing index
        $indexes = DB::select("SHOW INDEX FROM users WHERE Key_name = 'users_role_desa_id_kelompok_id_index'");
        if (!empty($indexes)) {
            DB::statement("ALTER TABLE users DROP INDEX users_role_desa_id_kelompok_id_index");
        }
        
        // Check and drop existing columns
        $columns = Schema::getColumnListing('users');
        if (in_array('desa_id', $columns)) {
            DB::statement("ALTER TABLE users DROP COLUMN desa_id");
        }
        if (in_array('kelompok_id', $columns)) {
            DB::statement("ALTER TABLE users DROP COLUMN kelompok_id");
        }

        // ===== STEP 1: Expand enum to include BOTH old and new values temporarily =====
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'operator', 'super_admin', 'admin_desa', 'admin_kelompok') DEFAULT 'admin_kelompok'");

        // ===== STEP 2: NOW migrate existing role values =====
        DB::table('users')->where('role', 'admin')->update(['role' => 'super_admin']);
        DB::table('users')->where('role', 'operator')->update(['role' => 'admin_kelompok']);

        // ===== STEP 3: Remove old enum values (admin, operator) =====
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin_desa', 'admin_kelompok') DEFAULT 'admin_kelompok'");

        // ===== STEP 4: Add new columns =====
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('desa_id')->nullable()->after('role');
            $table->unsignedBigInteger('kelompok_id')->nullable()->after('desa_id');
        });

        // ===== STEP 5: Add foreign key constraints =====
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('desa_id')->references('id')->on('desas')->onDelete('cascade');
            $table->foreign('kelompok_id')->references('id')->on('kelompoks')->onDelete('cascade');
            
            // Add index for performance
            $table->index(['role', 'desa_id', 'kelompok_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Drop foreign keys and index
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['desa_id']);
            $table->dropForeign(['kelompok_id']);
            $table->dropIndex(['role', 'desa_id', 'kelompok_id']);
        });

        // 2. Drop columns
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['desa_id', 'kelompok_id']);
        });

        // 3. Revert role values
        DB::table('users')->where('role', 'super_admin')->update(['role' => 'admin']);
        DB::table('users')->whereIn(' role', ['admin_desa', 'admin_kelompok'])->update(['role' => 'operator']);
        
        // 4. Revert enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'operator') DEFAULT 'operator'");
    }
};
