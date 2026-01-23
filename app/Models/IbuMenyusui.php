<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IbuMenyusui extends Model
{
    protected $table = 'ibu_menyusuis';
    protected $primaryKey = 'id_ibu_menyusui';

    protected $fillable = [
        'id_posyandu',
        'nama_ibu',
        'nama_suami',
        'nama_bayi',
    ];

    public function posyandu()
    {
        return $this->belongsTo(Posyandu::class, 'id_posyandu');
    }

    public function kunjungan()
    {
        return $this->hasMany(KunjunganIbuMenyusui::class, 'id_ibu_menyusui');
    }
}
