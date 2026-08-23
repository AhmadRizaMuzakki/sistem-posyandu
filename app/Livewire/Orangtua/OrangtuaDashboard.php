<?php

namespace App\Livewire\Orangtua;

use App\Livewire\Orangtua\Traits\ResolvesNoKk;
use App\Models\Aduan;
use App\Models\Orangtua;
use App\Models\SasaranBayibalita;
use App\Models\SasaranRemaja;
use App\Models\SasaranDewasa;
use App\Models\SasaranPralansia;
use App\Models\SasaranLansia;
use Livewire\Component;
use Livewire\Attributes\Layout;

class OrangtuaDashboard extends Component
{
    use ResolvesNoKk;

    #[Layout('layouts.orangtuadashboard')]

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

        $aduanStats = [
            'total' => 0,
            'menunggu' => 0,
            'diproses' => 0,
            'selesai' => 0,
            'ditolak' => 0,
        ];
        if ($noKk) {
            $aduanBase = Aduan::where('no_kk', $noKk);
            $aduanStats['total'] = (clone $aduanBase)->count();
            $aduanStats['menunggu'] = (clone $aduanBase)->where('status', 'menunggu')->count();
            $aduanStats['diproses'] = (clone $aduanBase)->where('status', 'diproses')->count();
            $aduanStats['selesai'] = (clone $aduanBase)->where('status', 'selesai')->count();
            $aduanStats['ditolak'] = (clone $aduanBase)->where('status', 'ditolak')->count();
        }

        return view('livewire.orangtua.orang-tua', [
            'allKeluarga' => $allKeluarga,
            'orangtua' => $orangtua,
            'aduanStats' => $aduanStats,
        ]);
    }
}
