<?php

namespace App\Livewire\SuperAdmin;

use App\Livewire\SuperAdmin\Traits\ImunisasiCrud;
use App\Models\Imunisasi;
use App\Models\Posyandu;
use Livewire\Component;
use Livewire\Attributes\Layout;

class PosyanduImunisasi extends Component
{
    use ImunisasiCrud;

    public $posyandu;
    public $posyanduId;

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
     * Load data posyandu
     */
    private function loadPosyandu()
    {
        $posyandu = Posyandu::find($this->posyanduId);

        if (!$posyandu) {
            abort(404, 'Posyandu tidak ditemukan');
        }

        $this->posyandu = $posyandu;
    }

    /**
     * Refresh data posyandu (agar Livewire view update)
     */
    protected function refreshPosyandu()
    {
        $this->loadPosyandu();
    }

    public function render()
    {
        $daftarPosyandu = Posyandu::select('id_posyandu', 'nama_posyandu')->get();

        // Ambil imunisasi untuk posyandu ini
        $imunisasiList = Imunisasi::where('id_posyandu', $this->posyanduId)
            ->with(['user', 'posyandu'])
            ->orderBy('tanggal_imunisasi', 'desc')
            ->get();

        return view('livewire.super-admin.posyandu-imunisasi', [
            'title' => 'Imunisasi - ' . $this->posyandu->nama_posyandu,
            'daftarPosyandu' => $daftarPosyandu,
            'dataPosyandu' => $daftarPosyandu,
            'imunisasiList' => $imunisasiList,
            'isImunisasiModalOpen' => $this->isImunisasiModalOpen,
            'id_imunisasi' => $this->id_imunisasi,
        ]);
    }
}

