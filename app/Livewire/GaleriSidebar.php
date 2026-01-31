<?php

namespace App\Livewire;

use App\Models\Galeri;
use App\Models\Kader;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GaleriSidebar extends Component
{
    /**
     * Tampilkan 9 foto terbaru: global untuk superadmin, per posyandu untuk admin posyandu.
     */
    public function render()
    {
        $user = Auth::user();
        $items = collect();

        if ($user->hasRole('superadmin')) {
            $items = Galeri::whereNull('id_posyandu')->latest()->take(9)->get();
            $galeriUrl = route('superadmin.galeri');
        } else {
            $idPosyandu = Kader::where('id_users', $user->id)->value('id_posyandu');
            if ($idPosyandu) {
                $items = Galeri::where('id_posyandu', $idPosyandu)->latest()->take(9)->get();
            }
            $galeriUrl = route('adminPosyandu.galeri');
        }

        return view('livewire.galeri-sidebar', [
            'items' => $items,
            'galeriUrl' => $galeriUrl,
        ]);
    }
}
