<?php

namespace App\Livewire\SuperAdmin\Traits;

use App\Models\SasaranIbuhamil;
use App\Models\SasaranDewasa;
use App\Models\SasaranPralansia;
use App\Models\SasaranLansia;
use Illuminate\Support\Facades\DB;
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
    public $hari_lahir_ibuhamil;
    public $bulan_lahir_ibuhamil;
    public $tahun_lahir_ibuhamil;
    public $jenis_kelamin_ibuhamil;
    public $status_keluarga_ibuhamil;
    public $umur_sasaran_ibuhamil;
    public $minggu_kandungan_ibuhamil;
    public $pekerjaan_ibuhamil;
    public $pendidikan_ibuhamil;
    public $alamat_sasaran_ibuhamil;
    public $rt_ibuhamil;
    public $rw_ibuhamil;
    public $kepersertaan_bpjs_ibuhamil;
    public $nomor_bpjs_ibuhamil;
    public $nomor_telepon_ibuhamil;

    // Field Biodata Suami
    public $nama_suami_ibuhamil;
    public $nik_suami_ibuhamil;
    public $tempat_lahir_suami_ibuhamil;
    public $tanggal_lahir_suami_ibuhamil;
    public $pekerjaan_suami_ibuhamil;
    public $status_keluarga_suami_ibuhamil;
    public $hari_lahir_suami_ibuhamil;
    public $bulan_lahir_suami_ibuhamil;
    public $tahun_lahir_suami_ibuhamil;

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
        $this->hari_lahir_ibuhamil = '';
        $this->bulan_lahir_ibuhamil = '';
        $this->tahun_lahir_ibuhamil = '';
        $this->jenis_kelamin_ibuhamil = '';
        $this->status_keluarga_ibuhamil = '';
        $this->umur_sasaran_ibuhamil = '';
        $this->minggu_kandungan_ibuhamil = '';
        $this->pekerjaan_ibuhamil = '';
        $this->pendidikan_ibuhamil = '';
        $this->alamat_sasaran_ibuhamil = '';
        $this->rt_ibuhamil = '';
        $this->rw_ibuhamil = '';
        $this->kepersertaan_bpjs_ibuhamil = '';
        $this->nomor_bpjs_ibuhamil = '';
        $this->nomor_telepon_ibuhamil = '';

        // Reset field suami
        $this->nama_suami_ibuhamil = '';
        $this->nik_suami_ibuhamil = '';
        $this->tempat_lahir_suami_ibuhamil = '';
        $this->tanggal_lahir_suami_ibuhamil = '';
        $this->pekerjaan_suami_ibuhamil = '';
        $this->status_keluarga_suami_ibuhamil = '';
        $this->hari_lahir_suami_ibuhamil = '';
        $this->bulan_lahir_suami_ibuhamil = '';
        $this->tahun_lahir_suami_ibuhamil = '';
    }

    /**
     * Proses simpan data ibu hamil, tambah/edit
     */
    public function storeIbuHamil()
    {
        // Combine hari, bulan, tahun menjadi tanggal lahir sebelum validasi
        $this->combineTanggalLahirIbuhamil();
        $this->combineTanggalLahirSuamiIbuhamil();

        $this->validate([
            'nama_sasaran_ibuhamil' => 'required|string|max:100',
            'nik_sasaran_ibuhamil' => 'required|numeric',
            'hari_lahir_ibuhamil' => 'required|numeric|min:1|max:31',
            'bulan_lahir_ibuhamil' => 'required|numeric|min:1|max:12',
            'tahun_lahir_ibuhamil' => 'required|numeric|min:1900|max:' . date('Y'),
            'tanggal_lahir_ibuhamil' => 'required|date',
            'jenis_kelamin_ibuhamil' => 'required|in:Laki-laki,Perempuan',
            'status_keluarga_ibuhamil' => 'nullable|in:kepala keluarga,istri,anak',
            'alamat_sasaran_ibuhamil' => 'required|string|max:225',
        ], [
            'nama_sasaran_ibuhamil.required' => 'Nama sasaran wajib diisi.',
            'nik_sasaran_ibuhamil.required' => 'NIK wajib diisi.',
            'nik_sasaran_ibuhamil.numeric' => 'NIK harus berupa angka.',
            'hari_lahir_ibuhamil.required' => 'Hari lahir wajib diisi.',
            'hari_lahir_ibuhamil.numeric' => 'Hari harus berupa angka.',
            'hari_lahir_ibuhamil.min' => 'Hari minimal 1.',
            'hari_lahir_ibuhamil.max' => 'Hari maksimal 31.',
            'bulan_lahir_ibuhamil.required' => 'Bulan lahir wajib diisi.',
            'bulan_lahir_ibuhamil.numeric' => 'Bulan harus berupa angka.',
            'bulan_lahir_ibuhamil.min' => 'Bulan minimal 1.',
            'bulan_lahir_ibuhamil.max' => 'Bulan maksimal 12.',
            'tahun_lahir_ibuhamil.required' => 'Tahun lahir wajib diisi.',
            'tahun_lahir_ibuhamil.numeric' => 'Tahun harus berupa angka.',
            'tahun_lahir_ibuhamil.min' => 'Tahun minimal 1900.',
            'tahun_lahir_ibuhamil.max' => 'Tahun maksimal ' . date('Y') . '.',
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

        // Dapatkan posyanduId dari berbagai konteks
        $posyanduId = $this->posyanduId ?? 
                     (isset($this->id_posyandu_sasaran) ? $this->id_posyandu_sasaran : null) ??
                     (method_exists($this, 'getPosyanduId') ? $this->getPosyanduId() : null);

        $data = [
            'id_posyandu' => $posyanduId,
            'nama_sasaran' => $this->nama_sasaran_ibuhamil,
            'nik_sasaran' => $this->nik_sasaran_ibuhamil,
            'no_kk_sasaran' => $this->no_kk_sasaran_ibuhamil ?: null,
            'tempat_lahir' => $this->tempat_lahir_ibuhamil ?: null,
            'tanggal_lahir' => $this->tanggal_lahir_ibuhamil,
            'jenis_kelamin' => $this->jenis_kelamin_ibuhamil,
            'status_keluarga' => $this->status_keluarga_ibuhamil ?: null,
            'umur_sasaran' => $umur,
            'minggu_kandungan' => $this->minggu_kandungan_ibuhamil ?: null,
            'pekerjaan' => $this->pekerjaan_ibuhamil ?: null,
            'pendidikan' => $this->pendidikan_ibuhamil ?: null,
            'alamat_sasaran' => $this->alamat_sasaran_ibuhamil,
            'rt' => $this->rt_ibuhamil ?: null,
            'rw' => $this->rw_ibuhamil ?: null,
            'kepersertaan_bpjs' => $this->kepersertaan_bpjs_ibuhamil ?: null,
            'nomor_bpjs' => $this->nomor_bpjs_ibuhamil ?: null,
            'nomor_telepon' => $this->nomor_telepon_ibuhamil ?: null,
            'nama_suami' => $this->nama_suami_ibuhamil ?: null,
            'nik_suami' => $this->nik_suami_ibuhamil ?: null,
            'tempat_lahir_suami' => $this->tempat_lahir_suami_ibuhamil ?: null,
            'tanggal_lahir_suami' => $this->tanggal_lahir_suami_ibuhamil ?: null,
            'pekerjaan_suami' => $this->pekerjaan_suami_ibuhamil ?: null,
            'status_keluarga_suami' => $this->status_keluarga_suami_ibuhamil ?: null,
        ];

        DB::transaction(function () use ($data) {
            if ($this->id_sasaran_ibuhamil) {
                // UPDATE
                $ibuhamil = SasaranIbuhamil::findOrFail($this->id_sasaran_ibuhamil);
                $ibuhamil->update($data);
            } else {
                // CREATE
                SasaranIbuhamil::create($data);
            }
        });
        
        if ($this->id_sasaran_ibuhamil) {
            session()->flash('message', 'Data Ibu Hamil berhasil diperbarui.');
        } else {
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
        $ibuhamil = SasaranIbuhamil::findOrFail($id);

        $this->id_sasaran_ibuhamil = $ibuhamil->id_sasaran_ibuhamil;
        $this->nama_sasaran_ibuhamil = $ibuhamil->nama_sasaran;
        $this->nik_sasaran_ibuhamil = $ibuhamil->nik_sasaran;
        $this->no_kk_sasaran_ibuhamil = $ibuhamil->no_kk_sasaran ?? '';
        $this->tempat_lahir_ibuhamil = $ibuhamil->tempat_lahir ?? '';
        // Split tanggal lahir menjadi hari, bulan, tahun
        if ($ibuhamil->tanggal_lahir) {
            $this->tanggal_lahir_ibuhamil = is_string($ibuhamil->tanggal_lahir) 
                ? $ibuhamil->tanggal_lahir 
                : $ibuhamil->tanggal_lahir->format('Y-m-d');
            $date = Carbon::parse($ibuhamil->tanggal_lahir);
            $this->hari_lahir_ibuhamil = $date->day;
            $this->bulan_lahir_ibuhamil = $date->month;
            $this->tahun_lahir_ibuhamil = $date->year;
        } else {
            $this->tanggal_lahir_ibuhamil = '';
            $this->hari_lahir_ibuhamil = '';
            $this->bulan_lahir_ibuhamil = '';
            $this->tahun_lahir_ibuhamil = '';
        }
        $this->jenis_kelamin_ibuhamil = $ibuhamil->jenis_kelamin;
        $this->status_keluarga_ibuhamil = $ibuhamil->status_keluarga ?? '';
        $this->umur_sasaran_ibuhamil = $ibuhamil->tanggal_lahir
            ? Carbon::parse($ibuhamil->tanggal_lahir)->age
            : $ibuhamil->umur_sasaran;
        $this->minggu_kandungan_ibuhamil = $ibuhamil->minggu_kandungan ?? '';
        $this->pekerjaan_ibuhamil = $ibuhamil->pekerjaan ?? '';
        $this->pendidikan_ibuhamil = $ibuhamil->pendidikan ?? '';
        $this->alamat_sasaran_ibuhamil = $ibuhamil->alamat_sasaran ?? '';
        $this->rt_ibuhamil = $ibuhamil->rt ?? '';
        $this->rw_ibuhamil = $ibuhamil->rw ?? '';
        $this->kepersertaan_bpjs_ibuhamil = $ibuhamil->kepersertaan_bpjs ?? '';
        $this->nomor_bpjs_ibuhamil = $ibuhamil->nomor_bpjs ?? '';
        $this->nomor_telepon_ibuhamil = $ibuhamil->nomor_telepon ?? '';

        // Load data suami
        $this->nama_suami_ibuhamil = $ibuhamil->nama_suami ?? '';
        $this->nik_suami_ibuhamil = $ibuhamil->nik_suami ?? '';
        $this->tempat_lahir_suami_ibuhamil = $ibuhamil->tempat_lahir_suami ?? '';
        if ($ibuhamil->tanggal_lahir_suami) {
            $this->tanggal_lahir_suami_ibuhamil = is_string($ibuhamil->tanggal_lahir_suami) 
                ? $ibuhamil->tanggal_lahir_suami 
                : $ibuhamil->tanggal_lahir_suami->format('Y-m-d');
            $dateSuami = Carbon::parse($ibuhamil->tanggal_lahir_suami);
            $this->hari_lahir_suami_ibuhamil = $dateSuami->day;
            $this->bulan_lahir_suami_ibuhamil = $dateSuami->month;
            $this->tahun_lahir_suami_ibuhamil = $dateSuami->year;
        } else {
            $this->tanggal_lahir_suami_ibuhamil = '';
            $this->hari_lahir_suami_ibuhamil = '';
            $this->bulan_lahir_suami_ibuhamil = '';
            $this->tahun_lahir_suami_ibuhamil = '';
        }
        $this->pekerjaan_suami_ibuhamil = $ibuhamil->pekerjaan_suami ?? '';
        $this->status_keluarga_suami_ibuhamil = $ibuhamil->status_keluarga_suami ?? '';

        $this->isSasaranIbuHamilModalOpen = true;
    }

    /**
     * Hapus data ibu hamil
     */
    public function deleteIbuHamil($id)
    {
        $ibuhamil = SasaranIbuhamil::findOrFail($id);
        $ibuhamil->delete();
        $this->refreshPosyandu();
        session()->flash('message', 'Data Ibu Hamil berhasil dihapus.');
    }

    /**
     * Combine hari, bulan, tahun menjadi tanggal lahir
     */
    private function combineTanggalLahirIbuhamil()
    {
        if ($this->hari_lahir_ibuhamil && $this->bulan_lahir_ibuhamil && $this->tahun_lahir_ibuhamil) {
            try {
                $this->tanggal_lahir_ibuhamil = Carbon::create(
                    $this->tahun_lahir_ibuhamil,
                    $this->bulan_lahir_ibuhamil,
                    $this->hari_lahir_ibuhamil
                )->format('Y-m-d');
            } catch (\Exception $e) {
                $this->tanggal_lahir_ibuhamil = null;
            }
        } else {
            $this->tanggal_lahir_ibuhamil = null;
        }
    }

    /**
     * Combine hari, bulan, tahun menjadi tanggal lahir suami
     */
    private function combineTanggalLahirSuamiIbuhamil()
    {
        if ($this->hari_lahir_suami_ibuhamil && $this->bulan_lahir_suami_ibuhamil && $this->tahun_lahir_suami_ibuhamil) {
            try {
                $this->tanggal_lahir_suami_ibuhamil = Carbon::create(
                    $this->tahun_lahir_suami_ibuhamil,
                    $this->bulan_lahir_suami_ibuhamil,
                    $this->hari_lahir_suami_ibuhamil
                )->format('Y-m-d');
            } catch (\Exception $e) {
                $this->tanggal_lahir_suami_ibuhamil = null;
            }
        } else {
            $this->tanggal_lahir_suami_ibuhamil = null;
        }
    }

    /**
     * Hitung umur otomatis ketika hari, bulan, atau tahun lahir berubah
     */
    public function updatedHariLahirIbuhamil()
    {
        $this->calculateUmurIbuhamil();
    }

    public function updatedBulanLahirIbuhamil()
    {
        $this->calculateUmurIbuhamil();
    }

    public function updatedTahunLahirIbuhamil()
    {
        $this->calculateUmurIbuhamil();
    }

    /**
     * Calculate umur dari hari, bulan, tahun lahir
     */
    private function calculateUmurIbuhamil()
    {
        if ($this->hari_lahir_ibuhamil && $this->bulan_lahir_ibuhamil && $this->tahun_lahir_ibuhamil) {
            try {
                $tanggalLahir = Carbon::create(
                    $this->tahun_lahir_ibuhamil,
                    $this->bulan_lahir_ibuhamil,
                    $this->hari_lahir_ibuhamil
                );
                $this->umur_sasaran_ibuhamil = $tanggalLahir->age;
                $this->tanggal_lahir_ibuhamil = $tanggalLahir->format('Y-m-d');
            } catch (\Exception $e) {
                $this->umur_sasaran_ibuhamil = '';
                $this->tanggal_lahir_ibuhamil = null;
            }
        } else {
            $this->umur_sasaran_ibuhamil = '';
            $this->tanggal_lahir_ibuhamil = null;
        }
    }

    /**
     * Auto-fill data ibu hamil ketika NIK diketik
     */
    public function updatedNikSasaranIbuhamil($value)
    {
        if ($value && strlen($value) >= 10) {
            $this->loadDataIbuHamilByNik($value);
        }
    }

    /**
     * Load data ibu hamil berdasarkan NIK dari sasaran dewasa/pralansia/lansia
     */
    public function loadDataIbuHamilByNik($nik)
    {
        if (!$nik) {
            return;
        }

        // Dapatkan posyanduId dari berbagai konteks
        $posyanduId = $this->posyanduId ?? 
                     (isset($this->id_posyandu_sasaran) ? $this->id_posyandu_sasaran : null) ??
                     (method_exists($this, 'getPosyanduId') ? $this->getPosyanduId() : null);
        
        if (!$posyanduId) {
            return;
        }

        // Cari dari sasaran dewasa
        $sasaran = SasaranDewasa::where('id_posyandu', $posyanduId)
            ->where('nik_sasaran', $nik)
            ->first();

        // Jika tidak ditemukan, cari dari pralansia
        if (!$sasaran) {
            $sasaran = SasaranPralansia::where('id_posyandu', $posyanduId)
                ->where('nik_sasaran', $nik)
                ->first();
        }

        // Jika tidak ditemukan, cari dari lansia
        if (!$sasaran) {
            $sasaran = SasaranLansia::where('id_posyandu', $posyanduId)
                ->where('nik_sasaran', $nik)
                ->first();
        }

        if ($sasaran) {
            // Auto-fill data jika field kosong atau belum diisi
            if (empty($this->nama_sasaran_ibuhamil)) {
                $this->nama_sasaran_ibuhamil = $sasaran->nama_sasaran ?? '';
            }
            
            if (empty($this->no_kk_sasaran_ibuhamil)) {
                $this->no_kk_sasaran_ibuhamil = $sasaran->no_kk_sasaran ?? '';
            }
            
            if (empty($this->tempat_lahir_ibuhamil)) {
                $this->tempat_lahir_ibuhamil = $sasaran->tempat_lahir ?? '';
            }
            
            if (empty($this->tanggal_lahir_ibuhamil) && $sasaran->tanggal_lahir) {
                $this->tanggal_lahir_ibuhamil = is_string($sasaran->tanggal_lahir) 
                    ? $sasaran->tanggal_lahir 
                    : $sasaran->tanggal_lahir->format('Y-m-d');
                
                // Split tanggal lahir menjadi hari, bulan, tahun
                $date = Carbon::parse($sasaran->tanggal_lahir);
                $this->hari_lahir_ibuhamil = $date->day;
                $this->bulan_lahir_ibuhamil = $date->month;
                $this->tahun_lahir_ibuhamil = $date->year;
                $this->umur_sasaran_ibuhamil = $date->age;
            }
            
            if (empty($this->jenis_kelamin_ibuhamil)) {
                $this->jenis_kelamin_ibuhamil = $sasaran->jenis_kelamin ?? '';
            }
            
            if (empty($this->status_keluarga_ibuhamil)) {
                $this->status_keluarga_ibuhamil = $sasaran->status_keluarga ?? '';
            }
            
            if (empty($this->pekerjaan_ibuhamil)) {
                $this->pekerjaan_ibuhamil = $sasaran->pekerjaan ?? '';
            }
            
            if (empty($this->pendidikan_ibuhamil)) {
                $this->pendidikan_ibuhamil = $sasaran->pendidikan ?? '';
            }
            
            if (empty($this->alamat_sasaran_ibuhamil)) {
                $this->alamat_sasaran_ibuhamil = $sasaran->alamat_sasaran ?? '';
            }
            
            if (empty($this->rt_ibuhamil)) {
                $this->rt_ibuhamil = $sasaran->rt ?? '';
            }
            
            if (empty($this->rw_ibuhamil)) {
                $this->rw_ibuhamil = $sasaran->rw ?? '';
            }
            
            if (empty($this->kepersertaan_bpjs_ibuhamil)) {
                $this->kepersertaan_bpjs_ibuhamil = $sasaran->kepersertaan_bpjs ?? '';
            }
            
            if (empty($this->nomor_bpjs_ibuhamil)) {
                $this->nomor_bpjs_ibuhamil = $sasaran->nomor_bpjs ?? '';
            }
            
            if (empty($this->nomor_telepon_ibuhamil)) {
                $this->nomor_telepon_ibuhamil = $sasaran->nomor_telepon ?? '';
            }

            // Dispatch event untuk memberi tahu bahwa data berhasil di-load
            $this->dispatch('data-loaded', ['type' => 'ibuhamil', 'message' => 'Data berhasil dimuat dari sasaran ' . ($sasaran instanceof SasaranDewasa ? 'Dewasa' : ($sasaran instanceof SasaranPralansia ? 'Pralansia' : 'Lansia'))]);
        }
    }

    /**
     * Auto-fill biodata suami ketika NIK suami diketik
     */
    public function updatedNikSuamiIbuhamil($value)
    {
        if ($value && strlen($value) >= 10) {
            $this->loadDataSuamiByNik($value);
        }
    }

    /**
     * Load data suami berdasarkan NIK dari sasaran dewasa/pralansia/lansia
     */
    public function loadDataSuamiByNik($nik)
    {
        if (!$nik) {
            return;
        }

        // Dapatkan posyanduId dari berbagai konteks
        $posyanduId = $this->posyanduId ?? 
                     (isset($this->id_posyandu_sasaran) ? $this->id_posyandu_sasaran : null) ??
                     (method_exists($this, 'getPosyanduId') ? $this->getPosyanduId() : null);
        
        if (!$posyanduId) {
            return;
        }

        // Cari dari sasaran dewasa
        $sasaran = SasaranDewasa::where('id_posyandu', $posyanduId)
            ->where('nik_sasaran', $nik)
            ->first();

        // Jika tidak ditemukan, cari dari pralansia
        if (!$sasaran) {
            $sasaran = SasaranPralansia::where('id_posyandu', $posyanduId)
                ->where('nik_sasaran', $nik)
                ->first();
        }

        // Jika tidak ditemukan, cari dari lansia
        if (!$sasaran) {
            $sasaran = SasaranLansia::where('id_posyandu', $posyanduId)
                ->where('nik_sasaran', $nik)
                ->first();
        }

        if ($sasaran) {
            // Auto-fill biodata suami jika field kosong
            if (empty($this->nama_suami_ibuhamil)) {
                $this->nama_suami_ibuhamil = $sasaran->nama_sasaran ?? '';
            }
            
            if (empty($this->tempat_lahir_suami_ibuhamil)) {
                $this->tempat_lahir_suami_ibuhamil = $sasaran->tempat_lahir ?? '';
            }
            
            if (empty($this->tanggal_lahir_suami_ibuhamil) && $sasaran->tanggal_lahir) {
                $this->tanggal_lahir_suami_ibuhamil = is_string($sasaran->tanggal_lahir) 
                    ? $sasaran->tanggal_lahir 
                    : $sasaran->tanggal_lahir->format('Y-m-d');
                
                // Split tanggal lahir menjadi hari, bulan, tahun
                $date = Carbon::parse($sasaran->tanggal_lahir);
                $this->hari_lahir_suami_ibuhamil = $date->day;
                $this->bulan_lahir_suami_ibuhamil = $date->month;
                $this->tahun_lahir_suami_ibuhamil = $date->year;
            }
            
            if (empty($this->pekerjaan_suami_ibuhamil)) {
                $this->pekerjaan_suami_ibuhamil = $sasaran->pekerjaan ?? '';
            }
            
            if (empty($this->status_keluarga_suami_ibuhamil)) {
                $this->status_keluarga_suami_ibuhamil = $sasaran->status_keluarga ?? '';
            }

            // Dispatch event untuk memberi tahu bahwa data berhasil di-load
            $this->dispatch('data-loaded', ['type' => 'suami', 'message' => 'Data suami berhasil dimuat dari sasaran ' . ($sasaran instanceof SasaranDewasa ? 'Dewasa' : ($sasaran instanceof SasaranPralansia ? 'Pralansia' : 'Lansia'))]);
        }
    }

    /**
     * Get list of NIK from sasaran dewasa/pralansia/lansia for dropdown suggestion
     */
    public function getNikListIbuHamil()
    {
        // Dapatkan posyanduId dari berbagai konteks
        $posyanduId = $this->posyanduId ?? 
                     (isset($this->id_posyandu_sasaran) ? $this->id_posyandu_sasaran : null) ??
                     (method_exists($this, 'getPosyanduId') ? $this->getPosyanduId() : null);
        
        if (!$posyanduId) {
            return [];
        }
        
        $nikList = [];
        
        // Ambil dari sasaran dewasa
        $dewasaList = SasaranDewasa::where('id_posyandu', $posyanduId)
            ->whereNotNull('nik_sasaran')
            ->select('nik_sasaran', 'nama_sasaran', 'no_kk_sasaran', 'jenis_kelamin', 'status_keluarga')
            ->get();
            
        foreach ($dewasaList as $dewasa) {
            $nik = $dewasa->nik_sasaran;
            if (!isset($nikList[$nik])) {
                $nikList[$nik] = [
                    'nik' => $nik,
                    'nama' => $dewasa->nama_sasaran,
                    'no_kk' => $dewasa->no_kk_sasaran ?? '-',
                    'jenis_kelamin' => $dewasa->jenis_kelamin ?? '-',
                    'status_keluarga' => $dewasa->status_keluarga ?? '-',
                    'kategori' => 'Dewasa'
                ];
            }
        }
        
        // Ambil dari sasaran pralansia
        $pralansiaList = SasaranPralansia::where('id_posyandu', $posyanduId)
            ->whereNotNull('nik_sasaran')
            ->select('nik_sasaran', 'nama_sasaran', 'no_kk_sasaran', 'jenis_kelamin', 'status_keluarga')
            ->get();
            
        foreach ($pralansiaList as $pralansia) {
            $nik = $pralansia->nik_sasaran;
            if (!isset($nikList[$nik])) {
                $nikList[$nik] = [
                    'nik' => $nik,
                    'nama' => $pralansia->nama_sasaran,
                    'no_kk' => $pralansia->no_kk_sasaran ?? '-',
                    'jenis_kelamin' => $pralansia->jenis_kelamin ?? '-',
                    'status_keluarga' => $pralansia->status_keluarga ?? '-',
                    'kategori' => 'Pralansia'
                ];
            }
        }
        
        // Ambil dari sasaran lansia
        $lansiaList = SasaranLansia::where('id_posyandu', $posyanduId)
            ->whereNotNull('nik_sasaran')
            ->select('nik_sasaran', 'nama_sasaran', 'no_kk_sasaran', 'jenis_kelamin', 'status_keluarga')
            ->get();
            
        foreach ($lansiaList as $lansia) {
            $nik = $lansia->nik_sasaran;
            if (!isset($nikList[$nik])) {
                $nikList[$nik] = [
                    'nik' => $nik,
                    'nama' => $lansia->nama_sasaran,
                    'no_kk' => $lansia->no_kk_sasaran ?? '-',
                    'jenis_kelamin' => $lansia->jenis_kelamin ?? '-',
                    'status_keluarga' => $lansia->status_keluarga ?? '-',
                    'kategori' => 'Lansia'
                ];
            }
        }
        
        // Sort by NIK
        ksort($nikList);
        
        return array_values($nikList);
    }

    /**
     * Get list of NIK for suami from sasaran dewasa/pralansia/lansia for dropdown suggestion
     */
    public function getNikListSuami()
    {
        // Dapatkan posyanduId dari berbagai konteks
        $posyanduId = $this->posyanduId ?? 
                     (isset($this->id_posyandu_sasaran) ? $this->id_posyandu_sasaran : null) ??
                     (method_exists($this, 'getPosyanduId') ? $this->getPosyanduId() : null);
        
        if (!$posyanduId) {
            return [];
        }
        
        $nikList = [];
        
        // Ambil dari sasaran dewasa
        $dewasaList = SasaranDewasa::where('id_posyandu', $posyanduId)
            ->whereNotNull('nik_sasaran')
            ->select('nik_sasaran', 'nama_sasaran', 'no_kk_sasaran', 'jenis_kelamin', 'status_keluarga')
            ->get();
            
        foreach ($dewasaList as $dewasa) {
            $nik = $dewasa->nik_sasaran;
            if (!isset($nikList[$nik])) {
                $nikList[$nik] = [
                    'nik' => $nik,
                    'nama' => $dewasa->nama_sasaran,
                    'no_kk' => $dewasa->no_kk_sasaran ?? '-',
                    'jenis_kelamin' => $dewasa->jenis_kelamin ?? '-',
                    'status_keluarga' => $dewasa->status_keluarga ?? '-',
                    'kategori' => 'Dewasa'
                ];
            }
        }
        
        // Ambil dari sasaran pralansia
        $pralansiaList = SasaranPralansia::where('id_posyandu', $posyanduId)
            ->whereNotNull('nik_sasaran')
            ->select('nik_sasaran', 'nama_sasaran', 'no_kk_sasaran', 'jenis_kelamin', 'status_keluarga')
            ->get();
            
        foreach ($pralansiaList as $pralansia) {
            $nik = $pralansia->nik_sasaran;
            if (!isset($nikList[$nik])) {
                $nikList[$nik] = [
                    'nik' => $nik,
                    'nama' => $pralansia->nama_sasaran,
                    'no_kk' => $pralansia->no_kk_sasaran ?? '-',
                    'jenis_kelamin' => $pralansia->jenis_kelamin ?? '-',
                    'status_keluarga' => $pralansia->status_keluarga ?? '-',
                    'kategori' => 'Pralansia'
                ];
            }
        }
        
        // Ambil dari sasaran lansia
        $lansiaList = SasaranLansia::where('id_posyandu', $posyanduId)
            ->whereNotNull('nik_sasaran')
            ->select('nik_sasaran', 'nama_sasaran', 'no_kk_sasaran', 'jenis_kelamin', 'status_keluarga')
            ->get();
            
        foreach ($lansiaList as $lansia) {
            $nik = $lansia->nik_sasaran;
            if (!isset($nikList[$nik])) {
                $nikList[$nik] = [
                    'nik' => $nik,
                    'nama' => $lansia->nama_sasaran,
                    'no_kk' => $lansia->no_kk_sasaran ?? '-',
                    'jenis_kelamin' => $lansia->jenis_kelamin ?? '-',
                    'status_keluarga' => $lansia->status_keluarga ?? '-',
                    'kategori' => 'Lansia'
                ];
            }
        }
        
        // Sort by NIK
        ksort($nikList);
        
        return array_values($nikList);
    }
}

