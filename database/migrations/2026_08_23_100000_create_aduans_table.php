<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aduans', function (Blueprint $table) {
            $table->id('id_aduan');
            $table->unsignedBigInteger('no_kk');
            $table->unsignedBigInteger('id_posyandu')->nullable();
            $table->string('judul');
            $table->text('isi_aduan');
            $table->enum('kategori', [
                'kesehatan',
                'pendidikan',
                'pekerjaan_umum',
                'perumahan_rakyat',
                'trantibumlinmas',
                'sosial',
            ])->default('kesehatan');
            $table->enum('status', ['menunggu', 'diproses', 'selesai', 'ditolak'])->default('menunggu');
            $table->text('tanggapan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('tanggal_aduan');
            $table->timestamps();

            $table->index('no_kk');
            $table->index('status');
            $table->index('tanggal_aduan');

            $table->foreign('id_posyandu')
                ->references('id_posyandu')
                ->on('posyandu')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aduans');
    }
};
