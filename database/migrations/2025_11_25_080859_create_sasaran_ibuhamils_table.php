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
        Schema::create('sasaran_ibuhamils', function (Blueprint $table) {
            $table->id('id_sasaran_ibuhamil');
            $table->unsignedBigInteger('id_posyandu')->nullable();
            $table->unsignedBigInteger('id_users')->nullable();
            $table->string('nama_sasaran')->nullable();
            $table->string('nik_sasaran')->nullable();
            $table->string('no_kk_sasaran')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->integer('umur_sasaran')->nullable();
            $table->string('nik_orangtua')->nullable();
            $table->text('alamat_sasaran')->nullable();
            $table->enum('kepersertaan_bpjs', ['PBI','NON PBI'])->nullable();
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
        Schema::dropIfExists('sasaran_ibuhamils');
    }
};
