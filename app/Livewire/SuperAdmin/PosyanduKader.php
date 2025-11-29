<?php

namespace App\Livewire\SuperAdmin;

use App\Livewire\SuperAdmin\Traits\KaderCrud;
use App\Models\Posyandu;
use Livewire\Component;
use Livewire\Attributes\Layout;

class PosyanduKader extends Component
{
    use KaderCrud;

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
        $posyandu = Posyandu::with('kader.user')->find($this->posyanduId);

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

        return view('livewire.super-admin.posyandu-kader', [
            'title' => 'Kader - ' . $this->posyandu->nama_posyandu,
            'daftarPosyandu' => $daftarPosyandu,
            'isKaderModalOpen' => $this->isKaderModalOpen,
            'id_kader' => $this->id_kader,
        ]);
    }
}

