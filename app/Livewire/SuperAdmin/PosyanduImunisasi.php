<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Posyandu;
use Livewire\Component;
use Livewire\Attributes\Layout;

class PosyanduImunisasi extends Component
{
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

    public function render()
    {
        $daftarPosyandu = Posyandu::select('id_posyandu', 'nama_posyandu')->get();

        return view('livewire.super-admin.posyandu-imunisasi', [
            'title' => 'Imunisasi - ' . $this->posyandu->nama_posyandu,
            'daftarPosyandu' => $daftarPosyandu,
        ]);
    }
}

