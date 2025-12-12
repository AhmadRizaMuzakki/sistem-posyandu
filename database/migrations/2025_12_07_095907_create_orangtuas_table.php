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
        Schema::create('orangtua', function (Blueprint $table) {
            $table->unsignedBigInteger('nik')->primary();
            $table->string('nama');
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->enum('pekerjaan', [
                'Belum/Tidak Bekerja',
                'Mengurus Rumah Tangga',
                'Pelajar/Mahasiswa',
                'Pensiunan',
                'Pegawai Negeri Sipil',
                'Tentara Nasional Indonesia',
                'Kepolisian RI',
                'Perdagangan',
                'Petani/Pekebun',
                'Peternak',
                'Nelayan/Perikanan',
                'Industri',
                'Konstruksi',
                'Transportasi',
                'Karyawan Swasta',
                'Karyawan BUMN',
                'Karyawan BUMD',
                'Karyawan Honorer',
                'Buruh Harian Lepas',
                'Buruh Tani/Perkebunan',
                'Buruh Nelayan/Perikanan',
                'Buruh Peternakan',
                'Pembantu Rumah Tangga',
                'Tukang Cukur',
                'Tukang Listrik',
                'Tukang Batu',
                'Tukang Kayu',
                'Tukang Sol Sepatu',
                'Tukang Las/Pandai Besi',
                'Tukang Jahit',
                'Tukang Gigi',
                'Penata Rias',
                'Penata Busana',
                'Penata Rambut',
                'Mekanik',
                'Seniman',
                'Tabib',
                'Paraji',
                'Perancang Busana',
                'Penterjemah',
                'Imam Masjid',
                'Pendeta',
                'Pastor',
                'Wartawan',
                'Ustadz/Mubaligh',
                'Juru Masak',
                'Promotor Acara',
                'Anggota DPR-RI',
                'Anggota DPD',
                'Anggota BPK',
                'Presiden',
                'Wakil Presiden',
                'Anggota Mahkamah Konstitusi',
                'Anggota Kabinet/Kementerian',
                'Duta Besar',
                'Gubernur',
                'Wakil Gubernur',
                'Bupati',
                'Wakil Bupati',
                'Walikota',
                'Wakil Walikota',
                'Anggota DPRD Provinsi',
                'Anggota DPRD Kabupaten/Kota',
                'Dosen',
                'Guru',
                'Pilot',
                'Pengacara',
                'Notaris',
                'Arsitek',
                'Akuntan',
                'Konsultan',
                'Dokter',
                'Bidan',
                'Perawat',
                'Apoteker',
                'Psikiater/Psikolog',
                'Penyiar Televisi',
                'Penyiar Radio',
                'Pelaut',
                'Peneliti',
                'Sopir',
                'Pialang',
                'Paranormal',
                'Pedagang',
                'Perangkat Desa',
                'Kepala Desa',
                'Biarawati',
                'Wiraswasta',
                'Lainnya'
            ]);
            $table->enum('kelamin', ['Laki-laki', 'Perempuan']);
            $table->enum('kepersertaan_bpjs', ['PBI', 'NON PBI'])->default('NON PBI')->nullable();
            $table->string('nomor_bpjs')->nullable();
            $table->string('nomor_telepon')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orangtua');
    }
};
