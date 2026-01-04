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
        $driver = DB::connection()->getDriverName();
        
        // SQLite tidak mendukung MODIFY COLUMN dan tidak benar-benar memvalidasi ENUM
        // Untuk SQLite, kita skip karena ENUM hanya disimpan sebagai string
        if ($driver === 'sqlite') {
            return;
        }

        $enumValues = "ENUM(
            'Tidak/Belum Sekolah',
            'PAUD',
            'TK',
            'Tidak Tamat SD/Sederajat',
            'Tamat SD/Sederajat',
            'SLTP/Sederajat',
            'SLTA/Sederajat',
            'Diploma I/II',
            'Akademi/Diploma III/Sarjana Muda',
            'Diploma IV/Strata I',
            'Strata II',
            'Strata III'
        ) NULL";

        $tables = [
            'sasaran_dewasas',
            'sasaran_pralansias',
            'sasaran_lansias',
            'sasaran_remajas',
            'orangtua',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'pendidikan')) {
                DB::statement("ALTER TABLE {$table} MODIFY COLUMN pendidikan {$enumValues}");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        
        // SQLite tidak mendukung MODIFY COLUMN
        if ($driver === 'sqlite') {
            return;
        }

        $enumValues = "ENUM(
            'Tidak/Belum Sekolah',
            'Tidak Tamat SD/Sederajat',
            'Tamat SD/Sederajat',
            'SLTP/Sederajat',
            'SLTA/Sederajat',
            'Diploma I/II',
            'Akademi/Diploma III/Sarjana Muda',
            'Diploma IV/Strata I',
            'Strata II',
            'Strata III'
        ) NULL";

        $tables = [
            'sasaran_dewasas',
            'sasaran_pralansias',
            'sasaran_lansias',
            'sasaran_remajas',
            'orangtua',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'pendidikan')) {
                DB::statement("ALTER TABLE {$table} MODIFY COLUMN pendidikan {$enumValues}");
            }
        }
    }
};
