<?php

namespace App\Livewire\SuperAdmin;

use App\Livewire\SuperAdmin\Traits\IbuMenyusuiCrud;
use App\Models\IbuMenyusui;
use Livewire\Component;
use Livewire\Attributes\Layout;

class PosyanduIbuMenyusui extends Component
{
    use IbuMenyusuiCrud {
        IbuMenyusuiCrud::storeIbuMenyusui as traitStoreIbuMenyusui;
        IbuMenyusuiCrud::editIbuMenyusui as traitEditIbuMenyusui;
        IbuMenyusuiCrud::deleteIbuMenyusui as traitDeleteIbuMenyusui;
    }

    public $search = '';
    public $tahunFilter;
    public $posyanduId;

    #[Layout('layouts.superadmindashboard')]

    public function mount($id)
    {
        $this->posyanduId = decrypt($id);
        $this->tahunFilter = date('Y');
    }

    /**
     * Override editIbuMenyusui untuk validasi posyandu
     */
    public function editIbuMenyusui($id)
    {
        $ibuMenyusui = IbuMenyusui::findOrFail($id);
        if ($ibuMenyusui->id_posyandu != $this->posyanduId) {
            abort(403, 'Unauthorized access');
        }
        $this->traitEditIbuMenyusui($id);
    }

    /**
     * Override deleteIbuMenyusui untuk validasi posyandu
     */
    public function deleteIbuMenyusui($id)
    {
        $ibuMenyusui = IbuMenyusui::findOrFail($id);
        if ($ibuMenyusui->id_posyandu != $this->posyanduId) {
            abort(403, 'Unauthorized access');
        }
        $this->traitDeleteIbuMenyusui($id);
    }

    /**
     * Refresh posyandu data
     */
    public function refreshPosyandu()
    {
        // No need to refresh for superadmin
    }

    public function render()
    {
        $posyandu = \App\Models\Posyandu::findOrFail($this->posyanduId);

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
            $q->where('tahun', $this->tahunFilter);
        }])->get();

        return view('livewire.super-admin.posyandu-detail.ibu-menyusui', [
            'title' => 'Ibu Menyusui - ' . $posyandu->nama_posyandu,
            'posyandu' => $posyandu,
            'ibuMenyusuiList' => $ibuMenyusuiList,
            'sasaranList' => $sasaranList,
            'isIbuMenyusuiModalOpen' => $this->isIbuMenyusuiModalOpen,
            'isKunjunganModalOpen' => $this->isKunjunganModalOpen,
            'isInputKunjunganModalOpen' => $this->isInputKunjunganModalOpen,
            'id_ibu_menyusui' => $this->id_ibu_menyusui,
            'bulanList' => $this->getBulanList(),
        ]);
    }
}
