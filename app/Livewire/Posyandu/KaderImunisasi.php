<?php

namespace App\Livewire\Posyandu;

use App\Livewire\SuperAdmin\Traits\ImunisasiCrud;
use App\Models\Imunisasi;
use App\Models\Kader;
use App\Models\Posyandu;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

class KaderImunisasi extends Component
{
    use ImunisasiCrud;

    public $posyandu;
    public $posyanduId;
    public $search = '';

    #[Layout('layouts.posyandudashboard')]

    public function mount()
    {
        // Ambil posyandu dari kader yang login
        $user = Auth::user();
        $kader = Kader::where('id_users', $user->id)->first();

        if (!$kader) {
            abort(403, 'Anda bukan kader terdaftar.');
        }

        $this->posyanduId = $kader->id_posyandu;
        $this->loadPosyandu();
    }

    /**
     * Load data posyandu
     */
    private function loadPosyandu()
    {
        $posyandu = Posyandu::find($this->posyanduId);

        if (!$posyandu) {
            abort(404, 'Posyandu tidak ditemukan');
        }

        $this->posyandu = $posyandu;
    }

    /**
     * Refresh data posyandu (agar Livewire view update)
     */
    protected function refreshPosyandu()
    {
        $this->loadPosyandu();
    }

    /**
     * Override openImunisasiModal untuk set posyandu otomatis
     */
    public function openImunisasiModal($id = null)
    {
        if ($id) {
            $this->editImunisasi($id);
        } else {
            $this->resetImunisasiFields();
            // Set posyandu otomatis dari kader
            $this->id_posyandu_imunisasi = $this->posyanduId;
            $this->loadSasaranList();
            $this->isImunisasiModalOpen = true;
        }
    }

    /**
     * Override storeImunisasi untuk validasi posyandu kader
     */
    public function storeImunisasi()
    {
        // Pastikan posyandu sesuai dengan kader
        $this->id_posyandu_imunisasi = $this->posyanduId;

        // Panggil method dari trait
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

    public function render()
    {
        // Ambil imunisasi hanya untuk posyandu kader ini dengan filter search
        $query = Imunisasi::where('id_posyandu', $this->posyanduId)
            ->with(['user', 'posyandu']);

        // Filter berdasarkan search
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('jenis_imunisasi', 'like', '%' . $this->search . '%')
                  ->orWhere('keterangan', 'like', '%' . $this->search . '%')
                  ->orWhere('kategori_sasaran', 'like', '%' . $this->search . '%');
            });
        }

        $imunisasiList = $query->orderBy('tanggal_imunisasi', 'desc')->get();

        return view('livewire.posyandu.kader-imunisasi', [
            'title' => 'Imunisasi - ' . $this->posyandu->nama_posyandu,
            'imunisasiList' => $imunisasiList,
            'isImunisasiModalOpen' => $this->isImunisasiModalOpen,
            'id_imunisasi' => $this->id_imunisasi,
            'posyandu' => $this->posyandu,
            'sasaranList' => $this->sasaranList,
        ]);
    }
}
