<?php

namespace App\Livewire\SuperAdmin\Traits;

use App\Models\Imunisasi;
use App\Models\Sasaran_Bayibalita;
use App\Models\sasaran_remaja;
use App\Models\sasaran_dewasa;
use App\Models\sasaran_pralansia;
use App\Models\sasaran_lansia;
use Illuminate\Support\Facades\Auth;

trait ImunisasiCrud
{
    // Modal State
    public $isImunisasiModalOpen = false;

    // Field Form Imunisasi
    public $id_imunisasi = null;
    public $id_posyandu_imunisasi;
    public $id_sasaran_imunisasi;
    public $kategori_sasaran_imunisasi = '';
    public $jenis_imunisasi = '';
    public $tanggal_imunisasi = '';
    public $keterangan = '';

    // Untuk dropdown sasaran
    public $sasaranList = [];

    /**
     * Buka modal tambah/edit Imunisasi
     */
    public function openImunisasiModal($id = null)
    {
        if ($id) {
            $this->editImunisasi($id);
        } else {
            $this->resetImunisasiFields();
            // Pre-fill dengan posyandu saat ini
            if (isset($this->posyanduId)) {
                $this->id_posyandu_imunisasi = $this->posyanduId;
                $this->loadSasaranList();
            }
            $this->isImunisasiModalOpen = true;
        }
    }

    /**
     * Tutup modal dan reset field
     */
    public function closeImunisasiModal()
    {
        $this->resetImunisasiFields();
        $this->isImunisasiModalOpen = false;
    }

    /**
     * Reset semua field form Imunisasi
     */
    private function resetImunisasiFields()
    {
        $this->id_imunisasi = null;
        $this->id_posyandu_imunisasi = '';
        $this->id_sasaran_imunisasi = '';
        $this->kategori_sasaran_imunisasi = '';
        $this->jenis_imunisasi = '';
        $this->tanggal_imunisasi = '';
        $this->keterangan = '';
        $this->sasaranList = [];
    }

    /**
     * Load sasaran list berdasarkan posyandu dan kategori
     */
    public function loadSasaranList()
    {
        if (!$this->id_posyandu_imunisasi) {
            $this->sasaranList = [];
            return;
        }

        $sasaranList = collect();

        // Ambil sasaran dari semua kategori
        $bayi = Sasaran_Bayibalita::where('id_posyandu', $this->id_posyandu_imunisasi)->get();
        foreach ($bayi as $s) {
            $sasaranList->push([
                'id' => $s->id_sasaran_bayibalita,
                'kategori' => 'bayibalita',
                'nama' => $s->nama_sasaran,
                'nik' => $s->nik_sasaran,
            ]);
        }

        $remaja = sasaran_remaja::where('id_posyandu', $this->id_posyandu_imunisasi)->get();
        foreach ($remaja as $s) {
            $sasaranList->push([
                'id' => $s->id_sasaran_remaja,
                'kategori' => 'remaja',
                'nama' => $s->nama_sasaran,
                'nik' => $s->nik_sasaran,
            ]);
        }

        $dewasa = sasaran_dewasa::where('id_posyandu', $this->id_posyandu_imunisasi)->get();
        foreach ($dewasa as $s) {
            $sasaranList->push([
                'id' => $s->id_sasaran_dewasa,
                'kategori' => 'dewasa',
                'nama' => $s->nama_sasaran,
                'nik' => $s->nik_sasaran,
            ]);
        }

        $pralansia = sasaran_pralansia::where('id_posyandu', $this->id_posyandu_imunisasi)->get();
        foreach ($pralansia as $s) {
            $sasaranList->push([
                'id' => $s->id_sasaran_pralansia,
                'kategori' => 'pralansia',
                'nama' => $s->nama_sasaran,
                'nik' => $s->nik_sasaran,
            ]);
        }

        $lansia = sasaran_lansia::where('id_posyandu', $this->id_posyandu_imunisasi)->get();
        foreach ($lansia as $s) {
            $sasaranList->push([
                'id' => $s->id_sasaran_lansia,
                'kategori' => 'lansia',
                'nama' => $s->nama_sasaran,
                'nik' => $s->nik_sasaran,
            ]);
        }

        $this->sasaranList = $sasaranList->toArray();
    }

    /**
     * Update kategori saat sasaran dipilih
     */
    public function updatedIdSasaranImunisasi($value)
    {
        if ($value) {
            $sasaran = collect($this->sasaranList)->firstWhere('id', $value);
            if ($sasaran) {
                $this->kategori_sasaran_imunisasi = $sasaran['kategori'];
            }
        }
    }

    /**
     * Update sasaran list saat posyandu berubah
     */
    public function updatedIdPosyanduImunisasi()
    {
        $this->id_sasaran_imunisasi = '';
        $this->kategori_sasaran_imunisasi = '';
        $this->loadSasaranList();
    }

    /**
     * Proses simpan data imunisasi, tambah/edit
     */
    public function storeImunisasi()
    {
        $this->validate([
            'id_posyandu_imunisasi' => 'required|exists:posyandu,id_posyandu',
            'id_sasaran_imunisasi' => 'required',
            'kategori_sasaran_imunisasi' => 'required|in:bayibalita,remaja,dewasa,pralansia,lansia',
            'jenis_imunisasi' => 'required|string|max:255',
            'tanggal_imunisasi' => 'required|date',
            'keterangan' => 'nullable|string',
        ], [
            'id_posyandu_imunisasi.required' => 'Posyandu wajib dipilih.',
            'id_posyandu_imunisasi.exists' => 'Posyandu yang dipilih tidak valid.',
            'id_sasaran_imunisasi.required' => 'Sasaran wajib dipilih.',
            'kategori_sasaran_imunisasi.required' => 'Kategori sasaran wajib dipilih.',
            'kategori_sasaran_imunisasi.in' => 'Kategori sasaran tidak valid.',
            'jenis_imunisasi.required' => 'Jenis imunisasi wajib diisi.',
            'tanggal_imunisasi.required' => 'Tanggal imunisasi wajib diisi.',
            'tanggal_imunisasi.date' => 'Tanggal imunisasi tidak valid.',
        ]);

        $data = [
            'id_posyandu' => $this->id_posyandu_imunisasi,
            'id_users' => Auth::id(),
            'id_sasaran' => $this->id_sasaran_imunisasi,
            'kategori_sasaran' => $this->kategori_sasaran_imunisasi,
            'jenis_imunisasi' => $this->jenis_imunisasi,
            'tanggal_imunisasi' => $this->tanggal_imunisasi,
            'keterangan' => $this->keterangan,
        ];

        if ($this->id_imunisasi) {
            // UPDATE
            $imunisasi = Imunisasi::findOrFail($this->id_imunisasi);
            $imunisasi->update($data);
            session()->flash('message', 'Data Imunisasi berhasil diperbarui.');
        } else {
            // CREATE
            Imunisasi::create($data);
            session()->flash('message', 'Data Imunisasi berhasil ditambahkan.');
        }

        $this->refreshPosyandu();
        $this->closeImunisasiModal();
    }

    /**
     * Inisialisasi form edit imunisasi
     */
    public function editImunisasi($id)
    {
        $imunisasi = Imunisasi::findOrFail($id);

        $this->id_imunisasi = $imunisasi->id_imunisasi;
        $this->id_posyandu_imunisasi = $imunisasi->id_posyandu;
        $this->id_sasaran_imunisasi = $imunisasi->id_sasaran;
        $this->kategori_sasaran_imunisasi = $imunisasi->kategori_sasaran;
        $this->jenis_imunisasi = $imunisasi->jenis_imunisasi;
        $this->tanggal_imunisasi = $imunisasi->tanggal_imunisasi ? $imunisasi->tanggal_imunisasi->format('Y-m-d') : '';
        $this->keterangan = $imunisasi->keterangan ?? '';

        $this->loadSasaranList();
        $this->isImunisasiModalOpen = true;
    }

    /**
     * Hapus data imunisasi
     */
    public function deleteImunisasi($id)
    {
        $imunisasi = Imunisasi::findOrFail($id);
        $imunisasi->delete();

        $this->refreshPosyandu();
        session()->flash('message', 'Data Imunisasi berhasil dihapus.');
    }
}

