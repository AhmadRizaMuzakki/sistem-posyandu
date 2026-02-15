<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Perpustakaan as PerpustakaanModel;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

class PerpustakaanSemua extends Component
{
    use WithPagination;

    #[Layout('layouts.superadmindashboard')]

    public $viewingBook = null;
    public $showFlipbookModal = false;

    public $kategoriOptions = [
        'kesehatan' => 'Kesehatan',
        'gizi' => 'Gizi & Nutrisi',
        'parenting' => 'Parenting',
        'ibu_hamil' => 'Ibu Hamil',
        'bayi_balita' => 'Bayi & Balita',
        'lansia' => 'Lansia',
        'umum' => 'Umum',
    ];

    public function openFlipbook($id)
    {
        $this->viewingBook = PerpustakaanModel::with('posyandu')->findOrFail($id);
        $this->showFlipbookModal = true;
    }

    public function closeFlipbook()
    {
        $this->showFlipbookModal = false;
        $this->viewingBook = null;
    }

    public function render()
    {
        $items = PerpustakaanModel::with('posyandu:id_posyandu,nama_posyandu')
            ->latest()
            ->paginate(24);

        return view('livewire.super-admin.perpustakaan', [
            'items' => $items,
            'title' => 'Perpustakaan',
        ]);
    }
}
