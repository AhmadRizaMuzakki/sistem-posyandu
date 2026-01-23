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
        // Update existing data dari 'belum_absen' ke 'belum_hadir'
        DB::table('jadwals')
            ->where('presensi', 'belum_absen')
            ->update(['presensi' => 'belum_hadir']);

        // Drop column presensi
        Schema::table('jadwals', function (Blueprint $table) {
            $table->dropColumn('presensi');
        });

        // Recreate dengan enum baru
        Schema::table('jadwals', function (Blueprint $table) {
            $table->enum('presensi', ['hadir', 'tidak_hadir', 'belum_hadir'])->default('belum_hadir')->after('keterangan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Update existing data dari 'belum_hadir' ke 'belum_absen'
        DB::table('jadwals')
            ->where('presensi', 'belum_hadir')
            ->update(['presensi' => 'belum_absen']);

        // Drop column presensi
        Schema::table('jadwals', function (Blueprint $table) {
            $table->dropColumn('presensi');
        });

        // Recreate dengan enum lama
        Schema::table('jadwals', function (Blueprint $table) {
            $table->enum('presensi', ['hadir', 'tidak_hadir', 'belum_absen'])->default('belum_absen')->after('keterangan');
        });
    }
};
