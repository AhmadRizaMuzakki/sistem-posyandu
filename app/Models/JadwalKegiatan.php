<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalKegiatan extends Model
{
    protected $table = 'jadwal_kegiatan';
    protected $primaryKey = 'id_jadwal_kegiatan';

    protected $fillable = [
        'id_posyandu',
        'tanggal',
        'nama_kegiatan',
        'tempat',
        'deskripsi',
        'jam_mulai',
        'jam_selesai',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function posyandu()
    {
        return $this->belongsTo(Posyandu::class, 'id_posyandu');
    }
}
