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
        // Tambahkan kolom status_keluarga ke semua tabel sasaran
        Schema::table('sasaran_bayibalita', function (Blueprint $table) {
            $table->enum('status_keluarga', ['kepala keluarga', 'istri', 'anak'])->nullable()->after('jenis_kelamin');
        });

        Schema::table('sasaran_remajas', function (Blueprint $table) {
            $table->enum('status_keluarga', ['kepala keluarga', 'istri', 'anak'])->nullable()->after('jenis_kelamin');
        });

        Schema::table('sasaran_dewasas', function (Blueprint $table) {
            $table->enum('status_keluarga', ['kepala keluarga', 'istri', 'anak'])->nullable()->after('jenis_kelamin');
        });

        Schema::table('sasaran_pralansias', function (Blueprint $table) {
            $table->enum('status_keluarga', ['kepala keluarga', 'istri', 'anak'])->nullable()->after('jenis_kelamin');
        });

        Schema::table('sasaran_lansias', function (Blueprint $table) {
            $table->enum('status_keluarga', ['kepala keluarga', 'istri', 'anak'])->nullable()->after('jenis_kelamin');
        });

        Schema::table('sasaran_ibuhamils', function (Blueprint $table) {
            $table->enum('status_keluarga', ['kepala keluarga', 'istri', 'anak'])->nullable()->after('jenis_kelamin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sasaran_bayibalita', function (Blueprint $table) {
            $table->dropColumn('status_keluarga');
        });

        Schema::table('sasaran_remajas', function (Blueprint $table) {
            $table->dropColumn('status_keluarga');
        });

        Schema::table('sasaran_dewasas', function (Blueprint $table) {
            $table->dropColumn('status_keluarga');
        });

        Schema::table('sasaran_pralansias', function (Blueprint $table) {
            $table->dropColumn('status_keluarga');
        });

        Schema::table('sasaran_lansias', function (Blueprint $table) {
            $table->dropColumn('status_keluarga');
        });

        Schema::table('sasaran_ibuhamils', function (Blueprint $table) {
            $table->dropColumn('status_keluarga');
        });
    }
};
