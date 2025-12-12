<?php

namespace App\Livewire\SuperAdmin\Traits;

use App\Models\sasaran_dewasa;
use Carbon\Carbon;

trait DewasaCrud
{
    // Modal State
    public $isSasaranDewasaModalOpen = false;

    // Field Form Sasaran Dewasa
    public $id_sasaran_dewasa = null;
    public $nama_sasaran_dewasa;
    public $nik_sasaran_dewasa;
    public $no_kk_sasaran_dewasa;
    public $tempat_lahir_dewasa;
    public $tanggal_lahir_dewasa;
    public $jenis_kelamin_dewasa;
    public $umur_sasaran_dewasa;
    public $nik_orangtua_dewasa;
    public $alamat_sasaran_dewasa;
    public $kepersertaan_bpjs_dewasa;
    public $nomor_bpjs_dewasa;
    public $nomor_telepon_dewasa;
    public $id_users_sasaran_dewasa;

    /**
     * Buka modal tambah/edit Sasaran Dewasa
     */
    public function openDewasaModal($id = null)
    {
        $this->searchUser = ''; // Reset search user
        if ($id) {
            $this->editDewasa($id);
        } else {
            $this->resetDewasaFields();
            $this->isSasaranDewasaModalOpen = true;
        }
    }

    /**
     * Tutup modal dan reset field
     */
    public function closeDewasaModal()
    {
        $this->resetDewasaFields();
        $this->searchUser = ''; // Reset search user
        $this->isSasaranDewasaModalOpen = false;
    }

    /**
     * Reset semua field form Sasaran Dewasa
     */
    private function resetDewasaFields()
    {
        $this->id_sasaran_dewasa = null;
        $this->nama_sasaran_dewasa = '';
        $this->nik_sasaran_dewasa = '';
        $this->no_kk_sasaran_dewasa = '';
        $this->tempat_lahir_dewasa = '';
        $this->tanggal_lahir_dewasa = '';
        $this->jenis_kelamin_dewasa = '';
        $this->umur_sasaran_dewasa = '';
        $this->nik_orangtua_dewasa = '';
        $this->alamat_sasaran_dewasa = '';
        $this->kepersertaan_bpjs_dewasa = '';
        $this->nomor_bpjs_dewasa = '';
        $this->nomor_telepon_dewasa = '';
        $this->id_users_sasaran_dewasa = '';
    }

    /**
     * Proses simpan data dewasa, tambah/edit
     */
    public function storeDewasa()
    {
        $this->validate([
            'nama_sasaran_dewasa' => 'required|string|max:100',
            'nik_sasaran_dewasa' => 'required|numeric',
            'tanggal_lahir_dewasa' => 'required|date',
            'jenis_kelamin_dewasa' => 'required|in:Laki-laki,Perempuan',
            'alamat_sasaran_dewasa' => 'required|string|max:225',
        ], [
            'nama_sasaran_dewasa.required' => 'Nama sasaran wajib diisi.',
            'nik_sasaran_dewasa.required' => 'NIK wajib diisi.',
            'nik_sasaran_dewasa.numeric' => 'NIK harus berupa angka.',
            'tanggal_lahir_dewasa.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir_dewasa.date' => 'Tanggal lahir harus berupa tanggal yang valid.',
            'jenis_kelamin_dewasa.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin_dewasa.in' => 'Jenis kelamin harus Laki-laki atau Perempuan.',
            'alamat_sasaran_dewasa.required' => 'Alamat wajib diisi.',
            'alamat_sasaran_dewasa.max' => 'Alamat maksimal 225 karakter.',
        ]);

        // Hitung umur dari tanggal_lahir_dewasa
        $umur = null;
        if ($this->tanggal_lahir_dewasa) {
            $umur = Carbon::parse($this->tanggal_lahir_dewasa)->age;
        }

        // Jika data berasal dari remaja atau balita, set nik_orangtua ke strip
        $nik_orangtua = $this->nik_orangtua_dewasa;
        // Cek apakah ada data di remaja atau balita dengan nik_sasaran yang sama
        $fromRemaja = \App\Models\sasaran_remaja::where('nik_sasaran', $this->nik_sasaran_dewasa)->exists();
        $fromBalita = \App\Models\Sasaran_Bayibalita::where('nik_sasaran', $this->nik_sasaran_dewasa)->exists();

        if ($fromRemaja || $fromBalita) {
            $nik_orangtua = '-';
        }

        $data = [
            'id_users' => $this->id_users_sasaran_dewasa !== '' ? $this->id_users_sasaran_dewasa : null,
            'id_posyandu' => $this->posyanduId,
            'nama_sasaran' => $this->nama_sasaran_dewasa,
            'nik_sasaran' => $this->nik_sasaran_dewasa,
            'no_kk_sasaran' => $this->no_kk_sasaran_dewasa ?: null,
            'tempat_lahir' => $this->tempat_lahir_dewasa ?: null,
            'tanggal_lahir' => $this->tanggal_lahir_dewasa,
            'jenis_kelamin' => $this->jenis_kelamin_dewasa,
            'umur_sasaran' => $umur,
            'nik_orangtua' => $nik_orangtua ?: null,
            'alamat_sasaran' => $this->alamat_sasaran_dewasa,
            'kepersertaan_bpjs' => $this->kepersertaan_bpjs_dewasa ?: null,
            'nomor_bpjs' => $this->nomor_bpjs_dewasa ?: null,
            'nomor_telepon' => $this->nomor_telepon_dewasa ?: null,
        ];

        if ($this->id_sasaran_dewasa) {
            // UPDATE
            $dewasa = sasaran_dewasa::findOrFail($this->id_sasaran_dewasa);
            $dewasa->update($data);
            session()->flash('message', 'Data Dewasa berhasil diperbarui.');
        } else {
            // CREATE
            sasaran_dewasa::create($data);
            session()->flash('message', 'Data Dewasa berhasil ditambahkan.');
        }

        $this->refreshPosyandu();
        $this->closeDewasaModal();
    }

    /**
     * Inisialisasi form edit dewasa
     */
    public function editDewasa($id)
    {
        $this->searchUser = ''; // Reset search user
        $dewasa = sasaran_dewasa::findOrFail($id);

        $this->id_sasaran_dewasa = $dewasa->id_sasaran_dewasa;
        $this->nama_sasaran_dewasa = $dewasa->nama_sasaran;
        $this->nik_sasaran_dewasa = $dewasa->nik_sasaran;
        $this->no_kk_sasaran_dewasa = $dewasa->no_kk_sasaran ?? '';
        $this->tempat_lahir_dewasa = $dewasa->tempat_lahir ?? '';
        $this->tanggal_lahir_dewasa = $dewasa->tanggal_lahir;
        $this->jenis_kelamin_dewasa = $dewasa->jenis_kelamin;
        $this->umur_sasaran_dewasa = $dewasa->tanggal_lahir
            ? Carbon::parse($dewasa->tanggal_lahir)->age
            : $dewasa->umur_sasaran;
        $this->nik_orangtua_dewasa = $dewasa->nik_orangtua ?? '';
        $this->alamat_sasaran_dewasa = $dewasa->alamat_sasaran ?? '';
        $this->kepersertaan_bpjs_dewasa = $dewasa->kepersertaan_bpjs ?? '';
        $this->nomor_bpjs_dewasa = $dewasa->nomor_bpjs ?? '';
        $this->nomor_telepon_dewasa = $dewasa->nomor_telepon ?? '';
        $this->id_users_sasaran_dewasa = $dewasa->id_users ?? '';

        $this->isSasaranDewasaModalOpen = true;
    }

    /**
     * Hapus data dewasa
     */
    public function deleteDewasa($id)
    {
        $dewasa = sasaran_dewasa::findOrFail($id);
        $dewasa->delete();
        $this->refreshPosyandu();
        session()->flash('message', 'Data Dewasa berhasil dihapus.');
    }

    /**
     * Hitung umur otomatis ketika tanggal lahir berubah
     */
    public function updatedTanggalLahirDewasa()
    {
        if ($this->tanggal_lahir_dewasa) {
            $this->umur_sasaran_dewasa = Carbon::parse($this->tanggal_lahir_dewasa)->age;
        } else {
            $this->umur_sasaran_dewasa = '';
        }
    }
}

