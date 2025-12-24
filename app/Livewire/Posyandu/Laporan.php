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
        ]);
    }
}


