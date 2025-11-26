<?php

namespace App\Livewire\SuperAdmin;

use App\Livewire\SuperAdmin\Traits\BalitaCrud;
use App\Livewire\SuperAdmin\Traits\KaderCrud;
use App\Livewire\SuperAdmin\Traits\RemajaCrud;
use App\Livewire\SuperAdmin\Traits\DewasaCrud;
use App\Livewire\SuperAdmin\Traits\PralansiaCrud;
use App\Livewire\SuperAdmin\Traits\LansiaCrud;
use App\Livewire\SuperAdmin\Traits\IbuHamilCrud;
use App\Models\Posyandu;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;

class PosyanduDetail extends Component
{
    use BalitaCrud, KaderCrud, RemajaCrud, DewasaCrud, PralansiaCrud, LansiaCrud, IbuHamilCrud;

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
        $relations = [
            'kader.user',
            'sasaran_bayibalita',
            'sasaran_remaja',
            'sasaran_dewasa',
            'sasaran_pralansia',
            'sasaran_lansia',
            'sasaran_ibuhamil',
        ];

        $posyandu = Posyandu::with($relations)->find($this->posyanduId);

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
        $daftarPosyandu = Posyandu::all(['id_posyandu', 'nama_posyandu']);
        $users = User::all();

        return view('livewire.super-admin.posyandu-detail', [
            'title' => 'Detail Posyandu - ' . $this->posyandu->nama_posyandu,
            'daftarPosyandu' => $daftarPosyandu,
            'dataPosyandu' => $daftarPosyandu,
            'users' => $users,
            'isSasaranBalitaModalOpen' => $this->isSasaranBalitaModalOpen,
            'isKaderModalOpen' => $this->isKaderModalOpen,
            'isSasaranRemajaModalOpen' => $this->isSasaranRemajaModalOpen,
            'isSasaranDewasaModalOpen' => $this->isSasaranDewasaModalOpen,
            'isSasaranPralansiaModalOpen' => $this->isSasaranPralansiaModalOpen,
            'isSasaranLansiaModalOpen' => $this->isSasaranLansiaModalOpen,
            'isSasaranIbuHamilModalOpen' => $this->isSasaranIbuHamilModalOpen,
            'id_sasaran_bayi_balita' => $this->id_sasaran_bayi_balita,
            'id_kader' => $this->id_kader,
            'id_sasaran_remaja' => $this->id_sasaran_remaja,
            'id_sasaran_dewasa' => $this->id_sasaran_dewasa,
            'id_sasaran_pralansia' => $this->id_sasaran_pralansia,
            'id_sasaran_lansia' => $this->id_sasaran_lansia,
            'id_sasaran_ibuhamil' => $this->id_sasaran_ibuhamil,
        ]);
    }
}
