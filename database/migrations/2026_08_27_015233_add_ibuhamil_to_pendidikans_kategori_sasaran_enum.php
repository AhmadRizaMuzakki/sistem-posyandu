<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('pendidikans') || !Schema::hasColumn('pendidikans', 'kategori_sasaran')) {
            return;
        }

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE pendidikans MODIFY COLUMN kategori_sasaran ENUM('bayibalita', 'remaja', 'dewasa', 'pralansia', 'lansia', 'ibuhamil') NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('pendidikans') || !Schema::hasColumn('pendidikans', 'kategori_sasaran')) {
            return;
        }

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE pendidikans MODIFY COLUMN kategori_sasaran ENUM('bayibalita', 'remaja', 'dewasa', 'pralansia', 'lansia') NULL");
        }
    }
};
