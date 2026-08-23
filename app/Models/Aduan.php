<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Aduan extends Model
{
    protected $table = 'aduans';

    protected $primaryKey = 'id_aduan';

    protected $fillable = [
        'no_kk',
        'id_posyandu',
        'judul',
        'isi_aduan',
        'kategori',
        'status',
        'tanggapan',
        'user_id',
        'tanggal_aduan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_aduan' => 'datetime',
        ];
    }

    public function posyandu(): BelongsTo
    {
        return $this->belongsTo(Posyandu::class, 'id_posyandu', 'id_posyandu');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
