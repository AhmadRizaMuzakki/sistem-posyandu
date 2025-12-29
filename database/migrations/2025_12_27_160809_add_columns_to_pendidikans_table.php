<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migration ini menambahkan kolom jika belum ada (untuk kompatibilitas)
     */
    public function up(): void
    {
        // Cek apakah tabel sudah ada dan kolom sudah ada
        if (!Schema::hasTable('pendidikans')) {
            return;
        }

        Schema::table('pendidikans', function (Blueprint $table) {
            // Cek dan tambahkan kolom hanya jika belum ada
            if (!Schema::hasColumn('pendidikans', 'id_posyandu')) {
                $table->unsignedBigInteger('id_posyandu')->nullable()->after('id_pendidikan');
            }
            if (!Schema::hasColumn('pendidikans', 'id_users')) {
                $table->unsignedBigInteger('id_users')->nullable()->after('id_posyandu');
            }
            if (!Schema::hasColumn('pendidikans', 'id_sasaran')) {
                $table->unsignedBigInteger('id_sasaran')->nullable()->after('id_users');
            }
            if (!Schema::hasColumn('pendidikans', 'kategori_sasaran')) {
                $table->enum('kategori_sasaran', ['bayibalita', 'remaja', 'dewasa', 'pralansia', 'lansia', 'ibuhamil'])->nullable()->after('id_sasaran');
            }
            if (!Schema::hasColumn('pendidikans', 'nik')) {
                $table->string('nik')->nullable()->after('kategori_sasaran');
            }
            if (!Schema::hasColumn('pendidikans', 'nama')) {
                $table->string('nama')->nullable()->after('nik');
            }
            if (!Schema::hasColumn('pendidikans', 'tanggal_lahir')) {
                $table->date('tanggal_lahir')->nullable()->after('nama');
            }
            if (!Schema::hasColumn('pendidikans', 'jenis_kelamin')) {
                $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->nullable()->after('tanggal_lahir');
            }
            if (!Schema::hasColumn('pendidikans', 'umur')) {
                $table->integer('umur')->nullable()->after('jenis_kelamin');
            }
            if (!Schema::hasColumn('pendidikans', 'pendidikan_terakhir')) {
                $table->enum('pendidikan_terakhir', [
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
                ])->nullable()->after('umur');
            }
        });

        // Tambahkan foreign keys hanya jika belum ada
        Schema::table('pendidikans', function (Blueprint $table) {
            // Cek apakah foreign key sudah ada
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'pendidikans' 
                AND CONSTRAINT_NAME LIKE 'pendidikans_%_foreign'
            ");
            
            $existingForeignKeys = array_column($foreignKeys, 'CONSTRAINT_NAME');
            
            if (!in_array('pendidikans_id_posyandu_foreign', $existingForeignKeys)) {
                $table->foreign('id_posyandu')->references('id_posyandu')->on('posyandu')->cascadeOnDelete();
            }
            if (!in_array('pendidikans_id_users_foreign', $existingForeignKeys)) {
                $table->foreign('id_users')->references('id')->on('users')->cascadeOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pendidikans', function (Blueprint $table) {
            $table->dropForeign(['id_posyandu']);
            $table->dropForeign(['id_users']);
            $table->dropColumn([
                'id_posyandu',
                'id_users',
                'id_sasaran',
                'kategori_sasaran',
                'nik',
                'nama',
                'tanggal_lahir',
                'jenis_kelamin',
                'umur',
                'pendidikan_terakhir',
            ]);
        });
    }
};
