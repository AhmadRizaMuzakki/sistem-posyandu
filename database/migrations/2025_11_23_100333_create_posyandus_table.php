<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posyandu', function (Blueprint $table) {
            $table->id('id_posyandu');
            $table->string('nama_posyandu');
            $table->text('alamat_posyandu')->nullable();
            $table->integer('jumlah_sasaran')->nullable();
            $table->text('sk_posyandu')->nullable();
            $table->string('domisili_posyandu')->nullable();
            $table->string('logo_posyandu')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posyandu');
    }
};