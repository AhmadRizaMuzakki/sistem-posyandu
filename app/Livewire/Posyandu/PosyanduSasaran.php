<?php

namespace App\Livewire\Posyandu;

use App\Livewire\SuperAdmin\Traits\BalitaCrud;
use App\Livewire\SuperAdmin\Traits\RemajaCrud;
use App\Livewire\SuperAdmin\Traits\DewasaCrud;
use App\Livewire\SuperAdmin\Traits\PralansiaCrud;
use App\Livewire\SuperAdmin\Traits\LansiaCrud;
use App\Livewire\SuperAdmin\Traits\IbuHamilCrud;
use App\Livewire\SuperAdmin\Traits\OrangtuaCrud;
use App\Livewire\Posyandu\Traits\PosyanduHelper;
use App\Livewire\Posyandu\Traits\SasaranHelper;
use App\Livewire\Posyandu\Traits\ModalHelper;
use App\Livewire\Posyandu\Traits\PosyanduCrudTrait;
use App\Models\User;
use App\Models\SasaranBayibalita;
use App\Models\SasaranRemaja;
use App\Models\SasaranDewasa;
use App\Models\SasaranPralansia;
use App\Models\SasaranLansia;
use App\Models\SasaranIbuhamil;
use Livewire\Component;
use Livewire\Attributes\Layout;

class PosyanduSasaran extends Component
{
    use BalitaCrud {
        BalitaCrud::storeBalita as traitStoreBalita;
        BalitaCrud::editBalita as traitEditBalita;
        BalitaCrud::deleteBalita as traitDeleteBalita;
    }
    use RemajaCrud {
        RemajaCrud::storeRemaja as traitStoreRemaja;
        RemajaCrud::editRemaja as traitEditRemaja;
        RemajaCrud::deleteRemaja as traitDeleteRemaja;
    }
    use DewasaCrud {
        DewasaCrud::storeDewasa as traitStoreDewasa;
        DewasaCrud::editDewasa as traitEditDewasa;
        DewasaCrud::deleteDewasa as traitDeleteDewasa;
    }
    use PralansiaCrud {
        PralansiaCrud::storePralansia as traitStorePralansia;
        PralansiaCrud::editPralansia as traitEditPralansia;
        PralansiaCrud::deletePralansia as traitDeletePralansia;
    }
    use LansiaCrud {
        LansiaCrud::storeLansia as traitStoreLansia;
        LansiaCrud::editLansia as traitEditLansia;
        LansiaCrud::deleteLansia as traitDeleteLansia;
    }
    use IbuHamilCrud {
        IbuHamilCrud::storeIbuHamil as traitStoreIbuHamil;
        IbuHamilCrud::editIbuHamil as traitEditIbuHamil;
        IbuHamilCrud::deleteIbuHamil as traitDeleteIbuHamil;
    }
    use OrangtuaCrud;
    use PosyanduHelper, SasaranHelper, ModalHelper, PosyanduCrudTrait;

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

    #[Layout('layouts.posyandudashboard')]

    public function mount()
    {
        $this->initializePosyandu();
        $this->loadPosyanduWithRelations();
    }

    /**
     * Refresh data posyandu + relasi (agar Livewire view update)
     */
    protected function refreshPosyandu()
    {
        $this->refreshPosyanduWithRelations();
    }

    /**
     * Override openBalitaModal untuk set posyandu otomatis
     */
    public function openBalitaModal($id = null)
    {
        if ($id) {
            $this->editBalita($id);
        } else {
            $this->resetBalitaFields();
            $this->id_posyandu_sasaran = $this->posyanduId;
            $this->isSasaranBalitaModalOpen = true;
        }
    }

    /**
     * Override openRemajaModal untuk set posyandu otomatis
     */
    public function openRemajaModal($id = null)
    {
        if ($id) {
            $this->editRemaja($id);
        } else {
            $this->resetRemajaFields();
            $this->isSasaranRemajaModalOpen = true;
        }
    }

    /**
     * Override openDewasaModal untuk set posyandu otomatis
     */
    public function openDewasaModal($id = null)
    {
        if ($id) {
            $this->editDewasa($id);
        } else {
            $this->resetDewasaFields();
            $this->isSasaranDewasaModalOpen = true;
        }
    }

    /**
     * Override openPralansiaModal untuk set posyandu otomatis
     */
    public function openPralansiaModal($id = null)
    {
        if ($id) {
            $this->editPralansia($id);
        } else {
            $this->resetPralansiaFields();
            $this->isSasaranPralansiaModalOpen = true;
        }
    }

    /**
     * Override openLansiaModal untuk set posyandu otomatis
     */
    public function openLansiaModal($id = null)
    {
        if ($id) {
            $this->editLansia($id);
        } else {
            $this->resetLansiaFields();
            $this->isSasaranLansiaModalOpen = true;
        }
    }

    /**
     * Override openIbuHamilModal untuk set posyandu otomatis
     */
    public function openIbuHamilModal($id = null)
    {
        if ($id) {
            $this->editIbuHamil($id);
        } else {
            $this->resetIbuHamilFields();
            $this->isSasaranIbuHamilModalOpen = true;
        }
    }

    /**
     * Reset pagination when search changes
     */
    public function updatedSearchBayibalita()
    {
        $this->resetPaginationOnSearch('search_bayibalita', 'page_bayibalita');
    }

    public function updatedSearchRemaja()
    {
        $this->resetPaginationOnSearch('search_remaja', 'page_remaja');
    }

    public function updatedSearchDewasa()
    {
        $this->resetPaginationOnSearch('search_dewasa', 'page_dewasa');
    }

    public function updatedSearchPralansia()
    {
        $this->resetPaginationOnSearch('search_pralansia', 'page_pralansia');
    }

    public function updatedSearchLansia()
    {
        $this->resetPaginationOnSearch('search_lansia', 'page_lansia');
    }

    public function updatedSearchIbuhamil()
    {
        $this->resetPaginationOnSearch('search_ibuhamil', 'page_ibuhamil');
    }

    /**
     * Override storeBalita untuk validasi posyandu
     */
    public function storeBalita()
    {
        // Set posyandu otomatis dari kader
        $this->id_posyandu_sasaran = $this->validatePosyanduAccess($this->id_posyandu_sasaran ?? null);

        // Jika edit, validasi akses
        if ($this->id_sasaran_bayi_balita) {
            $balita = SasaranBayibalita::findOrFail($this->id_sasaran_bayi_balita);
            $this->validateSasaranPosyanduAccess($balita, 'id_posyandu');
        }

        // Panggil method dari trait menggunakan alias
        $this->traitStoreBalita();
    }

    /**
     * Override editBalita untuk validasi posyandu
     */
    public function editBalita($id)
    {
        $balita = SasaranBayibalita::findOrFail($id);
        $this->validateSasaranPosyanduAccess($balita, 'id_posyandu');
        $this->traitEditBalita($id);
    }

    /**
     * Override deleteBalita untuk validasi posyandu
     */
    public function deleteBalita($id)
    {
        $balita = SasaranBayibalita::findOrFail($id);
        $this->validateSasaranPosyanduAccess($balita, 'id_posyandu');
        $this->traitDeleteBalita($id);
    }

    /**
     * Override storeRemaja untuk validasi posyandu
     */
    public function storeRemaja()
    {
        $this->id_posyandu_sasaran = $this->validatePosyanduAccess($this->id_posyandu_sasaran ?? null);
        if ($this->id_sasaran_remaja) {
            $remaja = SasaranRemaja::findOrFail($this->id_sasaran_remaja);
            $this->validateSasaranPosyanduAccess($remaja, 'id_posyandu');
        }
        $this->traitStoreRemaja();
    }

    public function editRemaja($id)
    {
        $remaja = SasaranRemaja::findOrFail($id);
        $this->validateSasaranPosyanduAccess($remaja, 'id_posyandu');
        $this->traitEditRemaja($id);
    }

    public function deleteRemaja($id)
    {
        $remaja = SasaranRemaja::findOrFail($id);
        $this->validateSasaranPosyanduAccess($remaja, 'id_posyandu');
        $this->traitDeleteRemaja($id);
    }

    /**
     * Override storeDewasa untuk validasi posyandu
     */
    public function storeDewasa()
    {
        $this->id_posyandu_sasaran = $this->validatePosyanduAccess($this->id_posyandu_sasaran ?? null);
        if ($this->id_sasaran_dewasa) {
            $dewasa = SasaranDewasa::findOrFail($this->id_sasaran_dewasa);
            $this->validateSasaranPosyanduAccess($dewasa, 'id_posyandu');
        }
        $this->traitStoreDewasa();
    }

    public function editDewasa($id)
    {
        $dewasa = SasaranDewasa::findOrFail($id);
        $this->validateSasaranPosyanduAccess($dewasa, 'id_posyandu');
        $this->traitEditDewasa($id);
    }

    public function deleteDewasa($id)
    {
        $dewasa = SasaranDewasa::findOrFail($id);
        $this->validateSasaranPosyanduAccess($dewasa, 'id_posyandu');
        $this->traitDeleteDewasa($id);
    }

    /**
     * Override storePralansia untuk validasi posyandu
     */
    public function storePralansia()
    {
        $this->id_posyandu_sasaran = $this->validatePosyanduAccess($this->id_posyandu_sasaran ?? null);
        if ($this->id_sasaran_pralansia) {
            $pralansia = SasaranPralansia::findOrFail($this->id_sasaran_pralansia);
            $this->validateSasaranPosyanduAccess($pralansia, 'id_posyandu');
        }
        $this->traitStorePralansia();
    }

    public function editPralansia($id)
    {
        $pralansia = SasaranPralansia::findOrFail($id);
        $this->validateSasaranPosyanduAccess($pralansia, 'id_posyandu');
        $this->traitEditPralansia($id);
    }

    public function deletePralansia($id)
    {
        $pralansia = SasaranPralansia::findOrFail($id);
        $this->validateSasaranPosyanduAccess($pralansia, 'id_posyandu');
        $this->traitDeletePralansia($id);
    }

    /**
     * Override storeLansia untuk validasi posyandu
     */
    public function storeLansia()
    {
        $this->id_posyandu_sasaran = $this->validatePosyanduAccess($this->id_posyandu_sasaran ?? null);
        if ($this->id_sasaran_lansia) {
            $lansia = SasaranLansia::findOrFail($this->id_sasaran_lansia);
            $this->validateSasaranPosyanduAccess($lansia, 'id_posyandu');
        }
        $this->traitStoreLansia();
    }

    public function editLansia($id)
    {
        $lansia = SasaranLansia::findOrFail($id);
        $this->validateSasaranPosyanduAccess($lansia, 'id_posyandu');
        $this->traitEditLansia($id);
    }

    public function deleteLansia($id)
    {
        $lansia = SasaranLansia::findOrFail($id);
        $this->validateSasaranPosyanduAccess($lansia, 'id_posyandu');
        $this->traitDeleteLansia($id);
    }

    /**
     * Override storeIbuHamil untuk validasi posyandu
     */
    public function storeIbuHamil()
    {
        $this->id_posyandu_sasaran = $this->validatePosyanduAccess($this->id_posyandu_sasaran ?? null);
        if ($this->id_sasaran_ibuhamil) {
            $ibuhamil = SasaranIbuhamil::findOrFail($this->id_sasaran_ibuhamil);
            $this->validateSasaranPosyanduAccess($ibuhamil, 'id_posyandu');
        }
        $this->traitStoreIbuHamil();
    }

    public function editIbuHamil($id)
    {
        $ibuhamil = SasaranIbuhamil::findOrFail($id);
        $this->validateSasaranPosyanduAccess($ibuhamil, 'id_posyandu');
        $this->traitEditIbuHamil($id);
    }

    public function deleteIbuHamil($id)
    {
        $ibuhamil = SasaranIbuhamil::findOrFail($id);
        $this->validateSasaranPosyanduAccess($ibuhamil, 'id_posyandu');
        $this->traitDeleteIbuHamil($id);
    }

    public function render()
    {
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

        return view('livewire.posyandu.posyandu-sasaran', [
            'title' => 'Sasaran - ' . $this->posyandu->nama_posyandu,
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

