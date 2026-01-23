<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    protected $table = 'jadwals';
    protected $primaryKey = 'id_jadwal';

    protected $fillable = [
        'id_posyandu',
        'id_petugas_kesehatan',
        'tanggal',
        'keterangan',
        'presensi',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'presensi' => 'string',
    ];

    public function posyandu()
    {
        return $this->belongsTo(Posyandu::class, 'id_posyandu');
    }

    public function petugasKesehatan()
    {
        return $this->belongsTo(PetugasKesehatan::class, 'id_petugas_kesehatan');
    }
}
