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
    public $hari_lahir_dewasa;
    public $bulan_lahir_dewasa;
    public $tahun_lahir_dewasa;
    public $jenis_kelamin_dewasa;
    public $umur_sasaran_dewasa;
    public $pekerjaan_dewasa;
    public $alamat_sasaran_dewasa;
    public $rt_dewasa;
    public $rw_dewasa;
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
        $this->hari_lahir_dewasa = '';
        $this->bulan_lahir_dewasa = '';
        $this->tahun_lahir_dewasa = '';
        $this->jenis_kelamin_dewasa = '';
        $this->umur_sasaran_dewasa = '';
        $this->pekerjaan_dewasa = '';
        $this->alamat_sasaran_dewasa = '';
        $this->rt_dewasa = '';
        $this->rw_dewasa = '';
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
        // Combine hari, bulan, tahun menjadi tanggal lahir
        $this->combineTanggalLahirDewasa();

        $this->validate([
            'nama_sasaran_dewasa' => 'required|string|max:100',
            'nik_sasaran_dewasa' => 'required|numeric',
            'hari_lahir_dewasa' => 'required|numeric|min:1|max:31',
            'bulan_lahir_dewasa' => 'required|numeric|min:1|max:12',
            'tahun_lahir_dewasa' => 'required|numeric|min:1900|max:' . date('Y'),
            'tanggal_lahir_dewasa' => 'required|date',
            'jenis_kelamin_dewasa' => 'required|in:Laki-laki,Perempuan',
            'alamat_sasaran_dewasa' => 'required|string|max:225',
        ], [
            'nama_sasaran_dewasa.required' => 'Nama sasaran wajib diisi.',
            'nik_sasaran_dewasa.required' => 'NIK wajib diisi.',
            'nik_sasaran_dewasa.numeric' => 'NIK harus berupa angka.',
            'hari_lahir_dewasa.required' => 'Hari lahir wajib diisi.',
            'hari_lahir_dewasa.numeric' => 'Hari harus berupa angka.',
            'hari_lahir_dewasa.min' => 'Hari minimal 1.',
            'hari_lahir_dewasa.max' => 'Hari maksimal 31.',
            'bulan_lahir_dewasa.required' => 'Bulan lahir wajib diisi.',
            'bulan_lahir_dewasa.numeric' => 'Bulan harus berupa angka.',
            'bulan_lahir_dewasa.min' => 'Bulan minimal 1.',
            'bulan_lahir_dewasa.max' => 'Bulan maksimal 12.',
            'tahun_lahir_dewasa.required' => 'Tahun lahir wajib diisi.',
            'tahun_lahir_dewasa.numeric' => 'Tahun harus berupa angka.',
            'tahun_lahir_dewasa.min' => 'Tahun minimal 1900.',
            'tahun_lahir_dewasa.max' => 'Tahun maksimal ' . date('Y') . '.',
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
            'alamat_sasaran' => $this->alamat_sasaran_dewasa,
            'rt' => $this->rt_dewasa ?: null,
            'rw' => $this->rw_dewasa ?: null,
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
        // Split tanggal lahir menjadi hari, bulan, tahun
        if ($dewasa->tanggal_lahir) {
            $date = Carbon::parse($dewasa->tanggal_lahir);
            $this->hari_lahir_dewasa = $date->day;
            $this->bulan_lahir_dewasa = $date->month;
            $this->tahun_lahir_dewasa = $date->year;
        } else {
            $this->hari_lahir_dewasa = '';
            $this->bulan_lahir_dewasa = '';
            $this->tahun_lahir_dewasa = '';
        }
        $this->jenis_kelamin_dewasa = $dewasa->jenis_kelamin;
        $this->umur_sasaran_dewasa = $dewasa->tanggal_lahir
            ? Carbon::parse($dewasa->tanggal_lahir)->age
            : $dewasa->umur_sasaran;
        $this->pekerjaan_dewasa = $dewasa->pekerjaan ?? '';
        $this->alamat_sasaran_dewasa = $dewasa->alamat_sasaran ?? '';
        $this->rt_dewasa = $dewasa->rt ?? '';
        $this->rw_dewasa = $dewasa->rw ?? '';
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
     * Combine hari, bulan, tahun menjadi tanggal lahir
     */
    private function combineTanggalLahirDewasa()
    {
        if ($this->hari_lahir_dewasa && $this->bulan_lahir_dewasa && $this->tahun_lahir_dewasa) {
            try {
                $this->tanggal_lahir_dewasa = Carbon::create(
                    $this->tahun_lahir_dewasa,
                    $this->bulan_lahir_dewasa,
                    $this->hari_lahir_dewasa
                )->format('Y-m-d');
            } catch (\Exception $e) {
                $this->tanggal_lahir_dewasa = null;
            }
        } else {
            $this->tanggal_lahir_dewasa = null;
        }
    }

    /**
     * Hitung umur otomatis ketika hari, bulan, atau tahun lahir berubah
     */
    public function updatedHariLahirDewasa()
    {
        $this->calculateUmurDewasa();
    }

    public function updatedBulanLahirDewasa()
    {
        $this->calculateUmurDewasa();
    }

    public function updatedTahunLahirDewasa()
    {
        $this->calculateUmurDewasa();
    }

    /**
     * Calculate umur dari hari, bulan, tahun lahir
     */
    private function calculateUmurDewasa()
    {
        if ($this->hari_lahir_dewasa && $this->bulan_lahir_dewasa && $this->tahun_lahir_dewasa) {
            try {
                $tanggalLahir = Carbon::create(
                    $this->tahun_lahir_dewasa,
                    $this->bulan_lahir_dewasa,
                    $this->hari_lahir_dewasa
                );
                $this->umur_sasaran_dewasa = $tanggalLahir->age;
                $this->tanggal_lahir_dewasa = $tanggalLahir->format('Y-m-d');
            } catch (\Exception $e) {
                $this->umur_sasaran_dewasa = '';
                $this->tanggal_lahir_dewasa = null;
            }
        } else {
            $this->umur_sasaran_dewasa = '';
            $this->tanggal_lahir_dewasa = null;
        }
    }
}

