<?php

namespace App\Livewire\SuperAdmin\Traits;

use App\Models\Kader;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

trait KaderCrud
{
    // Modal State
    public $isKaderModalOpen = false;

    // Field Form Kader
    public $id_kader = null;
    public $nama_kader;
    public $email_kader;
    public $password_kader;
    public $posyandu_id_kader;
    public $no_hp_kader;

    /**
     * Buka modal tambah/edit Kader
     */
    public function openKaderModal($id = null)
    {
        if ($id) {
            $this->editKader($id);
        } else {
            $this->resetKaderFields();
            // Pre-fill dengan posyandu saat ini
            $this->posyandu_id_kader = $this->posyanduId;
            $this->isKaderModalOpen = true;
        }
    }

    /**
     * Tutup modal dan reset field
     */
    public function closeKaderModal()
    {
        $this->resetKaderFields();
        $this->isKaderModalOpen = false;
    }

    /**
     * Reset semua field form Kader
     */
    private function resetKaderFields()
    {
        $this->id_kader = null;
        $this->nama_kader = '';
        $this->email_kader = '';
        $this->password_kader = '';
        $this->posyandu_id_kader = '';
        $this->no_hp_kader = '';
    }

    /**
     * Proses simpan data kader, tambah/edit
     */
    public function storeKader()
    {
        // Get user ID for unique email validation if editing
        $userId = null;
        if ($this->id_kader) {
            $kader = Kader::findOrFail($this->id_kader);
            $userId = $kader->id_users;
        }

        $this->validate([
            'nama_kader' => 'required|string|max:255',
            'email_kader' => 'required|string|email|max:255|unique:users,email' . ($userId ? ',' . $userId : ''),
            'password_kader' => $this->id_kader ? 'nullable|min:8' : 'required|min:8',
            'posyandu_id_kader' => 'required|exists:posyandu,id_posyandu',
        ], [
            'nama_kader.required' => 'Nama kader wajib diisi.',
            'email_kader.required' => 'Email wajib diisi.',
            'email_kader.email' => 'Format email tidak valid.',
            'email_kader.unique' => 'Email sudah terdaftar.',
            'password_kader.required' => 'Password wajib diisi.',
            'password_kader.min' => 'Password minimal 8 karakter.',
            'posyandu_id_kader.required' => 'Posyandu wajib dipilih.',
            'posyandu_id_kader.exists' => 'Posyandu yang dipilih tidak valid.',
        ]);

        if ($this->id_kader) {
            // UPDATE
            $kader = Kader::findOrFail($this->id_kader);
            $user = $kader->user;

            $user->name = $this->nama_kader;
            $user->email = $this->email_kader;
            if ($this->password_kader) {
                $user->password = Hash::make($this->password_kader);
            }
            $user->save();

            $kader->id_posyandu = $this->posyandu_id_kader;
            $kader->save();

            session()->flash('message', 'Data Kader berhasil diperbarui.');
        } else {
            // CREATE - Buat User baru
            $user = User::create([
                'name' => $this->nama_kader,
                'email' => $this->email_kader,
                'password' => Hash::make($this->password_kader),
            ]);

            // Assign role kader
            $user->assignRole('kader');

            // Buat record Kader
            Kader::create([
                'id_users' => $user->id,
                'id_posyandu' => $this->posyandu_id_kader,
                'nik_kader' => null,
                'tanggal_lahir' => null,
                'alamat_kader' => null,
                'jabatan_kader' => 'Kader',
            ]);

            session()->flash('message', 'Data Kader berhasil ditambahkan.');
        }

        $this->refreshPosyandu();
        $this->closeKaderModal();
    }

    /**
     * Inisialisasi form edit kader
     */
    public function editKader($id)
    {
        $kader = Kader::with('user')->findOrFail($id);

        $this->id_kader = $kader->id_kader;
        $this->nama_kader = $kader->user->name ?? '';
        $this->email_kader = $kader->user->email ?? '';
        $this->posyandu_id_kader = $kader->id_posyandu;
        $this->password_kader = '';
        $this->no_hp_kader = '';

        $this->isKaderModalOpen = true;
    }

    /**
     * Hapus data kader
     */
    public function deleteKader($id)
    {
        $kader = Kader::findOrFail($id);
        $kader->delete();

        $this->refreshPosyandu();
        session()->flash('message', 'Data Kader berhasil dihapus.');
    }
}

