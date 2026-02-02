<?php

namespace App\Livewire\Posyandu;

use App\Livewire\SuperAdmin\Traits\IbuMenyusuiCrud;
use App\Livewire\Posyandu\Traits\PosyanduHelper;
use App\Livewire\Posyandu\Traits\PosyanduCrudTrait;
use App\Models\IbuMenyusui;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

class PosyanduIbuMenyusui extends Component
{
    use IbuMenyusuiCrud {
        IbuMenyusuiCrud::storeIbuMenyusui as traitStoreIbuMenyusui;
        IbuMenyusuiCrud::editIbuMenyusui as traitEditIbuMenyusui;
        IbuMenyusuiCrud::deleteIbuMenyusui as traitDeleteIbuMenyusui;
    }
    use PosyanduHelper, PosyanduCrudTrait;

    public $search = '';
    public $tahunFilter;

    #[Layout('layouts.posyandudashboard')]

    public function mount(): void
    {
        $this->initializePosyandu();
        $this->tahunFilter = date('Y');
    }

    /**
     * Override openIbuMenyusuiModal untuk set posyandu otomatis
     */
    public function openIbuMenyusuiModal($id = null)
    {
        if ($id) {
            $this->editIbuMenyusui($id);
        } else {
            $this->resetIbuMenyusuiFields();
            $this->isIbuMenyusuiModalOpen = true;
        }
    }

    /**
     * Override editIbuMenyusui untuk validasi posyandu
     */
    public function editIbuMenyusui($id)
    {
        $ibuMenyusui = IbuMenyusui::findOrFail($id);
        $this->validateSasaranPosyanduAccess($ibuMenyusui, 'id_posyandu');
        $this->traitEditIbuMenyusui($id);
    }

    /**
     * Override deleteIbuMenyusui untuk validasi posyandu
     */
    public function deleteIbuMenyusui($id)
    {
        $ibuMenyusui = IbuMenyusui::findOrFail($id);
        $this->validateSasaranPosyanduAccess($ibuMenyusui, 'id_posyandu');
        $this->traitDeleteIbuMenyusui($id);
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
        // Ambil data dari sasaran dan sync ke tabel ibu_menyusuis
        $sasaranList = $this->getIbuMenyusuiFromSasaran();
        
        // Ambil data dari tabel ibu_menyusuis untuk ditampilkan
        $query = IbuMenyusui::where('id_posyandu', $this->posyanduId);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('nama_ibu', 'like', '%' . $this->search . '%')
                  ->orWhere('nama_suami', 'like', '%' . $this->search . '%')
                  ->orWhere('nama_bayi', 'like', '%' . $this->search . '%');
            });
        }

        $ibuMenyusuiList = $query->with(['kunjungan' => function($q) {
            $q->where('tahun', $this->tahunFilter)
              ->with(['petugasPenanggungJawab', 'petugasImunisasi', 'petugasInput']);
        }])->get();

        return view('livewire.posyandu.ibu-menyusui', [
            'title' => 'Absensi - ' . $this->posyandu->nama_posyandu,
            'posyandu' => $this->posyandu,
            'ibuMenyusuiList' => $ibuMenyusuiList,
            'sasaranList' => $sasaranList,
            'isIbuMenyusuiModalOpen' => $this->isIbuMenyusuiModalOpen,
            'isKunjunganModalOpen' => $this->isKunjunganModalOpen,
            'id_ibu_menyusui' => $this->id_ibu_menyusui,
            'bulanList' => $this->getBulanList(),
            'petugasKesehatanList' => $this->getPetugasKesehatanList(),
        ]);
    }
}
