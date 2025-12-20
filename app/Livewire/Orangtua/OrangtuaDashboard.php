<?php

namespace App\Livewire\Orangtua;

use App\Models\Orangtua;
use App\Models\SasaranBayibalita;
use App\Models\SasaranRemaja;
use App\Models\SasaranDewasa;
use App\Models\SasaranPralansia;
use App\Models\SasaranLansia;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

class OrangtuaDashboard extends Component
{
    #[Layout('layouts.orangtuadashboard')]
    public function render()
    {
        $user = Auth::user();

        // Cari orangtua berdasarkan nama user yang login
        $orangtua = Orangtua::where('nama', $user->name)->first();

        $allAnak = collect();

        if ($orangtua) {
            $noKkSasaran = $orangtua->no_kk_sasaran;

            // Ambil semua sasaran Bayi/Balita, Remaja, Dewasa, Pralansia, dan Lansia berdasarkan no_kk_sasaran
            $sasaranBayi = SasaranBayibalita::where('no_kk_sasaran', $noKkSasaran)->get();
            $sasaranRemaja = SasaranRemaja::where('no_kk_sasaran', $noKkSasaran)->get();
            $sasaranDewasa = SasaranDewasa::where('no_kk_sasaran', $noKkSasaran)->get();
            $sasaranPralansia = SasaranPralansia::where('no_kk_sasaran', $noKkSasaran)->get();
            $sasaranLansia = SasaranLansia::where('no_kk_sasaran', $noKkSasaran)->get();

            // Kumpulkan semua sasaran dengan informasi lengkap
            foreach ($sasaranBayi as $sasaran) {
                $allAnak->push([
                    'id' => $sasaran->id_sasaran_bayibalita,
                    'kategori' => 'Bayi/Balita',
                    'nama' => $sasaran->nama_sasaran,
                    'nik' => $sasaran->nik_sasaran,
                    'tanggal_lahir' => $sasaran->tanggal_lahir,
                    'jenis_kelamin' => $sasaran->jenis_kelamin,
                    'umur' => $sasaran->umur_sasaran,
                    'alamat' => $sasaran->alamat_sasaran,
                ]);
            }

            foreach ($sasaranRemaja as $sasaran) {
                $allAnak->push([
                    'id' => $sasaran->id_sasaran_remaja,
                    'kategori' => 'Remaja',
                    'nama' => $sasaran->nama_sasaran,
                    'nik' => $sasaran->nik_sasaran,
                    'tanggal_lahir' => $sasaran->tanggal_lahir,
                    'jenis_kelamin' => $sasaran->jenis_kelamin,
                    'umur' => $sasaran->umur_sasaran,
                    'alamat' => $sasaran->alamat_sasaran,
                ]);
            }

            foreach ($sasaranDewasa as $sasaran) {
                $allAnak->push([
                    'id' => $sasaran->id_sasaran_dewasa,
                    'kategori' => 'Dewasa',
                    'nama' => $sasaran->nama_sasaran,
                    'nik' => $sasaran->nik_sasaran,
                    'tanggal_lahir' => $sasaran->tanggal_lahir,
                    'jenis_kelamin' => $sasaran->jenis_kelamin,
                    'umur' => $sasaran->umur_sasaran,
                    'alamat' => $sasaran->alamat_sasaran,
                ]);
            }

            foreach ($sasaranPralansia as $sasaran) {
                $allAnak->push([
                    'id' => $sasaran->id_sasaran_pralansia,
                    'kategori' => 'Pralansia',
                    'nama' => $sasaran->nama_sasaran,
                    'nik' => $sasaran->nik_sasaran,
                    'tanggal_lahir' => $sasaran->tanggal_lahir,
                    'jenis_kelamin' => $sasaran->jenis_kelamin,
                    'umur' => $sasaran->umur_sasaran,
                    'alamat' => $sasaran->alamat_sasaran,
                ]);
            }

            foreach ($sasaranLansia as $sasaran) {
                $allAnak->push([
                    'id' => $sasaran->id_sasaran_lansia,
                    'kategori' => 'Lansia',
                    'nama' => $sasaran->nama_sasaran,
                    'nik' => $sasaran->nik_sasaran,
                    'tanggal_lahir' => $sasaran->tanggal_lahir,
                    'jenis_kelamin' => $sasaran->jenis_kelamin,
                    'umur' => $sasaran->umur_sasaran,
                    'alamat' => $sasaran->alamat_sasaran,
                ]);
            }
        }

        return view('livewire.orangtua.orang-tua', [
            'allAnak' => $allAnak,
        ]);
    }
}
