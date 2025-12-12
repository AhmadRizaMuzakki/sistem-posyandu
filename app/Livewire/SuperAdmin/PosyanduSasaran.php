<?php

namespace App\Livewire\SuperAdmin;

use App\Livewire\SuperAdmin\Traits\BalitaCrud;
use App\Livewire\SuperAdmin\Traits\RemajaCrud;
use App\Livewire\SuperAdmin\Traits\DewasaCrud;
use App\Livewire\SuperAdmin\Traits\PralansiaCrud;
use App\Livewire\SuperAdmin\Traits\LansiaCrud;
use App\Livewire\SuperAdmin\Traits\IbuHamilCrud;
use App\Livewire\SuperAdmin\Traits\OrangtuaCrud;
use App\Models\Posyandu;
use App\Models\User;
use App\Models\Orangtua;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Layout;

class PosyanduSasaran extends Component
{
    use BalitaCrud, RemajaCrud, DewasaCrud, PralansiaCrud, LansiaCrud, IbuHamilCrud, OrangtuaCrud;

    /**
     * Simple pseudo-user class for orangtua data
     */
    private function createPseudoUser($nama, $email = null)
    {
        return new class($nama, $email) {
            public $name;
            public $email;

            public function __construct($name, $email = null) {
                $this->name = $name;
                $this->email = $email;
            }

            public function hasRole($role) {
                return $role === 'orangtua';
            }
        };
    }

    /**
     * Create pseudo-sasaran object for orangtua data
     */
    private function createPseudoSasaran($orangtua, $type)
    {
        $pseudoUser = $this->createPseudoUser($orangtua->nama);

        return new class($orangtua, $pseudoUser, $type) {
            public $nik_sasaran;
            public $nama_sasaran;
            public $no_kk_sasaran;
            public $tempat_lahir;
            public $tanggal_lahir;
            public $jenis_kelamin;
            public $umur_sasaran;
            public $pekerjaan;
            public $alamat_sasaran;
            public $kepersertaan_bpjs;
            public $nomor_bpjs;
            public $nomor_telepon;
            public $orangtua;
            public $user;
            public $id_sasaran_dewasa;
            public $id_sasaran_pralansia;
            public $id_sasaran_lansia;

            public function __construct($orangtua, $pseudoUser, $type) {
                $this->nik_sasaran = $orangtua->nik;
                $this->nama_sasaran = $orangtua->nama;
                $this->no_kk_sasaran = $orangtua->no_kk;
                $this->tempat_lahir = $orangtua->tempat_lahir;
                $this->tanggal_lahir = $orangtua->tanggal_lahir;
                $this->jenis_kelamin = $orangtua->kelamin;
                $this->umur_sasaran = $orangtua->umur;
                $this->pekerjaan = $orangtua->pekerjaan;
                $this->alamat_sasaran = $orangtua->alamat;
                $this->kepersertaan_bpjs = $orangtua->kepersertaan_bpjs;
                $this->nomor_bpjs = $orangtua->nomor_bpjs;
                $this->nomor_telepon = $orangtua->nomor_telepon;
                $this->orangtua = $orangtua;
                $this->user = $pseudoUser;

                // Set appropriate ID based on type (all null for orangtua data)
                $this->id_sasaran_dewasa = null;
                $this->id_sasaran_pralansia = null;
                $this->id_sasaran_lansia = null;
            }

            public function getKey() {
                return $this->nik_sasaran; // Use NIK as the key for orangtua records
            }
        };
    }

    public $posyandu;
    public $posyanduId;

    // Search properties for each sasaran type
    public $search_bayibalita = '';
    public $search_remaja = '';
    public $search_dewasa = '';
    public $search_pralansia = '';
    public $search_lansia = '';
    public $search_ibuhamil = '';

    // Search property for user dropdown
    public $searchUser = '';

    // Pagination properties for each sasaran type
    public $page_bayibalita = 1;
    public $page_remaja = 1;
    public $page_dewasa = 1;
    public $page_pralansia = 1;
    public $page_lansia = 1;
    public $page_ibuhamil = 1;

    // Items per page
    public $perPage = 5;

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
     * Load data posyandu dengan relasi
     */
    private function loadPosyandu()
    {
        $relations = [
            'sasaran_bayibalita.user',
            'sasaran_bayibalita.orangtua',
            'sasaran_remaja.user',
            'sasaran_remaja.orangtua',
            'sasaran_dewasa.user',
            'sasaran_pralansia.user',
            'sasaran_lansia.user',
            'sasaran_ibuhamil',
        ];

        $posyandu = Posyandu::with($relations)->find($this->posyanduId);

        if (!$posyandu) {
            abort(404, 'Posyandu tidak ditemukan');
        }

        $this->posyandu = $posyandu;
    }

    /**
     * Refresh data posyandu + relasi (agar Livewire view update)
     */
    protected function refreshPosyandu()
    {
        $this->loadPosyandu();
    }

    /**
     * Reset pagination when search changes
     */
    public function updatedSearchBayibalita()
    {
        $this->page_bayibalita = 1;
    }

    public function updatedSearchRemaja()
    {
        $this->page_remaja = 1;
    }

    public function updatedSearchDewasa()
    {
        $this->page_dewasa = 1;
    }

    public function updatedSearchPralansia()
    {
        $this->page_pralansia = 1;
    }

    public function updatedSearchLansia()
    {
        $this->page_lansia = 1;
    }

    public function updatedSearchIbuhamil()
    {
        $this->page_ibuhamil = 1;
    }

    /**
     * Get filtered and paginated sasaran data
     */
    public function getFilteredSasaran($sasaranCollection, $search, $page)
    {
        $query = $sasaranCollection;

        if (!empty($search)) {
            $query = $query->filter(function ($item) use ($search) {
                return stripos($item->nama_sasaran ?? '', $search) !== false;
            })->values();
        } else {
            $query = $query->values();
        }

        $total = $query->count();
        $totalPages = $total > 0 ? ceil($total / $this->perPage) : 1;

        $paginated = $query->slice(($page - 1) * $this->perPage, $this->perPage);

        return [
            'data' => $paginated,
            'total' => $total,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'per_page' => $this->perPage,
        ];
    }

    /**
     * Get orangtua data by age range and format as sasaran
     */
    public function getOrangtuaByUmur($minAge, $maxAge = null, $search = '', $page = 1, $type = 'dewasa')
    {
        $query = Orangtua::query();

        // Filter by age
        if ($maxAge !== null) {
            $query->byAgeRange($minAge, $maxAge);
        } else {
            $query->byMinAge($minAge);
        }

        // Filter by search
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('nik', 'like', '%' . $search . '%');
            });
        }

        // Get all results
        $allOrangtua = $query->get();

        // Format data to match sasaran structure
        $formattedData = $allOrangtua->map(function($orangtua) use ($type) {
            return $this->createPseudoSasaran($orangtua, $type);
        });

        $total = $formattedData->count();
        $totalPages = $total > 0 ? ceil($total / $this->perPage) : 1;

        $paginated = $formattedData->slice(($page - 1) * $this->perPage, $this->perPage);

        return [
            'data' => $paginated,
            'total' => $total,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'per_page' => $this->perPage,
        ];
    }

    public function render()
    {
        $daftarPosyandu = Posyandu::select('id_posyandu', 'nama_posyandu')->get();
        // Hanya ambil user dengan role orangtua untuk dropdown di modal sasaran
        $usersQuery = User::whereHas('roles', function ($query) {
            $query->where('name', 'orangtua');
        });

        // Filter users berdasarkan search
        if (!empty($this->searchUser)) {
            $usersQuery->where(function($q) {
                $q->where('name', 'like', '%' . $this->searchUser . '%')
                  ->orWhere('email', 'like', '%' . $this->searchUser . '%');
            });
        }

        $users = $usersQuery->orderBy('name')->get();
        $orangtua = User::whereHas('roles', function ($query) {
            $query->where('name', 'orangtua');
        })->get();

        return view('livewire.super-admin.posyandu-sasaran', [
            'title' => 'Sasaran - ' . $this->posyandu->nama_posyandu,
            'daftarPosyandu' => $daftarPosyandu,
            'users' => $users,
            'orangtua' => $orangtua,
            'isSasaranBalitaModalOpen' => $this->isSasaranBalitaModalOpen,
            'isSasaranRemajaModalOpen' => $this->isSasaranRemajaModalOpen,
            'isSasaranDewasaModalOpen' => $this->isSasaranDewasaModalOpen,
            'isSasaranPralansiaModalOpen' => $this->isSasaranPralansiaModalOpen,
            'isSasaranLansiaModalOpen' => $this->isSasaranLansiaModalOpen,
            'isSasaranIbuHamilModalOpen' => $this->isSasaranIbuHamilModalOpen,
            'isOrangtuaModalOpen' => $this->isOrangtuaModalOpen,
            'id_sasaran_bayi_balita' => $this->id_sasaran_bayi_balita,
            'id_sasaran_remaja' => $this->id_sasaran_remaja,
            'id_sasaran_dewasa' => $this->id_sasaran_dewasa,
            'id_sasaran_pralansia' => $this->id_sasaran_pralansia,
            'id_sasaran_lansia' => $this->id_sasaran_lansia,
            'id_sasaran_ibuhamil' => $this->id_sasaran_ibuhamil,
        ]);
    }
}

