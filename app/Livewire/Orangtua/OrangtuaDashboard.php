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

    protected function resolveNoKk(): ?string
    {
        $user = Auth::user();
        if ($user->email && str_ends_with($user->email, '@gmail.com')) {
            return str_replace('@gmail.com', '', $user->email);
        }

        return null;
    }

    public function render()
    {
        $noKk = $this->resolveNoKk();
        $orangtua = $noKk ? Orangtua::where('no_kk', $noKk)->first() : null;
        $allKeluarga = collect();

        if ($noKk) {
            $sasaranBayi = SasaranBayibalita::where('no_kk_sasaran', $noKk)->get();
            $sasaranRemaja = SasaranRemaja::where('no_kk_sasaran', $noKk)->get();
            $sasaranDewasa = SasaranDewasa::where('no_kk_sasaran', $noKk)->get();
            $sasaranPralansia = SasaranPralansia::where('no_kk_sasaran', $noKk)->get();
            $sasaranLansia = SasaranLansia::where('no_kk_sasaran', $noKk)->get();

            foreach ($sasaranBayi as $sasaran) {
                $allKeluarga->push([
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
                $allKeluarga->push([
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
                $allKeluarga->push([
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
                $allKeluarga->push([
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
                $allKeluarga->push([
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
            'allKeluarga' => $allKeluarga,
            'orangtua' => $orangtua,
        ]);
    }
}
