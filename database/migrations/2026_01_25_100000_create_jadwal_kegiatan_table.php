<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_kegiatan', function (Blueprint $table) {
            $table->id('id_jadwal_kegiatan');
            $table->unsignedBigInteger('id_posyandu');
            $table->date('tanggal');
            $table->string('nama_kegiatan');
            $table->text('deskripsi')->nullable();
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->timestamps();

            $table->foreign('id_posyandu')->references('id_posyandu')->on('posyandu')->cascadeOnDelete();
            $table->index(['id_posyandu', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_kegiatan');
    }
};
