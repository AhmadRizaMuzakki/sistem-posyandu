<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PosyanduSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Posyandu::insert([
            [
                'nama_posyandu' => 'Posyandu Sedap Malam',
                'alamat_posyandu' => 'Jl. Mawar No. 123',
                'domisili_posyandu' => 'Kelurahan Mawar',
                'jumlah_sasaran' => 100,
                'sk_posyandu' => '',
                'logo_posyandu' => null,
            ],
            [
                'nama_posyandu' => 'Posyandu Melati',
                'alamat_posyandu' => 'Jl. Melati No. 45',
                'domisili_posyandu' => 'Kelurahan Melati',
                'jumlah_sasaran' => 85,
                'sk_posyandu' => '',
                'logo_posyandu' => null,
            ],
            [
                'nama_posyandu' => 'Posyandu Cempaka',
                'alamat_posyandu' => 'Jl. Cempaka No. 12',
                'domisili_posyandu' => 'Kelurahan Cempaka',
                'jumlah_sasaran' => 120,
                'sk_posyandu' => '',
                'logo_posyandu' => null,
            ],
            [
                'nama_posyandu' => 'Posyandu Plamboyan',
                'alamat_posyandu' => 'Jl. Plamboyan No. 80',
                'domisili_posyandu' => 'Kelurahan Plamboyan',
                'jumlah_sasaran' => 95,
                'sk_posyandu' => '',
                'logo_posyandu' => null,
            ],
            [
                'nama_posyandu' => 'Posyandu Tulip',
                'alamat_posyandu' => 'Jl. Tulip No. 7',
                'domisili_posyandu' => 'Kelurahan Tulip',
                'jumlah_sasaran' => 70,
                'sk_posyandu' => '',
                'logo_posyandu' => null,
            ],
            [
                'nama_posyandu' => 'Posyandu Mawar',
                'alamat_posyandu' => 'Jl. Mawar Indah',
                'domisili_posyandu' => 'Kelurahan Mawar Indah',
                'jumlah_sasaran' => 110,
                'sk_posyandu' => '',
                'logo_posyandu' => null,
            ],
            [
                'nama_posyandu' => 'Posyandu Kenanga',
                'alamat_posyandu' => 'Jl. Kenanga No. 9',
                'domisili_posyandu' => 'Kelurahan Kenanga',
                'jumlah_sasaran' => 60,
                'sk_posyandu' => '',
                'logo_posyandu' => null,
            ],
            [
                'nama_posyandu' => 'Posyandu Anggrek',
                'alamat_posyandu' => 'Jl. Anggrek Baru',
                'domisili_posyandu' => 'Kelurahan Anggrek',
                'jumlah_sasaran' => 90,
                'sk_posyandu' => '',
                'logo_posyandu' => null,
            ],
            [
                'nama_posyandu' => 'Posyandu Teratai',
                'alamat_posyandu' => 'Jl. Teratai No. 34',
                'domisili_posyandu' => 'Kelurahan Teratai',
                'jumlah_sasaran' => 105,
                'sk_posyandu' => '',
                'logo_posyandu' => null,
            ],
            [
                'nama_posyandu' => 'Posyandu Bunga Tanjung',
                'alamat_posyandu' => 'Jl. Tanjung Raya',
                'domisili_posyandu' => 'Kelurahan Tanjung',
                'jumlah_sasaran' => 75,
                'sk_posyandu' => '',
                'logo_posyandu' => null,
            ],
            [
                'nama_posyandu' => 'Posyandu Dahlia',
                'alamat_posyandu' => 'Jl. Dahlia Merah',
                'domisili_posyandu' => 'Kelurahan Dahlia',
                'jumlah_sasaran' => 80,
                'sk_posyandu' => '',
                'logo_posyandu' => null,
            ],
            [
                'nama_posyandu' => 'Posyandu Anyelir',
                'alamat_posyandu' => 'Jl. Anyelir Kuning',
                'domisili_posyandu' => 'Kelurahan Anyelir',
                'jumlah_sasaran' => 55,
                'sk_posyandu' => '',
                'logo_posyandu' => null,
            ],
            [
                'nama_posyandu' => 'Posyandu Bougenville',
                'alamat_posyandu' => 'Jl. Bougenville 2',
                'domisili_posyandu' => 'Kelurahan Bougenville',
                'jumlah_sasaran' => 65,
                'sk_posyandu' => '',
                'logo_posyandu' => null,
            ],
            [
                'nama_posyandu' => 'Posyandu Asoka',
                'alamat_posyandu' => 'Jl. Asoka Indah',
                'domisili_posyandu' => 'Kelurahan Asoka',
                'jumlah_sasaran' => 50,
                'sk_posyandu' => '',
                'logo_posyandu' => null,
            ],
            [
                'nama_posyandu' => 'Posyandu Wijaya Kusuma',
                'alamat_posyandu' => 'Jl. Wijaya Kusuma',
                'domisili_posyandu' => 'Kelurahan Wijaya Kusuma',
                'jumlah_sasaran' => 100,
                'sk_posyandu' => '',
                'logo_posyandu' => null,
            ],
            [
                'nama_posyandu' => 'Posyandu Nusa Indah',
                'alamat_posyandu' => 'Jl. Nusa Indah',
                'domisili_posyandu' => 'Kelurahan Nusa Indah',
                'jumlah_sasaran' => 75,
                'sk_posyandu' => '',
                'logo_posyandu' => null,
            ],
        ]);
    }
}
