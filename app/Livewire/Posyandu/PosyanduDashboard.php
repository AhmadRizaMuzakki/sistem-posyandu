<?php

namespace App\Livewire\Posyandu;

use App\Livewire\Posyandu\Traits\PosyanduHelper;
use App\Livewire\Posyandu\Traits\DashboardHelper;
use App\Models\Kader;
use App\Models\Imunisasi;
use App\Models\Pendidikan;
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

        // Data untuk dropdown filter imunisasi
        $kategoriSasaranList = Imunisasi::where('id_posyandu', $this->posyanduId)
            ->distinct()
            ->orderBy('kategori_sasaran')
            ->pluck('kategori_sasaran')
            ->toArray();

        $jenisVaksinList = Imunisasi::where('id_posyandu', $this->posyanduId)
            ->distinct()
            ->orderBy('jenis_imunisasi')
            ->pluck('jenis_imunisasi')
            ->toArray();

        $imunisasiList = Imunisasi::where('id_posyandu', $this->posyanduId)->get();
        $namaSasaranList = collect();
        foreach ($imunisasiList as $imunisasi) {
            $sasaran = $imunisasi->sasaran;
            if ($sasaran && $sasaran->nama_sasaran) {
                $namaSasaranList->push($sasaran->nama_sasaran);
            }
        }
        $namaSasaranList = $namaSasaranList->unique()->sort()->values()->toArray();

        // Data untuk dropdown filter pendidikan
        $kategoriPendidikanList = Pendidikan::where('id_posyandu', $this->posyanduId)
            ->distinct()
            ->orderBy('pendidikan_terakhir')
            ->pluck('pendidikan_terakhir')
            ->toArray();

        // Mapping label kategori
        $kategoriLabels = [
            'bayibalita' => 'Bayi dan Balita',
            'remaja' => 'Remaja',
            'dewasa' => 'Dewasa',
            'pralansia' => 'Pralansia',
            'lansia' => 'Lansia',
            'ibuhamil' => 'Ibu Hamil',
        ];

        return view('livewire.posyandu.admin-posyandu', [
            'posyandu' => $this->posyandu,
            'totalKader' => $totalKader,
            'totalSasaran' => $totalSasaran,
            'sasaranByCategory' => $sasaranByCategory,
            'pendidikanData' => $pendidikanData,
            'kategoriSasaranList' => $kategoriSasaranList,
            'kategoriLabels' => $kategoriLabels,
            'jenisVaksinList' => $jenisVaksinList,
            'namaSasaranList' => $namaSasaranList,
            'kategoriPendidikanList' => $kategoriPendidikanList,
        ]);
    }
}
