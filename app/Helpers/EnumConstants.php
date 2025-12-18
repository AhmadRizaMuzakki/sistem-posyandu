<?php

namespace App\Helpers;

class EnumConstants
{
    /**
     * Daftar pekerjaan yang digunakan di sistem
     */
    public static function pekerjaan(): array
    {
        return [
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
        ];
    }

    /**
     * Daftar tingkat pendidikan yang digunakan di sistem
     */
    public static function pendidikan(): array
    {
        return [
            'Tidak/Belum Sekolah',
            'Tidak Tamat SD/Sederajat',
            'Tamat SD/Sederajat',
            'SLTP/Sederajat',
            'SLTA/Sederajat',
            'Diploma I/II',
            'Akademi/Diploma III/Sarjana Muda',
            'Diploma IV/Strata I',
            'Strata II',
            'Strata III'
        ];
    }

    /**
     * Daftar kepersertaan BPJS
     */
    public static function kepersertaanBpjs(): array
    {
        return ['PBI', 'NON PBI'];
    }
}

