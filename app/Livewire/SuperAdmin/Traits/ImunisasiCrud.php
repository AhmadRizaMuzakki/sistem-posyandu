<?php

namespace App\Livewire\SuperAdmin\Traits;

use App\Models\Imunisasi;
use App\Models\SasaranBayibalita;
use App\Models\SasaranRemaja;
use App\Models\SasaranDewasa;
use App\Models\SasaranPralansia;
use App\Models\SasaranLansia;
use App\Models\PetugasKesehatan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
    public $hari_imunisasi;
    public $bulan_imunisasi;
    public $tahun_imunisasi;
    public $tinggi_badan;
    public $berat_badan;
    public $sistol;
    public $diastol;
    public $keterangan = '';
    public $id_petugas_kesehatan_imunisasi;

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
        $this->hari_imunisasi = '';
        $this->bulan_imunisasi = '';
        $this->tahun_imunisasi = '';
        $this->tinggi_badan = '';
        $this->berat_badan = '';
        $this->sistol = '';
        $this->diastol = '';
        $this->keterangan = '';
        $this->id_petugas_kesehatan_imunisasi = null;
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
        $bayi = SasaranBayibalita::where('id_posyandu', $this->id_posyandu_imunisasi)->get();
        foreach ($bayi as $s) {
            $sasaranList->push([
                'id' => $s->id_sasaran_bayibalita,
                'kategori' => 'bayibalita',
                'nama' => $s->nama_sasaran,
                'nik' => $s->nik_sasaran,
            ]);
        }

        $remaja = SasaranRemaja::where('id_posyandu', $this->id_posyandu_imunisasi)->get();
        foreach ($remaja as $s) {
            $sasaranList->push([
                'id' => $s->id_sasaran_remaja,
                'kategori' => 'remaja',
                'nama' => $s->nama_sasaran,
                'nik' => $s->nik_sasaran,
            ]);
        }

        $dewasa = SasaranDewasa::where('id_posyandu', $this->id_posyandu_imunisasi)->get();
        foreach ($dewasa as $s) {
            $sasaranList->push([
                'id' => $s->id_sasaran_dewasa,
                'kategori' => 'dewasa',
                'nama' => $s->nama_sasaran,
                'nik' => $s->nik_sasaran,
            ]);
        }

        $pralansia = SasaranPralansia::where('id_posyandu', $this->id_posyandu_imunisasi)->get();
        foreach ($pralansia as $s) {
            $sasaranList->push([
                'id' => $s->id_sasaran_pralansia,
                'kategori' => 'pralansia',
                'nama' => $s->nama_sasaran,
                'nik' => $s->nik_sasaran,
            ]);
        }

        $lansia = SasaranLansia::where('id_posyandu', $this->id_posyandu_imunisasi)->get();
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
     * Method ini dipanggil otomatis oleh Livewire ketika id_sasaran_imunisasi berubah
     */
    public function updatedIdSasaranImunisasi($value)
    {
        if ($value && !empty($this->sasaranList)) {
            // Cari sasaran dari list berdasarkan ID dan kategori (jika sudah ada)
            // Ini penting untuk menghindari konflik ID antar kategori
            $sasaran = null;
            
            // Jika sudah ada kategori, cari yang sesuai dengan ID dan kategori
            if ($this->kategori_sasaran_imunisasi) {
                $sasaran = collect($this->sasaranList)->first(function($s) use ($value) {
                    return $s['id'] == $value && $s['kategori'] == $this->kategori_sasaran_imunisasi;
                });
            }
            
            // Jika tidak ditemukan dengan kategori, cari berdasarkan ID saja
            if (!$sasaran) {
                $sasaran = collect($this->sasaranList)->firstWhere('id', $value);
            }
            
            if ($sasaran && isset($sasaran['kategori'])) {
                // Set kategori langsung dari list untuk menghindari konflik ID
                $this->kategori_sasaran_imunisasi = $sasaran['kategori'];
            }
        } else {
            $this->kategori_sasaran_imunisasi = '';
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
        // Gabungkan hari, bulan, tahun menjadi tanggal imunisasi
        $this->combineTanggalImunisasi();

        $this->validate([
            'id_posyandu_imunisasi' => 'required|exists:posyandu,id_posyandu',
            'id_sasaran_imunisasi' => 'required',
            'kategori_sasaran_imunisasi' => 'required|in:bayibalita,remaja,dewasa,pralansia,lansia',
            'jenis_imunisasi' => 'required|string|max:255',
            'hari_imunisasi' => 'required|numeric|min:1|max:31',
            'bulan_imunisasi' => 'required|numeric|min:1|max:12',
            'tahun_imunisasi' => 'required|numeric|min:1900|max:' . date('Y'),
            'tanggal_imunisasi' => 'required|date',
            'tinggi_badan' => 'nullable|numeric|min:0|max:300',
            'berat_badan' => 'nullable|numeric|min:0|max:300',
            'sistol' => 'nullable|numeric|min:50|max:300',
            'diastol' => 'nullable|numeric|min:30|max:200',
            'keterangan' => 'nullable|string',
            'id_petugas_kesehatan_imunisasi' => 'nullable|exists:petugas_kesehatan,id_petugas_kesehatan',
        ], [
            'id_posyandu_imunisasi.required' => 'Posyandu wajib dipilih.',
            'id_posyandu_imunisasi.exists' => 'Posyandu yang dipilih tidak valid.',
            'id_sasaran_imunisasi.required' => 'Sasaran wajib dipilih.',
            'kategori_sasaran_imunisasi.required' => 'Kategori sasaran wajib dipilih.',
            'kategori_sasaran_imunisasi.in' => 'Kategori sasaran tidak valid.',
            'jenis_imunisasi.required' => 'Jenis imunisasi wajib diisi.',
            'hari_imunisasi.required' => 'Hari imunisasi wajib diisi.',
            'hari_imunisasi.numeric' => 'Hari imunisasi harus berupa angka.',
            'hari_imunisasi.min' => 'Hari imunisasi minimal 1.',
            'hari_imunisasi.max' => 'Hari imunisasi maksimal 31.',
            'bulan_imunisasi.required' => 'Bulan imunisasi wajib diisi.',
            'bulan_imunisasi.numeric' => 'Bulan imunisasi harus berupa angka.',
            'bulan_imunisasi.min' => 'Bulan imunisasi minimal 1.',
            'bulan_imunisasi.max' => 'Bulan imunisasi maksimal 12.',
            'tahun_imunisasi.required' => 'Tahun imunisasi wajib diisi.',
            'tahun_imunisasi.numeric' => 'Tahun imunisasi harus berupa angka.',
            'tahun_imunisasi.min' => 'Tahun imunisasi minimal 1900.',
            'tahun_imunisasi.max' => 'Tahun imunisasi maksimal ' . date('Y') . '.',
            'tanggal_imunisasi.required' => 'Tanggal imunisasi wajib diisi.',
            'tanggal_imunisasi.date' => 'Tanggal imunisasi tidak valid.',
            'tinggi_badan.numeric' => 'Tinggi badan harus berupa angka.',
            'tinggi_badan.min' => 'Tinggi badan minimal 0 cm.',
            'tinggi_badan.max' => 'Tinggi badan maksimal 300 cm.',
            'berat_badan.numeric' => 'Berat badan harus berupa angka.',
            'berat_badan.min' => 'Berat badan minimal 0 kg.',
            'berat_badan.max' => 'Berat badan maksimal 300 kg.',
            'sistol.min' => 'Sistol minimal 50 mmHg.',
            'sistol.max' => 'Sistol maksimal 300 mmHg.',
            'diastol.min' => 'Diastol minimal 30 mmHg.',
            'diastol.max' => 'Diastol maksimal 200 mmHg.',
        ]);

        $data = [
            'id_posyandu' => $this->id_posyandu_imunisasi,
            'id_users' => Auth::id(),
            'id_petugas_kesehatan' => $this->id_petugas_kesehatan_imunisasi ?: null,
            'id_sasaran' => $this->id_sasaran_imunisasi,
            'kategori_sasaran' => $this->kategori_sasaran_imunisasi,
            'jenis_imunisasi' => $this->jenis_imunisasi,
            'tanggal_imunisasi' => $this->tanggal_imunisasi,
            'tinggi_badan' => $this->tinggi_badan !== '' ? $this->tinggi_badan : null,
            'berat_badan' => $this->berat_badan !== '' ? $this->berat_badan : null,
            'sistol' => $this->sistol !== '' ? (int) $this->sistol : null,
            'diastol' => $this->diastol !== '' ? (int) $this->diastol : null,
            'keterangan' => $this->keterangan,
        ];

        DB::transaction(function () use ($data) {
            if ($this->id_imunisasi) {
                // UPDATE
                $imunisasi = Imunisasi::findOrFail($this->id_imunisasi);
                $imunisasi->update($data);
            } else {
                // CREATE
                Imunisasi::create($data);
            }
        });
        
        if ($this->id_imunisasi) {
            session()->flash('message', 'Data Imunisasi berhasil diperbarui.');
        } else {
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
        
        // Load sasaran list dulu sebelum set ID dan kategori
        $this->loadSasaranList();
        
        // Set kategori DULU sebelum ID untuk menghindari konflik ID antar kategori
        $this->kategori_sasaran_imunisasi = $imunisasi->kategori_sasaran;
        
        // Set ID setelah kategori untuk memastikan updatedIdSasaranImunisasi menggunakan kategori yang benar
        $this->id_sasaran_imunisasi = $imunisasi->id_sasaran;
        
        $this->jenis_imunisasi = $imunisasi->jenis_imunisasi;
        $this->tanggal_imunisasi = $imunisasi->tanggal_imunisasi ? $imunisasi->tanggal_imunisasi->format('Y-m-d') : '';
        if ($imunisasi->tanggal_imunisasi) {
            $this->hari_imunisasi = $imunisasi->tanggal_imunisasi->day;
            $this->bulan_imunisasi = $imunisasi->tanggal_imunisasi->month;
            $this->tahun_imunisasi = $imunisasi->tanggal_imunisasi->year;
        } else {
            $this->hari_imunisasi = '';
            $this->bulan_imunisasi = '';
            $this->tahun_imunisasi = '';
        }
        $this->tinggi_badan = $imunisasi->tinggi_badan ?? '';
        $this->berat_badan = $imunisasi->berat_badan ?? '';
        $this->sistol = $imunisasi->sistol ?? '';
        $this->diastol = $imunisasi->diastol ?? '';
        $this->keterangan = $imunisasi->keterangan ?? '';
        $this->id_petugas_kesehatan_imunisasi = $imunisasi->id_petugas_kesehatan;

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

    /**
     * Combine hari, bulan, tahun menjadi tanggal imunisasi
     */
    protected function combineTanggalImunisasi(): void
    {
        if ($this->hari_imunisasi && $this->bulan_imunisasi && $this->tahun_imunisasi) {
            try {
                $this->tanggal_imunisasi = Carbon::create(
                    $this->tahun_imunisasi,
                    $this->bulan_imunisasi,
                    $this->hari_imunisasi
                )->format('Y-m-d');
            } catch (\Exception $e) {
                $this->tanggal_imunisasi = null;
            }
        } else {
            $this->tanggal_imunisasi = null;
        }
    }

    /**
     * Get list petugas kesehatan untuk posyandu
     */
    public function getPetugasKesehatanList()
    {
        // Coba ambil posyanduId dari berbagai sumber
        $posyanduId = null;
        
        // Prioritas 1: dari field id_posyandu_imunisasi
        if (isset($this->id_posyandu_imunisasi) && $this->id_posyandu_imunisasi) {
            $posyanduId = $this->id_posyandu_imunisasi;
        }
        // Prioritas 2: dari property posyanduId
        elseif (isset($this->posyanduId) && $this->posyanduId) {
            $posyanduId = $this->posyanduId;
        }
        // Prioritas 3: dari method getPosyanduId jika ada
        elseif (method_exists($this, 'getPosyanduId')) {
            $posyanduId = $this->getPosyanduId();
        }

        if (!$posyanduId) {
            return collect();
        }

        return PetugasKesehatan::where('id_posyandu', $posyanduId)
            ->orderBy('nama_petugas_kesehatan')
            ->get();
    }
}

