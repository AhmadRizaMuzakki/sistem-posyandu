<?php

namespace App\Livewire\Posyandu;

use App\Livewire\Posyandu\Traits\PosyanduHelper;
use App\Livewire\Posyandu\Traits\DashboardHelper;
use App\Models\Kader;
use Livewire\Component;
use Livewire\Attributes\Layout;

class PosyanduDashboard extends Component
{
    use PosyanduHelper, DashboardHelper;

    #[Layout('layouts.posyandudashboard')]

    public function mount()
    {
        $this->initializePosyandu();
    }

    public function render()
    {
        $totalKader = Kader::where('id_posyandu', $this->posyanduId)->count();
        $totalSasaran = $this->getTotalSasaran($this->posyanduId);
        $sasaranByCategory = $this->getSasaranCountsByCategory($this->posyanduId);
        $pendidikanData = $this->getPendidikanData($this->posyanduId);

        return view('livewire.posyandu.admin-posyandu', [
            'posyandu' => $this->posyandu,
            'totalKader' => $totalKader,
            'totalSasaran' => $totalSasaran,
            'sasaranByCategory' => $sasaranByCategory,
            'pendidikanData' => $pendidikanData,
        ]);
    }
}
