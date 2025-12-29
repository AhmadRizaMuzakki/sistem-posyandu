<?php

namespace App\Livewire\Posyandu;

use App\Livewire\SuperAdmin\Traits\PendidikanCrud;
use App\Livewire\Posyandu\Traits\PosyanduHelper;
use App\Livewire\Posyandu\Traits\PosyanduCrudTrait;
use App\Models\Pendidikan as PendidikanModel;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

class Pendidikan extends Component
{
    use PendidikanCrud {
        PendidikanCrud::storePendidikan as traitStorePendidikan;
        PendidikanCrud::editPendidikan as traitEditPendidikan;
        PendidikanCrud::deletePendidikan as traitDeletePendidikan;
    }
    use PosyanduHelper, PosyanduCrudTrait;

    public $search = '';

    #[Layout('layouts.posyandudashboard')]

    public function mount(): void
    {
        $this->initializePosyandu();
    }

    /**
     * Override openPendidikanModal untuk set posyandu otomatis
     */
    public function openPendidikanModal($id = null)
    {
        if ($id) {
            $this->editPendidikan($id);
        } else {
            $this->resetPendidikanFields();
            // Set posyandu otomatis dari kader
            $this->id_posyandu_pendidikan = $this->posyanduId;
            $this->loadSasaranList();
            $this->isPendidikanModalOpen = true;
        }
    }

    /**
     * Override editPendidikan untuk validasi posyandu
     */
    public function editPendidikan($id)
    {
        $pendidikan = PendidikanModel::findOrFail($id);
        $this->validateSasaranPosyanduAccess($pendidikan, 'id_posyandu');
        $this->traitEditPendidikan($id);
    }

    /**
     * Override deletePendidikan untuk validasi posyandu
     */
    public function deletePendidikan($id)
    {
        $pendidikan = PendidikanModel::findOrFail($id);
        $this->validateSasaranPosyanduAccess($pendidikan, 'id_posyandu');
        $this->traitDeletePendidikan($id);
    }

    /**
     * Refresh posyandu data
     */
    public function refreshPosyandu()
    {
        $this->initializePosyandu();
    }

    public function render()
    {
        $pendidikanList = $this->getPendidikanQuery($this->posyanduId)->get();

        return view('livewire.posyandu.pendidikan', [
            'title' => 'Pendidikan - ' . $this->posyandu->nama_posyandu,
            'posyandu' => $this->posyandu,
            'pendidikanList' => $pendidikanList,
            'isPendidikanModalOpen' => $this->isPendidikanModalOpen,
            'id_pendidikan' => $this->id_pendidikan,
            'sasaranList' => $this->sasaranList,
        ]);
    }
}
