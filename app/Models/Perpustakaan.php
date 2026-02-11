<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perpustakaan extends Model
{
    use HasFactory;

    protected $table = 'perpustakaan';

    protected $fillable = [
        'id_posyandu',
        'judul',
        'deskripsi',
        'penulis',
        'kategori',
        'cover_image',
        'file_path',
        'halaman_images',
        'jumlah_halaman',
        'is_active',
    ];

    protected $casts = [
        'halaman_images' => 'array',
        'is_active' => 'boolean',
    ];

    public function posyandu()
    {
        return $this->belongsTo(Posyandu::class, 'id_posyandu');
    }

    /**
     * Get cover image URL
     */
    public function getCoverUrlAttribute()
    {
        if ($this->cover_image) {
            return uploads_asset($this->cover_image);
        }
        return null;
    }

    /**
     * Get array of page image URLs
     */
    public function getPageUrlsAttribute()
    {
        if ($this->halaman_images && is_array($this->halaman_images)) {
            return array_map(function ($path) {
                return uploads_asset($path);
            }, $this->halaman_images);
        }
        return [];
    }
}
