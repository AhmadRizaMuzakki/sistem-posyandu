<?php

namespace App\Livewire\SuperAdmin;

use App\Livewire\SuperAdmin\Traits\BalitaCrud;
use App\Livewire\SuperAdmin\Traits\RemajaCrud;
use App\Livewire\SuperAdmin\Traits\DewasaCrud;
use App\Livewire\SuperAdmin\Traits\PralansiaCrud;
use App\Livewire\SuperAdmin\Traits\LansiaCrud;
use App\Livewire\SuperAdmin\Traits\IbuHamilCrud;
use App\Models\Posyandu;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;

class PosyanduSasaran extends Component
{
    use BalitaCrud, RemajaCrud, DewasaCrud, PralansiaCrud, LansiaCrud, IbuHamilCrud;

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
            'sasaran_remaja.user',
            'sasaran_dewasa.user',
            'sasaran_pralansia.user',
            'sasaran_lansia.user',
            'sasaran_ibuhamil.user',
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
            'id_sasaran_bayi_balita' => $this->id_sasaran_bayi_balita,
            'id_sasaran_remaja' => $this->id_sasaran_remaja,
            'id_sasaran_dewasa' => $this->id_sasaran_dewasa,
            'id_sasaran_pralansia' => $this->id_sasaran_pralansia,
            'id_sasaran_lansia' => $this->id_sasaran_lansia,
            'id_sasaran_ibuhamil' => $this->id_sasaran_ibuhamil,
        ]);
    }
}

