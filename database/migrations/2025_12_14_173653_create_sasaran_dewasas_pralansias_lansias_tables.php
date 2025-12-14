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
        // Tabel sasaran_dewasas
        Schema::create('sasaran_dewasas', function (Blueprint $table) {
            $table->id('id_sasaran_dewasa');
            $table->unsignedBigInteger('id_posyandu')->nullable();
            $table->unsignedBigInteger('id_users')->nullable();
            $table->string('nama_sasaran')->nullable();
            $table->string('nik_sasaran')->nullable();
            $table->string('no_kk_sasaran')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->integer('umur_sasaran')->nullable();
            $table->string('pekerjaan')->nullable();
            $table->string('nik_orangtua')->nullable();
            $table->text('alamat_sasaran')->nullable();
            $table->string('rt')->nullable();
            $table->string('rw')->nullable();
            $table->enum('kepersertaan_bpjs', ['PBI','NON PBI'])->default('NON PBI')->nullable();
            $table->string('nomor_bpjs')->nullable();
            $table->string('nomor_telepon')->nullable();

            $table->timestamps();

            $table->foreign('id_posyandu')->references('id_posyandu')->on('posyandu')->cascadeOnDelete();
            $table->foreign('id_users')->references('id')->on('users')->cascadeOnDelete();
        });

        // Tabel sasaran_pralansias
        Schema::create('sasaran_pralansias', function (Blueprint $table) {
            $table->id('id_sasaran_pralansia');
            $table->unsignedBigInteger('id_posyandu')->nullable();
            $table->unsignedBigInteger('id_users')->nullable();
            $table->string('nama_sasaran')->nullable();
            $table->string('nik_sasaran')->nullable();
            $table->string('no_kk_sasaran')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->integer('umur_sasaran')->nullable();
            $table->string('pekerjaan')->nullable();
            $table->string('nik_orangtua')->nullable();
            $table->text('alamat_sasaran')->nullable();
            $table->string('rt')->nullable();
            $table->string('rw')->nullable();
            $table->enum('kepersertaan_bpjs', ['PBI','NON PBI'])->default('NON PBI')->nullable();
            $table->string('nomor_bpjs')->nullable();
            $table->string('nomor_telepon')->nullable();

            $table->timestamps();

            $table->foreign('id_posyandu')->references('id_posyandu')->on('posyandu')->cascadeOnDelete();
            $table->foreign('id_users')->references('id')->on('users')->cascadeOnDelete();
        });

        // Tabel sasaran_lansias
        Schema::create('sasaran_lansias', function (Blueprint $table) {
            $table->id('id_sasaran_lansia');
            $table->unsignedBigInteger('id_posyandu')->nullable();
            $table->unsignedBigInteger('id_users')->nullable();
            $table->string('nama_sasaran')->nullable();
            $table->string('nik_sasaran')->nullable();
            $table->string('no_kk_sasaran')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->integer('umur_sasaran')->nullable();
            $table->string('pekerjaan')->nullable();
            $table->string('nik_orangtua')->nullable();
            $table->text('alamat_sasaran')->nullable();
            $table->string('rt')->nullable();
            $table->string('rw')->nullable();
            $table->enum('kepersertaan_bpjs', ['PBI','NON PBI'])->default('NON PBI')->nullable();
            $table->string('nomor_bpjs')->nullable();
            $table->string('nomor_telepon')->nullable();

            $table->timestamps();

            $table->foreign('id_posyandu')->references('id_posyandu')->on('posyandu')->cascadeOnDelete();
            $table->foreign('id_users')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sasaran_lansias');
        Schema::dropIfExists('sasaran_pralansias');
        Schema::dropIfExists('sasaran_dewasas');
    }
};
