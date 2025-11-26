<?php

namespace App\Livewire\SuperAdmin\Traits;

use App\Models\sasaran_ibuhamil;
use Carbon\Carbon;

trait IbuHamilCrud
{
    // Modal State
    public $isSasaranIbuHamilModalOpen = false;

    // Field Form Sasaran Ibu Hamil
    public $id_sasaran_ibuhamil = null;
    public $nama_sasaran_ibuhamil;
    public $nik_sasaran_ibuhamil;
    public $no_kk_sasaran_ibuhamil;
    public $tempat_lahir_ibuhamil;
    public $tanggal_lahir_ibuhamil;
    public $jenis_kelamin_ibuhamil;
    public $umur_sasaran_ibuhamil;
    public $nik_orangtua_ibuhamil;
    public $alamat_sasaran_ibuhamil;
    public $kepersertaan_bpjs_ibuhamil;
    public $nomor_bpjs_ibuhamil;
    public $nomor_telepon_ibuhamil;
    public $id_users_sasaran_ibuhamil;

    /**
     * Buka modal tambah/edit Sasaran Ibu Hamil
     */
    public function openIbuHamilModal($id = null)
    {
        $this->searchUser = ''; // Reset search user
        if ($id) {
            $this->editIbuHamil($id);
        } else {
            $this->resetIbuHamilFields();
            $this->isSasaranIbuHamilModalOpen = true;
        }
    }

    /**
     * Tutup modal dan reset field
     */
    public function closeIbuHamilModal()
    {
        $this->resetIbuHamilFields();
        $this->searchUser = ''; // Reset search user
        $this->isSasaranIbuHamilModalOpen = false;
    }

    /**
     * Reset semua field form Sasaran Ibu Hamil
     */
    private function resetIbuHamilFields()
    {
        $this->id_sasaran_ibuhamil = null;
        $this->nama_sasaran_ibuhamil = '';
        $this->nik_sasaran_ibuhamil = '';
        $this->no_kk_sasaran_ibuhamil = '';
        $this->tempat_lahir_ibuhamil = '';
        $this->tanggal_lahir_ibuhamil = '';
        $this->jenis_kelamin_ibuhamil = '';
        $this->umur_sasaran_ibuhamil = '';
        $this->nik_orangtua_ibuhamil = '';
        $this->alamat_sasaran_ibuhamil = '';
        $this->kepersertaan_bpjs_ibuhamil = '';
        $this->nomor_bpjs_ibuhamil = '';
        $this->nomor_telepon_ibuhamil = '';
        $this->id_users_sasaran_ibuhamil = '';
    }

    /**
     * Proses simpan data ibu hamil, tambah/edit
     */
    public function storeIbuHamil()
    {
        $this->validate([
            'nama_sasaran_ibuhamil' => 'required|string|max:100',
            'nik_sasaran_ibuhamil' => 'required|numeric',
            'tanggal_lahir_ibuhamil' => 'required|date',
            'jenis_kelamin_ibuhamil' => 'required|in:Laki-laki,Perempuan',
            'alamat_sasaran_ibuhamil' => 'required|string|max:225',
        ], [
            'nama_sasaran_ibuhamil.required' => 'Nama sasaran wajib diisi.',
            'nik_sasaran_ibuhamil.required' => 'NIK wajib diisi.',
            'nik_sasaran_ibuhamil.numeric' => 'NIK harus berupa angka.',
            'tanggal_lahir_ibuhamil.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir_ibuhamil.date' => 'Tanggal lahir harus berupa tanggal yang valid.',
            'jenis_kelamin_ibuhamil.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin_ibuhamil.in' => 'Jenis kelamin harus Laki-laki atau Perempuan.',
            'alamat_sasaran_ibuhamil.required' => 'Alamat wajib diisi.',
            'alamat_sasaran_ibuhamil.max' => 'Alamat maksimal 225 karakter.',
        ]);

        // Hitung umur dari tanggal_lahir_ibuhamil
        $umur = null;
        if ($this->tanggal_lahir_ibuhamil) {
            $umur = Carbon::parse($this->tanggal_lahir_ibuhamil)->age;
        }

        $data = [
            'id_users' => $this->id_users_sasaran_ibuhamil !== '' ? $this->id_users_sasaran_ibuhamil : null,
            'id_posyandu' => $this->posyanduId,
            'nama_sasaran' => $this->nama_sasaran_ibuhamil,
            'nik_sasaran' => $this->nik_sasaran_ibuhamil,
            'no_kk_sasaran' => $this->no_kk_sasaran_ibuhamil ?: null,
            'tempat_lahir' => $this->tempat_lahir_ibuhamil ?: null,
            'tanggal_lahir' => $this->tanggal_lahir_ibuhamil,
            'jenis_kelamin' => $this->jenis_kelamin_ibuhamil,
            'umur_sasaran' => $umur,
            'nik_orangtua' => $this->nik_orangtua_ibuhamil ?: null,
            'alamat_sasaran' => $this->alamat_sasaran_ibuhamil,
            'kepersertaan_bpjs' => $this->kepersertaan_bpjs_ibuhamil ?: null,
            'nomor_bpjs' => $this->nomor_bpjs_ibuhamil ?: null,
            'nomor_telepon' => $this->nomor_telepon_ibuhamil ?: null,
        ];

        if ($this->id_sasaran_ibuhamil) {
            // UPDATE
            $ibuhamil = sasaran_ibuhamil::findOrFail($this->id_sasaran_ibuhamil);
            $ibuhamil->update($data);
            session()->flash('message', 'Data Ibu Hamil berhasil diperbarui.');
        } else {
            // CREATE
            sasaran_ibuhamil::create($data);
            session()->flash('message', 'Data Ibu Hamil berhasil ditambahkan.');
        }

        $this->refreshPosyandu();
        $this->closeIbuHamilModal();
    }

    /**
     * Inisialisasi form edit ibu hamil
     */
    public function editIbuHamil($id)
    {
        $this->searchUser = ''; // Reset search user
        $ibuhamil = sasaran_ibuhamil::findOrFail($id);

        $this->id_sasaran_ibuhamil = $ibuhamil->id_sasaran;
        $this->nama_sasaran_ibuhamil = $ibuhamil->nama_sasaran;
        $this->nik_sasaran_ibuhamil = $ibuhamil->nik_sasaran;
        $this->no_kk_sasaran_ibuhamil = $ibuhamil->no_kk_sasaran ?? '';
        $this->tempat_lahir_ibuhamil = $ibuhamil->tempat_lahir ?? '';
        $this->tanggal_lahir_ibuhamil = $ibuhamil->tanggal_lahir;
        $this->jenis_kelamin_ibuhamil = $ibuhamil->jenis_kelamin;
        $this->umur_sasaran_ibuhamil = $ibuhamil->tanggal_lahir 
            ? Carbon::parse($ibuhamil->tanggal_lahir)->age 
            : $ibuhamil->umur_sasaran;
        $this->nik_orangtua_ibuhamil = $ibuhamil->nik_orangtua ?? '';
        $this->alamat_sasaran_ibuhamil = $ibuhamil->alamat_sasaran ?? '';
        $this->kepersertaan_bpjs_ibuhamil = $ibuhamil->kepersertaan_bpjs ?? '';
        $this->nomor_bpjs_ibuhamil = $ibuhamil->nomor_bpjs ?? '';
        $this->nomor_telepon_ibuhamil = $ibuhamil->nomor_telepon ?? '';
        $this->id_users_sasaran_ibuhamil = $ibuhamil->id_users ?? '';

        $this->isSasaranIbuHamilModalOpen = true;
    }

    /**
     * Hapus data ibu hamil
     */
    public function deleteIbuHamil($id)
    {
        $ibuhamil = sasaran_ibuhamil::findOrFail($id);
        $ibuhamil->delete();
        $this->refreshPosyandu();
        session()->flash('message', 'Data Ibu Hamil berhasil dihapus.');
    }
}

