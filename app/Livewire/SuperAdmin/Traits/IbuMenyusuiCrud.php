<?php

namespace App\Livewire\SuperAdmin\Traits;

use App\Models\IbuMenyusui;
use App\Models\KunjunganIbuMenyusui;
use App\Models\SasaranIbuhamil;
use App\Models\SasaranDewasa;
use App\Models\SasaranPralansia;
use App\Models\SasaranLansia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

trait IbuMenyusuiCrud
{
    // Modal State
    public $isIbuMenyusuiModalOpen = false;
    public $isKunjunganModalOpen = false;
    public $isInputKunjunganModalOpen = false;

    // Field Form Ibu Menyusui
    public $id_ibu_menyusui = null;
    public $nama_ibu;
    public $nama_suami;
    public $nama_bayi;

    // Field Form Kunjungan
    public $id_kunjungan = null;
    public $id_ibu_menyusui_kunjungan;
    public $bulan_kunjungan;
    public $tahun_kunjungan;
    public $status_kunjungan;
    public $tanggal_kunjungan;

    // Field Input Kunjungan Bulk (per bulan)
    public $bulan_input_kunjungan;
    public $tahun_input_kunjungan;
    public $tanggal_agenda_kunjungan;
    public $selectedIbuMenyusui = []; // Array ID ibu menyusui yang dicentang (untuk Livewire array)

    /**
     * Buka modal tambah/edit Ibu Menyusui
     */
    public function openIbuMenyusuiModal($id = null)
    {
        if ($id) {
            $this->editIbuMenyusui($id);
        } else {
            $this->resetIbuMenyusuiFields();
            $this->isIbuMenyusuiModalOpen = true;
        }
    }

    /**
     * Tutup modal dan reset field
     */
    public function closeIbuMenyusuiModal()
    {
        $this->resetIbuMenyusuiFields();
        $this->isIbuMenyusuiModalOpen = false;
    }

    /**
     * Reset semua field form Ibu Menyusui
     */
    private function resetIbuMenyusuiFields()
    {
        $this->id_ibu_menyusui = null;
        $this->nama_ibu = '';
        $this->nama_suami = '';
        $this->nama_bayi = '';
    }

    /**
     * Proses simpan data ibu menyusui, tambah/edit
     */
    public function storeIbuMenyusui()
    {
        $this->validate([
            'nama_ibu' => 'required|string|max:100',
        ], [
            'nama_ibu.required' => 'Nama ibu wajib diisi.',
            'nama_ibu.max' => 'Nama ibu maksimal 100 karakter.',
        ]);

        // Dapatkan posyanduId dari berbagai konteks
        $posyanduId = $this->posyanduId ?? 
                     (isset($this->id_posyandu_sasaran) ? $this->id_posyandu_sasaran : null) ??
                     (method_exists($this, 'getPosyanduId') ? $this->getPosyanduId() : null);

        $data = [
            'id_posyandu' => $posyanduId,
            'nama_ibu' => $this->nama_ibu,
            'nama_suami' => $this->nama_suami ?: null,
            'nama_bayi' => $this->nama_bayi ?: null,
        ];

        DB::transaction(function () use ($data) {
            if ($this->id_ibu_menyusui) {
                // UPDATE
                $ibuMenyusui = IbuMenyusui::findOrFail($this->id_ibu_menyusui);
                $ibuMenyusui->update($data);
            } else {
                // CREATE
                IbuMenyusui::create($data);
            }
        });
        
        if ($this->id_ibu_menyusui) {
            session()->flash('message', 'Data Ibu Menyusui berhasil diperbarui.');
        } else {
            session()->flash('message', 'Data Ibu Menyusui berhasil ditambahkan.');
        }

        $this->refreshPosyandu();
        $this->closeIbuMenyusuiModal();
    }

    /**
     * Inisialisasi form edit ibu menyusui
     */
    public function editIbuMenyusui($id)
    {
        $ibuMenyusui = IbuMenyusui::findOrFail($id);

        $this->id_ibu_menyusui = $ibuMenyusui->id_ibu_menyusui;
        $this->nama_ibu = $ibuMenyusui->nama_ibu;
        $this->nama_suami = $ibuMenyusui->nama_suami ?? '';
        $this->nama_bayi = $ibuMenyusui->nama_bayi ?? '';

        $this->isIbuMenyusuiModalOpen = true;
    }

    /**
     * Hapus data ibu menyusui
     */
    public function deleteIbuMenyusui($id)
    {
        $ibuMenyusui = IbuMenyusui::findOrFail($id);
        $ibuMenyusui->delete();
        $this->refreshPosyandu();
        session()->flash('message', 'Data Ibu Menyusui berhasil dihapus.');
    }

    /**
     * Buka modal tambah/edit Kunjungan
     */
    public function openKunjunganModal($idIbuMenyusui, $idKunjungan = null, $bulan = null, $tahun = null)
    {
        $this->id_ibu_menyusui_kunjungan = $idIbuMenyusui;
        $this->bulan_kunjungan = $bulan ?? date('n');
        $this->tahun_kunjungan = $tahun ?? date('Y');
        $this->status_kunjungan = '';
        $this->tanggal_kunjungan = date('Y-m-d');

        if ($idKunjungan) {
            $kunjungan = KunjunganIbuMenyusui::findOrFail($idKunjungan);
            $this->id_kunjungan = $kunjungan->id_kunjungan;
            $this->bulan_kunjungan = $kunjungan->bulan;
            $this->tahun_kunjungan = $kunjungan->tahun;
            $this->status_kunjungan = $kunjungan->status ?? '';
            $this->tanggal_kunjungan = $kunjungan->tanggal_kunjungan ? 
                (is_string($kunjungan->tanggal_kunjungan) ? $kunjungan->tanggal_kunjungan : $kunjungan->tanggal_kunjungan->format('Y-m-d')) : '';
        } else {
            $this->id_kunjungan = null;
        }

        $this->isKunjunganModalOpen = true;
    }

    /**
     * Tutup modal kunjungan
     */
    public function closeKunjunganModal()
    {
        $this->resetKunjunganFields();
        $this->isKunjunganModalOpen = false;
    }

    /**
     * Reset field kunjungan
     */
    private function resetKunjunganFields()
    {
        $this->id_kunjungan = null;
        $this->id_ibu_menyusui_kunjungan = null;
        $this->bulan_kunjungan = date('n');
        $this->tahun_kunjungan = date('Y');
        $this->status_kunjungan = '';
        $this->tanggal_kunjungan = date('Y-m-d');
    }

    /**
     * Simpan kunjungan
     */
    public function storeKunjungan()
    {
        $this->validate([
            'id_ibu_menyusui_kunjungan' => 'required|exists:ibu_menyusuis,id_ibu_menyusui',
            'bulan_kunjungan' => 'required|integer|min:1|max:12',
            'tahun_kunjungan' => 'required|integer|min:2020|max:' . (date('Y') + 1),
            'status_kunjungan' => 'nullable|in:success',
            'tanggal_kunjungan' => 'nullable|date',
        ], [
            'id_ibu_menyusui_kunjungan.required' => 'Ibu menyusui wajib dipilih.',
            'bulan_kunjungan.required' => 'Bulan wajib diisi.',
            'bulan_kunjungan.min' => 'Bulan minimal 1.',
            'bulan_kunjungan.max' => 'Bulan maksimal 12.',
            'tahun_kunjungan.required' => 'Tahun wajib diisi.',
        ]);

        $data = [
            'id_ibu_menyusui' => $this->id_ibu_menyusui_kunjungan,
            'bulan' => $this->bulan_kunjungan,
            'tahun' => $this->tahun_kunjungan,
            'status' => $this->status_kunjungan ?: null,
            'tanggal_kunjungan' => $this->tanggal_kunjungan ?: null,
        ];

        DB::transaction(function () use ($data) {
            if ($this->id_kunjungan) {
                // UPDATE
                $kunjungan = KunjunganIbuMenyusui::findOrFail($this->id_kunjungan);
                $kunjungan->update($data);
            } else {
                // CREATE
                KunjunganIbuMenyusui::create($data);
            }
        });

        if ($this->id_kunjungan) {
            session()->flash('message', 'Data kunjungan berhasil diperbarui.');
        } else {
            session()->flash('message', 'Data kunjungan berhasil ditambahkan.');
        }

        $this->refreshPosyandu();
        $this->closeKunjunganModal();
    }

    /**
     * Hapus kunjungan
     */
    public function deleteKunjungan($id)
    {
        $kunjungan = KunjunganIbuMenyusui::findOrFail($id);
        $kunjungan->delete();
        $this->refreshPosyandu();
        session()->flash('message', 'Data kunjungan berhasil dihapus.');
    }

    /**
     * Get list bulan
     */
    public function getBulanList()
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }

    /**
     * Buka modal input kunjungan per bulan
     */
    public function openInputKunjunganModal($bulan = null, $tahun = null)
    {
        $this->bulan_input_kunjungan = $bulan ?? date('n');
        $this->tahun_input_kunjungan = $tahun ?? date('Y');
        $this->tanggal_agenda_kunjungan = date('Y-m-d');
        $this->selectedIbuMenyusui = [];
        
        $this->isInputKunjunganModalOpen = true;
        
        // Load checkbox yang sudah ada untuk bulan ini setelah modal dibuka
        $this->loadExistingKunjungan();
    }

    /**
     * Updated hook untuk bulan input kunjungan
     */
    public function updatedBulanInputKunjungan()
    {
        $this->loadExistingKunjungan();
    }

    /**
     * Updated hook untuk tahun input kunjungan
     */
    public function updatedTahunInputKunjungan()
    {
        $this->loadExistingKunjungan();
    }

    /**
     * Tutup modal input kunjungan
     */
    public function closeInputKunjunganModal()
    {
        $this->bulan_input_kunjungan = null;
        $this->tahun_input_kunjungan = null;
        $this->tanggal_agenda_kunjungan = date('Y-m-d');
        $this->selectedIbuMenyusui = [];
        $this->isInputKunjunganModalOpen = false;
    }

    /**
     * Load kunjungan yang sudah ada untuk bulan yang dipilih
     */
    public function loadExistingKunjungan()
    {
        if (!$this->bulan_input_kunjungan || !$this->tahun_input_kunjungan) {
            $this->selectedIbuMenyusui = [];
            return;
        }

        // Dapatkan posyanduId
        $posyanduId = $this->posyanduId ?? 
                     (isset($this->id_posyandu_sasaran) ? $this->id_posyandu_sasaran : null) ??
                     (method_exists($this, 'getPosyanduId') ? $this->getPosyanduId() : null);

        if (!$posyanduId) {
            $this->selectedIbuMenyusui = [];
            return;
        }

        // Ambil kunjungan yang sudah ada dengan status success
        $kunjungan = KunjunganIbuMenyusui::whereHas('ibuMenyusui', function($q) use ($posyanduId) {
            $q->where('id_posyandu', $posyanduId);
        })
        ->where('bulan', $this->bulan_input_kunjungan)
        ->where('tahun', $this->tahun_input_kunjungan)
        ->where('status', 'success')
        ->pluck('id_ibu_menyusui')
        ->map(function($id) {
            return (int)$id; // Pastikan integer untuk Livewire
        })
        ->toArray();

        $this->selectedIbuMenyusui = array_values($kunjungan); // Pastikan array dengan key numerik

        // Set tanggal agenda dari kunjungan pertama jika ada
        $firstKunjungan = KunjunganIbuMenyusui::whereHas('ibuMenyusui', function($q) use ($posyanduId) {
            $q->where('id_posyandu', $posyanduId);
        })
        ->where('bulan', $this->bulan_input_kunjungan)
        ->where('tahun', $this->tahun_input_kunjungan)
        ->whereNotNull('tanggal_kunjungan')
        ->first();

        if ($firstKunjungan && $firstKunjungan->tanggal_kunjungan) {
            $this->tanggal_agenda_kunjungan = is_string($firstKunjungan->tanggal_kunjungan) 
                ? $firstKunjungan->tanggal_kunjungan 
                : $firstKunjungan->tanggal_kunjungan->format('Y-m-d');
        } else {
            $this->tanggal_agenda_kunjungan = date('Y-m-d');
        }
    }

    /**
     * Simpan kunjungan bulk berdasarkan checkbox
     */
    public function storeBulkKunjungan()
    {
        $this->validate([
            'bulan_input_kunjungan' => 'required|integer|min:1|max:12',
            'tahun_input_kunjungan' => 'required|integer|min:2020|max:' . (date('Y') + 1),
            'tanggal_agenda_kunjungan' => 'required|date',
        ], [
            'bulan_input_kunjungan.required' => 'Bulan wajib dipilih.',
            'tahun_input_kunjungan.required' => 'Tahun wajib dipilih.',
            'tanggal_agenda_kunjungan.required' => 'Tanggal agenda wajib diisi.',
        ]);

        // Dapatkan posyanduId
        $posyanduId = $this->posyanduId ?? 
                     (isset($this->id_posyandu_sasaran) ? $this->id_posyandu_sasaran : null) ??
                     (method_exists($this, 'getPosyanduId') ? $this->getPosyanduId() : null);

        if (!$posyanduId) {
            session()->flash('error', 'Posyandu tidak ditemukan.');
            return;
        }

        // Ambil semua ibu menyusui dari posyandu ini
        $allIbuMenyusui = IbuMenyusui::where('id_posyandu', $posyanduId)->pluck('id_ibu_menyusui')->toArray();

        DB::transaction(function () use ($allIbuMenyusui, $posyanduId) {
            // Hapus semua kunjungan untuk bulan dan tahun ini yang tidak dicentang
            $ibuMenyusuiToRemove = array_diff($allIbuMenyusui, $this->selectedIbuMenyusui);
            
            if (!empty($ibuMenyusuiToRemove)) {
                KunjunganIbuMenyusui::whereIn('id_ibu_menyusui', $ibuMenyusuiToRemove)
                    ->where('bulan', $this->bulan_input_kunjungan)
                    ->where('tahun', $this->tahun_input_kunjungan)
                    ->delete();
            }

            // Simpan kunjungan untuk yang dicentang
            if (!empty($this->selectedIbuMenyusui)) {
                foreach ($this->selectedIbuMenyusui as $idIbuMenyusui) {
                    $idIbuMenyusui = (int)$idIbuMenyusui; // Pastikan integer
                    KunjunganIbuMenyusui::updateOrCreate(
                        [
                            'id_ibu_menyusui' => $idIbuMenyusui,
                            'bulan' => $this->bulan_input_kunjungan,
                            'tahun' => $this->tahun_input_kunjungan,
                        ],
                        [
                            'status' => 'success',
                            'tanggal_kunjungan' => $this->tanggal_agenda_kunjungan,
                        ]
                    );
                }
            }
        });

        session()->flash('message', 'Data kunjungan berhasil disimpan.');
        $this->refreshPosyandu();
        $this->closeInputKunjunganModal();
    }

    /**
     * Get list ibu menyusui dari sasaran
     * Data diambil dari sasaran ibu hamil, dewasa, pralansia, lansia (perempuan)
     * Method ini akan sync data dari sasaran ke tabel ibu_menyusuis
     */
    public function getIbuMenyusuiFromSasaran()
    {
        // Dapatkan posyanduId
        $posyanduId = $this->posyanduId ?? 
                     (isset($this->id_posyandu_sasaran) ? $this->id_posyandu_sasaran : null) ??
                     (method_exists($this, 'getPosyanduId') ? $this->getPosyanduId() : null);

        if (!$posyanduId) {
            return collect();
        }

        $namaIbuFromSasaran = collect();

        // Ambil nama dari sasaran ibu hamil
        $ibuHamil = SasaranIbuhamil::where('id_posyandu', $posyanduId)->get();
        foreach ($ibuHamil as $ibu) {
            $namaIbuFromSasaran->push([
                'nama_ibu' => $ibu->nama_sasaran,
                'nama_suami' => $ibu->nama_suami,
                'nama_bayi' => null,
            ]);
        }

        // Ambil nama dari sasaran dewasa (perempuan)
        $dewasa = SasaranDewasa::where('id_posyandu', $posyanduId)
            ->where('jenis_kelamin', 'Perempuan')
            ->get();
        foreach ($dewasa as $ibu) {
            $namaIbuFromSasaran->push([
                'nama_ibu' => $ibu->nama_sasaran,
                'nama_suami' => null,
                'nama_bayi' => null,
            ]);
        }

        // Ambil nama dari sasaran pralansia (perempuan)
        $pralansia = SasaranPralansia::where('id_posyandu', $posyanduId)
            ->where('jenis_kelamin', 'Perempuan')
            ->get();
        foreach ($pralansia as $ibu) {
            $namaIbuFromSasaran->push([
                'nama_ibu' => $ibu->nama_sasaran,
                'nama_suami' => null,
                'nama_bayi' => null,
            ]);
        }

        // Ambil nama dari sasaran lansia (perempuan)
        $lansia = SasaranLansia::where('id_posyandu', $posyanduId)
            ->where('jenis_kelamin', 'Perempuan')
            ->get();
        foreach ($lansia as $ibu) {
            $namaIbuFromSasaran->push([
                'nama_ibu' => $ibu->nama_sasaran,
                'nama_suami' => null,
                'nama_bayi' => null,
            ]);
        }

        // Buat atau update record di tabel ibu_menyusuis
        $ibuMenyusuiList = collect();
        $uniqueNamaIbu = $namaIbuFromSasaran->unique('nama_ibu');

        foreach ($uniqueNamaIbu as $data) {
            $ibuMenyusui = IbuMenyusui::firstOrCreate(
                [
                    'id_posyandu' => $posyanduId,
                    'nama_ibu' => $data['nama_ibu'],
                ],
                [
                    'nama_suami' => $data['nama_suami'],
                    'nama_bayi' => $data['nama_bayi'],
                ]
            );

            // Update nama suami jika ada di sasaran tapi belum ada di tabel
            if ($data['nama_suami'] && !$ibuMenyusui->nama_suami) {
                $ibuMenyusui->update(['nama_suami' => $data['nama_suami']]);
            }

            $ibuMenyusuiList->push([
                'id_ibu_menyusui' => $ibuMenyusui->id_ibu_menyusui,
                'nama_ibu' => $ibuMenyusui->nama_ibu,
                'nama_suami' => $ibuMenyusui->nama_suami,
                'nama_bayi' => $ibuMenyusui->nama_bayi,
            ]);
        }

        // Tambahkan juga yang sudah ada di tabel tapi tidak ada di sasaran
        $existingIbuMenyusui = IbuMenyusui::where('id_posyandu', $posyanduId)->get();
        foreach ($existingIbuMenyusui as $ibu) {
            $exists = $ibuMenyusuiList->firstWhere('id_ibu_menyusui', $ibu->id_ibu_menyusui);
            if (!$exists) {
                $ibuMenyusuiList->push([
                    'id_ibu_menyusui' => $ibu->id_ibu_menyusui,
                    'nama_ibu' => $ibu->nama_ibu,
                    'nama_suami' => $ibu->nama_suami,
                    'nama_bayi' => $ibu->nama_bayi,
                ]);
            }
        }

        return $ibuMenyusuiList->sortBy('nama_ibu')->values();
    }

    /**
     * Toggle kunjungan langsung dari checkbox di tabel
     */
    public function toggleKunjungan($idIbuMenyusui, $bulan, $tahun, $checked)
    {
        // Dapatkan posyanduId
        $posyanduId = $this->posyanduId ?? 
                     (isset($this->id_posyandu_sasaran) ? $this->id_posyandu_sasaran : null) ??
                     (method_exists($this, 'getPosyanduId') ? $this->getPosyanduId() : null);

        if (!$posyanduId) {
            session()->flash('error', 'Posyandu tidak ditemukan.');
            return;
        }

        // Validasi bahwa ibu menyusui ini milik posyandu yang benar
        $ibuMenyusui = IbuMenyusui::where('id_ibu_menyusui', $idIbuMenyusui)
            ->where('id_posyandu', $posyanduId)
            ->first();

        if (!$ibuMenyusui) {
            session()->flash('error', 'Data ibu menyusui tidak ditemukan.');
            return;
        }

        DB::transaction(function () use ($checked, $idIbuMenyusui, $bulan, $tahun) {
            if ($checked) {
                // Jika checkbox dicentang, buat/update kunjungan dengan status success
                KunjunganIbuMenyusui::updateOrCreate(
                    [
                        'id_ibu_menyusui' => $idIbuMenyusui,
                        'bulan' => $bulan,
                        'tahun' => $tahun,
                    ],
                    [
                        'status' => 'success',
                        'tanggal_kunjungan' => date('Y-m-d'),
                    ]
                );
                session()->flash('message', 'Kunjungan berhasil ditambahkan.');
            } else {
                // Jika checkbox tidak dicentang, hapus kunjungan
                KunjunganIbuMenyusui::where('id_ibu_menyusui', $idIbuMenyusui)
                    ->where('bulan', $bulan)
                    ->where('tahun', $tahun)
                    ->delete();
                session()->flash('message', 'Kunjungan berhasil dihapus.');
            }
        });

        $this->refreshPosyandu();
    }
}
