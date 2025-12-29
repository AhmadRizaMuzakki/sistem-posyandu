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
        // Cek apakah tabel sudah ada
        if (Schema::hasTable('pendidikans')) {
            return;
        }

        Schema::create('pendidikans', function (Blueprint $table) {
            $table->id('id_pendidikan');
            $table->unsignedBigInteger('id_posyandu')->nullable();
            $table->unsignedBigInteger('id_users')->nullable(); // User yang menginput data
            
            // Relasi Dinamis: Mengambil data dari salah satu tabel sasaran
            $table->unsignedBigInteger('id_sasaran')->nullable(); // ID dari tabel sasaran yang sesuai
            $table->enum('kategori_sasaran', ['bayibalita', 'remaja', 'dewasa', 'pralansia', 'lansia'])->nullable(); // Penunjuk tabel asal
            
            // Data Pendidikan
            $table->string('nik')->nullable();
            $table->string('nama')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->nullable();
            $table->integer('umur')->nullable();
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
            ])->nullable();
            
            $table->timestamps();
            
            // Foreign Keys
            $table->foreign('id_posyandu')->references('id_posyandu')->on('posyandu')->cascadeOnDelete();
            $table->foreign('id_users')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendidikans');
    }
};
