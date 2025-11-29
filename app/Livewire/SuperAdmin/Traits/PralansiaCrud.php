<?php

namespace App\Livewire\SuperAdmin\Traits;

use App\Models\sasaran_pralansia;
use Carbon\Carbon;

trait PralansiaCrud
{
    // Modal State
    public $isSasaranPralansiaModalOpen = false;

    // Field Form Sasaran Pralansia
    public $id_sasaran_pralansia = null;
    public $nama_sasaran_pralansia;
    public $nik_sasaran_pralansia;
    public $no_kk_sasaran_pralansia;
    public $tempat_lahir_pralansia;
    public $tanggal_lahir_pralansia;
    public $jenis_kelamin_pralansia;
    public $umur_sasaran_pralansia;
    public $nik_orangtua_pralansia;
    public $alamat_sasaran_pralansia;
    public $kepersertaan_bpjs_pralansia;
    public $nomor_bpjs_pralansia;
    public $nomor_telepon_pralansia;
    public $id_users_sasaran_pralansia;

    /**
     * Buka modal tambah/edit Sasaran Pralansia
     */
    public function openPralansiaModal($id = null)
    {
        $this->searchUser = ''; // Reset search user
        if ($id) {
            $this->editPralansia($id);
        } else {
            $this->resetPralansiaFields();
            $this->isSasaranPralansiaModalOpen = true;
        }
    }

    /**
     * Tutup modal dan reset field
     */
    public function closePralansiaModal()
    {
        $this->resetPralansiaFields();
        $this->searchUser = ''; // Reset search user
        $this->isSasaranPralansiaModalOpen = false;
    }

    /**
     * Reset semua field form Sasaran Pralansia
     */
    private function resetPralansiaFields()
    {
        $this->id_sasaran_pralansia = null;
        $this->nama_sasaran_pralansia = '';
        $this->nik_sasaran_pralansia = '';
        $this->no_kk_sasaran_pralansia = '';
        $this->tempat_lahir_pralansia = '';
        $this->tanggal_lahir_pralansia = '';
        $this->jenis_kelamin_pralansia = '';
        $this->umur_sasaran_pralansia = '';
        $this->nik_orangtua_pralansia = '';
        $this->alamat_sasaran_pralansia = '';
        $this->kepersertaan_bpjs_pralansia = '';
        $this->nomor_bpjs_pralansia = '';
        $this->nomor_telepon_pralansia = '';
        $this->id_users_sasaran_pralansia = '';
    }

    /**
     * Proses simpan data pralansia, tambah/edit
     */
    public function storePralansia()
    {
        $this->validate([
            'nama_sasaran_pralansia' => 'required|string|max:100',
            'nik_sasaran_pralansia' => 'required|numeric',
            'tanggal_lahir_pralansia' => 'required|date',
            'jenis_kelamin_pralansia' => 'required|in:Laki-laki,Perempuan',
            'alamat_sasaran_pralansia' => 'required|string|max:225',
        ], [
            'nama_sasaran_pralansia.required' => 'Nama sasaran wajib diisi.',
            'nik_sasaran_pralansia.required' => 'NIK wajib diisi.',
            'nik_sasaran_pralansia.numeric' => 'NIK harus berupa angka.',
            'tanggal_lahir_pralansia.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir_pralansia.date' => 'Tanggal lahir harus berupa tanggal yang valid.',
            'jenis_kelamin_pralansia.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin_pralansia.in' => 'Jenis kelamin harus Laki-laki atau Perempuan.',
            'alamat_sasaran_pralansia.required' => 'Alamat wajib diisi.',
            'alamat_sasaran_pralansia.max' => 'Alamat maksimal 225 karakter.',
        ]);

        // Hitung umur dari tanggal_lahir_pralansia
        $umur = null;
        if ($this->tanggal_lahir_pralansia) {
            $umur = Carbon::parse($this->tanggal_lahir_pralansia)->age;
        }

        $data = [
            'id_users' => $this->id_users_sasaran_pralansia !== '' ? $this->id_users_sasaran_pralansia : null,
            'id_posyandu' => $this->posyanduId,
            'nama_sasaran' => $this->nama_sasaran_pralansia,
            'nik_sasaran' => $this->nik_sasaran_pralansia,
            'no_kk_sasaran' => $this->no_kk_sasaran_pralansia ?: null,
            'tempat_lahir' => $this->tempat_lahir_pralansia ?: null,
            'tanggal_lahir' => $this->tanggal_lahir_pralansia,
            'jenis_kelamin' => $this->jenis_kelamin_pralansia,
            'umur_sasaran' => $umur,
            'nik_orangtua' => $this->nik_orangtua_pralansia ?: null,
            'alamat_sasaran' => $this->alamat_sasaran_pralansia,
            'kepersertaan_bpjs' => $this->kepersertaan_bpjs_pralansia ?: null,
            'nomor_bpjs' => $this->nomor_bpjs_pralansia ?: null,
            'nomor_telepon' => $this->nomor_telepon_pralansia ?: null,
        ];

        if ($this->id_sasaran_pralansia) {
            // UPDATE
            $pralansia = sasaran_pralansia::findOrFail($this->id_sasaran_pralansia);
            $pralansia->update($data);
            session()->flash('message', 'Data Pralansia berhasil diperbarui.');
        } else {
            // CREATE
            sasaran_pralansia::create($data);
            session()->flash('message', 'Data Pralansia berhasil ditambahkan.');
        }

        $this->refreshPosyandu();
        $this->closePralansiaModal();
    }

    /**
     * Inisialisasi form edit pralansia
     */
    public function editPralansia($id)
    {
        $this->searchUser = ''; // Reset search user
        $pralansia = sasaran_pralansia::findOrFail($id);

        $this->id_sasaran_pralansia = $pralansia->id_sasaran_pralansia;
        $this->nama_sasaran_pralansia = $pralansia->nama_sasaran;
        $this->nik_sasaran_pralansia = $pralansia->nik_sasaran;
        $this->no_kk_sasaran_pralansia = $pralansia->no_kk_sasaran ?? '';
        $this->tempat_lahir_pralansia = $pralansia->tempat_lahir ?? '';
        $this->tanggal_lahir_pralansia = $pralansia->tanggal_lahir;
        $this->jenis_kelamin_pralansia = $pralansia->jenis_kelamin;
        $this->umur_sasaran_pralansia = $pralansia->tanggal_lahir 
            ? Carbon::parse($pralansia->tanggal_lahir)->age 
            : $pralansia->umur_sasaran;
        $this->nik_orangtua_pralansia = $pralansia->nik_orangtua ?? '';
        $this->alamat_sasaran_pralansia = $pralansia->alamat_sasaran ?? '';
        $this->kepersertaan_bpjs_pralansia = $pralansia->kepersertaan_bpjs ?? '';
        $this->nomor_bpjs_pralansia = $pralansia->nomor_bpjs ?? '';
        $this->nomor_telepon_pralansia = $pralansia->nomor_telepon ?? '';
        $this->id_users_sasaran_pralansia = $pralansia->id_users ?? '';

        $this->isSasaranPralansiaModalOpen = true;
    }

    /**
     * Hapus data pralansia
     */
    public function deletePralansia($id)
    {
        $pralansia = sasaran_pralansia::findOrFail($id);
        $pralansia->delete();
        $this->refreshPosyandu();
        session()->flash('message', 'Data Pralansia berhasil dihapus.');
    }

    /**
     * Hitung umur otomatis ketika tanggal lahir berubah
     */
    public function updatedTanggalLahirPralansia()
    {
        if ($this->tanggal_lahir_pralansia) {
            $this->umur_sasaran_pralansia = Carbon::parse($this->tanggal_lahir_pralansia)->age;
        } else {
            $this->umur_sasaran_pralansia = '';
        }
    }
}

