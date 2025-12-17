<?php

namespace App\Livewire\Posyandu;

use App\Models\Kader;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Laporan extends Component
{
    public $posyandu;

    #[Layout('layouts.posyandudashboard')]
    public function mount(): void
    {
        $user = Auth::user();

        $kader = Kader::with('posyandu')
            ->where('id_users', $user->id)
            ->first();

        if (! $kader || ! $kader->posyandu) {
            abort(403, 'Posyandu untuk akun ini tidak ditemukan.');
        }

        $this->posyandu = $kader->posyandu;
    }

    public function render()
    {
        return view('livewire.posyandu.laporan', [
            'title' => 'Laporan - '.$this->posyandu->nama_posyandu,
            'posyandu' => $this->posyandu,
        ]);
    }
}


