<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orangtua extends Model
{
    use HasFactory;

    protected $table = 'orangtua';
    protected $primaryKey = 'nik';
    public $incrementing = false;
    protected $keyType = 'integer';

    protected $fillable = [
        'nik',
        'nama',
        'tempat_lahir',
        'tanggal_lahir',
        'pekerjaan',
        'kelamin',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }

    /**
     * Scope untuk filter berdasarkan umur minimum
     */
    public function scopeByMinAge($query, $minAge)
    {
        $maxDate = now()->subYears($minAge);
        return $query->where('tanggal_lahir', '<=', $maxDate);
    }

    /**
     * Scope untuk filter berdasarkan umur maksimum
     */
    public function scopeByMaxAge($query, $maxAge)
    {
        $minDate = now()->subYears($maxAge + 1);
        return $query->where('tanggal_lahir', '>', $minDate);
    }

    /**
     * Scope untuk filter berdasarkan range umur
     */
    public function scopeByAgeRange($query, $minAge, $maxAge = null)
    {
        $maxDate = now()->subYears($minAge);
        $query->where('tanggal_lahir', '<=', $maxDate);

        if ($maxAge !== null) {
            $minDate = now()->subYears($maxAge + 1);
            $query->where('tanggal_lahir', '>', $minDate);
        }

        return $query;
    }

    /**
     * Accessor untuk menghitung umur
     */
    public function getUmurAttribute()
    {
        if (!$this->tanggal_lahir) {
            return null;
        }
        return $this->tanggal_lahir->age;
    }
}
