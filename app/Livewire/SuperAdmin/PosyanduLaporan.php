<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Posyandu;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PosyanduLaporan extends Component
{
    public $posyandu;
    public $posyanduId;

    #[Layout('layouts.superadmindashboard')]
    public function mount($id): void
    {
        try {
            $decryptedId = decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID tidak valid');
        }

        $this->posyanduId = $decryptedId;
        $this->loadPosyandu();
    }

    private function loadPosyandu(): void
    {
        $posyandu = Posyandu::find($this->posyanduId);

        if (! $posyandu) {
            abort(404, 'Posyandu tidak ditemukan');
        }

        $this->posyandu = $posyandu;
    }

    public function render()
    {
        $daftarPosyandu = Posyandu::select('id_posyandu', 'nama_posyandu')->get();

        return view('livewire.super-admin.posyandu-laporan', [
            'title' => 'Laporan - '.$this->posyandu->nama_posyandu,
            'daftarPosyandu' => $daftarPosyandu,
            'dataPosyandu' => $daftarPosyandu,
            'posyandu' => $this->posyandu,
        ]);
    }
}


