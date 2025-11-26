<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Kader;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Posyandu;
use App\Models\User;
use App\Models\Sasaran_Bayibalita;

class SuperAdminDashboard extends Component
{
    #[Layout('layouts.superadmindashboard')]
    public function render()
    {
        // Hitung jumlah posyandu
        $totalPosyandu = Posyandu::count();

        // Hitung jumlah kader (role = 'kader')
        $totalKader = Kader::count();

        // Hitung jumlah sa saran (asumsi ada model Sasaran, ganti dengan nama model/table sesuai DB Anda)
        $totalSasaran = Sasaran_Bayibalita::count();

        return view('livewire.super-admin.super-admin', [
            'totalPosyandu' => $totalPosyandu,
            'totalKader' => $totalKader,
            'totalSasaran' => $totalSasaran,
        ]);
    }
}
