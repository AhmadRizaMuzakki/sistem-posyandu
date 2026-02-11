<?php

namespace App\Livewire\SuperAdmin\Traits;

use App\Models\SasaranBayibalita;
use App\Models\SasaranRemaja;
use App\Models\SasaranDewasa;
use App\Models\SasaranPralansia;
use App\Models\SasaranLansia;

/**
 * Trait untuk menghitung jumlah anggota keluarga secara efisien
 * Mencegah N+1 query dengan preloading counts per no_kk_sasaran
 */
trait SasaranCountHelper
{
    /**
     * Preload semua counts per no_kk_sasaran dalam satu query per tabel
     * Mengembalikan array dengan struktur: ['no_kk' => count]
     */
    protected function preloadSasaranCounts(int $posyanduId): array
    {
        // Batch query untuk setiap kategori sasaran
        $balitaCounts = SasaranBayibalita::where('id_posyandu', $posyanduId)
            ->whereNotNull('no_kk_sasaran')
            ->selectRaw('no_kk_sasaran, COUNT(*) as cnt')
            ->groupBy('no_kk_sasaran')
            ->pluck('cnt', 'no_kk_sasaran')
            ->toArray();

        $remajaCounts = SasaranRemaja::where('id_posyandu', $posyanduId)
            ->whereNotNull('no_kk_sasaran')
            ->selectRaw('no_kk_sasaran, COUNT(*) as cnt')
            ->groupBy('no_kk_sasaran')
            ->pluck('cnt', 'no_kk_sasaran')
            ->toArray();

        $dewasaCounts = SasaranDewasa::where('id_posyandu', $posyanduId)
            ->whereNotNull('no_kk_sasaran')
            ->selectRaw('no_kk_sasaran, COUNT(*) as cnt')
            ->groupBy('no_kk_sasaran')
            ->pluck('cnt', 'no_kk_sasaran')
            ->toArray();

        $pralansiaCounts = SasaranPralansia::where('id_posyandu', $posyanduId)
            ->whereNotNull('no_kk_sasaran')
            ->selectRaw('no_kk_sasaran, COUNT(*) as cnt')
            ->groupBy('no_kk_sasaran')
            ->pluck('cnt', 'no_kk_sasaran')
            ->toArray();

        $lansiaCounts = SasaranLansia::where('id_posyandu', $posyanduId)
            ->whereNotNull('no_kk_sasaran')
            ->selectRaw('no_kk_sasaran, COUNT(*) as cnt')
            ->groupBy('no_kk_sasaran')
            ->pluck('cnt', 'no_kk_sasaran')
            ->toArray();

        return [
            'balita' => $balitaCounts,
            'remaja' => $remajaCounts,
            'dewasa' => $dewasaCounts,
            'pralansia' => $pralansiaCounts,
            'lansia' => $lansiaCounts,
        ];
    }

    /**
     * Hitung total anggota keluarga berdasarkan no_kk menggunakan preloaded counts
     */
    protected function getTotalAnggotaFromCounts(string $noKk, array $counts): int
    {
        return ($counts['balita'][$noKk] ?? 0) +
               ($counts['remaja'][$noKk] ?? 0) +
               ($counts['dewasa'][$noKk] ?? 0) +
               ($counts['pralansia'][$noKk] ?? 0) +
               ($counts['lansia'][$noKk] ?? 0);
    }
}
