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
        // Tambah field pendidikan ke tabel sasaran_dewasas
        if (Schema::hasTable('sasaran_dewasas')) {
            Schema::table('sasaran_dewasas', function (Blueprint $table) {
                $table->enum('pendidikan', [
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
                ])->nullable()->after('pekerjaan');
            });
        }

        // Tambah field pendidikan ke tabel sasaran_remajas
        if (Schema::hasTable('sasaran_remajas')) {
            Schema::table('sasaran_remajas', function (Blueprint $table) {
                $table->enum('pendidikan', [
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
                ])->nullable()->after('umur_sasaran');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus field pendidikan dari tabel sasaran_dewasas
        if (Schema::hasTable('sasaran_dewasas')) {
            Schema::table('sasaran_dewasas', function (Blueprint $table) {
                $table->dropColumn('pendidikan');
            });
        }

        // Hapus field pendidikan dari tabel sasaran_remajas
        if (Schema::hasTable('sasaran_remajas')) {
            Schema::table('sasaran_remajas', function (Blueprint $table) {
                $table->dropColumn('pendidikan');
            });
        }
    }
};
