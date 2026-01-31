<?php

namespace App\Livewire\Posyandu;

use App\Livewire\SuperAdmin\Traits\KaderCrud;
use App\Livewire\Posyandu\Traits\PosyanduHelper;
use App\Livewire\Posyandu\Traits\PosyanduCrudTrait;
use App\Livewire\Traits\NotificationModal;
use App\Models\Kader;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;

class Kaders extends Component
{
    use WithFileUploads;
    use KaderCrud {
        KaderCrud::storeKader as traitStoreKader;
        KaderCrud::editKader as traitEditKader;
        KaderCrud::deleteKader as traitDeleteKader;
    }
    use PosyanduHelper, PosyanduCrudTrait, NotificationModal;

    #[Layout('layouts.posyandudashboard')]

    public function mount()
    {
        $this->initializePosyandu();
        $this->loadPosyanduWithRelations(['kader.user']);
    }

    /**
     * Override openKaderModal untuk set posyandu otomatis
     */
    public function openKaderModal($id = null)
    {
        if ($id) {
            $this->editKader($id);
        } else {
            $this->resetKaderFields();
            // Set posyandu otomatis dari kader
            $this->posyandu_id_kader = $this->posyanduId;
            $this->isKaderModalOpen = true;
        }
    }

    /**
     * Override editKader untuk validasi posyandu
     */
    public function editKader($id)
    {
        $kader = Kader::findOrFail($id);
        $this->validateSasaranPosyanduAccess($kader, 'id_posyandu');
        $this->traitEditKader($id);
    }

    /**
     * Override deleteKader untuk validasi posyandu
     */
    public function deleteKader($id)
    {
        $kader = Kader::findOrFail($id);
        $this->validateSasaranPosyanduAccess($kader, 'id_posyandu');
        $this->traitDeleteKader($id);
    }

    /**
     * Override storeKader untuk validasi posyandu kader
     */
    public function storeKader()
    {
        // Validasi dan set posyandu dari kader
        $this->posyandu_id_kader = $this->validatePosyanduAccess($this->posyandu_id_kader ?? null);

        // Jika edit, validasi akses
        if ($this->id_kader) {
            $kader = Kader::findOrFail($this->id_kader);
            $this->validateSasaranPosyanduAccess($kader, 'id_posyandu');
        }

        // Panggil method dari trait
        $this->traitStoreKader();
    }

    /**
     * Override refreshPosyandu untuk load relasi kader
     */
    protected function refreshPosyandu()
    {
        $this->loadPosyanduWithRelations(['kader.user']);
    }

    public function render()
    {
        return view('livewire.posyandu.kaders', [
            'title' => 'Kader - ' . $this->posyandu->nama_posyandu,
            'isKaderModalOpen' => $this->isKaderModalOpen,
            'id_kader' => $this->id_kader,
        ]);
    }
}

