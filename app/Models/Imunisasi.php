<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Imunisasi extends Model
{
    protected $table = 'imunisasi';
    protected $primaryKey = 'id_imunisasi';

    protected $fillable = [
        'id_posyandu',
        'id_users',
        'id_petugas_kesehatan',
        'id_sasaran',
        'kategori_sasaran',
        'jenis_imunisasi',
        'tanggal_imunisasi',
        'tinggi_badan',
        'berat_badan',
        'sistol',
        'diastol',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_imunisasi' => 'date',
        'tinggi_badan' => 'decimal:2',
        'berat_badan' => 'decimal:2',
        'sistol' => 'integer',
        'diastol' => 'integer',
    ];

    // Cache untuk menyimpan sasaran yang sudah di-load
    protected static array $sasaranCache = [];

    // Relasi ke Posyandu
    public function posyandu()
    {
        return $this->belongsTo(Posyandu::class, 'id_posyandu');
    }

    // Relasi ke User (yang menginput)
    public function user()
    {
        return $this->belongsTo(User::class, 'id_users');
    }

    // Relasi ke Petugas Kesehatan
    public function petugasKesehatan()
    {
        return $this->belongsTo(PetugasKesehatan::class, 'id_petugas_kesehatan');
    }

    /**
     * Preload sasaran untuk koleksi Imunisasi (mencegah N+1 query)
     * Panggil method ini sebelum mengakses $imunisasi->sasaran dalam loop
     */
    public static function preloadSasaran(Collection $imunisasiList): void
    {
        // Group by kategori_sasaran
        $grouped = $imunisasiList->groupBy('kategori_sasaran');

        $kategoris = [
            'bayibalita' => [SasaranBayibalita::class, 'id_sasaran_bayibalita'],
            'remaja' => [SasaranRemaja::class, 'id_sasaran_remaja'],
            'dewasa' => [SasaranDewasa::class, 'id_sasaran_dewasa'],
            'pralansia' => [SasaranPralansia::class, 'id_sasaran_pralansia'],
            'lansia' => [SasaranLansia::class, 'id_sasaran_lansia'],
        ];

        foreach ($kategoris as $kategori => [$modelClass, $primaryKey]) {
            if (!isset($grouped[$kategori])) continue;

            $ids = $grouped[$kategori]->pluck('id_sasaran')->unique()->filter()->values()->toArray();
            if (empty($ids)) continue;

            $sasaranList = $modelClass::whereIn($primaryKey, $ids)->get();

            foreach ($sasaranList as $sasaran) {
                $cacheKey = $kategori . '_' . $sasaran->$primaryKey;
                self::$sasaranCache[$cacheKey] = $sasaran;
            }
        }
    }

    /**
     * Clear sasaran cache
     */
    public static function clearSasaranCache(): void
    {
        self::$sasaranCache = [];
    }

    /**
     * Get sasaran dengan cache support
     */
    public function getSasaranAttribute()
    {
        if (!$this->kategori_sasaran || !$this->id_sasaran) {
            return null;
        }

        // Cek cache dulu
        $cacheKey = $this->kategori_sasaran . '_' . $this->id_sasaran;
        if (isset(self::$sasaranCache[$cacheKey])) {
            return self::$sasaranCache[$cacheKey];
        }

        // Fallback ke query langsung (untuk kasus single record)
        $modelClass = match($this->kategori_sasaran) {
            'bayibalita' => SasaranBayibalita::class,
            'remaja' => SasaranRemaja::class,
            'dewasa' => SasaranDewasa::class,
            'pralansia' => SasaranPralansia::class,
            'lansia' => SasaranLansia::class,
            default => null,
        };

        if ($modelClass) {
            $primaryKey = match($this->kategori_sasaran) {
                'bayibalita' => 'id_sasaran_bayibalita',
                'remaja' => 'id_sasaran_remaja',
                'dewasa' => 'id_sasaran_dewasa',
                'pralansia' => 'id_sasaran_pralansia',
                'lansia' => 'id_sasaran_lansia',
                default => 'id',
            };

            $sasaran = $modelClass::where($primaryKey, $this->id_sasaran)->first();

            // Simpan ke cache
            if ($sasaran) {
                self::$sasaranCache[$cacheKey] = $sasaran;
            }

            return $sasaran;
        }

        return null;
    }
}
