<?php

namespace App\Livewire\SuperAdmin\Traits;

use App\Models\Sasaran_Bayibalita;
use Carbon\Carbon;

trait BalitaCrud
{
    // Modal State
    public $isSasaranBalitaModalOpen = false;

    // Field Form Sasaran Bayi & Balita
    public $id_sasaran_bayi_balita = null;
    public $nama_sasaran;
    public $nik_sasaran;
    public $no_kk_sasaran;
    public $tempat_lahir;
    public $tanggal_lahir_sasaran;
    public $jenis_kelamin;
    public $umur_sasaran;
    public $nik_orangtua;
    public $alamat_sasaran;
    public $kepersertaan_bpjs;
    public $nomor_bpjs;
    public $nomor_telepon;
    public $id_users_sasaran;

    /**
     * Buka modal tambah/edit Sasaran Balita
     */
    public function openBalitaModal($id = null)
    {
        $this->searchUser = ''; // Reset search user
        if ($id) {
            $this->editBalita($id);
        } else {
            $this->resetBalitaFields();
            $this->isSasaranBalitaModalOpen = true;
        }
    }

    /**
     * Tutup modal dan reset field
     */
    public function closeBalitaModal()
    {
        $this->resetBalitaFields();
        $this->searchUser = ''; // Reset search user
        $this->isSasaranBalitaModalOpen = false;
    }

    /**
     * Reset semua field form Sasaran Bayi Balita
     */
    private function resetBalitaFields()
    {
        $this->id_sasaran_bayi_balita = null;
        $this->nama_sasaran = '';
        $this->nik_sasaran = '';
        $this->no_kk_sasaran = '';
        $this->tempat_lahir = '';
        $this->tanggal_lahir_sasaran = '';
        $this->jenis_kelamin = '';
        $this->umur_sasaran = '';
        $this->nik_orangtua = '';
        $this->alamat_sasaran = '';
        $this->kepersertaan_bpjs = '';
        $this->nomor_bpjs = '';
        $this->nomor_telepon = '';
        $this->id_users_sasaran = '';
    }

    /**
     * Proses simpan data balita, tambah/edit
     */
    public function storeBalita()
    {
        $this->validate([
            'nama_sasaran' => 'required|string|max:100',
            'nik_sasaran' => 'required|numeric',
            'tanggal_lahir_sasaran' => 'required|date',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'alamat_sasaran' => 'required|string|max:225',
        ], [
            'nama_sasaran.required' => 'Nama sasaran wajib diisi.',
            'nik_sasaran.required' => 'NIK wajib diisi.',
            'nik_sasaran.numeric' => 'NIK harus berupa angka.',
            'tanggal_lahir_sasaran.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir_sasaran.date' => 'Tanggal lahir harus berupa tanggal yang valid.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin harus Laki-laki atau Perempuan.',
            'alamat_sasaran.required' => 'Alamat wajib diisi.',
            'alamat_sasaran.max' => 'Alamat maksimal 225 karakter.',
        ]);

        // Hitung umur dari tanggal_lahir_sasaran
        $umur = null;
        if ($this->tanggal_lahir_sasaran) {
            $umur = Carbon::parse($this->tanggal_lahir_sasaran)->age;
        }

        $data = [
            'id_users' => $this->id_users_sasaran !== '' ? $this->id_users_sasaran : null,
            'id_posyandu' => $this->posyanduId,
            'nama_sasaran' => $this->nama_sasaran,
            'nik_sasaran' => $this->nik_sasaran,
            'no_kk_sasaran' => $this->no_kk_sasaran ?: null,
            'tempat_lahir' => $this->tempat_lahir ?: null,
            'tanggal_lahir' => $this->tanggal_lahir_sasaran,
            'jenis_kelamin' => $this->jenis_kelamin,
            'umur_sasaran' => $umur,
            'nik_orangtua' => $this->nik_orangtua ?: null,
            'alamat_sasaran' => $this->alamat_sasaran,
            'kepersertaan_bpjs' => $this->kepersertaan_bpjs ?: null,
            'nomor_bpjs' => $this->nomor_bpjs ?: null,
            'nomor_telepon' => $this->nomor_telepon ?: null,
        ];

        if ($this->id_sasaran_bayi_balita) {
            // UPDATE
            $balita = Sasaran_Bayibalita::findOrFail($this->id_sasaran_bayi_balita);
            $balita->update($data);
            session()->flash('message', 'Data Balita berhasil diperbarui.');
        } else {
            // CREATE
            Sasaran_Bayibalita::create($data);
            session()->flash('message', 'Data Balita berhasil ditambahkan.');
        }

        $this->refreshPosyandu();
        $this->closeBalitaModal();
    }

    /**
     * Inisialisasi form edit balita
     */
    public function editBalita($id)
    {
        $this->searchUser = ''; // Reset search user
        $balita = Sasaran_Bayibalita::findOrFail($id);

        $this->id_sasaran_bayi_balita = $balita->id_sasaran_bayibalita;
        $this->nama_sasaran = $balita->nama_sasaran;
        $this->nik_sasaran = $balita->nik_sasaran;
        $this->no_kk_sasaran = $balita->no_kk_sasaran ?? '';
        $this->tempat_lahir = $balita->tempat_lahir ?? '';
        $this->tanggal_lahir_sasaran = $balita->tanggal_lahir;
        $this->jenis_kelamin = $balita->jenis_kelamin;
        $this->umur_sasaran = $balita->tanggal_lahir
            ? Carbon::parse($balita->tanggal_lahir)->age
            : $balita->umur_sasaran;
        $this->nik_orangtua = $balita->nik_orangtua ?? '';
        $this->alamat_sasaran = $balita->alamat_sasaran ?? '';
        $this->kepersertaan_bpjs = $balita->kepersertaan_bpjs ?? '';
        $this->nomor_bpjs = $balita->nomor_bpjs ?? '';
        $this->nomor_telepon = $balita->nomor_telepon ?? '';
        $this->id_users_sasaran = $balita->id_users ?? '';

        $this->isSasaranBalitaModalOpen = true;
    }

    /**
     * Hapus data balita
     */
    public function deleteBalita($id)
    {
        $balita = Sasaran_Bayibalita::findOrFail($id);
        $balita->delete();
        $this->refreshPosyandu();
        session()->flash('message', 'Data Balita berhasil dihapus.');
    }

    /**
     * Hitung umur otomatis ketika tanggal lahir berubah
     */
    public function updatedTanggalLahirSasaran()
    {
        if ($this->tanggal_lahir_sasaran) {
            $this->umur_sasaran = Carbon::parse($this->tanggal_lahir_sasaran)->age;
        } else {
            $this->umur_sasaran = '';
        }
    }
}

