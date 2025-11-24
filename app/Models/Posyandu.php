<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Posyandu extends Model
{
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

    public function sasaran()
    {
        return $this->hasMany(Sasaran::class, 'id_posyandu');
    }
}
