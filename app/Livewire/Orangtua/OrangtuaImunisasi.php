<?php

namespace App\Livewire\Orangtua;

use App\Models\Imunisasi;
use App\Models\SasaranBayibalita;
use App\Models\SasaranRemaja;
use App\Models\SasaranDewasa;
use App\Models\SasaranPralansia;
use App\Models\SasaranLansia;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.orangtuadashboard')]
class OrangtuaImunisasi extends Component
{
    public $userId;

    public function mount()
    {
        $this->userId = Auth::id();
    }

    public function render()
    {
        // Ambil semua sasaran milik user dari semua kategori
        $sasaranBayi = SasaranBayibalita::where('id_users', $this->userId)->get();
        $sasaranRemaja = SasaranRemaja::where('id_users', $this->userId)->get();
        $sasaranDewasa = SasaranDewasa::where('id_users', $this->userId)->get();
        $sasaranPralansia = SasaranPralansia::where('id_users', $this->userId)->get();
        $sasaranLansia = SasaranLansia::where('id_users', $this->userId)->get();

        // Kumpulkan semua ID sasaran dengan kategorinya
        $allSasaran = collect();

        foreach ($sasaranBayi as $sasaran) {
            $allSasaran->push([
                'id' => $sasaran->id_sasaran_bayibalita,
                'kategori' => 'bayibalita',
                'nama' => $sasaran->nama_sasaran,
                'nik' => $sasaran->nik_sasaran,
                'tanggal_lahir' => $sasaran->tanggal_lahir,
            ]);
        }

        foreach ($sasaranRemaja as $sasaran) {
            $allSasaran->push([
                'id' => $sasaran->id_sasaran_remaja,
                'kategori' => 'remaja',
                'nama' => $sasaran->nama_sasaran,
                'nik' => $sasaran->nik_sasaran,
                'tanggal_lahir' => $sasaran->tanggal_lahir,
            ]);
        }

        foreach ($sasaranDewasa as $sasaran) {
            $allSasaran->push([
                'id' => $sasaran->id_sasaran_dewasa,
                'kategori' => 'dewasa',
                'nama' => $sasaran->nama_sasaran,
                'nik' => $sasaran->nik_sasaran,
                'tanggal_lahir' => $sasaran->tanggal_lahir,
            ]);
        }

        foreach ($sasaranPralansia as $sasaran) {
            $allSasaran->push([
                'id' => $sasaran->id_sasaran_pralansia,
                'kategori' => 'pralansia',
                'nama' => $sasaran->nama_sasaran,
                'nik' => $sasaran->nik_sasaran,
                'tanggal_lahir' => $sasaran->tanggal_lahir,
            ]);
        }

        foreach ($sasaranLansia as $sasaran) {
            $allSasaran->push([
                'id' => $sasaran->id_sasaran_lansia,
                'kategori' => 'lansia',
                'nama' => $sasaran->nama_sasaran,
                'nik' => $sasaran->nik_sasaran,
                'tanggal_lahir' => $sasaran->tanggal_lahir,
            ]);
        }

        // Ambil semua imunisasi untuk sasaran-sasaran tersebut
        $imunisasiList = collect();

        foreach ($allSasaran as $sasaran) {
            $imunisasi = Imunisasi::where('id_sasaran', $sasaran['id'])
                ->where('kategori_sasaran', $sasaran['kategori'])
                ->orderBy('tanggal_imunisasi', 'desc')
                ->get();

            if ($imunisasi->count() > 0) {
                $imunisasiList->push([
                    'sasaran' => $sasaran,
                    'imunisasi' => $imunisasi,
                ]);
            }
        }

        return view('livewire.orangtua.orangtua-imunisasi', [
            'imunisasiList' => $imunisasiList,
            'allSasaran' => $allSasaran,
        ]);
    }
}
