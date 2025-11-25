<?php

namespace App\Livewire\Posyandu;

use App\Models\User;
use App\Models\Kader;
use Livewire\Component;
use App\Models\Posyandu;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

class Kaders extends Component
{
    use WithPagination;
    #[Layout('layouts.dashboard')]

    // Properti untuk Form
    public $nik_kader, $id_users, $tanggal_lahir, $alamat_kader, $jabatan_kader, $id_posyandu;
    public $id_kader; // Untuk mode edit

    public $isModalOpen = false; // Kontrol status Modal

    public function render()
    {
        return view('livewire.posyandu.kaders', [
            'kaders' => Kader::paginate(10), // Ganti sesuai model Anda
            'users' => User::all(), // Data untuk dropdown user
            'posyandus' => Posyandu::all(), // Data untuk dropdown posyandu
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
    }

    private function resetInputFields()
    {
        $this->nik_kader = '';
        $this->id_users = '';
        $this->tanggal_lahir = '';
        $this->alamat_kader = '';
        $this->jabatan_kader = '';
        $this->id_posyandu = '';
        $this->id_kader = null;
    }

    public function store()
    {
        $this->validate([
            'nik_kader' => 'required|numeric',
            'id_users' => 'required',
            'tanggal_lahir' => 'required|date',
            'alamat_kader' => 'required',
            'jabatan_kader' => 'required',
            'id_posyandu' => 'required',
        ]);

        Kader::updateOrCreate(['id_kader' => $this->id_kader], [
            'nik_kader' => $this->nik_kader,
            'id_users' => $this->id_users,
            'tanggal_lahir' => $this->tanggal_lahir,
            'alamat_kader' => $this->alamat_kader,
            // Asumsi: nama_posyandu di database adalah ID atau redudansi,
            // kita isi sama dengan id_posyandu untuk keamanan data.
            'nama_posyandu' => $this->id_posyandu,
            'jabatan_kader' => $this->jabatan_kader,
            'id_posyandu' => $this->id_posyandu,
        ]);

        $user = User::find($this->id_users);
        if ($user) {
            $user->assignRole('kader');
        }
        session()->flash('message', $this->id_kader ? 'Data Kader berhasil diperbarui.' : 'Data Kader berhasil ditambahkan.');

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $kader = Kader::findOrFail($id);
        $this->id_kader = $id;
        $this->nik_kader = $kader->nik_kader;
        $this->id_users = $kader->id_users;
        $this->tanggal_lahir = $kader->tanggal_lahir;
        $this->alamat_kader = $kader->alamat_kader;
        $this->jabatan_kader = $kader->jabatan_kader;
        $this->id_posyandu = $kader->id_posyandu;

        $this->openModal();
    }

    public function delete($id)
    {
        Kader::find($id)->delete();
        session()->flash('message', 'Data Kader berhasil dihapus.');

    }
}
