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
        Schema::table('jadwals', function (Blueprint $table) {
            // Tambahkan kolom id_petugas_kesehatan (nullable dulu untuk migrasi data)
            $table->unsignedBigInteger('id_petugas_kesehatan')->nullable()->after('id_posyandu');
        });
        
        // Migrasi data dari nama_petugas ke id_petugas_kesehatan
        $jadwals = \DB::table('jadwals')->whereNotNull('nama_petugas')->get();
        foreach ($jadwals as $jadwal) {
            $petugas = \DB::table('petugas_kesehatan')
                ->where('nama_petugas_kesehatan', $jadwal->nama_petugas)
                ->where('id_posyandu', $jadwal->id_posyandu)
                ->first();
            
            if ($petugas) {
                \DB::table('jadwals')
                    ->where('id_jadwal', $jadwal->id_jadwal)
                    ->update(['id_petugas_kesehatan' => $petugas->id_petugas_kesehatan]);
            }
        }
        
        Schema::table('jadwals', function (Blueprint $table) {
            // Hapus kolom nama_petugas
            $table->dropColumn('nama_petugas');
            
            // Tambahkan foreign key
            $table->foreign('id_petugas_kesehatan')->references('id_petugas_kesehatan')->on('petugas_kesehatan')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwals', function (Blueprint $table) {
            // Hapus foreign key
            $table->dropForeign(['id_petugas_kesehatan']);
            
            // Tambahkan kembali kolom nama_petugas
            $table->string('nama_petugas')->after('id_posyandu');
            
            // Migrasi data kembali dari id_petugas_kesehatan ke nama_petugas
            $jadwals = \DB::table('jadwals')->whereNotNull('id_petugas_kesehatan')->get();
            foreach ($jadwals as $jadwal) {
                $petugas = \DB::table('petugas_kesehatan')
                    ->where('id_petugas_kesehatan', $jadwal->id_petugas_kesehatan)
                    ->first();
                
                if ($petugas) {
                    \DB::table('jadwals')
                        ->where('id_jadwal', $jadwal->id_jadwal)
                        ->update(['nama_petugas' => $petugas->nama_petugas_kesehatan]);
                }
            }
            
            // Hapus kolom id_petugas_kesehatan
            $table->dropColumn('id_petugas_kesehatan');
        });
    }
};
