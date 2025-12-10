<?php

namespace App\Livewire\SuperAdmin\Traits;

use App\Models\Orangtua;
use Carbon\Carbon;

trait OrangtuaCrud
{
    // Modal State
    public $isOrangtuaModalOpen = false;

    // Field Form Orangtua
    public $nik_orangtua = null;
    public $nama_orangtua;
    public $tempat_lahir_orangtua;
    public $tanggal_lahir_orangtua;
    public $pekerjaan_orangtua;
    public $kelamin_orangtua;

    /**
     * Buka modal tambah/edit Orangtua
     */
    public function openOrangtuaModal($nik = null)
    {
        if ($nik) {
            $this->editOrangtua($nik);
        } else {
            $this->resetOrangtuaFields();
            $this->isOrangtuaModalOpen = true;
        }
    }

    /**
     * Tutup modal dan reset field
     */
    public function closeOrangtuaModal()
    {
        $this->resetOrangtuaFields();
        $this->isOrangtuaModalOpen = false;
    }

    /**
     * Reset semua field form Orangtua
     */
    private function resetOrangtuaFields()
    {
        $this->nik_orangtua = null;
        $this->nama_orangtua = '';
        $this->tempat_lahir_orangtua = '';
        $this->tanggal_lahir_orangtua = '';
        $this->pekerjaan_orangtua = '';
        $this->kelamin_orangtua = '';
    }

    /**
     * Proses simpan data orangtua, tambah/edit
     */
    public function storeOrangtua()
    {
        $this->validate([
            'nik_orangtua' => 'required|numeric|unique:orangtua,nik' . ($this->nik_orangtua ? ',' . $this->nik_orangtua . ',nik' : ''),
            'nama_orangtua' => 'required|string|max:100',
            'tempat_lahir_orangtua' => 'required|string|max:50',
            'tanggal_lahir_orangtua' => 'required|date',
            'kelamin_orangtua' => 'required|in:Laki-laki,Perempuan',
        ], [
            'nik_orangtua.required' => 'NIK wajib diisi.',
            'nik_orangtua.numeric' => 'NIK harus berupa angka.',
            'nik_orangtua.unique' => 'NIK sudah terdaftar.',
            'nama_orangtua.required' => 'Nama wajib diisi.',
            'nama_orangtua.max' => 'Nama maksimal 100 karakter.',
            'tempat_lahir_orangtua.required' => 'Tempat lahir wajib diisi.',
            'tempat_lahir_orangtua.max' => 'Tempat lahir maksimal 50 karakter.',
            'tanggal_lahir_orangtua.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir_orangtua.date' => 'Tanggal lahir harus berupa tanggal yang valid.',
            'kelamin_orangtua.required' => 'Jenis kelamin wajib dipilih.',
            'kelamin_orangtua.in' => 'Jenis kelamin harus Laki-laki atau Perempuan.',
        ]);

        $data = [
            'nik' => $this->nik_orangtua,
            'nama' => $this->nama_orangtua,
            'tempat_lahir' => $this->tempat_lahir_orangtua,
            'tanggal_lahir' => $this->tanggal_lahir_orangtua,
            'pekerjaan' => $this->pekerjaan_orangtua ?: null,
            'kelamin' => $this->kelamin_orangtua,
        ];

        if ($this->nik_orangtua) {
            // UPDATE - find by nik since that's the primary key
            $orangtua = Orangtua::findOrFail($this->nik_orangtua);
            $orangtua->update($data);
            session()->flash('message', 'Data Orangtua berhasil diperbarui.');
        } else {
            // CREATE
            Orangtua::create($data);
            session()->flash('message', 'Data Orangtua berhasil ditambahkan.');
        }

        $this->refreshPosyandu();
        $this->closeOrangtuaModal();
    }

    /**
     * Inisialisasi form edit orangtua
     */
    public function editOrangtua($nik)
    {
        $orangtua = Orangtua::findOrFail($nik);

        $this->nik_orangtua = $orangtua->nik;
        $this->nama_orangtua = $orangtua->nama;
        $this->tempat_lahir_orangtua = $orangtua->tempat_lahir ?? '';
        $this->tanggal_lahir_orangtua = $orangtua->tanggal_lahir ? $orangtua->tanggal_lahir->format('Y-m-d') : '';
        $this->pekerjaan_orangtua = $orangtua->pekerjaan ?? '';
        $this->kelamin_orangtua = $orangtua->kelamin;

        $this->isOrangtuaModalOpen = true;
    }

    /**
     * Hapus data orangtua
     */
    public function deleteOrangtua($nik)
    {
        $orangtua = Orangtua::findOrFail($nik);
        $orangtua->delete();
        $this->refreshPosyandu();
        session()->flash('message', 'Data Orangtua berhasil dihapus.');
    }
}
