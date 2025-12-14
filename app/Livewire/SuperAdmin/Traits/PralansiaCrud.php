<?php

namespace App\Livewire\SuperAdmin\Traits;

use App\Models\sasaran_pralansia;
use Carbon\Carbon;

trait PralansiaCrud
{
    // Modal State
    public $isSasaranPralansiaModalOpen = false;

    // Field Form Sasaran Pralansia
    public $id_sasaran_pralansia = null;
    public $nama_sasaran_pralansia;
    public $nik_sasaran_pralansia;
    public $no_kk_sasaran_pralansia;
    public $tempat_lahir_pralansia;
    public $tanggal_lahir_pralansia;
    public $hari_lahir_pralansia;
    public $bulan_lahir_pralansia;
    public $tahun_lahir_pralansia;
    public $jenis_kelamin_pralansia;
    public $umur_sasaran_pralansia;
    public $pekerjaan_pralansia;
    public $alamat_sasaran_pralansia;
    public $rt_pralansia;
    public $rw_pralansia;
    public $kepersertaan_bpjs_pralansia;
    public $nomor_bpjs_pralansia;
    public $nomor_telepon_pralansia;
    public $id_users_sasaran_pralansia;

    /**
     * Buka modal tambah/edit Sasaran Pralansia
     */
    public function openPralansiaModal($id = null)
    {
        $this->searchUser = ''; // Reset search user
        if ($id) {
            $this->editPralansia($id);
        } else {
            $this->resetPralansiaFields();
            $this->isSasaranPralansiaModalOpen = true;
        }
    }

    /**
     * Tutup modal dan reset field
     */
    public function closePralansiaModal()
    {
        $this->resetPralansiaFields();
        $this->searchUser = ''; // Reset search user
        $this->isSasaranPralansiaModalOpen = false;
    }

    /**
     * Reset semua field form Sasaran Pralansia
     */
    private function resetPralansiaFields()
    {
        $this->id_sasaran_pralansia = null;
        $this->nama_sasaran_pralansia = '';
        $this->nik_sasaran_pralansia = '';
        $this->no_kk_sasaran_pralansia = '';
        $this->tempat_lahir_pralansia = '';
        $this->tanggal_lahir_pralansia = '';
        $this->hari_lahir_pralansia = '';
        $this->bulan_lahir_pralansia = '';
        $this->tahun_lahir_pralansia = '';
        $this->jenis_kelamin_pralansia = '';
        $this->umur_sasaran_pralansia = '';
        $this->pekerjaan_pralansia = '';
        $this->alamat_sasaran_pralansia = '';
        $this->rt_pralansia = '';
        $this->rw_pralansia = '';
        $this->kepersertaan_bpjs_pralansia = '';
        $this->nomor_bpjs_pralansia = '';
        $this->nomor_telepon_pralansia = '';
        $this->id_users_sasaran_pralansia = '';
    }

    /**
     * Proses simpan data pralansia, tambah/edit
     */
    public function storePralansia()
    {
        $this->validate([
            'nama_sasaran_pralansia' => 'required|string|max:100',
            'nik_sasaran_pralansia' => 'required|numeric',
            'tanggal_lahir_pralansia' => 'required|date',
            'jenis_kelamin_pralansia' => 'required|in:Laki-laki,Perempuan',
            'alamat_sasaran_pralansia' => 'required|string|max:225',
        ], [
            'nama_sasaran_pralansia.required' => 'Nama sasaran wajib diisi.',
            'nik_sasaran_pralansia.required' => 'NIK wajib diisi.',
            'nik_sasaran_pralansia.numeric' => 'NIK harus berupa angka.',
            'hari_lahir_pralansia.required' => 'Hari lahir wajib diisi.',
            'hari_lahir_pralansia.numeric' => 'Hari harus berupa angka.',
            'hari_lahir_pralansia.min' => 'Hari minimal 1.',
            'hari_lahir_pralansia.max' => 'Hari maksimal 31.',
            'bulan_lahir_pralansia.required' => 'Bulan lahir wajib diisi.',
            'bulan_lahir_pralansia.numeric' => 'Bulan harus berupa angka.',
            'bulan_lahir_pralansia.min' => 'Bulan minimal 1.',
            'bulan_lahir_pralansia.max' => 'Bulan maksimal 12.',
            'tahun_lahir_pralansia.required' => 'Tahun lahir wajib diisi.',
            'tahun_lahir_pralansia.numeric' => 'Tahun harus berupa angka.',
            'tahun_lahir_pralansia.min' => 'Tahun minimal 1900.',
            'tahun_lahir_pralansia.max' => 'Tahun maksimal ' . date('Y') . '.',
            'tanggal_lahir_pralansia.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir_pralansia.date' => 'Tanggal lahir harus berupa tanggal yang valid.',
            'jenis_kelamin_pralansia.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin_pralansia.in' => 'Jenis kelamin harus Laki-laki atau Perempuan.',
            'alamat_sasaran_pralansia.required' => 'Alamat wajib diisi.',
            'alamat_sasaran_pralansia.max' => 'Alamat maksimal 225 karakter.',
        ]);

        // Hitung umur dari tanggal_lahir_pralansia
        $umur = null;
        if ($this->tanggal_lahir_pralansia) {
            $umur = Carbon::parse($this->tanggal_lahir_pralansia)->age;
        }

        $data = [
            'id_users' => $this->id_users_sasaran_pralansia !== '' ? $this->id_users_sasaran_pralansia : null,
            'id_posyandu' => $this->posyanduId,
            'nama_sasaran' => $this->nama_sasaran_pralansia,
            'nik_sasaran' => $this->nik_sasaran_pralansia,
            'no_kk_sasaran' => $this->no_kk_sasaran_pralansia ?: null,
            'tempat_lahir' => $this->tempat_lahir_pralansia ?: null,
            'tanggal_lahir' => $this->tanggal_lahir_pralansia,
            'jenis_kelamin' => $this->jenis_kelamin_pralansia,
            'umur_sasaran' => $umur,
            'alamat_sasaran' => $this->alamat_sasaran_pralansia,
            'rt' => $this->rt_pralansia ?: null,
            'rw' => $this->rw_pralansia ?: null,
            'kepersertaan_bpjs' => $this->kepersertaan_bpjs_pralansia ?: null,
            'nomor_bpjs' => $this->nomor_bpjs_pralansia ?: null,
            'nomor_telepon' => $this->nomor_telepon_pralansia ?: null,
        ];

        if ($this->id_sasaran_pralansia) {
            // UPDATE
            $pralansia = sasaran_pralansia::findOrFail($this->id_sasaran_pralansia);
            $pralansia->update($data);
            session()->flash('message', 'Data Pralansia berhasil diperbarui.');
        } else {
            // CREATE
            sasaran_pralansia::create($data);
            session()->flash('message', 'Data Pralansia berhasil ditambahkan.');
        }

        $this->refreshPosyandu();
        $this->closePralansiaModal();
    }

    /**
     * Inisialisasi form edit pralansia
     */
    public function editPralansia($id)
    {
        $this->searchUser = ''; // Reset search user
        $pralansia = sasaran_pralansia::findOrFail($id);

        $this->id_sasaran_pralansia = $pralansia->id_sasaran_pralansia;
        $this->nama_sasaran_pralansia = $pralansia->nama_sasaran;
        $this->nik_sasaran_pralansia = $pralansia->nik_sasaran;
        $this->no_kk_sasaran_pralansia = $pralansia->no_kk_sasaran ?? '';
        $this->tempat_lahir_pralansia = $pralansia->tempat_lahir ?? '';
        $this->tanggal_lahir_pralansia = $pralansia->tanggal_lahir;
        // Split tanggal lahir menjadi hari, bulan, tahun
        if ($pralansia->tanggal_lahir) {
            $date = Carbon::parse($pralansia->tanggal_lahir);
            $this->hari_lahir_pralansia = $date->day;
            $this->bulan_lahir_pralansia = $date->month;
            $this->tahun_lahir_pralansia = $date->year;
        } else {
            $this->hari_lahir_pralansia = '';
            $this->bulan_lahir_pralansia = '';
            $this->tahun_lahir_pralansia = '';
        }
        $this->jenis_kelamin_pralansia = $pralansia->jenis_kelamin;
        $this->umur_sasaran_pralansia = $pralansia->tanggal_lahir
            ? Carbon::parse($pralansia->tanggal_lahir)->age
            : $pralansia->umur_sasaran;
        $this->pekerjaan_pralansia = $pralansia->pekerjaan ?? '';
        $this->alamat_sasaran_pralansia = $pralansia->alamat_sasaran ?? '';
        $this->rt_pralansia = $pralansia->rt ?? '';
        $this->rw_pralansia = $pralansia->rw ?? '';
        $this->kepersertaan_bpjs_pralansia = $pralansia->kepersertaan_bpjs ?? '';
        $this->nomor_bpjs_pralansia = $pralansia->nomor_bpjs ?? '';
        $this->nomor_telepon_pralansia = $pralansia->nomor_telepon ?? '';
        $this->id_users_sasaran_pralansia = $pralansia->id_users ?? '';

        $this->isSasaranPralansiaModalOpen = true;
    }

    /**
     * Hapus data pralansia
     */
    public function deletePralansia($id)
    {
        $pralansia = sasaran_pralansia::findOrFail($id);
        $pralansia->delete();
        $this->refreshPosyandu();
        session()->flash('message', 'Data Pralansia berhasil dihapus.');
    }

    /**
     * Combine hari, bulan, tahun menjadi tanggal lahir
     */
    private function combineTanggalLahirPralansia()
    {
        if ($this->hari_lahir_pralansia && $this->bulan_lahir_pralansia && $this->tahun_lahir_pralansia) {
            try {
                $this->tanggal_lahir_pralansia = Carbon::create(
                    $this->tahun_lahir_pralansia,
                    $this->bulan_lahir_pralansia,
                    $this->hari_lahir_pralansia
                )->format('Y-m-d');
            } catch (\Exception $e) {
                $this->tanggal_lahir_pralansia = null;
            }
        } else {
            $this->tanggal_lahir_pralansia = null;
        }
    }

    /**
     * Hitung umur otomatis ketika hari, bulan, atau tahun lahir berubah
     */
    public function updatedHariLahirPralansia()
    {
        $this->calculateUmurPralansia();
    }

    public function updatedBulanLahirPralansia()
    {
        $this->calculateUmurPralansia();
    }

    public function updatedTahunLahirPralansia()
    {
        $this->calculateUmurPralansia();
    }

    /**
     * Calculate umur dari hari, bulan, tahun lahir
     */
    private function calculateUmurPralansia()
    {
        if ($this->hari_lahir_pralansia && $this->bulan_lahir_pralansia && $this->tahun_lahir_pralansia) {
            try {
                $tanggalLahir = Carbon::create(
                    $this->tahun_lahir_pralansia,
                    $this->bulan_lahir_pralansia,
                    $this->hari_lahir_pralansia
                );
                $this->umur_sasaran_pralansia = $tanggalLahir->age;
                $this->tanggal_lahir_pralansia = $tanggalLahir->format('Y-m-d');
            } catch (\Exception $e) {
                $this->umur_sasaran_pralansia = '';
                $this->tanggal_lahir_pralansia = null;
            }
        } else {
            $this->umur_sasaran_pralansia = '';
            $this->tanggal_lahir_pralansia = null;
        }
    }
}

