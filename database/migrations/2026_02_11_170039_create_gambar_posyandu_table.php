<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gambar_posyandu', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_posyandu');
            $table->string('path'); // path gambar
            $table->string('caption')->nullable(); // keterangan gambar
            $table->integer('urutan')->default(0); // urutan tampil
            $table->timestamps();

            $table->foreign('id_posyandu')
                ->references('id_posyandu')
                ->on('posyandu')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gambar_posyandu');
    }
};
