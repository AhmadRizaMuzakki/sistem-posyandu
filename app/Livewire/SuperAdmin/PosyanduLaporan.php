<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Imunisasi;
use App\Models\Pendidikan;
use App\Models\Posyandu;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PosyanduLaporan extends Component
{
    public $posyandu;
    public $posyanduId;

    #[Layout('layouts.superadmindashboard')]
    public function mount($id): void
    {
        try {
            $decryptedId = decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID tidak valid');
        }

        $this->posyanduId = $decryptedId;
        $this->loadPosyandu();
    }

    private function loadPosyandu(): void
    {
        $posyandu = Posyandu::find($this->posyanduId);

        if (! $posyandu) {
            abort(404, 'Posyandu tidak ditemukan');
        }

        $this->posyandu = $posyandu;
    }

    public function render()
    {
        $daftarPosyandu = Posyandu::select('id_posyandu', 'nama_posyandu')->get();

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

        // Ambil daftar kategori pendidikan yang unik dari database
        $kategoriPendidikanList = Pendidikan::where('id_posyandu', $this->posyandu->id_posyandu)
            ->distinct()
            ->orderBy('pendidikan_terakhir')
            ->pluck('pendidikan_terakhir')
            ->filter()
            ->toArray();

        // Ambil daftar kategori sasaran dari pendidikan
        $kategoriSasaranPendidikanList = Pendidikan::where('id_posyandu', $this->posyandu->id_posyandu)
            ->distinct()
            ->orderBy('kategori_sasaran')
            ->pluck('kategori_sasaran')
            ->filter()
            ->toArray();

        // Ambil daftar nama sasaran dari pendidikan
        $namaSasaranPendidikanList = Pendidikan::where('id_posyandu', $this->posyandu->id_posyandu)
            ->distinct()
            ->orderBy('nama')
            ->pluck('nama')
            ->filter()
            ->toArray();

        return view('livewire.super-admin.posyandu-laporan', [
            'title' => 'Laporan - '.$this->posyandu->nama_posyandu,
            'daftarPosyandu' => $daftarPosyandu,
            'dataPosyandu' => $daftarPosyandu,
            'posyandu' => $this->posyandu,
            'kategoriSasaranList' => $kategoriSasaranList,
            'kategoriLabels' => $kategoriLabels,
            'jenisVaksinList' => $jenisVaksinList,
            'namaSasaranList' => $namaSasaranList,
            'kategoriPendidikanList' => $kategoriPendidikanList,
            'kategoriSasaranPendidikanList' => $kategoriSasaranPendidikanList,
            'namaSasaranPendidikanList' => $namaSasaranPendidikanList,
        ]);
    }
}


