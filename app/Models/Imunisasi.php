<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Imunisasi extends Model
{
    protected $table = 'imunisasi';
    protected $primaryKey = 'id_imunisasi';

    protected $fillable = [
        'id_posyandu',
        'id_users',
        'id_petugas_kesehatan',
        'id_sasaran',
        'kategori_sasaran',
        'jenis_imunisasi',
        'tanggal_imunisasi',
        'tinggi_badan',
        'berat_badan',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_imunisasi' => 'date',
        'tinggi_badan' => 'decimal:2',
        'berat_badan' => 'decimal:2',
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

    // Relasi ke Petugas Kesehatan
    public function petugasKesehatan()
    {
        return $this->belongsTo(PetugasKesehatan::class, 'id_petugas_kesehatan');
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
            default => null,
        };

        if ($modelClass) {
            $primaryKey = match($this->kategori_sasaran) {
                'bayibalita' => 'id_sasaran_bayibalita',
                'remaja' => 'id_sasaran_remaja',
                'dewasa' => 'id_sasaran_dewasa',
                'pralansia' => 'id_sasaran_pralansia',
                'lansia' => 'id_sasaran_lansia',
                default => 'id',
            };

            return $modelClass::where($primaryKey, $this->id_sasaran)->first();
        }

        return null;
    }
}
