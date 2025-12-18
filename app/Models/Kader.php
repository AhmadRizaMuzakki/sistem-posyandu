<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kader extends Model
{
    use HasFactory;
    protected $table = 'kader';
    protected $primaryKey = 'id_kader';

    protected $fillable = [
        'nik_kader',
        'nama_kader',
        'id_users',
        'tanggal_lahir',
        'alamat_kader',
        'jabatan_kader',
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
