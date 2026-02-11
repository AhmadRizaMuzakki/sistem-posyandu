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
        Schema::create('perpustakaan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_posyandu');
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('penulis')->nullable();
            $table->string('kategori')->nullable(); // kesehatan, gizi, parenting, umum, dll
            $table->string('cover_image')->nullable(); // path gambar cover
            $table->string('file_path')->nullable(); // path file PDF/document
            $table->json('halaman_images')->nullable(); // JSON array path gambar halaman untuk flipbook
            $table->integer('jumlah_halaman')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('id_posyandu')
                ->references('id_posyandu')
                ->on('posyandu')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perpustakaan');
    }
};
