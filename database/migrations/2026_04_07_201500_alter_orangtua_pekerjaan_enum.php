<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tahap 1: longgarkan enum agar nilai lama dan baru sama-sama valid.
        $transitionalList = [
            'Belum/Tidak Bekerja', 'Mengurus Rumah Tangga', 'Pelajar/Mahasiswa', 'Pensiunan',
            'Pegawai Negeri Sipil', 'Aparatur Sipil Negara (ASN)',
            'Tentara Nasional Indonesia', 'Kepolisian RI', 'Kepolisian RI (POLRI)',
            'Perdagangan', 'Petani/Pekebun', 'Peternak', 'Nelayan/Perikanan', 'Industri',
            'Konstruksi', 'Transportasi', 'Karyawan Swasta', 'Karyawan BUMN', 'Karyawan BUMD',
            'Karyawan Honorer', 'Buruh Harian Lepas', 'Buruh Tani/Perkebunan', 'Buruh Nelayan/Perikanan',
            'Buruh Peternakan', 'Pembantu Rumah Tangga', 'Tukang Cukur', 'Tukang Listrik', 'Tukang Batu',
            'Tukang Kayu', 'Tukang Sol Sepatu', 'Tukang Las/Pandai Besi', 'Tukang Jahit', 'Tukang Gigi',
            'Penata Rias', 'Penata Busana', 'Penata Rambut', 'Mekanik', 'Seniman', 'Tabib', 'Paraji',
            'Perancang Busana', 'Penterjemah', 'Penerjemah', 'Imam Masjid', 'Pendeta', 'Pastor', 'Wartawan',
            'Ustadz/Mubaligh', 'Juru Masak', 'Promotor Acara', 'Anggota DPR-RI', 'Anggota DPD',
            'Anggota BPK', 'Presiden', 'Wakil Presiden', 'Anggota Mahkamah Konstitusi',
            'Anggota Kabinet/Kementerian', 'Duta Besar/Kepala Perwakilan', 'Gubernur', 'Wakil Gubernur',
            'Bupati', 'Wakil Bupati', 'Walikota', 'Wakil Walikota', 'Anggota DPRD Provinsi',
            'Anggota DPRD Kabupaten/Kota', 'Anggota DPRD Kab/Kota', 'Dosen', 'Guru', 'Pilot', 'Pengacara',
            'Notaris', 'Arsitek', 'Akuntan', 'Konsultan', 'Dokter', 'Bidan', 'Perawat', 'Apoteker',
            'Psikiater/Psikolog', 'Penyiar Televisi', 'Penyiar Radio', 'Pelaut', 'Peneliti', 'Sopir',
            'Pialang', 'Paranormal', 'Pedagang', 'Perangkat Desa', 'Kepala Desa', 'Biarawati',
            'Wiraswasta', 'Artis', 'Atlet', 'Chef', 'Manajer', 'Tenaga Tata Usaha', 'Operator',
            'Pekerja Pengolahan, Kerajinan', 'Teknisi', 'Asisten Ahli', 'Gembala', 'Uskup', 'Biarawan',
            'Pandita', 'Pinandita', 'Bhikhu', 'Bhikkhu', 'Xueshi', 'Wenshi', 'Jiaosheng', 'Lainnya',
        ];

        $this->alterEnum($transitionalList);

        // Tahap 2: normalisasi nilai lama ke nilai baru.
        DB::table('orangtua')
            ->where('pekerjaan', 'Pegawai Negeri Sipil')
            ->update(['pekerjaan' => 'Aparatur Sipil Negara (ASN)']);

        DB::table('orangtua')
            ->where('pekerjaan', 'Kepolisian RI')
            ->update(['pekerjaan' => 'Kepolisian RI (POLRI)']);

        DB::table('orangtua')
            ->where('pekerjaan', 'Penterjemah')
            ->update(['pekerjaan' => 'Penerjemah']);

        DB::table('orangtua')
            ->where('pekerjaan', 'Anggota DPRD Kabupaten/Kota')
            ->update(['pekerjaan' => 'Anggota DPRD Kab/Kota']);

        DB::table('orangtua')
            ->where('pekerjaan', 'Bhikhu')
            ->update(['pekerjaan' => 'Bhikkhu']);

        // Tahap 3: kunci ke enum final.
        $pekerjaanList = [
            'Belum/Tidak Bekerja', 'Mengurus Rumah Tangga', 'Pelajar/Mahasiswa', 'Pensiunan',
            'Aparatur Sipil Negara (ASN)', 'Tentara Nasional Indonesia', 'Kepolisian RI (POLRI)',
            'Perdagangan', 'Petani/Pekebun', 'Peternak', 'Nelayan/Perikanan', 'Industri',
            'Konstruksi', 'Transportasi', 'Karyawan Swasta', 'Karyawan BUMN', 'Karyawan BUMD',
            'Karyawan Honorer', 'Buruh Harian Lepas', 'Buruh Tani/Perkebunan', 'Buruh Nelayan/Perikanan',
            'Buruh Peternakan', 'Pembantu Rumah Tangga', 'Tukang Cukur', 'Tukang Listrik', 'Tukang Batu',
            'Tukang Kayu', 'Tukang Sol Sepatu', 'Tukang Las/Pandai Besi', 'Tukang Jahit', 'Tukang Gigi',
            'Penata Rias', 'Penata Busana', 'Penata Rambut', 'Mekanik', 'Seniman', 'Tabib', 'Paraji',
            'Perancang Busana', 'Penerjemah', 'Imam Masjid', 'Pendeta', 'Pastor', 'Wartawan',
            'Ustadz/Mubaligh', 'Juru Masak', 'Promotor Acara', 'Anggota DPR-RI', 'Anggota DPD',
            'Anggota BPK', 'Presiden', 'Wakil Presiden', 'Anggota Mahkamah Konstitusi',
            'Anggota Kabinet/Kementerian', 'Duta Besar/Kepala Perwakilan', 'Gubernur', 'Wakil Gubernur',
            'Bupati', 'Wakil Bupati', 'Walikota', 'Wakil Walikota', 'Anggota DPRD Provinsi',
            'Anggota DPRD Kab/Kota', 'Dosen', 'Guru', 'Pilot', 'Pengacara', 'Notaris', 'Arsitek',
            'Akuntan', 'Konsultan', 'Dokter', 'Bidan', 'Perawat', 'Apoteker', 'Psikiater/Psikolog',
            'Penyiar Televisi', 'Penyiar Radio', 'Pelaut', 'Peneliti', 'Sopir', 'Pialang', 'Paranormal',
            'Pedagang', 'Perangkat Desa', 'Kepala Desa', 'Biarawati', 'Wiraswasta', 'Artis', 'Atlet',
            'Chef', 'Manajer', 'Tenaga Tata Usaha', 'Operator', 'Pekerja Pengolahan, Kerajinan',
            'Teknisi', 'Asisten Ahli', 'Gembala', 'Uskup', 'Biarawan', 'Pandita', 'Pinandita',
            'Bhikkhu', 'Xueshi', 'Wenshi', 'Jiaosheng', 'Lainnya',
        ];

        $this->alterEnum($pekerjaanList);
    }

    private function alterEnum(array $values): void
    {
        $enumString = "'" . implode("', '", array_map(
            static fn (string $value): string => str_replace("'", "''", $value),
            $values
        )) . "'";

        DB::statement("ALTER TABLE `orangtua` MODIFY COLUMN `pekerjaan` ENUM($enumString) NOT NULL");
    }

    public function down(): void
    {
        // Dibiarkan kosong sesuai permintaan.
    }
};
