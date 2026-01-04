<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Kader;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Posyandu;
use App\Models\User;
use App\Models\SasaranBayibalita;
use App\Models\Orangtua;
use App\Models\Imunisasi;
use App\Models\Pendidikan;
use Illuminate\Support\Facades\Schema;

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

        // Ambil data pendidikan gabungan untuk grafik pendidikan
        $pendidikanData = $this->getPendidikanData();

        // Ambil semua posyandu untuk dropdown
        $posyanduList = Posyandu::select('id_posyandu', 'nama_posyandu')->orderBy('nama_posyandu')->get();

        // Data untuk dropdown filter (untuk semua posyandu atau posyandu pertama sebagai contoh)
        $allKategoriSasaranList = Imunisasi::distinct()
            ->orderBy('kategori_sasaran')
            ->pluck('kategori_sasaran')
            ->toArray();

        $allJenisVaksinList = Imunisasi::distinct()
            ->orderBy('jenis_imunisasi')
            ->pluck('jenis_imunisasi')
            ->toArray();

        $allImunisasiList = Imunisasi::get();
        $allNamaSasaranList = collect();
        foreach ($allImunisasiList as $imunisasi) {
            $sasaran = $imunisasi->sasaran;
            if ($sasaran && $sasaran->nama_sasaran) {
                $allNamaSasaranList->push($sasaran->nama_sasaran);
            }
        }
        $allNamaSasaranList = $allNamaSasaranList->unique()->sort()->values()->toArray();

        $allKategoriPendidikanList = Pendidikan::distinct()
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

        return view('livewire.super-admin.super-admin', [
            'totalPosyandu' => $totalPosyandu,
            'totalKader' => $totalKader,
            'totalSasaran' => $totalSasaran,
            'posyanduData' => $posyanduData,
            'sasaranByCategory' => $sasaranByCategory,
            'pendidikanData' => $pendidikanData,
            'posyanduList' => $posyanduList,
            'kategoriSasaranList' => $allKategoriSasaranList,
            'kategoriLabels' => $kategoriLabels,
            'jenisVaksinList' => $allJenisVaksinList,
            'namaSasaranList' => $allNamaSasaranList,
            'kategoriPendidikanList' => $allKategoriPendidikanList,
        ]);
    }

    /**
     * Hitung total sasaran dari semua jenis
     */
    private function getTotalSasaran()
    {
        $bayibalita = SasaranBayibalita::count();
        $remaja = \App\Models\SasaranRemaja::count();
        $ibuhamil = \App\Models\SasaranIbuhamil::count();
        $dewasa = \App\Models\SasaranDewasa::count();
        $pralansia = \App\Models\SasaranPralansia::count();
        $lansia = \App\Models\SasaranLansia::count();

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
            'bayibalita' => SasaranBayibalita::count(),
            'remaja' => \App\Models\SasaranRemaja::count(),
            'ibuhamil' => \App\Models\SasaranIbuhamil::count(),
            'dewasa' => \App\Models\SasaranDewasa::count(),
            'pralansia' => \App\Models\SasaranPralansia::count(),
            'lansia' => \App\Models\SasaranLansia::count(),
        ];
    }

    /**
     * Ambil data pendidikan gabungan dari remaja, dewasa, ibu hamil, pralansia, dan lansia
     * Aman meskipun kolom "pendidikan" belum ada di salah satu tabel (tidak menyebabkan error SQL).
     */
    private function getPendidikanData(): array
    {
        $levels = [
            'Tidak/Belum Sekolah',
            'Tidak Tamat SD/Sederajat',
            'Tamat SD/Sederajat',
            'SLTP/Sederajat',
            'SLTA/Sederajat',
            'Diploma I/II',
            'Akademi/Diploma III/Sarjana Muda',
            'Diploma IV/Strata I',
            'Strata II',
            'Strata III',
        ];

        $counts = [];

        foreach ($levels as $level) {
            $counts[$level] = 0;

            // Remaja
            if (Schema::hasTable('sasaran_remajas') && Schema::hasColumn('sasaran_remajas', 'pendidikan')) {
                $counts[$level] += \App\Models\SasaranRemaja::where('pendidikan', $level)->count();
            }

            // Dewasa
            if (Schema::hasTable('sasaran_dewasas') && Schema::hasColumn('sasaran_dewasas', 'pendidikan')) {
                $counts[$level] += \App\Models\SasaranDewasa::where('pendidikan', $level)->count();
            }

            // Pralansia
            if (Schema::hasTable('sasaran_pralansias') && Schema::hasColumn('sasaran_pralansias', 'pendidikan')) {
                $counts[$level] += \App\Models\SasaranPralansia::where('pendidikan', $level)->count();
            }

            // Lansia
            if (Schema::hasTable('sasaran_lansias') && Schema::hasColumn('sasaran_lansias', 'pendidikan')) {
                $counts[$level] += \App\Models\SasaranLansia::where('pendidikan', $level)->count();
            }

            // Ibu Hamil (opsional, jika kolom sudah ada)
            if (Schema::hasTable('sasaran_ibuhamils') && Schema::hasColumn('sasaran_ibuhamils', 'pendidikan')) {
                $counts[$level] += \App\Models\SasaranIbuhamil::where('pendidikan', $level)->count();
            }
        }

        return [
            'labels' => array_keys($counts),
            'data' => array_values($counts),
        ];
    }
}
