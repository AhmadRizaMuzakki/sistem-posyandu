<?php

namespace App\Livewire\Posyandu\Traits;

use App\Models\SasaranBayibalita;
use App\Models\SasaranRemaja;
use App\Models\SasaranDewasa;
use App\Models\SasaranPralansia;
use App\Models\SasaranLansia;
use App\Models\SasaranIbuhamil;
use Illuminate\Support\Facades\Schema;

trait DashboardHelper
{
    /**
     * Get total sasaran count for posyandu
     */
    protected function getTotalSasaran($posyanduId)
    {
        $counts = $this->getSasaranCountsByCategory($posyanduId);
        
        return array_sum($counts);
    }

    /**
     * Get sasaran counts by category - Optimasi dengan single query per table
     */
    protected function getSasaranCountsByCategory($posyanduId)
    {
        // Gunakan DB::raw untuk optimasi lebih lanjut jika diperlukan
        return [
            'bayibalita' => SasaranBayibalita::where('id_posyandu', $posyanduId)->count(),
            'remaja' => SasaranRemaja::where('id_posyandu', $posyanduId)->count(),
            'ibuhamil' => SasaranIbuhamil::where('id_posyandu', $posyanduId)->count(),
            'dewasa' => SasaranDewasa::where('id_posyandu', $posyanduId)->count(),
            'pralansia' => SasaranPralansia::where('id_posyandu', $posyanduId)->count(),
            'lansia' => SasaranLansia::where('id_posyandu', $posyanduId)->count(),
        ];
    }

    /**
     * Get pendidikan data for chart - Optimasi dengan batch query
     */
    protected function getPendidikanData($posyanduId): array
    {
        $levels = $this->getPendidikanLevels();
        $counts = array_fill_keys($levels, 0);

        // Optimasi: Ambil semua data pendidikan sekaligus, lalu group by
        $pendidikanData = collect();

        // Batch query untuk semua kategori sekaligus
        $pendidikanData = $pendidikanData->merge(
            SasaranRemaja::where('id_posyandu', $posyanduId)
                ->whereNotNull('pendidikan')
                ->select('pendidikan')
                ->get()
                ->pluck('pendidikan')
        );

        $pendidikanData = $pendidikanData->merge(
            SasaranDewasa::where('id_posyandu', $posyanduId)
                ->whereNotNull('pendidikan')
                ->select('pendidikan')
                ->get()
                ->pluck('pendidikan')
        );

        $pendidikanData = $pendidikanData->merge(
            SasaranPralansia::where('id_posyandu', $posyanduId)
                ->whereNotNull('pendidikan')
                ->select('pendidikan')
                ->get()
                ->pluck('pendidikan')
        );

        $pendidikanData = $pendidikanData->merge(
            SasaranLansia::where('id_posyandu', $posyanduId)
                ->whereNotNull('pendidikan')
                ->select('pendidikan')
                ->get()
                ->pluck('pendidikan')
        );

        $pendidikanData = $pendidikanData->merge(
            SasaranIbuhamil::where('id_posyandu', $posyanduId)
                ->whereNotNull('pendidikan')
                ->select('pendidikan')
                ->get()
                ->pluck('pendidikan')
        );

        // Count by level
        $grouped = $pendidikanData->countBy();
        foreach ($grouped as $level => $count) {
            if (isset($counts[$level])) {
                $counts[$level] = $count;
            }
        }

        return [
            'labels' => array_keys($counts),
            'data' => array_values($counts),
        ];
    }

    /**
     * Get pendidikan levels
     */
    protected function getPendidikanLevels(): array
    {
        return [
            'Tidak/Belum Sekolah',
            'Tidak Tamat SD/Sederajat',
            'Tamat SD/Sederajat',
            'SLTP/Sederajat',
            'SLTA/Sederajat',
            'Diploma I/II',
            'Akademi/Diploma III/Sarjana Muda',
            'Diploma IV/Strata I',
            'Strata II',
            'Strata III',
        ];
    }
}

