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

        // Ambil data sasaran per kategori untuk bar chart
        $sasaranByCategory = $this->getSasaranByCategory();

        return view('livewire.super-admin.super-admin', [
            'totalPosyandu' => $totalPosyandu,
            'totalKader' => $totalKader,
            'totalSasaran' => $totalSasaran,
            'posyanduData' => $posyanduData,
            'sasaranByCategory' => $sasaranByCategory,
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
        $dewasa = \App\Models\sasaran_dewasa::count();
        $pralansia = \App\Models\sasaran_pralansia::count();
        $lansia = \App\Models\sasaran_lansia::count();

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
            'sasaran_dewasa',
            'sasaran_ibuhamil',
            'sasaran_pralansia',
            'sasaran_lansia'
        ])->get();

        $data = [];
        foreach ($posyanduList as $posyandu) {
            // Hitung sasaran yang punya relasi langsung dengan posyandu
            $totalSasaran =
                $posyandu->sasaran_bayibalita->count() +
                $posyandu->sasaran_remaja->count() +
                $posyandu->sasaran_dewasa->count() +
                $posyandu->sasaran_ibuhamil->count() +
                $posyandu->sasaran_pralansia->count() +
                $posyandu->sasaran_lansia->count();

            $data[] = [
                'nama' => $posyandu->nama_posyandu,
                'jumlah' => $totalSasaran
            ];
        }

        return $data;
    }

    /**
     * Ambil data sasaran per kategori untuk bar chart
     */
    private function getSasaranByCategory()
    {
        return [
            'bayibalita' => Sasaran_Bayibalita::count(),
            'remaja' => \App\Models\sasaran_remaja::count(),
            'ibuhamil' => \App\Models\sasaran_ibuhamil::count(),
            'dewasa' => \App\Models\sasaran_dewasa::count(),
            'pralansia' => \App\Models\sasaran_pralansia::count(),
            'lansia' => \App\Models\sasaran_lansia::count(),
        ];
    }
}
