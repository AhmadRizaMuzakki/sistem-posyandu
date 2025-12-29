<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pendidikan extends Model
{
    protected $table = 'pendidikans';
    protected $primaryKey = 'id_pendidikan';

    protected $fillable = [
        'id_posyandu',
        'id_users',
        'id_sasaran',
        'kategori_sasaran',
        'nik',
        'nama',
        'tanggal_lahir',
        'jenis_kelamin',
        'umur',
        'pendidikan_terakhir',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'umur' => 'integer',
    ];

    // Relasi ke Posyandu
    public function posyandu()
    {
        return $this->belongsTo(Posyandu::class, 'id_posyandu');
    }

    // Relasi ke User (yang menginput)
    public function user()
    {
        return $this->belongsTo(User::class, 'id_users');
    }

    // Method untuk mendapatkan sasaran berdasarkan kategori
    public function getSasaranAttribute()
    {
        if (!$this->kategori_sasaran || !$this->id_sasaran) {
            return null;
        }

        $modelClass = match($this->kategori_sasaran) {
            'bayibalita' => SasaranBayibalita::class,
            'remaja' => SasaranRemaja::class,
            'dewasa' => SasaranDewasa::class,
            'pralansia' => SasaranPralansia::class,
            'lansia' => SasaranLansia::class,
            'ibuhamil' => SasaranIbuhamil::class,
            default => null,
        };

        if ($modelClass) {
            $primaryKey = match($this->kategori_sasaran) {
                'bayibalita' => 'id_sasaran_bayibalita',
                'remaja' => 'id_sasaran_remaja',
                'dewasa' => 'id_sasaran_dewasa',
                'pralansia' => 'id_sasaran_pralansia',
                'lansia' => 'id_sasaran_lansia',
                'ibuhamil' => 'id_sasaran_ibuhamil',
                default => 'id',
            };

            return $modelClass::where($primaryKey, $this->id_sasaran)->first();
        }

        return null;
    }
}
