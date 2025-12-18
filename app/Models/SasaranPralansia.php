<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SasaranPralansia extends Model
{
    protected $table = 'sasaran_pralansias';
    protected $primaryKey = 'id_sasaran_pralansia';

    protected $fillable = [
        'id_posyandu',
        'id_users',
        'nama_sasaran',
        'nik_sasaran',
        'no_kk_sasaran',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'umur_sasaran',
        'pekerjaan',
        'pendidikan',
        'nik_orangtua',
        'alamat_sasaran',
        'rt',
        'rw',
        'kepersertaan_bpjs',
        'nomor_bpjs',
        'nomor_telepon',
    ];

    public function posyandu()
    {
        return $this->belongsTo(Posyandu::class, 'id_posyandu');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_users');
    }

    public function orangtua()
    {
        return $this->belongsTo(Orangtua::class, 'nik_orangtua', 'nik');
    }
}
