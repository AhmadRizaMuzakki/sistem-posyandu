<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GambarPosyandu extends Model
{
    protected $table = 'gambar_posyandu';

    protected $fillable = [
        'id_posyandu',
        'path',
        'caption',
        'urutan',
    ];

    public function posyandu()
    {
        return $this->belongsTo(Posyandu::class, 'id_posyandu');
    }
}
