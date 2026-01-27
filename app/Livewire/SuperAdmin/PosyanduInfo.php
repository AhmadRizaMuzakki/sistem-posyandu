<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Posyandu;
use App\Models\Orangtua;
use Livewire\Component;
use Livewire\Attributes\Layout;

class PosyanduInfo extends Component
{

    public $posyandu;
    public $posyanduId;
    
    // Modal konfirmasi (untuk kompatibilitas dengan confirm-modal)
    public $showConfirmModal = false;
    public $confirmMessage = '';
    public $confirmAction = '';

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
        $relations = [
            'kader.user',
            'sasaran_bayibalita.user',
            'sasaran_remaja.user',
            'sasaran_dewasa.user',
            'sasaran_pralansia.user',
            'sasaran_lansia.user',
            'sasaran_ibuhamil',
        ];

        $posyandu = Posyandu::with($relations)->find($this->posyanduId);

        if (!$posyandu) {
            abort(404, 'Posyandu tidak ditemukan');
        }

        $this->posyandu = $posyandu;
    }


    /**
     * Get count of orangtua by age range (for statistics)
     */
    public function getOrangtuaCountByUmur($minAge, $maxAge = null)
    {
        $query = Orangtua::query();

        // Filter by age
        if ($maxAge !== null) {
            $query->byAgeRange($minAge, $maxAge);
        } else {
            $query->byMinAge($minAge);
        }

        return $query->count();
    }

    /**
     * Tutup modal konfirmasi (untuk kompatibilitas dengan confirm-modal)
     */
    public function closeConfirmModal()
    {
        $this->showConfirmModal = false;
        $this->confirmMessage = '';
        $this->confirmAction = '';
    }

    /**
     * Eksekusi action setelah konfirmasi (untuk kompatibilitas dengan confirm-modal)
     */
    public function executeConfirmAction()
    {
        // Tidak ada action yang perlu dieksekusi di PosyanduInfo
        $this->closeConfirmModal();
    }

    public function render()
    {
        $daftarPosyandu = Posyandu::select('id_posyandu', 'nama_posyandu')->get();

        return view('livewire.super-admin.posyandu-info', [
            'title' => 'Info Posyandu - ' . $this->posyandu->nama_posyandu,
            'daftarPosyandu' => $daftarPosyandu,
        ]);
    }
}

