<?php

namespace App\Livewire\Posyandu;

use App\Models\Imunisasi;
use App\Models\Kader;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Laporan extends Component
{
    public $posyandu;

    #[Layout('layouts.posyandudashboard')]
    public function mount(): void
    {
        $user = Auth::user();

        $kader = Kader::with('posyandu')
            ->where('id_users', $user->id)
            ->first();

        if (! $kader || ! $kader->posyandu) {
            abort(403, 'Posyandu untuk akun ini tidak ditemukan.');
        }

        $this->posyandu = $kader->posyandu;
    }

    public function render()
    {
        // Ambil daftar kategori sasaran yang unik dari database
        $kategoriSasaranList = Imunisasi::where('id_posyandu', $this->posyandu->id_posyandu)
            ->distinct()
            ->orderBy('kategori_sasaran')
            ->pluck('kategori_sasaran')
            ->toArray();

        // Ambil daftar jenis vaksin yang unik dari database
        $jenisVaksinList = Imunisasi::where('id_posyandu', $this->posyandu->id_posyandu)
            ->distinct()
            ->orderBy('jenis_imunisasi')
            ->pluck('jenis_imunisasi')
            ->toArray();

        // Ambil daftar nama sasaran yang unik dari database
        $imunisasiList = Imunisasi::where('id_posyandu', $this->posyandu->id_posyandu)->get();
        $namaSasaranList = collect();
        foreach ($imunisasiList as $imunisasi) {
            $sasaran = $imunisasi->sasaran;
            if ($sasaran && $sasaran->nama_sasaran) {
                $namaSasaranList->push($sasaran->nama_sasaran);
            }
        }
        $namaSasaranList = $namaSasaranList->unique()->sort()->values()->toArray();

        // Mapping label kategori
        $kategoriLabels = [
            'bayibalita' => 'Bayi dan Balita',
            'remaja' => 'Remaja',
            'dewasa' => 'Dewasa',
            'pralansia' => 'Pralansia',
            'lansia' => 'Lansia',
            'ibuhamil' => 'Ibu Hamil',
        ];

        return view('livewire.posyandu.laporan', [
            'title' => 'Laporan - '.$this->posyandu->nama_posyandu,
            'posyandu' => $this->posyandu,
            'kategoriSasaranList' => $kategoriSasaranList,
            'kategoriLabels' => $kategoriLabels,
            'jenisVaksinList' => $jenisVaksinList,
            'namaSasaranList' => $namaSasaranList,
        ]);
    }
}


