<?php

namespace App\Livewire\SuperAdmin\Traits;

use App\Models\sasaran_lansia;
use Carbon\Carbon;

trait LansiaCrud
{
    // Modal State
    public $isSasaranLansiaModalOpen = false;

    // Field Form Sasaran Lansia
    public $id_sasaran_lansia = null;
    public $nama_sasaran_lansia;
    public $nik_sasaran_lansia;
    public $no_kk_sasaran_lansia;
    public $tempat_lahir_lansia;
    public $tanggal_lahir_lansia;
    public $jenis_kelamin_lansia;
    public $umur_sasaran_lansia;
    public $nik_orangtua_lansia;
    public $alamat_sasaran_lansia;
    public $kepersertaan_bpjs_lansia;
    public $nomor_bpjs_lansia;
    public $nomor_telepon_lansia;
    public $id_users_sasaran_lansia;

    /**
     * Buka modal tambah/edit Sasaran Lansia
     */
    public function openLansiaModal($id = null)
    {
        $this->searchUser = ''; // Reset search user
        if ($id) {
            $this->editLansia($id);
        } else {
            $this->resetLansiaFields();
            $this->isSasaranLansiaModalOpen = true;
        }
    }

    /**
     * Tutup modal dan reset field
     */
    public function closeLansiaModal()
    {
        $this->resetLansiaFields();
        $this->searchUser = ''; // Reset search user
        $this->isSasaranLansiaModalOpen = false;
    }

    /**
     * Reset semua field form Sasaran Lansia
     */
    private function resetLansiaFields()
    {
        $this->id_sasaran_lansia = null;
        $this->nama_sasaran_lansia = '';
        $this->nik_sasaran_lansia = '';
        $this->no_kk_sasaran_lansia = '';
        $this->tempat_lahir_lansia = '';
        $this->tanggal_lahir_lansia = '';
        $this->jenis_kelamin_lansia = '';
        $this->umur_sasaran_lansia = '';
        $this->nik_orangtua_lansia = '';
        $this->alamat_sasaran_lansia = '';
        $this->kepersertaan_bpjs_lansia = '';
        $this->nomor_bpjs_lansia = '';
        $this->nomor_telepon_lansia = '';
        $this->id_users_sasaran_lansia = '';
    }

    /**
     * Proses simpan data lansia, tambah/edit
     */
    public function storeLansia()
    {
        $this->validate([
            'nama_sasaran_lansia' => 'required|string|max:100',
            'nik_sasaran_lansia' => 'required|numeric',
            'tanggal_lahir_lansia' => 'required|date',
            'jenis_kelamin_lansia' => 'required|in:Laki-laki,Perempuan',
            'alamat_sasaran_lansia' => 'required|string|max:225',
        ], [
            'nama_sasaran_lansia.required' => 'Nama sasaran wajib diisi.',
            'nik_sasaran_lansia.required' => 'NIK wajib diisi.',
            'nik_sasaran_lansia.numeric' => 'NIK harus berupa angka.',
            'tanggal_lahir_lansia.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir_lansia.date' => 'Tanggal lahir harus berupa tanggal yang valid.',
            'jenis_kelamin_lansia.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin_lansia.in' => 'Jenis kelamin harus Laki-laki atau Perempuan.',
            'alamat_sasaran_lansia.required' => 'Alamat wajib diisi.',
            'alamat_sasaran_lansia.max' => 'Alamat maksimal 225 karakter.',
        ]);

        // Hitung umur dari tanggal_lahir_lansia
        $umur = null;
        if ($this->tanggal_lahir_lansia) {
            $umur = Carbon::parse($this->tanggal_lahir_lansia)->age;
        }

        $data = [
            'id_users' => $this->id_users_sasaran_lansia !== '' ? $this->id_users_sasaran_lansia : null,
            'id_posyandu' => $this->posyanduId,
            'nama_sasaran' => $this->nama_sasaran_lansia,
            'nik_sasaran' => $this->nik_sasaran_lansia,
            'no_kk_sasaran' => $this->no_kk_sasaran_lansia ?: null,
            'tempat_lahir' => $this->tempat_lahir_lansia ?: null,
            'tanggal_lahir' => $this->tanggal_lahir_lansia,
            'jenis_kelamin' => $this->jenis_kelamin_lansia,
            'umur_sasaran' => $umur,
            'nik_orangtua' => $this->nik_orangtua_lansia ?: null,
            'alamat_sasaran' => $this->alamat_sasaran_lansia,
            'kepersertaan_bpjs' => $this->kepersertaan_bpjs_lansia ?: null,
            'nomor_bpjs' => $this->nomor_bpjs_lansia ?: null,
            'nomor_telepon' => $this->nomor_telepon_lansia ?: null,
        ];

        if ($this->id_sasaran_lansia) {
            // UPDATE
            $lansia = sasaran_lansia::findOrFail($this->id_sasaran_lansia);
            $lansia->update($data);
            session()->flash('message', 'Data Lansia berhasil diperbarui.');
        } else {
            // CREATE
            sasaran_lansia::create($data);
            session()->flash('message', 'Data Lansia berhasil ditambahkan.');
        }

        $this->refreshPosyandu();
        $this->closeLansiaModal();
    }

    /**
     * Inisialisasi form edit lansia
     */
    public function editLansia($id)
    {
        $this->searchUser = ''; // Reset search user
        $lansia = sasaran_lansia::findOrFail($id);

        $this->id_sasaran_lansia = $lansia->id_sasaran_lansia;
        $this->nama_sasaran_lansia = $lansia->nama_sasaran;
        $this->nik_sasaran_lansia = $lansia->nik_sasaran;
        $this->no_kk_sasaran_lansia = $lansia->no_kk_sasaran ?? '';
        $this->tempat_lahir_lansia = $lansia->tempat_lahir ?? '';
        $this->tanggal_lahir_lansia = $lansia->tanggal_lahir;
        $this->jenis_kelamin_lansia = $lansia->jenis_kelamin;
        $this->umur_sasaran_lansia = $lansia->tanggal_lahir 
            ? Carbon::parse($lansia->tanggal_lahir)->age 
            : $lansia->umur_sasaran;
        $this->nik_orangtua_lansia = $lansia->nik_orangtua ?? '';
        $this->alamat_sasaran_lansia = $lansia->alamat_sasaran ?? '';
        $this->kepersertaan_bpjs_lansia = $lansia->kepersertaan_bpjs ?? '';
        $this->nomor_bpjs_lansia = $lansia->nomor_bpjs ?? '';
        $this->nomor_telepon_lansia = $lansia->nomor_telepon ?? '';
        $this->id_users_sasaran_lansia = $lansia->id_users ?? '';

        $this->isSasaranLansiaModalOpen = true;
    }

    /**
     * Hapus data lansia
     */
    public function deleteLansia($id)
    {
        $lansia = sasaran_lansia::findOrFail($id);
        $lansia->delete();
        $this->refreshPosyandu();
        session()->flash('message', 'Data Lansia berhasil dihapus.');
    }

    /**
     * Hitung umur otomatis ketika tanggal lahir berubah
     */
    public function updatedTanggalLahirLansia()
    {
        if ($this->tanggal_lahir_lansia) {
            $this->umur_sasaran_lansia = Carbon::parse($this->tanggal_lahir_lansia)->age;
        } else {
            $this->umur_sasaran_lansia = '';
        }
    }
}

