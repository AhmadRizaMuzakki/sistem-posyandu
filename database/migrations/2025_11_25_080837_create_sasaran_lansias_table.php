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
        Schema::create('sasaran_lansias', function (Blueprint $table) {
            $table->id('id_sasaran_lansia');
            $table->unsignedBigInteger('id_posyandu')->nullable();
            $table->unsignedBigInteger('id_users')->nullable();
            $table->string('nama_sasaran')->nullable();
            $table->string('nik_sasaran')->nullable();
            $table->string('no_kk_sasaran')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->integer('umur_sasaran')->nullable();
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
            ])->nullable();
            $table->string('nik_orangtua')->nullable();
            $table->text('alamat_sasaran')->nullable();
            $table->enum('kepersertaan_bpjs', ['PBI','NON PBI'])->nullable();
            $table->string('nomor_bpjs')->nullable();
            $table->string('nomor_telepon')->nullable();

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
        Schema::dropIfExists('sasaran_lansias');
    }
};
