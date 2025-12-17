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
        // Tambah field pendidikan ke tabel sasaran_pralansias
        if (Schema::hasTable('sasaran_pralansias')) {
            Schema::table('sasaran_pralansias', function (Blueprint $table) {
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
                    'Strata III',
                ])->nullable()->after('pekerjaan');
            });
        }

        // Tambah field pendidikan ke tabel sasaran_lansias
        if (Schema::hasTable('sasaran_lansias')) {
            Schema::table('sasaran_lansias', function (Blueprint $table) {
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
                    'Strata III',
                ])->nullable()->after('pekerjaan');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus field pendidikan dari tabel sasaran_pralansias
        if (Schema::hasTable('sasaran_pralansias')) {
            Schema::table('sasaran_pralansias', function (Blueprint $table) {
                if (Schema::hasColumn('sasaran_pralansias', 'pendidikan')) {
                    $table->dropColumn('pendidikan');
                }
            });
        }

        // Hapus field pendidikan dari tabel sasaran_lansias
        if (Schema::hasTable('sasaran_lansias')) {
            Schema::table('sasaran_lansias', function (Blueprint $table) {
                if (Schema::hasColumn('sasaran_lansias', 'pendidikan')) {
                    $table->dropColumn('pendidikan');
                }
            });
        }
    }
};


