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
        Schema::create('ibu_menyusuis', function (Blueprint $table) {
            $table->id('id_ibu_menyusui');
            $table->unsignedBigInteger('id_posyandu')->nullable();
            $table->string('nama_ibu');
            $table->string('nama_suami')->nullable();
            $table->string('nama_bayi')->nullable();
            $table->timestamps();

            $table->foreign('id_posyandu')->references('id_posyandu')->on('posyandu')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ibu_menyusuis');
    }
};
