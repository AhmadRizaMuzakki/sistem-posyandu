<?php

namespace App\Livewire\SuperAdmin\Traits;

use App\Models\sasaran_remaja;
use Carbon\Carbon;

trait RemajaCrud
{
    // Modal State
    public $isSasaranRemajaModalOpen = false;

    // Field Form Sasaran Remaja
    public $id_sasaran_remaja = null;
    public $nama_sasaran_remaja;
    public $nik_sasaran_remaja;
    public $no_kk_sasaran_remaja;
    public $tempat_lahir_remaja;
    public $tanggal_lahir_remaja;
    public $jenis_kelamin_remaja;
    public $umur_sasaran_remaja;
    public $nik_orangtua_remaja;
    public $alamat_sasaran_remaja;
    public $kepersertaan_bpjs_remaja;
    public $nomor_bpjs_remaja;
    public $nomor_telepon_remaja;
    public $id_users_sasaran_remaja;

    /**
     * Buka modal tambah/edit Sasaran Remaja
     */
    public function openRemajaModal($id = null)
    {
        $this->searchUser = ''; // Reset search user
        if ($id) {
            $this->editRemaja($id);
        } else {
            $this->resetRemajaFields();
            $this->isSasaranRemajaModalOpen = true;
        }
    }

    /**
     * Tutup modal dan reset field
     */
    public function closeRemajaModal()
    {
        $this->resetRemajaFields();
        $this->searchUser = ''; // Reset search user
        $this->isSasaranRemajaModalOpen = false;
    }

    /**
     * Reset semua field form Sasaran Remaja
     */
    private function resetRemajaFields()
    {
        $this->id_sasaran_remaja = null;
        $this->nama_sasaran_remaja = '';
        $this->nik_sasaran_remaja = '';
        $this->no_kk_sasaran_remaja = '';
        $this->tempat_lahir_remaja = '';
        $this->tanggal_lahir_remaja = '';
        $this->jenis_kelamin_remaja = '';
        $this->umur_sasaran_remaja = '';
        $this->nik_orangtua_remaja = '';
        $this->alamat_sasaran_remaja = '';
        $this->kepersertaan_bpjs_remaja = '';
        $this->nomor_bpjs_remaja = '';
        $this->nomor_telepon_remaja = '';
        $this->id_users_sasaran_remaja = '';
    }

    /**
     * Proses simpan data remaja, tambah/edit
     */
    public function storeRemaja()
    {
        $this->validate([
            'nama_sasaran_remaja' => 'required|string|max:100',
            'nik_sasaran_remaja' => 'required|numeric',
            'tanggal_lahir_remaja' => 'required|date',
            'jenis_kelamin_remaja' => 'required|in:Laki-laki,Perempuan',
            'alamat_sasaran_remaja' => 'required|string|max:225',
        ], [
            'nama_sasaran_remaja.required' => 'Nama sasaran wajib diisi.',
            'nik_sasaran_remaja.required' => 'NIK wajib diisi.',
            'nik_sasaran_remaja.numeric' => 'NIK harus berupa angka.',
            'tanggal_lahir_remaja.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir_remaja.date' => 'Tanggal lahir harus berupa tanggal yang valid.',
            'jenis_kelamin_remaja.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin_remaja.in' => 'Jenis kelamin harus Laki-laki atau Perempuan.',
            'alamat_sasaran_remaja.required' => 'Alamat wajib diisi.',
            'alamat_sasaran_remaja.max' => 'Alamat maksimal 225 karakter.',
        ]);

        // Hitung umur dari tanggal_lahir_remaja
        $umur = null;
        if ($this->tanggal_lahir_remaja) {
            $umur = Carbon::parse($this->tanggal_lahir_remaja)->age;
        }

        $data = [
            'id_users' => $this->id_users_sasaran_remaja !== '' ? $this->id_users_sasaran_remaja : null,
            'id_posyandu' => $this->posyanduId,
            'nama_sasaran' => $this->nama_sasaran_remaja,
            'nik_sasaran' => $this->nik_sasaran_remaja,
            'no_kk_sasaran' => $this->no_kk_sasaran_remaja ?: null,
            'tempat_lahir' => $this->tempat_lahir_remaja ?: null,
            'tanggal_lahir' => $this->tanggal_lahir_remaja,
            'jenis_kelamin' => $this->jenis_kelamin_remaja,
            'umur_sasaran' => $umur,
            'nik_orangtua' => $this->nik_orangtua_remaja ?: null,
            'alamat_sasaran' => $this->alamat_sasaran_remaja,
            'kepersertaan_bpjs' => $this->kepersertaan_bpjs_remaja ?: null,
            'nomor_bpjs' => $this->nomor_bpjs_remaja ?: null,
            'nomor_telepon' => $this->nomor_telepon_remaja ?: null,
        ];

        if ($this->id_sasaran_remaja) {
            // UPDATE
            $remaja = sasaran_remaja::findOrFail($this->id_sasaran_remaja);
            $remaja->update($data);
            session()->flash('message', 'Data Remaja berhasil diperbarui.');
        } else {
            // CREATE
            sasaran_remaja::create($data);
            session()->flash('message', 'Data Remaja berhasil ditambahkan.');
        }

        $this->refreshPosyandu();
        $this->closeRemajaModal();
    }

    /**
     * Inisialisasi form edit remaja
     */
    public function editRemaja($id)
    {
        $this->searchUser = ''; // Reset search user
        $remaja = sasaran_remaja::findOrFail($id);

        $this->id_sasaran_remaja = $remaja->id_sasaran;
        $this->nama_sasaran_remaja = $remaja->nama_sasaran;
        $this->nik_sasaran_remaja = $remaja->nik_sasaran;
        $this->no_kk_sasaran_remaja = $remaja->no_kk_sasaran ?? '';
        $this->tempat_lahir_remaja = $remaja->tempat_lahir ?? '';
        $this->tanggal_lahir_remaja = $remaja->tanggal_lahir;
        $this->jenis_kelamin_remaja = $remaja->jenis_kelamin;
        $this->umur_sasaran_remaja = $remaja->tanggal_lahir 
            ? Carbon::parse($remaja->tanggal_lahir)->age 
            : $remaja->umur_sasaran;
        $this->nik_orangtua_remaja = $remaja->nik_orangtua ?? '';
        $this->alamat_sasaran_remaja = $remaja->alamat_sasaran ?? '';
        $this->kepersertaan_bpjs_remaja = $remaja->kepersertaan_bpjs ?? '';
        $this->nomor_bpjs_remaja = $remaja->nomor_bpjs ?? '';
        $this->nomor_telepon_remaja = $remaja->nomor_telepon ?? '';
        $this->id_users_sasaran_remaja = $remaja->id_users ?? '';

        $this->isSasaranRemajaModalOpen = true;
    }

    /**
     * Hapus data remaja
     */
    public function deleteRemaja($id)
    {
        $remaja = sasaran_remaja::findOrFail($id);
        $remaja->delete();
        $this->refreshPosyandu();
        session()->flash('message', 'Data Remaja berhasil dihapus.');
    }

    /**
     * Hitung umur otomatis ketika tanggal lahir berubah
     */
    public function updatedTanggalLahirRemaja()
    {
        if ($this->tanggal_lahir_remaja) {
            $this->umur_sasaran_remaja = Carbon::parse($this->tanggal_lahir_remaja)->age;
        } else {
            $this->umur_sasaran_remaja = '';
        }
    }
}

