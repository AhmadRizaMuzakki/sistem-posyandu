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
        Schema::create('imunisasi', function (Blueprint $table) {
            $table->id('id_imunisasi');
            $table->unsignedBigInteger('id_posyandu')->nullable();
            $table->unsignedBigInteger('id_users')->nullable(); // User yang menginput data
            
            // Relasi Dinamis: Mengambil data dari salah satu tabel sasaran
            $table->unsignedBigInteger('id_sasaran')->nullable(); // ID dari tabel sasaran yang sesuai
            $table->enum('kategori_sasaran', ['bayibalita', 'remaja', 'dewasa', 'pralansia', 'lansia'])->nullable(); // Penunjuk tabel asal
            
            // Data Imunisasi dibuat per baris (Vertical Structure)
            $table->string('jenis_imunisasi')->nullable(); // Contoh: BCG, Polio 1, DPT 1, Booster COVID, dll
            $table->date('tanggal_imunisasi')->nullable();
            $table->text('keterangan')->nullable();
            
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
        Schema::dropIfExists('imunisasi');
    }
};
