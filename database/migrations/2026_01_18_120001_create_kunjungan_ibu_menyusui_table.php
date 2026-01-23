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
        Schema::create('kunjungan_ibu_menyusui', function (Blueprint $table) {
            $table->id('id_kunjungan');
            $table->unsignedBigInteger('id_ibu_menyusui');
            $table->integer('bulan'); // 1-12
            $table->integer('tahun');
            $table->enum('status', ['success'])->nullable();
            $table->date('tanggal_kunjungan')->nullable();
            $table->timestamps();

            $table->foreign('id_ibu_menyusui')->references('id_ibu_menyusui')->on('ibu_menyusuis')->cascadeOnDelete();
            $table->unique(['id_ibu_menyusui', 'bulan', 'tahun'], 'unique_kunjungan_per_bulan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kunjungan_ibu_menyusui');
    }
};
