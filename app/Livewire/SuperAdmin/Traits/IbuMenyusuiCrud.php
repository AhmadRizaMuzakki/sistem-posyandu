<?php

namespace App\Livewire\SuperAdmin\Traits;

use App\Models\IbuMenyusui;
use App\Models\KunjunganIbuMenyusui;
use App\Models\SasaranIbuhamil;
use App\Models\SasaranDewasa;
use App\Models\SasaranPralansia;
use App\Models\SasaranLansia;
use App\Models\SasaranBayibalita;
use App\Models\PetugasKesehatan;
use App\Models\Kader;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

trait IbuMenyusuiCrud
{
    // Modal State
    public $isIbuMenyusuiModalOpen = false;
    public $isKunjunganModalOpen = false;

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
    public $id_petugas_penanggung_jawab;
    public $id_petugas_imunisasi;
    public $id_petugas_input;

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
            $this->id_petugas_penanggung_jawab = $kunjungan->id_petugas_penanggung_jawab;
            $this->id_petugas_imunisasi = $kunjungan->id_petugas_imunisasi;
            $this->id_petugas_input = $kunjungan->id_petugas_input;
        } else {
            $this->id_kunjungan = null;
            $this->id_petugas_penanggung_jawab = $this->getPetugasIdFromKaderJabatan('Ketua');
            $this->id_petugas_imunisasi = null;
            $this->id_petugas_input = $this->getPetugasIdFromKaderJabatan('Sekretaris');
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
        $this->id_petugas_penanggung_jawab = null;
        $this->id_petugas_imunisasi = null;
        $this->id_petugas_input = null;
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
            'id_petugas_penanggung_jawab' => 'nullable|exists:petugas_kesehatan,id_petugas_kesehatan',
            'id_petugas_imunisasi' => 'nullable|exists:petugas_kesehatan,id_petugas_kesehatan',
            'id_petugas_input' => 'nullable|exists:petugas_kesehatan,id_petugas_kesehatan',
        ], [
            'id_ibu_menyusui_kunjungan.required' => 'Ibu menyusui wajib dipilih.',
            'bulan_kunjungan.required' => 'Bulan wajib diisi.',
            'bulan_kunjungan.min' => 'Bulan minimal 1.',
            'bulan_kunjungan.max' => 'Bulan maksimal 12.',
            'tahun_kunjungan.required' => 'Tahun wajib diisi.',
            'id_petugas_penanggung_jawab.exists' => 'Petugas penanggung jawab tidak ditemukan.',
            'id_petugas_imunisasi.exists' => 'Petugas imunisasi tidak ditemukan.',
            'id_petugas_input.exists' => 'Petugas input tidak ditemukan.',
        ]);

        $data = [
            'id_ibu_menyusui' => $this->id_ibu_menyusui_kunjungan,
            'bulan' => $this->bulan_kunjungan,
            'tahun' => $this->tahun_kunjungan,
            'status' => $this->status_kunjungan ?: null,
            'tanggal_kunjungan' => $this->tanggal_kunjungan ?: null,
            'id_petugas_penanggung_jawab' => $this->id_petugas_penanggung_jawab ?: null,
            'id_petugas_imunisasi' => $this->id_petugas_imunisasi ?: null,
            'id_petugas_input' => $this->id_petugas_input ?: null,
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
     * Get list ibu menyusui dari sasaran balita
     * Data diambil dari sasaran balita, nama ibu dan suami diambil dari susunan keluarga berdasarkan KK
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

        $ibuMenyusuiList = collect();

        // Ambil semua sasaran balita dari posyandu ini
        $balitaList = SasaranBayibalita::where('id_posyandu', $posyanduId)
            ->whereNotNull('no_kk_sasaran')
            ->get();

        foreach ($balitaList as $balita) {
            $noKk = $balita->no_kk_sasaran;
            $namaBayi = $balita->nama_sasaran;
            
            // Cari nama ibu dari susunan keluarga (perempuan dengan status 'istri' atau 'kepala keluarga')
            $namaIbu = null;
            $namaSuami = null;

            // Cari ibu dari sasaran dewasa (perempuan dengan status 'istri' atau 'kepala keluarga')
            $ibuDewasa = SasaranDewasa::where('id_posyandu', $posyanduId)
                ->where('no_kk_sasaran', $noKk)
                ->where('jenis_kelamin', 'Perempuan')
                ->whereIn('status_keluarga', ['istri', 'kepala keluarga'])
                ->first();
            
            if ($ibuDewasa) {
                $namaIbu = $ibuDewasa->nama_sasaran;
            } else {
                // Jika tidak ada di dewasa, cari di pralansia
                $ibuPralansia = SasaranPralansia::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->where('jenis_kelamin', 'Perempuan')
                    ->whereIn('status_keluarga', ['istri', 'kepala keluarga'])
                    ->first();
                
                if ($ibuPralansia) {
                    $namaIbu = $ibuPralansia->nama_sasaran;
                } else {
                    // Jika tidak ada di pralansia, cari di lansia
                    $ibuLansia = SasaranLansia::where('id_posyandu', $posyanduId)
                        ->where('no_kk_sasaran', $noKk)
                        ->where('jenis_kelamin', 'Perempuan')
                        ->whereIn('status_keluarga', ['istri', 'kepala keluarga'])
                        ->first();
                    
                    if ($ibuLansia) {
                        $namaIbu = $ibuLansia->nama_sasaran;
                    }
                }
            }

            // Cari suami dari susunan keluarga (laki-laki dengan status 'kepala keluarga')
            $suamiDewasa = SasaranDewasa::where('id_posyandu', $posyanduId)
                ->where('no_kk_sasaran', $noKk)
                ->where('jenis_kelamin', 'Laki-laki')
                ->where('status_keluarga', 'kepala keluarga')
                ->first();
            
            if ($suamiDewasa) {
                $namaSuami = $suamiDewasa->nama_sasaran;
            } else {
                // Jika tidak ada di dewasa, cari di pralansia
                $suamiPralansia = SasaranPralansia::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->where('jenis_kelamin', 'Laki-laki')
                    ->where('status_keluarga', 'kepala keluarga')
                    ->first();
                
                if ($suamiPralansia) {
                    $namaSuami = $suamiPralansia->nama_sasaran;
                } else {
                    // Jika tidak ada di pralansia, cari di lansia
                    $suamiLansia = SasaranLansia::where('id_posyandu', $posyanduId)
                        ->where('no_kk_sasaran', $noKk)
                        ->where('jenis_kelamin', 'Laki-laki')
                        ->where('status_keluarga', 'kepala keluarga')
                        ->first();
                    
                    if ($suamiLansia) {
                        $namaSuami = $suamiLansia->nama_sasaran;
                    }
                }
            }

        // Jika ditemukan nama ibu, buat atau update record di tabel ibu_menyusuis
        if ($namaIbu) {
            $ibuMenyusui = IbuMenyusui::updateOrCreate(
                [
                    'id_posyandu' => $posyanduId,
                    'nama_bayi' => $namaBayi,
                ],
                [
                    'nama_ibu' => $namaIbu,
                    'nama_suami' => $namaSuami,
                ]
            );

            // Jangan auto-buat kunjungan di sini. Absensi (hadir/tidak hadir) hanya diisi
            // lewat checkbox di tabel atau modal "Input Kunjungan Per Bulan", agar yang
            // tidak hadir tetap tampil unchecked (tidak hadir).

            $ibuMenyusuiList->push([
                'id_ibu_menyusui' => $ibuMenyusui->id_ibu_menyusui,
                'nama_ibu' => $ibuMenyusui->nama_ibu,
                'nama_suami' => $ibuMenyusui->nama_suami,
                'nama_bayi' => $ibuMenyusui->nama_bayi,
            ]);
        }
        }

        // Tambahkan juga yang sudah ada di tabel tapi tidak ada di sasaran balita
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

        return $ibuMenyusuiList->sortBy('nama_bayi')->values();
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

    /**
     * Sync ibu menyusui dari sasaran balita berdasarkan KK
     * Dipanggil setelah balita disimpan untuk auto-create/update ibu_menyusui
     */
    public function syncIbuMenyusuiFromBalita($balitaId, $posyanduId)
    {
        $balita = SasaranBayibalita::find($balitaId);
        if (!$balita || !$balita->no_kk_sasaran) {
            return;
        }

        $noKk = $balita->no_kk_sasaran;
        $namaBayi = $balita->nama_sasaran;
        
        // Cari nama ibu dari susunan keluarga (perempuan dengan status 'istri' atau 'kepala keluarga')
        $namaIbu = null;
        $namaSuami = null;

        // Cari ibu dari sasaran dewasa (perempuan dengan status 'istri' atau 'kepala keluarga')
        $ibuDewasa = SasaranDewasa::where('id_posyandu', $posyanduId)
            ->where('no_kk_sasaran', $noKk)
            ->where('jenis_kelamin', 'Perempuan')
            ->whereIn('status_keluarga', ['istri', 'kepala keluarga'])
            ->first();
        
        if ($ibuDewasa) {
            $namaIbu = $ibuDewasa->nama_sasaran;
        } else {
            // Jika tidak ada di dewasa, cari di pralansia
            $ibuPralansia = SasaranPralansia::where('id_posyandu', $posyanduId)
                ->where('no_kk_sasaran', $noKk)
                ->where('jenis_kelamin', 'Perempuan')
                ->whereIn('status_keluarga', ['istri', 'kepala keluarga'])
                ->first();
            
            if ($ibuPralansia) {
                $namaIbu = $ibuPralansia->nama_sasaran;
            } else {
                // Jika tidak ada di pralansia, cari di lansia
                $ibuLansia = SasaranLansia::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->where('jenis_kelamin', 'Perempuan')
                    ->whereIn('status_keluarga', ['istri', 'kepala keluarga'])
                    ->first();
                
                if ($ibuLansia) {
                    $namaIbu = $ibuLansia->nama_sasaran;
                }
            }
        }

        // Cari suami dari susunan keluarga (laki-laki dengan status 'kepala keluarga')
        $suamiDewasa = SasaranDewasa::where('id_posyandu', $posyanduId)
            ->where('no_kk_sasaran', $noKk)
            ->where('jenis_kelamin', 'Laki-laki')
            ->where('status_keluarga', 'kepala keluarga')
            ->first();
        
        if ($suamiDewasa) {
            $namaSuami = $suamiDewasa->nama_sasaran;
        } else {
            // Jika tidak ada di dewasa, cari di pralansia
            $suamiPralansia = SasaranPralansia::where('id_posyandu', $posyanduId)
                ->where('no_kk_sasaran', $noKk)
                ->where('jenis_kelamin', 'Laki-laki')
                ->where('status_keluarga', 'kepala keluarga')
                ->first();
            
            if ($suamiPralansia) {
                $namaSuami = $suamiPralansia->nama_sasaran;
            } else {
                // Jika tidak ada di pralansia, cari di lansia
                $suamiLansia = SasaranLansia::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->where('jenis_kelamin', 'Laki-laki')
                    ->where('status_keluarga', 'kepala keluarga')
                    ->first();
                
                if ($suamiLansia) {
                    $namaSuami = $suamiLansia->nama_sasaran;
                }
            }
        }

        // Jika ditemukan nama ibu, buat atau update record di tabel ibu_menyusuis
        if ($namaIbu) {
            $ibuMenyusui = IbuMenyusui::updateOrCreate(
                [
                    'id_posyandu' => $posyanduId,
                    'nama_bayi' => $namaBayi,
                ],
                [
                    'nama_ibu' => $namaIbu,
                    'nama_suami' => $namaSuami,
                ]
            );

            // Jangan auto-buat kunjungan. Absensi hanya diisi lewat checkbox atau modal.
        }
    }

    /**
     * Get list petugas kesehatan untuk posyandu
     */
    public function getPetugasKesehatanList()
    {
        $posyanduId = $this->posyanduId ?? 
                     (isset($this->id_posyandu_sasaran) ? $this->id_posyandu_sasaran : null) ??
                     (method_exists($this, 'getPosyanduId') ? $this->getPosyanduId() : null);

        if (!$posyanduId) {
            return collect();
        }

        return PetugasKesehatan::where('id_posyandu', $posyanduId)
            ->orderBy('nama_petugas_kesehatan')
            ->get();
    }

    /**
     * Ambil id_petugas_kesehatan dari kader dengan jabatan tertentu (Ketua/Sekretaris).
     * Cocokkan via id_users atau nik.
     */
    private function getPetugasIdFromKaderJabatan(string $jabatan): ?int
    {
        $posyanduId = $this->posyanduId ?? 
                     (isset($this->id_posyandu_sasaran) ? $this->id_posyandu_sasaran : null) ??
                     (method_exists($this, 'getPosyanduId') ? $this->getPosyanduId() : null);

        if (!$posyanduId) {
            return null;
        }

        $kader = Kader::where('id_posyandu', $posyanduId)
            ->where('jabatan_kader', $jabatan)
            ->first();

        if (!$kader) {
            return null;
        }

        // Cari PetugasKesehatan yang sama: via id_users atau nik
        $petugas = null;
        if ($kader->id_users) {
            $petugas = PetugasKesehatan::where('id_posyandu', $posyanduId)
                ->where('id_users', $kader->id_users)
                ->first();
        }
        if (!$petugas && $kader->nik_kader) {
            $petugas = PetugasKesehatan::where('id_posyandu', $posyanduId)
                ->where('nik_petugas_kesehatan', $kader->nik_kader)
                ->first();
        }
        if (!$petugas && $kader->nama_kader) {
            $petugas = PetugasKesehatan::where('id_posyandu', $posyanduId)
                ->where('nama_petugas_kesehatan', $kader->nama_kader)
                ->first();
        }

        return $petugas?->id_petugas_kesehatan;
    }
}
