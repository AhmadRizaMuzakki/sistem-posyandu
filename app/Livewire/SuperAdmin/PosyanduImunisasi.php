<?php

namespace App\Livewire\SuperAdmin;

use App\Livewire\SuperAdmin\Traits\ImunisasiCrud;
use App\Models\Imunisasi;
use App\Models\Posyandu;
use App\Models\SasaranBayibalita;
use App\Models\SasaranRemaja;
use App\Models\SasaranDewasa;
use App\Models\SasaranPralansia;
use App\Models\SasaranLansia;
use Livewire\Component;
use Livewire\Attributes\Layout;

class PosyanduImunisasi extends Component
{
    use ImunisasiCrud;

    public $posyandu;
    public $posyanduId;
    public $search = '';

    #[Layout('layouts.superadmindashboard')]

    public function mount($id)
    {
        try {
            $decryptedId = decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID tidak valid');
        }

        $this->posyanduId = $decryptedId;
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

    /**
     * Refresh data posyandu (agar Livewire view update)
     */
    protected function refreshPosyandu()
    {
        $this->loadPosyandu();
    }

    /**
     * Cari sasaran berdasarkan nama di semua kategori
     */
    private function searchSasaranByNama($searchTerm)
    {
        $results = [];

        // Cari di setiap kategori sasaran
        $kategoriConfig = [
            'bayibalita' => [
                'model' => SasaranBayibalita::class,
                'primaryKey' => 'id_sasaran_bayibalita',
            ],
            'remaja' => [
                'model' => SasaranRemaja::class,
                'primaryKey' => 'id_sasaran_remaja',
            ],
            'dewasa' => [
                'model' => SasaranDewasa::class,
                'primaryKey' => 'id_sasaran_dewasa',
            ],
            'pralansia' => [
                'model' => SasaranPralansia::class,
                'primaryKey' => 'id_sasaran_pralansia',
            ],
            'lansia' => [
                'model' => SasaranLansia::class,
                'primaryKey' => 'id_sasaran_lansia',
            ],
        ];

        foreach ($kategoriConfig as $kategori => $config) {
            $sasarans = $config['model']::where('id_posyandu', $this->posyanduId)
                ->where('nama_sasaran', 'like', '%' . $searchTerm . '%')
                ->get();

            foreach ($sasarans as $sasaran) {
                $results[] = [
                    'id' => $sasaran->{$config['primaryKey']},
                    'kategori' => $kategori,
                ];
            }
        }

        return $results;
    }

    public function render()
    {
        $daftarPosyandu = Posyandu::select('id_posyandu', 'nama_posyandu')->get();

        // Ambil imunisasi untuk posyandu ini dengan filter search
        $query = Imunisasi::where('id_posyandu', $this->posyanduId)
            ->with(['user', 'posyandu', 'petugasKesehatan']);

        // Filter berdasarkan search
        if (!empty($this->search)) {
            // Cari sasaran berdasarkan nama
            $sasaranResults = $this->searchSasaranByNama($this->search);

            $query->where(function($q) use ($sasaranResults) {
                // Pencarian berdasarkan field imunisasi (jenis, keterangan, kategori)
                $q->where('jenis_imunisasi', 'like', '%' . $this->search . '%')
                  ->orWhere('keterangan', 'like', '%' . $this->search . '%')
                  ->orWhere('kategori_sasaran', 'like', '%' . $this->search . '%');

                // Pencarian berdasarkan nama sasaran
                if (!empty($sasaranResults)) {
                    foreach ($sasaranResults as $sasaran) {
                        $q->orWhere(function($subQ) use ($sasaran) {
                            $subQ->where('id_sasaran', $sasaran['id'])
                                 ->where('kategori_sasaran', $sasaran['kategori']);
                        });
                    }
                }
            });
        }

        $imunisasiList = $query->orderBy('tanggal_imunisasi', 'desc')->get();

        // Keterangan per kategori: X sasaran sudah imunisasi dari Y total sasaran (tanpa filter search)
        $kategoriConfig = [
            'bayibalita' => ['label' => 'Bayi/Balita', 'model' => SasaranBayibalita::class, 'primaryKey' => 'id_sasaran_bayibalita'],
            'remaja'     => ['label' => 'Remaja', 'model' => SasaranRemaja::class, 'primaryKey' => 'id_sasaran_remaja'],
            'dewasa'     => ['label' => 'Dewasa', 'model' => SasaranDewasa::class, 'primaryKey' => 'id_sasaran_dewasa'],
            'pralansia'  => ['label' => 'Pralansia', 'model' => SasaranPralansia::class, 'primaryKey' => 'id_sasaran_pralansia'],
            'lansia'     => ['label' => 'Lansia', 'model' => SasaranLansia::class, 'primaryKey' => 'id_sasaran_lansia'],
        ];
        $imunisasiKeteranganPerKategori = collect();
        foreach ($kategoriConfig as $kategori => $config) {
            $totalSasaran = $config['model']::where('id_posyandu', $this->posyanduId)->count();
            $sudahImunisasi = Imunisasi::where('id_posyandu', $this->posyanduId)
                ->where('kategori_sasaran', $kategori)
                ->select('id_sasaran')
                ->groupBy('id_sasaran')
                ->get()
                ->count();
            $imunisasiKeteranganPerKategori->push([
                'kategori' => $kategori,
                'label' => $config['label'],
                'sudah_imunisasi' => $sudahImunisasi,
                'total_sasaran' => $totalSasaran,
            ]);
        }
        $totalSemuaSasaran = $imunisasiKeteranganPerKategori->sum('total_sasaran');
        $totalSudahImunisasi = Imunisasi::where('id_posyandu', $this->posyanduId)
            ->select('id_sasaran', 'kategori_sasaran')
            ->groupBy('id_sasaran', 'kategori_sasaran')
            ->get()
            ->count();

        return view('livewire.super-admin.posyandu-imunisasi', [
            'title' => 'Imunisasi - ' . $this->posyandu->nama_posyandu,
            'daftarPosyandu' => $daftarPosyandu,
            'dataPosyandu' => $daftarPosyandu,
            'imunisasiList' => $imunisasiList,
            'imunisasiKeteranganPerKategori' => $imunisasiKeteranganPerKategori,
            'totalSemuaSasaran' => $totalSemuaSasaran,
            'totalSudahImunisasi' => $totalSudahImunisasi,
            'isImunisasiModalOpen' => $this->isImunisasiModalOpen,
            'id_imunisasi' => $this->id_imunisasi,
            'petugasKesehatanList' => $this->getPetugasKesehatanList(),
        ]);
    }
}

