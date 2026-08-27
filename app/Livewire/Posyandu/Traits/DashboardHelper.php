<?php

namespace App\Livewire\Posyandu\Traits;

use App\Services\PendidikanChartService;

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
        return [
            'bayibalita' => \App\Models\SasaranBayibalita::where('id_posyandu', $posyanduId)->count(),
            'remaja' => \App\Models\SasaranRemaja::where('id_posyandu', $posyanduId)->count(),
            'ibuhamil' => \App\Models\SasaranIbuhamil::where('id_posyandu', $posyanduId)->count(),
            'dewasa' => \App\Models\SasaranDewasa::where('id_posyandu', $posyanduId)->count(),
            'pralansia' => \App\Models\SasaranPralansia::where('id_posyandu', $posyanduId)->count(),
            'lansia' => \App\Models\SasaranLansia::where('id_posyandu', $posyanduId)->count(),
        ];
    }

    /**
     * Get pendidikan data for chart — dari kolom pendidikan sasaran (selaras dengan superadmin).
     */
    protected function getPendidikanData($posyanduId): array
    {
        return PendidikanChartService::getChartData((int) $posyanduId);
    }

    /**
     * Get pendidikan levels
     */
    protected function getPendidikanLevels(): array
    {
        return PendidikanChartService::levels();
    }
}
