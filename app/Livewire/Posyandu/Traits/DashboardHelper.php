<?php

namespace App\Livewire\Posyandu\Traits;

use App\Models\SasaranBayibalita;
use App\Models\SasaranRemaja;
use App\Models\SasaranDewasa;
use App\Models\SasaranPralansia;
use App\Models\SasaranLansia;
use App\Models\SasaranIbuhamil;
use App\Models\Pendidikan;
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
     * Get pendidikan data for chart - Menggunakan data dari tabel pendidikan
     */
    protected function getPendidikanData($posyanduId): array
    {
        $levels = $this->getPendidikanLevels();
        $counts = array_fill_keys($levels, 0);

        // Ambil data dari tabel pendidikan
        $pendidikanData = Pendidikan::where('id_posyandu', $posyanduId)
            ->whereNotNull('pendidikan_terakhir')
            ->selectRaw('pendidikan_terakhir, COUNT(*) as jumlah')
            ->groupBy('pendidikan_terakhir')
            ->orderByRaw('
                CASE 
                    WHEN pendidikan_terakhir = "Tidak/Belum Sekolah" THEN 1
                    WHEN pendidikan_terakhir = "PAUD" THEN 2
                    WHEN pendidikan_terakhir = "TK" THEN 3
                    WHEN pendidikan_terakhir = "Tidak Tamat SD/Sederajat" THEN 4
                    WHEN pendidikan_terakhir = "Tamat SD/Sederajat" THEN 5
                    WHEN pendidikan_terakhir = "SLTP/Sederajat" THEN 6
                    WHEN pendidikan_terakhir = "SLTA/Sederajat" THEN 7
                    WHEN pendidikan_terakhir = "Diploma I/II" THEN 8
                    WHEN pendidikan_terakhir = "Akademi/Diploma III/Sarjana Muda" THEN 9
                    WHEN pendidikan_terakhir = "Diploma IV/Strata I" THEN 10
                    WHEN pendidikan_terakhir = "Strata II" THEN 11
                    WHEN pendidikan_terakhir = "Strata III" THEN 12
                    ELSE 13
                END
            ')
            ->get();

        // Map hasil ke array counts
        foreach ($pendidikanData as $item) {
            if (isset($counts[$item->pendidikan_terakhir])) {
                $counts[$item->pendidikan_terakhir] = $item->jumlah;
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
            'PAUD',
            'TK',
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

