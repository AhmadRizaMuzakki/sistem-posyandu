<?php

namespace App\Livewire\Posyandu;

use App\Models\Kader;
use App\Models\Posyandu;
use App\Models\SasaranBayibalita;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\Attributes\Layout;

class PosyanduDashboard extends Component
{
    public $posyandu;
    public $posyanduId;

    #[Layout('layouts.posyandudashboard')]
    
    public function mount()
    {
        // Ambil posyandu dari kader yang login
        $user = Auth::user();
        $kader = Kader::where('id_users', $user->id)->first();

        if (!$kader) {
            abort(403, 'Anda bukan kader terdaftar.');
        }

        $this->posyanduId = $kader->id_posyandu;
        $this->loadPosyandu();
    }

    /**
     * Load data posyandu
     */
    private function loadPosyandu()
    {
        $posyandu = Posyandu::find($this->posyanduId);

        if (!$posyandu) {
            abort(404, 'Posyandu tidak ditemukan');
        }

        $this->posyandu = $posyandu;
    }

    public function render()
    {
        // Hitung jumlah kader di posyandu ini
        $totalKader = Kader::where('id_posyandu', $this->posyanduId)->count();

        // Hitung total sasaran dari semua jenis untuk posyandu ini
        $totalSasaran = $this->getTotalSasaran();

        // Ambil data sasaran per kategori untuk bar chart
        $sasaranByCategory = $this->getSasaranByCategory();

        // Ambil data pendidikan gabungan untuk grafik pendidikan
        $pendidikanData = $this->getPendidikanData();

        return view('livewire.posyandu.admin-posyandu', [
            'posyandu' => $this->posyandu,
            'totalKader' => $totalKader,
            'totalSasaran' => $totalSasaran,
            'sasaranByCategory' => $sasaranByCategory,
            'pendidikanData' => $pendidikanData,
        ]);
    }

    /**
     * Hitung total sasaran dari semua jenis untuk posyandu ini
     */
    private function getTotalSasaran()
    {
        $bayibalita = SasaranBayibalita::where('id_posyandu', $this->posyanduId)->count();
        $remaja = \App\Models\SasaranRemaja::where('id_posyandu', $this->posyanduId)->count();
        $ibuhamil = \App\Models\SasaranIbuhamil::where('id_posyandu', $this->posyanduId)->count();
        $dewasa = \App\Models\SasaranDewasa::where('id_posyandu', $this->posyanduId)->count();
        $pralansia = \App\Models\SasaranPralansia::where('id_posyandu', $this->posyanduId)->count();
        $lansia = \App\Models\SasaranLansia::where('id_posyandu', $this->posyanduId)->count();

        return $bayibalita + $remaja + $ibuhamil + $dewasa + $pralansia + $lansia;
    }

    /**
     * Ambil data sasaran per kategori untuk bar chart
     */
    private function getSasaranByCategory()
    {
        return [
            'bayibalita' => SasaranBayibalita::where('id_posyandu', $this->posyanduId)->count(),
            'remaja' => \App\Models\SasaranRemaja::where('id_posyandu', $this->posyanduId)->count(),
            'ibuhamil' => \App\Models\SasaranIbuhamil::where('id_posyandu', $this->posyanduId)->count(),
            'dewasa' => \App\Models\SasaranDewasa::where('id_posyandu', $this->posyanduId)->count(),
            'pralansia' => \App\Models\SasaranPralansia::where('id_posyandu', $this->posyanduId)->count(),
            'lansia' => \App\Models\SasaranLansia::where('id_posyandu', $this->posyanduId)->count(),
        ];
    }

    /**
     * Ambil data pendidikan gabungan dari remaja, dewasa, ibu hamil, pralansia, dan lansia
     * Hanya untuk posyandu ini
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
                $counts[$level] += \App\Models\SasaranRemaja::where('id_posyandu', $this->posyanduId)
                    ->where('pendidikan', $level)->count();
            }

            // Dewasa
            if (Schema::hasTable('sasaran_dewasas') && Schema::hasColumn('sasaran_dewasas', 'pendidikan')) {
                $counts[$level] += \App\Models\SasaranDewasa::where('id_posyandu', $this->posyanduId)
                    ->where('pendidikan', $level)->count();
            }

            // Pralansia
            if (Schema::hasTable('sasaran_pralansias') && Schema::hasColumn('sasaran_pralansias', 'pendidikan')) {
                $counts[$level] += \App\Models\SasaranPralansia::where('id_posyandu', $this->posyanduId)
                    ->where('pendidikan', $level)->count();
            }

            // Lansia
            if (Schema::hasTable('sasaran_lansias') && Schema::hasColumn('sasaran_lansias', 'pendidikan')) {
                $counts[$level] += \App\Models\SasaranLansia::where('id_posyandu', $this->posyanduId)
                    ->where('pendidikan', $level)->count();
            }

            // Ibu Hamil (opsional, jika kolom sudah ada)
            if (Schema::hasTable('sasaran_ibuhamils') && Schema::hasColumn('sasaran_ibuhamils', 'pendidikan')) {
                $counts[$level] += \App\Models\SasaranIbuhamil::where('id_posyandu', $this->posyanduId)
                    ->where('pendidikan', $level)->count();
            }
        }

        return [
            'labels' => array_keys($counts),
            'data' => array_values($counts),
        ];
    }
}
