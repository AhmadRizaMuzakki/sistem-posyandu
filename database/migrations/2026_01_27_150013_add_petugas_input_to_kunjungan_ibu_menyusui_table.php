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
        Schema::table('kunjungan_ibu_menyusui', function (Blueprint $table) {
            $table->unsignedBigInteger('id_petugas_input')->nullable()->after('id_petugas_imunisasi');

            $table->foreign('id_petugas_input')->references('id_petugas_kesehatan')->on('petugas_kesehatan')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kunjungan_ibu_menyusui', function (Blueprint $table) {
            $table->dropForeign(['id_petugas_input']);
            $table->dropColumn('id_petugas_input');
        });
    }
};
