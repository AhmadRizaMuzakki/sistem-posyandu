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
     * Get sasaran counts by category
     */
    protected function getSasaranCountsByCategory($posyanduId)
    {
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
     * Get pendidikan data for chart
     */
    protected function getPendidikanData($posyanduId): array
    {
        $levels = $this->getPendidikanLevels();
        $counts = [];

        foreach ($levels as $level) {
            $counts[$level] = 0;

            // Remaja
            if (Schema::hasTable('sasaran_remajas') && Schema::hasColumn('sasaran_remajas', 'pendidikan')) {
                $counts[$level] += SasaranRemaja::where('id_posyandu', $posyanduId)
                    ->where('pendidikan', $level)->count();
            }

            // Dewasa
            if (Schema::hasTable('sasaran_dewasas') && Schema::hasColumn('sasaran_dewasas', 'pendidikan')) {
                $counts[$level] += SasaranDewasa::where('id_posyandu', $posyanduId)
                    ->where('pendidikan', $level)->count();
            }

            // Pralansia
            if (Schema::hasTable('sasaran_pralansias') && Schema::hasColumn('sasaran_pralansias', 'pendidikan')) {
                $counts[$level] += SasaranPralansia::where('id_posyandu', $posyanduId)
                    ->where('pendidikan', $level)->count();
            }

            // Lansia
            if (Schema::hasTable('sasaran_lansias') && Schema::hasColumn('sasaran_lansias', 'pendidikan')) {
                $counts[$level] += SasaranLansia::where('id_posyandu', $posyanduId)
                    ->where('pendidikan', $level)->count();
            }

            // Ibu Hamil (opsional, jika kolom sudah ada)
            if (Schema::hasTable('sasaran_ibuhamils') && Schema::hasColumn('sasaran_ibuhamils', 'pendidikan')) {
                $counts[$level] += SasaranIbuhamil::where('id_posyandu', $posyanduId)
                    ->where('pendidikan', $level)->count();
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

