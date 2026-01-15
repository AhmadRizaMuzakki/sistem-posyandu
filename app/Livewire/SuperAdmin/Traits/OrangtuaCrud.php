<?php

namespace App\Livewire\SuperAdmin\Traits;

use App\Models\Orangtua;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

trait OrangtuaCrud
{
    // Modal State
    public $isOrangtuaModalOpen = false;

    // Field Form Orangtua
    public $nik_orangtua = null;
    public $nama_orangtua;
    public $no_kk_orangtua;
    public $tempat_lahir_orangtua;
    public $tanggal_lahir_orangtua;
    public $hari_lahir_orangtua;
    public $bulan_lahir_orangtua;
    public $tahun_lahir_orangtua;
    public $pekerjaan_orangtua;
    public $pendidikan_orangtua;
    public $kelamin_orangtua;
    public $alamat_orangtua;
    public $kepersertaan_bpjs_orangtua;
    public $nomor_bpjs_orangtua;
    public $nomor_telepon_orangtua;

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
        $this->no_kk_orangtua = '';
        $this->tempat_lahir_orangtua = '';
        $this->tanggal_lahir_orangtua = '';
        $this->hari_lahir_orangtua = '';
        $this->bulan_lahir_orangtua = '';
        $this->tahun_lahir_orangtua = '';
        $this->pekerjaan_orangtua = '';
        $this->pendidikan_orangtua = '';
        $this->kelamin_orangtua = '';
        $this->alamat_orangtua = '';
        $this->kepersertaan_bpjs_orangtua = '';
        $this->nomor_bpjs_orangtua = '';
        $this->nomor_telepon_orangtua = '';
    }

    /**
     * Proses simpan data orangtua, tambah/edit
     */
    public function storeOrangtua()
    {
        // Combine hari, bulan, tahun menjadi tanggal lahir
        $this->combineTanggalLahirOrangtua();

        $this->validate([
            'nik_orangtua' => 'required|numeric|unique:orangtua,nik' . ($this->nik_orangtua ? ',' . $this->nik_orangtua . ',nik' : ''),
            'nama_orangtua' => 'required|string|max:100',
            'no_kk_orangtua' => 'nullable|numeric',
            'tempat_lahir_orangtua' => 'required|string|max:50',
            'hari_lahir_orangtua' => 'required|numeric|min:1|max:31',
            'bulan_lahir_orangtua' => 'required|numeric|min:1|max:12',
            'tahun_lahir_orangtua' => 'required|numeric|min:1900|max:' . date('Y'),
            'tanggal_lahir_orangtua' => 'required|date',
            'pendidikan_orangtua' => 'nullable|string',
            'kelamin_orangtua' => 'required|in:Laki-laki,Perempuan',
            'alamat_orangtua' => 'required|string|max:225',
            'kepersertaan_bpjs_orangtua' => 'nullable|in:PBI,NON PBI',
            'nomor_bpjs_orangtua' => 'nullable|string|max:50',
            'nomor_telepon_orangtua' => 'nullable|string|max:20',
        ], [
            'nik_orangtua.required' => 'NIK wajib diisi.',
            'nik_orangtua.numeric' => 'NIK harus berupa angka.',
            'nik_orangtua.unique' => 'NIK sudah terdaftar.',
            'nama_orangtua.required' => 'Nama wajib diisi.',
            'nama_orangtua.max' => 'Nama maksimal 100 karakter.',
            'tempat_lahir_orangtua.required' => 'Tempat lahir wajib diisi.',
            'tempat_lahir_orangtua.max' => 'Tempat lahir maksimal 50 karakter.',
            'hari_lahir_orangtua.required' => 'Hari lahir wajib diisi.',
            'hari_lahir_orangtua.numeric' => 'Hari harus berupa angka.',
            'hari_lahir_orangtua.min' => 'Hari minimal 1.',
            'hari_lahir_orangtua.max' => 'Hari maksimal 31.',
            'bulan_lahir_orangtua.required' => 'Bulan lahir wajib diisi.',
            'bulan_lahir_orangtua.numeric' => 'Bulan harus berupa angka.',
            'bulan_lahir_orangtua.min' => 'Bulan minimal 1.',
            'bulan_lahir_orangtua.max' => 'Bulan maksimal 12.',
            'tahun_lahir_orangtua.required' => 'Tahun lahir wajib diisi.',
            'tahun_lahir_orangtua.numeric' => 'Tahun harus berupa angka.',
            'tahun_lahir_orangtua.min' => 'Tahun minimal 1900.',
            'tahun_lahir_orangtua.max' => 'Tahun maksimal ' . date('Y') . '.',
            'tanggal_lahir_orangtua.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir_orangtua.date' => 'Tanggal lahir harus berupa tanggal yang valid.',
            'pendidikan_orangtua.string' => 'Pendidikan harus berupa teks.',
            'kelamin_orangtua.required' => 'Jenis kelamin wajib dipilih.',
            'kelamin_orangtua.in' => 'Jenis kelamin harus Laki-laki atau Perempuan.',
            'alamat_orangtua.required' => 'Alamat wajib diisi.',
            'alamat_orangtua.max' => 'Alamat maksimal 225 karakter.',
            'kepersertaan_bpjs_orangtua.in' => 'Kepersertaan BPJS harus PBI atau NON PBI.',
            'nomor_bpjs_orangtua.max' => 'Nomor BPJS maksimal 50 karakter.',
            'nomor_telepon_orangtua.max' => 'Nomor telepon maksimal 20 karakter.',
        ]);

        $data = [
            'nik' => $this->nik_orangtua,
            'nama' => $this->nama_orangtua,
            'no_kk' => $this->no_kk_orangtua ?: null,
            'tempat_lahir' => $this->tempat_lahir_orangtua,
            'tanggal_lahir' => $this->tanggal_lahir_orangtua,
            'pekerjaan' => $this->pekerjaan_orangtua ?: null,
            'pendidikan' => $this->pendidikan_orangtua ?: null,
            'kelamin' => $this->kelamin_orangtua,
            'alamat' => $this->alamat_orangtua,
            'kepersertaan_bpjs' => $this->kepersertaan_bpjs_orangtua ?: null,
            'nomor_bpjs' => $this->nomor_bpjs_orangtua ?: null,
            'nomor_telepon' => $this->nomor_telepon_orangtua ?: null,
        ];

        DB::transaction(function () use ($data) {
            if ($this->nik_orangtua) {
                // UPDATE - find by nik since that's the primary key
                $orangtua = Orangtua::findOrFail($this->nik_orangtua);
                $orangtua->update($data);
            } else {
                // CREATE
                Orangtua::create($data);
            }
        });
        
        if ($this->nik_orangtua) {
            session()->flash('message', 'Data Orangtua berhasil diperbarui.');
        } else {
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
        $this->no_kk_orangtua = $orangtua->no_kk ?? '';
        $this->tempat_lahir_orangtua = $orangtua->tempat_lahir ?? '';
        $this->tanggal_lahir_orangtua = $orangtua->tanggal_lahir ? $orangtua->tanggal_lahir->format('Y-m-d') : '';
        // Split tanggal lahir menjadi hari, bulan, tahun
        if ($orangtua->tanggal_lahir) {
            $date = Carbon::parse($orangtua->tanggal_lahir);
            $this->hari_lahir_orangtua = $date->day;
            $this->bulan_lahir_orangtua = $date->month;
            $this->tahun_lahir_orangtua = $date->year;
        } else {
            $this->hari_lahir_orangtua = '';
            $this->bulan_lahir_orangtua = '';
            $this->tahun_lahir_orangtua = '';
        }
        $this->pekerjaan_orangtua = $orangtua->pekerjaan ?? '';
        $this->pendidikan_orangtua = $orangtua->pendidikan ?? '';
        $this->kelamin_orangtua = $orangtua->kelamin;
        $this->alamat_orangtua = $orangtua->alamat ?? '';
        $this->kepersertaan_bpjs_orangtua = $orangtua->kepersertaan_bpjs ?? '';
        $this->nomor_bpjs_orangtua = $orangtua->nomor_bpjs ?? '';
        $this->nomor_telepon_orangtua = $orangtua->nomor_telepon ?? '';

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

    /**
     * Combine hari, bulan, tahun menjadi tanggal lahir
     */
    private function combineTanggalLahirOrangtua()
    {
        if ($this->hari_lahir_orangtua && $this->bulan_lahir_orangtua && $this->tahun_lahir_orangtua) {
            try {
                $this->tanggal_lahir_orangtua = Carbon::create(
                    $this->tahun_lahir_orangtua,
                    $this->bulan_lahir_orangtua,
                    $this->hari_lahir_orangtua
                )->format('Y-m-d');
            } catch (\Exception $e) {
                $this->tanggal_lahir_orangtua = null;
            }
        } else {
            $this->tanggal_lahir_orangtua = null;
        }
    }
}
