<?php

namespace App\Livewire\Posyandu;

use App\Livewire\SuperAdmin\Traits\PetugasKesehatanCrud;
use App\Livewire\Posyandu\Traits\PosyanduHelper;
use App\Livewire\Posyandu\Traits\PosyanduCrudTrait;
use App\Models\PetugasKesehatan;
use Livewire\Component;
use Livewire\Attributes\Layout;

class PosyanduPetugasKesehatan extends Component
{
    use PetugasKesehatanCrud {
        PetugasKesehatanCrud::storePetugasKesehatan as traitStorePetugasKesehatan;
        PetugasKesehatanCrud::editPetugasKesehatan as traitEditPetugasKesehatan;
        PetugasKesehatanCrud::deletePetugasKesehatan as traitDeletePetugasKesehatan;
    }
    use PosyanduHelper, PosyanduCrudTrait;

    #[Layout('layouts.posyandudashboard')]

    public function mount()
    {
        $this->initializePosyandu();
        $this->loadPosyanduWithRelations(['petugas_kesehatan.user']);
    }

    /**
     * Override openPetugasKesehatanModal untuk set posyandu otomatis
     */
    public function openPetugasKesehatanModal($id = null)
    {
        if ($id) {
            $this->editPetugasKesehatan($id);
        } else {
            $this->resetPetugasKesehatanFields();
            // Set posyandu otomatis dari kader
            $this->posyandu_id_petugas_kesehatan = $this->posyanduId;
            $this->isPetugasKesehatanModalOpen = true;
        }
    }

    /**
     * Override editPetugasKesehatan untuk validasi posyandu
     */
    public function editPetugasKesehatan($id)
    {
        $petugasKesehatan = PetugasKesehatan::findOrFail($id);
        $this->validateSasaranPosyanduAccess($petugasKesehatan, 'id_posyandu');
        $this->traitEditPetugasKesehatan($id);
    }

    /**
     * Override deletePetugasKesehatan untuk validasi posyandu
     */
    public function deletePetugasKesehatan($id)
    {
        $petugasKesehatan = PetugasKesehatan::findOrFail($id);
        $this->validateSasaranPosyanduAccess($petugasKesehatan, 'id_posyandu');
        $this->traitDeletePetugasKesehatan($id);
    }

    /**
     * Override storePetugasKesehatan untuk validasi posyandu kader
     */
    public function storePetugasKesehatan()
    {
        // Validasi dan set posyandu dari kader
        $this->posyandu_id_petugas_kesehatan = $this->validatePosyanduAccess($this->posyandu_id_petugas_kesehatan ?? null);

        // Jika edit, validasi akses
        if ($this->id_petugas_kesehatan) {
            $petugasKesehatan = PetugasKesehatan::findOrFail($this->id_petugas_kesehatan);
            $this->validateSasaranPosyanduAccess($petugasKesehatan, 'id_posyandu');
        }

        // Panggil method dari trait
        $this->traitStorePetugasKesehatan();
    }

    /**
     * Override refreshPosyandu untuk load relasi petugas kesehatan
     */
    protected function refreshPosyandu()
    {
        $this->loadPosyanduWithRelations(['petugas_kesehatan.user']);
    }

    public function render()
    {
        return view('livewire.posyandu.petugas-kesehatan', [
            'title' => 'Petugas Kesehatan - ' . $this->posyandu->nama_posyandu,
            'isPetugasKesehatanModalOpen' => $this->isPetugasKesehatanModalOpen,
            'id_petugas_kesehatan' => $this->id_petugas_kesehatan,
        ]);
    }
}

