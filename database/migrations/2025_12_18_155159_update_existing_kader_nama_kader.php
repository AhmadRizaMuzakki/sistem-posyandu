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
        // Update nama_kader dari user->name untuk kader yang sudah ada
        DB::statement("
            UPDATE kader 
            SET nama_kader = (
                SELECT name 
                FROM users 
                WHERE users.id = kader.id_users
            )
            WHERE id_users IS NOT NULL 
            AND (nama_kader IS NULL OR nama_kader = '')
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu rollback karena ini hanya data migration
    }
};
