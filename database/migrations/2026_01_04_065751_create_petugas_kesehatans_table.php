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
        Schema::create('petugas_kesehatan', function (Blueprint $table) {
            $table->id('id_petugas_kesehatan');
            $table->string('nik_petugas_kesehatan');
            $table->string('nama_petugas_kesehatan')->nullable();
            $table->unsignedBigInteger('id_users')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->text('alamat_petugas_kesehatan')->nullable();
            $table->enum('jabatan_petugas_kesehatan', ['Ketua', 'Sekretaris', 'Bendahara', 'Anggota'])->nullable();
            $table->string('bidan')->nullable(); // Kolom bidan
            $table->unsignedBigInteger('id_posyandu')->nullable();
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
        Schema::dropIfExists('petugas_kesehatan');
    }
};
