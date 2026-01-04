<?php

namespace App\Livewire\SuperAdmin;

use App\Livewire\SuperAdmin\Traits\PetugasKesehatanCrud;
use App\Models\Posyandu;
use Livewire\Component;
use Livewire\Attributes\Layout;

class PosyanduPetugasKesehatan extends Component
{
    use PetugasKesehatanCrud;

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
     * Load data posyandu dengan relasi
     */
    private function loadPosyandu()
    {
        $posyandu = Posyandu::with('petugas_kesehatan.user')->find($this->posyanduId);

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

    public function render()
    {
        $daftarPosyandu = Posyandu::select('id_posyandu', 'nama_posyandu')->get();

        return view('livewire.super-admin.posyandu-petugas-kesehatan', [
            'title' => 'Petugas Kesehatan - ' . $this->posyandu->nama_posyandu,
            'daftarPosyandu' => $daftarPosyandu,
            'dataPosyandu' => $daftarPosyandu,
            'isPetugasKesehatanModalOpen' => $this->isPetugasKesehatanModalOpen,
            'id_petugas_kesehatan' => $this->id_petugas_kesehatan,
        ]);
    }
}
