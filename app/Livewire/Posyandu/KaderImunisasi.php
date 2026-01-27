<?php

namespace App\Livewire\Posyandu;

use App\Livewire\SuperAdmin\Traits\ImunisasiCrud;
use App\Livewire\Posyandu\Traits\PosyanduHelper;
use App\Livewire\Posyandu\Traits\PosyanduCrudTrait;
use App\Livewire\Traits\NotificationModal;
use App\Models\Imunisasi;
use App\Models\SasaranBayibalita;
use App\Models\SasaranRemaja;
use App\Models\SasaranDewasa;
use App\Models\SasaranPralansia;
use App\Models\SasaranLansia;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

class KaderImunisasi extends Component
{
    use ImunisasiCrud {
        ImunisasiCrud::storeImunisasi as traitStoreImunisasi;
        ImunisasiCrud::editImunisasi as traitEditImunisasi;
        ImunisasiCrud::deleteImunisasi as traitDeleteImunisasi;
    }
    use PosyanduHelper, PosyanduCrudTrait, NotificationModal;

    public $search = '';

    #[Layout('layouts.posyandudashboard')]

    public function mount()
    {
        $this->initializePosyandu();
    }

    /**
     * Override openImunisasiModal untuk set posyandu otomatis
     */
    public function openImunisasiModal($id = null)
    {
        if ($id) {
            $this->editImunisasi($id);
        } else {
            $this->resetImunisasiFields();
            // Set posyandu otomatis dari kader
            $this->id_posyandu_imunisasi = $this->posyanduId;
            $this->loadSasaranList();
            $this->isImunisasiModalOpen = true;
        }
    }

    /**
     * Override editImunisasi untuk validasi posyandu
     */
    public function editImunisasi($id)
    {
        $imunisasi = Imunisasi::findOrFail($id);
        $this->validateSasaranPosyanduAccess($imunisasi, 'id_posyandu');
        $this->traitEditImunisasi($id);
    }

    /**
     * Override deleteImunisasi untuk validasi posyandu
     */
    public function deleteImunisasi($id)
    {
        $imunisasi = Imunisasi::findOrFail($id);
        $this->validateSasaranPosyanduAccess($imunisasi, 'id_posyandu');
        $this->traitDeleteImunisasi($id);
    }

    /**
     * Override updatedIdSasaranImunisasi untuk memastikan method bisa dipanggil
     * Method ini dipanggil otomatis oleh Livewire ketika id_sasaran_imunisasi berubah
     */
    public function updatedIdSasaranImunisasi($value)
    {
        if ($value && !empty($this->sasaranList)) {
            // Cari sasaran dari list berdasarkan ID
            $sasaran = null;

            // Jika sudah ada kategori, cari yang sesuai dengan ID dan kategori
            if ($this->kategori_sasaran_imunisasi) {
                $sasaran = collect($this->sasaranList)->first(function($s) use ($value) {
                    return $s['id'] == $value && $s['kategori'] == $this->kategori_sasaran_imunisasi;
                });
            }

            // Jika tidak ditemukan dengan kategori, cari berdasarkan ID saja
            if (!$sasaran) {
                $sasaran = collect($this->sasaranList)->firstWhere('id', $value);
            }

            if ($sasaran && isset($sasaran['kategori'])) {
                // Set kategori langsung dari list untuk menghindari konflik ID
                $this->kategori_sasaran_imunisasi = $sasaran['kategori'];
            }
        } else {
            $this->kategori_sasaran_imunisasi = '';
        }
    }

    /**
     * Override storeImunisasi untuk validasi posyandu kader
     */
    public function storeImunisasi()
    {
        // Validasi dan set posyandu dari kader
        $this->id_posyandu_imunisasi = $this->validatePosyanduAccess($this->id_posyandu_imunisasi ?? null);

        // Jika edit, validasi akses
        if ($this->id_imunisasi) {
            $imunisasi = Imunisasi::findOrFail($this->id_imunisasi);
            $this->validateSasaranPosyanduAccess($imunisasi, 'id_posyandu');
        }

        // Panggil method dari trait
        $this->traitStoreImunisasi();
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
        // Ambil imunisasi hanya untuk posyandu kader ini dengan filter search
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

        return view('livewire.posyandu.kader-imunisasi', [
            'title' => 'Imunisasi - ' . $this->posyandu->nama_posyandu,
            'imunisasiList' => $imunisasiList,
            'isImunisasiModalOpen' => $this->isImunisasiModalOpen,
            'id_imunisasi' => $this->id_imunisasi,
            'posyandu' => $this->posyandu,
            'sasaranList' => $this->sasaranList,
            'petugasKesehatanList' => $this->getPetugasKesehatanList(),
        ]);
    }
}
