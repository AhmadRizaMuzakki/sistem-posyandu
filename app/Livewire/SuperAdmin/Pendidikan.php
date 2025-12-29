<?php

namespace App\Livewire\SuperAdmin;

use App\Livewire\SuperAdmin\Traits\PendidikanCrud;
use App\Models\Posyandu;
use Livewire\Component;
use Livewire\Attributes\Layout;

class Pendidikan extends Component
{
    use PendidikanCrud;

    public $posyandu;
    public $search = '';

    #[Layout('layouts.superadmindashboard')]

    /**
     * Initialize component dengan mengambil ID dari route
     */
    public function mount()
    {
        $id = request()->route('id');

        if (!$id) {
            abort(404, 'ID tidak ditemukan');
        }

        try {
            $decryptedId = decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID tidak valid');
        }

        $this->loadPosyandu($decryptedId);
    }

    /**
     * Load data posyandu berdasarkan ID
     */
    private function loadPosyandu($id): void
    {
        $posyandu = Posyandu::find($id);

        if (!$posyandu) {
            abort(404, 'Posyandu tidak ditemukan');
        }

        $this->posyandu = $posyandu;
    }

    /**
     * Refresh posyandu data (reload dari database)
     * Hanya untuk refresh konteks posyandu, data pendidikan berasal dari sasaran
     */
    public function refreshPosyandu()
    {
        if ($this->posyandu && $this->posyandu->id_posyandu) {
            $this->loadPosyandu($this->posyandu->id_posyandu);
        }
    }

    /**
     * Render component
     * Data pendidikan diambil berdasarkan sasaran yang dipilih
     * Posyandu hanya sebagai filter/konteks
     */
    public function render()
    {
        // Query pendidikan berdasarkan posyandu (hanya sebagai filter)
        // Data pendidikan sendiri berasal dari sasaran yang dipilih saat input
        $pendidikanList = $this->getPendidikanQuery($this->posyandu->id_posyandu)->get();

        return view('livewire.super-admin.pendidikan', [
            'title' => 'Pendidikan - ' . $this->posyandu->nama_posyandu,
            'posyandu' => $this->posyandu, // Hanya untuk konteks/filter
            'pendidikanList' => $pendidikanList, // Data dari sasaran
            'isPendidikanModalOpen' => $this->isPendidikanModalOpen,
            'id_pendidikan' => $this->id_pendidikan,
            'sasaranList' => $this->sasaranList, // List sasaran untuk dipilih
        ]);
    }
}
