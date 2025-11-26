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
        'logo_posyandu',
    ];

    public function kader()
    {
        return $this->hasMany(Kader::class, 'id_posyandu');
    }

    public function sasaran_bayibalita()
    {
        return $this->hasMany(Sasaran_Bayibalita::class, 'id_posyandu');
    }
    public function sasaran_dewasa()
    {
        return $this->hasMany(sasaran_dewasa::class, 'id_posyandu');
    }
    public function sasaran_ibuhamil()
    {
        return $this->hasMany(sasaran_ibuhamil::class, 'id_posyandu');
    }
    public function sasaran_lansia()
    {
        return $this->hasMany(sasaran_lansia::class, 'id_posyandu');
    }
    public function sasaran_remaja()
    {
        return $this->hasMany(sasaran_remaja::class, 'id_posyandu');
    }
    public function sasaran_pralansia()
    {
        return $this->hasMany(sasaran_pralansia::class, 'id_posyandu');
    }

}
