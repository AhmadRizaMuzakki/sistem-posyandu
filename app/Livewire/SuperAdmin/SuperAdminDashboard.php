<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Kader;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Posyandu;
use App\Models\User;
use App\Models\Sasaran_Bayibalita;
use App\Models\Orangtua;

class SuperAdminDashboard extends Component
{
    #[Layout('layouts.superadmindashboard')]
    public function render()
    {
        // Hitung jumlah posyandu
        $totalPosyandu = Posyandu::count();

        // Hitung jumlah kader (role = 'kader')
        $totalKader = Kader::count();

        // Hitung total sasaran dari semua jenis
        $totalSasaran = $this->getTotalSasaran();

        // Ambil data posyandu dengan jumlah sasaran untuk grafik
        $posyanduData = $this->getPosyanduSasaranData();

        return view('livewire.super-admin.super-admin', [
            'totalPosyandu' => $totalPosyandu,
            'totalKader' => $totalKader,
            'totalSasaran' => $totalSasaran,
            'posyanduData' => $posyanduData,
        ]);
    }

    /**
     * Hitung total sasaran dari semua jenis
     */
    private function getTotalSasaran()
    {
        $bayibalita = Sasaran_Bayibalita::count();
        $remaja = \App\Models\sasaran_remaja::count();
        $ibuhamil = \App\Models\sasaran_ibuhamil::count();
        $dewasa = Orangtua::byAgeRange(20, 40)->count();
        $pralansia = Orangtua::byAgeRange(40, 60)->count();
        $lansia = Orangtua::byMinAge(60)->count();

        return $bayibalita + $remaja + $ibuhamil + $dewasa + $pralansia + $lansia;
    }

    /**
     * Ambil data posyandu dengan jumlah sasaran untuk grafik
     */
    private function getPosyanduSasaranData()
    {
        $posyanduList = Posyandu::with([
            'sasaran_bayibalita',
            'sasaran_remaja',
            'sasaran_ibuhamil'
        ])->get();

        $data = [];
        foreach ($posyanduList as $posyandu) {
            // Hitung sasaran yang punya relasi langsung dengan posyandu
            $totalSasaran =
                $posyandu->sasaran_bayibalita->count() +
                $posyandu->sasaran_remaja->count() +
                $posyandu->sasaran_ibuhamil->count();

            $data[] = [
                'nama' => $posyandu->nama_posyandu,
                'jumlah' => $totalSasaran
            ];
        }

        return $data;
    }
}
