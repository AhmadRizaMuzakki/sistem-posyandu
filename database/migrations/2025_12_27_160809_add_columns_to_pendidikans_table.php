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
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite tidak memiliki information_schema
            // Untuk SQLite, langsung tambahkan foreign key tanpa pengecekan
            // Jika sudah ada, akan error, tapi kita tangani dengan try-catch
            if (Schema::hasColumn('pendidikans', 'id_posyandu')) {
                try {
                    Schema::table('pendidikans', function (Blueprint $table) {
                        $table->foreign('id_posyandu')->references('id_posyandu')->on('posyandu')->cascadeOnDelete();
                    });
                } catch (\Exception $e) {
                    // Foreign key mungkin sudah ada, skip
                }
            }
            if (Schema::hasColumn('pendidikans', 'id_users')) {
                try {
                    Schema::table('pendidikans', function (Blueprint $table) {
                        $table->foreign('id_users')->references('id')->on('users')->cascadeOnDelete();
                    });
                } catch (\Exception $e) {
                    // Foreign key mungkin sudah ada, skip
                }
            }
        } else {
            // Untuk MySQL/MariaDB, cek dulu menggunakan information_schema
            try {
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'pendidikans' 
                    AND CONSTRAINT_NAME LIKE 'pendidikans_%_foreign'
                ");
                
                $existingForeignKeys = array_column($foreignKeys, 'CONSTRAINT_NAME');
                
                Schema::table('pendidikans', function (Blueprint $table) use ($existingForeignKeys) {
                    if (!in_array('pendidikans_id_posyandu_foreign', $existingForeignKeys)) {
                        $table->foreign('id_posyandu')->references('id_posyandu')->on('posyandu')->cascadeOnDelete();
                    }
                    if (!in_array('pendidikans_id_users_foreign', $existingForeignKeys)) {
                        $table->foreign('id_users')->references('id')->on('users')->cascadeOnDelete();
                    }
                });
            } catch (\Exception $e) {
                // Jika error, coba tambahkan langsung tanpa pengecekan
                try {
                    Schema::table('pendidikans', function (Blueprint $table) {
                        $table->foreign('id_posyandu')->references('id_posyandu')->on('posyandu')->cascadeOnDelete();
                    });
                } catch (\Exception $e2) {
                    // Skip jika sudah ada
                }
                try {
                    Schema::table('pendidikans', function (Blueprint $table) {
                        $table->foreign('id_users')->references('id')->on('users')->cascadeOnDelete();
                    });
                } catch (\Exception $e2) {
                    // Skip jika sudah ada
                }
            }
        }
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
