<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SasaranIbuhamil extends Model
{
    protected $table = 'sasaran_ibuhamils';
    protected $primaryKey = 'id_sasaran_ibuhamil';

    protected $fillable = [
        'id_posyandu',
        'nama_sasaran',
        'nik_sasaran',
        'no_kk_sasaran',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'status_keluarga',
        'umur_sasaran',
        'minggu_kandungan',
        'pekerjaan',
        'pendidikan',
        'nik_orangtua',
        'alamat_sasaran',
        'rt',
        'rw',
        'kepersertaan_bpjs',
        'nomor_bpjs',
        'nomor_telepon',
        'nama_suami',
        'nik_suami',
        'tempat_lahir_suami',
        'tanggal_lahir_suami',
        'pekerjaan_suami',
        'status_keluarga_suami',
    ];

    public function posyandu()
    {
        return $this->belongsTo(Posyandu::class, 'id_posyandu');
    }
}
