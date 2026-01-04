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
        Schema::table('petugas_kesehatan', function (Blueprint $table) {
            // Hapus kolom jabatan_petugas_kesehatan
            $table->dropColumn('jabatan_petugas_kesehatan');
            
            // Ubah kolom bidan menjadi enum
            $table->enum('bidan', ['Bidan Desa', 'Dokter Desa'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petugas_kesehatan', function (Blueprint $table) {
            // Kembalikan kolom jabatan_petugas_kesehatan
            $table->enum('jabatan_petugas_kesehatan', ['Ketua', 'Sekretaris', 'Bendahara', 'Anggota'])->nullable();
            
            // Kembalikan kolom bidan menjadi string
            $table->string('bidan')->nullable()->change();
        });
    }
};
