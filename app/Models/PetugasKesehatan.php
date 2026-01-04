<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PetugasKesehatan extends Model
{
    use HasFactory;
    protected $table = 'petugas_kesehatan';
    protected $primaryKey = 'id_petugas_kesehatan';

    protected $fillable = [
        'nik_petugas_kesehatan',
        'nama_petugas_kesehatan',
        'id_users',
        'tanggal_lahir',
        'alamat_petugas_kesehatan',
        'bidan',
        'id_posyandu',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_users');
    }

    public function posyandu()
    {
        return $this->belongsTo(Posyandu::class, 'id_posyandu');
    }
}
