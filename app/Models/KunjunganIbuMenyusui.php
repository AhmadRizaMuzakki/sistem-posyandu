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
        'id_petugas_penanggung_jawab',
        'id_petugas_imunisasi',
        'id_petugas_input',
    ];

    protected $casts = [
        'tanggal_kunjungan' => 'date',
    ];

    public function ibuMenyusui()
    {
        return $this->belongsTo(IbuMenyusui::class, 'id_ibu_menyusui');
    }

    public function petugasPenanggungJawab()
    {
        return $this->belongsTo(PetugasKesehatan::class, 'id_petugas_penanggung_jawab');
    }

    public function petugasImunisasi()
    {
        return $this->belongsTo(PetugasKesehatan::class, 'id_petugas_imunisasi');
    }

    public function petugasInput()
    {
        return $this->belongsTo(PetugasKesehatan::class, 'id_petugas_input');
    }
}
