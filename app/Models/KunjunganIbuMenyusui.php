<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KunjunganIbuMenyusui extends Model
{
    protected $table = 'kunjungan_ibu_menyusui';
    protected $primaryKey = 'id_kunjungan';

    protected $fillable = [
        'id_ibu_menyusui',
        'bulan',
        'tahun',
        'status',
        'tanggal_kunjungan',
    ];

    protected $casts = [
        'tanggal_kunjungan' => 'date',
    ];

    public function ibuMenyusui()
    {
        return $this->belongsTo(IbuMenyusui::class, 'id_ibu_menyusui');
    }
}
