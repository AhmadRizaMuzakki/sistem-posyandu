<?php

namespace App\Livewire\Orangtua;

use App\Models\Imunisasi;
use App\Models\Sasaran_Bayibalita;
use App\Models\sasaran_remaja;
use App\Models\sasaran_dewasa;
use App\Models\sasaran_pralansia;
use App\Models\sasaran_lansia;
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
        $sasaranBayi = Sasaran_Bayibalita::where('id_users', $this->userId)->get();
        $sasaranRemaja = sasaran_remaja::where('id_users', $this->userId)->get();
        $sasaranDewasa = sasaran_dewasa::where('id_users', $this->userId)->get();
        $sasaranPralansia = sasaran_pralansia::where('id_users', $this->userId)->get();
        $sasaranLansia = sasaran_lansia::where('id_users', $this->userId)->get();

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
