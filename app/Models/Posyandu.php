<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Posyandu extends Model
{
    protected $table = 'posyandu';
    protected $primaryKey = 'id_posyandu';

    protected $fillable = [
        'nama_posyandu',
        'alamat_posyandu',
        'jumlah_sasaran',
        'sk_posyandu',
        'domisili_posyandu',
        'link_maps',
        'logo_posyandu',
    ];

    public function kader()
    {
        return $this->hasMany(Kader::class, 'id_posyandu');
    }

    public function sasaran_bayibalita()
    {
        return $this->hasMany(SasaranBayibalita::class, 'id_posyandu');
    }

    public function sasaran_dewasa()
    {
        return $this->hasMany(SasaranDewasa::class, 'id_posyandu');
    }

    public function sasaran_ibuhamil()
    {
        return $this->hasMany(SasaranIbuhamil::class, 'id_posyandu');
    }

    public function sasaran_lansia()
    {
        return $this->hasMany(SasaranLansia::class, 'id_posyandu');
    }

    public function sasaran_remaja()
    {
        return $this->hasMany(SasaranRemaja::class, 'id_posyandu');
    }

    public function sasaran_pralansia()
    {
        return $this->hasMany(SasaranPralansia::class, 'id_posyandu');
    }

    public function imunisasi()
    {
        return $this->hasMany(Imunisasi::class, 'id_posyandu');
    }

    public function petugas_kesehatan()
    {
        return $this->hasMany(PetugasKesehatan::class, 'id_posyandu');
    }

    public function jadwal()
    {
        return $this->hasMany(Jadwal::class, 'id_posyandu');
    }

    public function jadwalKegiatan()
    {
        return $this->hasMany(JadwalKegiatan::class, 'id_posyandu');
    }
}
