<?php

namespace App\Livewire\SuperAdmin\Traits;

use App\Models\User;
use App\Models\PetugasKesehatan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

trait PetugasKesehatanCrud
{
    // Modal State
    public $isPetugasKesehatanModalOpen = false;

    // Field Form Petugas Kesehatan
    public $id_petugas_kesehatan = null;
    public $nama_petugas_kesehatan;
    public $posyandu_id_petugas_kesehatan;
    public $nik_petugas_kesehatan;
    public $tanggal_lahir;
    public $hari_lahir;
    public $bulan_lahir;
    public $tahun_lahir;
    public $alamat_petugas_kesehatan;
    public $bidan;

    /**
     * Buka modal tambah/edit Petugas Kesehatan
     */
    public function openPetugasKesehatanModal($id = null)
    {
        if ($id) {
            $this->editPetugasKesehatan($id);
        } else {
            $this->resetPetugasKesehatanFields();
            $this->posyandu_id_petugas_kesehatan = $this->posyanduId;
            $this->isPetugasKesehatanModalOpen = true;
        }
    }

    /**
     * Tutup modal dan reset field
     */
    public function closePetugasKesehatanModal()
    {
        $this->resetPetugasKesehatanFields();
        $this->isPetugasKesehatanModalOpen = false;
    }

    /**
     * Reset semua field form Petugas Kesehatan
     */
    private function resetPetugasKesehatanFields()
    {
        $this->id_petugas_kesehatan = null;
        $this->nama_petugas_kesehatan = '';
        $this->posyandu_id_petugas_kesehatan = '';
        $this->nik_petugas_kesehatan = '';
        $this->tanggal_lahir = '';
        $this->hari_lahir = '';
        $this->bulan_lahir = '';
        $this->tahun_lahir = '';
        $this->alamat_petugas_kesehatan = '';
        $this->bidan = '';
    }

    /**
     * Proses simpan data petugas kesehatan, tambah/edit
     */
    public function storePetugasKesehatan()
    {
        if (empty($this->posyandu_id_petugas_kesehatan) && isset($this->posyanduId)) {
            $this->posyandu_id_petugas_kesehatan = $this->posyanduId;
        }
        
        $user = Auth::user();
        
        if (!$user) {
            session()->flash('message', 'Anda harus login terlebih dahulu.');
            session()->flash('messageType', 'error');
            return;
        }
        
        $isSuperadmin = $user->hasRole('superadmin');
        $isKetua = false;

        if (!$isSuperadmin) {
            $kaderUser = \App\Models\Kader::where('id_users', $user->id)
                ->where('id_posyandu', $this->posyandu_id_petugas_kesehatan ?? $this->posyanduId)
                ->where('jabatan_kader', 'Ketua')
                ->first();
            $isKetua = $kaderUser !== null;
        }

        $this->combineTanggalLahirPetugasKesehatan();

        $rules = [
            'nama_petugas_kesehatan' => 'required|string|max:255',
            'posyandu_id_petugas_kesehatan' => 'required|exists:posyandu,id_posyandu',
            'nik_petugas_kesehatan' => 'required|string|max:50',
            'hari_lahir' => 'required|numeric|min:1|max:31',
            'bulan_lahir' => 'required|numeric|min:1|max:12',
            'tahun_lahir' => 'required|numeric|min:1900|max:' . date('Y'),
            'tanggal_lahir' => 'required|date',
            'alamat_petugas_kesehatan' => 'required|string|max:255',
            'bidan' => 'required|in:Bidan Desa,Dokter Desa',
        ];

        $messages = [
            'nama_petugas_kesehatan.required' => 'Nama petugas kesehatan wajib diisi.',
            'posyandu_id_petugas_kesehatan.required' => 'Posyandu wajib dipilih.',
            'posyandu_id_petugas_kesehatan.exists' => 'Posyandu yang dipilih tidak valid.',
            'nik_petugas_kesehatan.required' => 'NIK petugas kesehatan wajib diisi.',
            'hari_lahir.required' => 'Hari lahir wajib diisi.',
            'bulan_lahir.required' => 'Bulan lahir wajib diisi.',
            'tahun_lahir.required' => 'Tahun lahir wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'alamat_petugas_kesehatan.required' => 'Alamat petugas kesehatan wajib diisi.',
            'bidan.required' => 'Jenis petugas kesehatan wajib dipilih.',
            'bidan.in' => 'Jenis petugas kesehatan harus Bidan Desa atau Dokter Desa.',
        ];

        $this->validate($rules, $messages);

        if ($this->id_petugas_kesehatan) {
            // UPDATE
            $petugasKesehatan = PetugasKesehatan::findOrFail($this->id_petugas_kesehatan);
            
            DB::transaction(function () use ($petugasKesehatan) {
                // Hapus user jika ada (petugas kesehatan tidak boleh punya akun)
                if ($petugasKesehatan->id_users) {
                    $user = $petugasKesehatan->user;
                    $petugasKesehatan->id_users = null;
                    $petugasKesehatan->save();
                    
                    // Hapus user jika tidak digunakan oleh petugas kesehatan lain
                    if ($user && PetugasKesehatan::where('id_users', $user->id)->count() === 0) {
                        $user->delete();
                    }
                }

                $petugasKesehatan->id_posyandu = $this->posyandu_id_petugas_kesehatan;
                $petugasKesehatan->nama_petugas_kesehatan = $this->nama_petugas_kesehatan;
                $petugasKesehatan->nik_petugas_kesehatan = $this->nik_petugas_kesehatan;
                $petugasKesehatan->tanggal_lahir = $this->tanggal_lahir;
                $petugasKesehatan->alamat_petugas_kesehatan = $this->alamat_petugas_kesehatan;
                $petugasKesehatan->bidan = $this->bidan;
                $petugasKesehatan->save();
            });

            session()->flash('message', 'Data Petugas Kesehatan berhasil diperbarui.');
        } else {
            // CREATE - Tidak membuat user account
            DB::transaction(function () {
                PetugasKesehatan::create([
                    'id_users' => null,
                    'nama_petugas_kesehatan' => $this->nama_petugas_kesehatan,
                    'id_posyandu' => $this->posyandu_id_petugas_kesehatan,
                    'nik_petugas_kesehatan' => $this->nik_petugas_kesehatan,
                    'tanggal_lahir' => $this->tanggal_lahir,
                    'alamat_petugas_kesehatan' => $this->alamat_petugas_kesehatan,
                    'bidan' => $this->bidan,
                ]);
            });

            session()->flash('message', 'Data Petugas Kesehatan berhasil ditambahkan.');
        }

        $this->refreshPosyandu();
        $this->closePetugasKesehatanModal();
    }

    /**
     * Inisialisasi form edit petugas kesehatan
     */
    public function editPetugasKesehatan($id)
    {
        $petugasKesehatan = PetugasKesehatan::findOrFail($id);

        $this->id_petugas_kesehatan = $petugasKesehatan->id_petugas_kesehatan;
        $this->nama_petugas_kesehatan = $petugasKesehatan->nama_petugas_kesehatan ?? '';
        $this->posyandu_id_petugas_kesehatan = $petugasKesehatan->id_posyandu;
        $this->nik_petugas_kesehatan = $petugasKesehatan->nik_petugas_kesehatan ?? '';
        $this->tanggal_lahir = $petugasKesehatan->tanggal_lahir ?? '';
        
        if ($petugasKesehatan->tanggal_lahir) {
            $tanggal = Carbon::parse($petugasKesehatan->tanggal_lahir);
            $this->hari_lahir = $tanggal->day;
            $this->bulan_lahir = $tanggal->month;
            $this->tahun_lahir = $tanggal->year;
        } else {
            $this->hari_lahir = '';
            $this->bulan_lahir = '';
            $this->tahun_lahir = '';
        }
        
        $this->alamat_petugas_kesehatan = $petugasKesehatan->alamat_petugas_kesehatan ?? '';
        $this->bidan = $petugasKesehatan->bidan ?? '';

        $this->isPetugasKesehatanModalOpen = true;
    }

    /**
     * Combine hari, bulan, tahun menjadi tanggal lahir
     */
    private function combineTanggalLahirPetugasKesehatan()
    {
        if ($this->hari_lahir && $this->bulan_lahir && $this->tahun_lahir) {
            try {
                $this->tanggal_lahir = Carbon::create(
                    $this->tahun_lahir,
                    $this->bulan_lahir,
                    $this->hari_lahir
                )->format('Y-m-d');
            } catch (\Exception $e) {
                $this->tanggal_lahir = null;
            }
        } else {
            $this->tanggal_lahir = null;
        }
    }

    /**
     * Hapus data petugas kesehatan
     */
    public function deletePetugasKesehatan($id)
    {
        $petugasKesehatan = PetugasKesehatan::findOrFail($id);

        $user = $petugasKesehatan->user;
        
        DB::transaction(function () use ($petugasKesehatan, $user) {
            $petugasKesehatan->delete();

            if ($user && PetugasKesehatan::where('id_users', $user->id)->count() === 0) {
                $user->delete();
            }
        });

        $this->refreshPosyandu();
        session()->flash('message', 'Data Petugas Kesehatan berhasil dihapus.');
    }
}

