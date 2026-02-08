<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tambah pilihan status keluarga: mertua, menantu, kerabat lain (untuk bagian orang tua balita/remaja).
     */
    public function up(): void
    {
        $enumValues = "'kepala keluarga','istri','anak','mertua','menantu','kerabat lain'";
        foreach (['sasaran_dewasas', 'sasaran_pralansias', 'sasaran_lansias'] as $table) {
            DB::statement("ALTER TABLE {$table} MODIFY COLUMN status_keluarga ENUM({$enumValues}) NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $enumValues = "'kepala keluarga','istri','anak'";
        foreach (['sasaran_dewasas', 'sasaran_pralansias', 'sasaran_lansias'] as $table) {
            DB::statement("ALTER TABLE {$table} MODIFY COLUMN status_keluarga ENUM({$enumValues}) NULL");
        }
    }
};
