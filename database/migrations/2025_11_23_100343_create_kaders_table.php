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
        Schema::create('kader', function (Blueprint $table) {
            $table->id('id_kader');
            $table->string('nik_kader');
            $table->unsignedBigInteger('id_users')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->text('alamat_kader')->nullable();
            $table->enum('jabatan_kader', ['Ketua', 'Sekretaris', 'Bendahara', 'Anggota'])->nullable();
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
        Schema::dropIfExists('kader');
    }
};