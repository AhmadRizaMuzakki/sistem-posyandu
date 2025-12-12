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
    public $alamat_sasaran_ibuhamil;
    public $kepersertaan_bpjs_ibuhamil;
    public $nomor_bpjs_ibuhamil;
    public $nomor_telepon_ibuhamil;
    
    // Field Biodata Suami
    public $nama_suami_ibuhamil;
    public $nik_suami_ibuhamil;
    public $tempat_lahir_suami_ibuhamil;
    public $tanggal_lahir_suami_ibuhamil;
    public $pekerjaan_suami_ibuhamil;

    /**
     * Buka modal tambah/edit Sasaran Ibu Hamil
     */
    public function openIbuHamilModal($id = null)
    {
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
        $this->alamat_sasaran_ibuhamil = '';
        $this->kepersertaan_bpjs_ibuhamil = '';
        $this->nomor_bpjs_ibuhamil = '';
        $this->nomor_telepon_ibuhamil = '';
        
        // Reset field suami
        $this->nama_suami_ibuhamil = '';
        $this->nik_suami_ibuhamil = '';
        $this->tempat_lahir_suami_ibuhamil = '';
        $this->tanggal_lahir_suami_ibuhamil = '';
        $this->pekerjaan_suami_ibuhamil = '';
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
            'id_posyandu' => $this->posyanduId,
            'nama_sasaran' => $this->nama_sasaran_ibuhamil,
            'nik_sasaran' => $this->nik_sasaran_ibuhamil,
            'no_kk_sasaran' => $this->no_kk_sasaran_ibuhamil ?: null,
            'tempat_lahir' => $this->tempat_lahir_ibuhamil ?: null,
            'tanggal_lahir' => $this->tanggal_lahir_ibuhamil,
            'jenis_kelamin' => $this->jenis_kelamin_ibuhamil,
            'umur_sasaran' => $umur,
            'alamat_sasaran' => $this->alamat_sasaran_ibuhamil,
            'kepersertaan_bpjs' => $this->kepersertaan_bpjs_ibuhamil ?: null,
            'nomor_bpjs' => $this->nomor_bpjs_ibuhamil ?: null,
            'nomor_telepon' => $this->nomor_telepon_ibuhamil ?: null,
            'nama_suami' => $this->nama_suami_ibuhamil ?: null,
            'nik_suami' => $this->nik_suami_ibuhamil ?: null,
            'tempat_lahir_suami' => $this->tempat_lahir_suami_ibuhamil ?: null,
            'tanggal_lahir_suami' => $this->tanggal_lahir_suami_ibuhamil ?: null,
            'pekerjaan_suami' => $this->pekerjaan_suami_ibuhamil ?: null,
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
        $ibuhamil = sasaran_ibuhamil::findOrFail($id);

        $this->id_sasaran_ibuhamil = $ibuhamil->id_sasaran_ibuhamil;
        $this->nama_sasaran_ibuhamil = $ibuhamil->nama_sasaran;
        $this->nik_sasaran_ibuhamil = $ibuhamil->nik_sasaran;
        $this->no_kk_sasaran_ibuhamil = $ibuhamil->no_kk_sasaran ?? '';
        $this->tempat_lahir_ibuhamil = $ibuhamil->tempat_lahir ?? '';
        $this->tanggal_lahir_ibuhamil = $ibuhamil->tanggal_lahir;
        $this->jenis_kelamin_ibuhamil = $ibuhamil->jenis_kelamin;
        $this->umur_sasaran_ibuhamil = $ibuhamil->tanggal_lahir 
            ? Carbon::parse($ibuhamil->tanggal_lahir)->age 
            : $ibuhamil->umur_sasaran;
        $this->alamat_sasaran_ibuhamil = $ibuhamil->alamat_sasaran ?? '';
        $this->kepersertaan_bpjs_ibuhamil = $ibuhamil->kepersertaan_bpjs ?? '';
        $this->nomor_bpjs_ibuhamil = $ibuhamil->nomor_bpjs ?? '';
        $this->nomor_telepon_ibuhamil = $ibuhamil->nomor_telepon ?? '';
        
        // Load data suami
        $this->nama_suami_ibuhamil = $ibuhamil->nama_suami ?? '';
        $this->nik_suami_ibuhamil = $ibuhamil->nik_suami ?? '';
        $this->tempat_lahir_suami_ibuhamil = $ibuhamil->tempat_lahir_suami ?? '';
        $this->tanggal_lahir_suami_ibuhamil = $ibuhamil->tanggal_lahir_suami ? $ibuhamil->tanggal_lahir_suami->format('Y-m-d') : '';
        $this->pekerjaan_suami_ibuhamil = $ibuhamil->pekerjaan_suami ?? '';

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

    /**
     * Hitung umur otomatis ketika tanggal lahir berubah
     */
    public function updatedTanggalLahirIbuhamil()
    {
        if ($this->tanggal_lahir_ibuhamil) {
            $this->umur_sasaran_ibuhamil = Carbon::parse($this->tanggal_lahir_ibuhamil)->age;
        } else {
            $this->umur_sasaran_ibuhamil = '';
        }
    }
}

