<?php

namespace App\Livewire\Posyandu;

use App\Livewire\SuperAdmin\Traits\PendidikanCrud;
use App\Livewire\Posyandu\Traits\PosyanduHelper;
use App\Livewire\Posyandu\Traits\PosyanduCrudTrait;
use App\Models\Pendidikan as PendidikanModel;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

class Pendidikan extends Component
{
    use PendidikanCrud {
        editPendidikan as protected traitEditPendidikan;
        deletePendidikan as protected traitDeletePendidikan;
    }
    use PosyanduHelper, PosyanduCrudTrait, WithPagination;

    public $search = '';

    #[Layout('layouts.posyandudashboard')]

    public function mount(): void
    {
        $this->initializePosyandu();
    }

    public function openPendidikanModal($id = null)
    {
        if ($id) {
            $this->editPendidikan($id);
        } else {
            $this->resetPendidikanFields();
            $this->id_posyandu_pendidikan = $this->posyanduId;
            $this->loadSasaranList();
            $this->isPendidikanModalOpen = true;
        }
    }

    public function editPendidikan($id = null)
    {
        $pendidikan = PendidikanModel::findOrFail($id);
        $this->validateSasaranPosyanduAccess($pendidikan, 'id_posyandu');
        $this->traitEditPendidikan($id);
    }

    public function deletePendidikan($id = null)
    {
        $pendidikan = PendidikanModel::findOrFail($id);
        $this->validateSasaranPosyanduAccess($pendidikan, 'id_posyandu');
        $this->traitDeletePendidikan($id);
    }

    public function refreshPosyandu()
    {
        $this->initializePosyandu();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $pendidikanList = $this->getPendidikanList($this->posyanduId);

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
