<?php

namespace App\Livewire\Posyandu\Traits;

use App\Models\SasaranLansia;
use App\Models\SasaranBayibalita;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

trait LansiaCrud
{
    // Modal State
    public $isSasaranLansiaModalOpen = false;

    // Field Form Sasaran Lansia
    public $id_sasaran_lansia = null;
    public $nama_sasaran_lansia;
    public $nik_sasaran_lansia;
    public $no_kk_sasaran_lansia;
    public $tempat_lahir_lansia;
    public $tanggal_lahir_lansia;
    public $hari_lahir_lansia;
    public $bulan_lahir_lansia;
    public $tahun_lahir_lansia;
    public $jenis_kelamin_lansia;
    public $umur_sasaran_lansia;
    public $pekerjaan_lansia;
    public $pendidikan_lansia;
    public $alamat_sasaran_lansia;
    public $rt_lansia;
    public $rw_lansia;
    public $kepersertaan_bpjs_lansia;
    public $nomor_bpjs_lansia;
    public $nomor_telepon_lansia;
    public $id_users_sasaran_lansia;
    public $id_posyandu_sasaran;

    /**
     * Buka modal tambah/edit Sasaran Lansia
     */
    public function openLansiaModal($id = null)
    {
        $this->searchUser = ''; // Reset search user
        if ($id) {
            $this->editLansia($id);
        } else {
            $this->resetLansiaFields();
            $this->isSasaranLansiaModalOpen = true;
        }
    }

    /**
     * Tutup modal dan reset field
     */
    public function closeLansiaModal()
    {
        $this->resetLansiaFields();
        $this->searchUser = ''; // Reset search user
        $this->isSasaranLansiaModalOpen = false;
    }

    /**
     * Reset semua field form Sasaran Lansia
     */
    private function resetLansiaFields()
    {
        $this->id_sasaran_lansia = null;
        $this->nama_sasaran_lansia = '';
        $this->nik_sasaran_lansia = '';
        $this->no_kk_sasaran_lansia = '';
        $this->tempat_lahir_lansia = '';
        $this->tanggal_lahir_lansia = '';
        $this->hari_lahir_lansia = '';
        $this->bulan_lahir_lansia = '';
        $this->tahun_lahir_lansia = '';
        $this->jenis_kelamin_lansia = '';
        $this->umur_sasaran_lansia = '';
        $this->pekerjaan_lansia = '';
        $this->pendidikan_lansia = '';
        $this->alamat_sasaran_lansia = '';
        $this->rt_lansia = '';
        $this->rw_lansia = '';
        $this->kepersertaan_bpjs_lansia = '';
        $this->nomor_bpjs_lansia = '';
        $this->nomor_telepon_lansia = '';
        $this->id_users_sasaran_lansia = '';
    }

    /**
     * Proses simpan data lansia, tambah/edit
     */
    public function storeLansia()
    {
        // Combine hari, bulan, tahun menjadi tanggal lahir
        $this->combineTanggalLahirLansia();

        $this->validate([
            'nama_sasaran_lansia' => 'required|string|max:100',
            'nik_sasaran_lansia' => 'required|numeric',
            'no_kk_sasaran_lansia' => 'required|numeric',
            'hari_lahir_lansia' => 'required|numeric|min:1|max:31',
            'bulan_lahir_lansia' => 'required|numeric|min:1|max:12',
            'tahun_lahir_lansia' => 'required|numeric|min:1900|max:' . date('Y'),
            'tanggal_lahir_lansia' => 'required|date',
            'jenis_kelamin_lansia' => 'required|in:Laki-laki,Perempuan',
            'alamat_sasaran_lansia' => 'required|string|max:225',
            'pendidikan_lansia' => 'nullable|string',
        ], [
            'nama_sasaran_lansia.required' => 'Nama sasaran wajib diisi.',
            'nik_sasaran_lansia.required' => 'NIK wajib diisi.',
            'nik_sasaran_lansia.numeric' => 'NIK harus berupa angka.',
            'no_kk_sasaran_lansia.required' => 'No KK wajib diisi.',
            'no_kk_sasaran_lansia.numeric' => 'No KK harus berupa angka.',
            'hari_lahir_lansia.required' => 'Hari lahir wajib diisi.',
            'hari_lahir_lansia.numeric' => 'Hari harus berupa angka.',
            'hari_lahir_lansia.min' => 'Hari minimal 1.',
            'hari_lahir_lansia.max' => 'Hari maksimal 31.',
            'bulan_lahir_lansia.required' => 'Bulan lahir wajib diisi.',
            'bulan_lahir_lansia.numeric' => 'Bulan harus berupa angka.',
            'bulan_lahir_lansia.min' => 'Bulan minimal 1.',
            'bulan_lahir_lansia.max' => 'Bulan maksimal 12.',
            'tahun_lahir_lansia.required' => 'Tahun lahir wajib diisi.',
            'tahun_lahir_lansia.numeric' => 'Tahun harus berupa angka.',
            'tahun_lahir_lansia.min' => 'Tahun minimal 1900.',
            'tahun_lahir_lansia.max' => 'Tahun maksimal ' . date('Y') . '.',
            'tanggal_lahir_lansia.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir_lansia.date' => 'Tanggal lahir harus berupa tanggal yang valid.',
            'jenis_kelamin_lansia.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin_lansia.in' => 'Jenis kelamin harus Laki-laki atau Perempuan.',
            'alamat_sasaran_lansia.required' => 'Alamat wajib diisi.',
            'alamat_sasaran_lansia.max' => 'Alamat maksimal 225 karakter.',
            'pendidikan_lansia.string' => 'Pendidikan harus berupa teks.',
        ]);

        // Hitung umur dari tanggal_lahir_lansia
        $umur = null;
        if ($this->tanggal_lahir_lansia) {
            $umur = Carbon::parse($this->tanggal_lahir_lansia)->age;
        }

        // Cari nik_orangtua dari sasaran balita jika ada no_kk dan alamat yang sama
        $nik_orangtua = null;
        $lansia = null;
        
        // Jika update, cek apakah sudah ada nik_orangtua
        if ($this->id_sasaran_lansia) {
            $lansia = SasaranLansia::find($this->id_sasaran_lansia);
            if ($lansia && $lansia->nik_orangtua) {
                $nik_orangtua = $lansia->nik_orangtua;
            }
        }
        
        // Jika belum ada nik_orangtua, cari dari sasaran balita
        if (!$nik_orangtua && $this->no_kk_sasaran_lansia && $this->alamat_sasaran_lansia) {
            $existingBalita = SasaranBayibalita::where('no_kk_sasaran', $this->no_kk_sasaran_lansia)
                ->where('alamat_sasaran', $this->alamat_sasaran_lansia)
                ->whereNotNull('nik_orangtua')
                ->first();
            
            if ($existingBalita && $existingBalita->nik_orangtua) {
                $nik_orangtua = $existingBalita->nik_orangtua;
            }
        }

        // Buat atau update user untuk sasaran lansia berdasarkan No KK
        $userId = null;
        if ($this->id_users_sasaran_lansia !== '') {
            // Jika user sudah dipilih manual, gunakan itu
            $userId = $this->id_users_sasaran_lansia;
        } else {
            // Pastikan no_kk tersedia
            if (empty($this->no_kk_sasaran_lansia)) {
                throw new \Exception('No KK wajib diisi untuk membuat akun.');
            }

            // Buat akun otomatis berdasarkan No KK sasaran
            $email = $this->no_kk_sasaran_lansia . '@gmail.com';
            $userExists = User::where('email', $email)->first();

            if ($userExists) {
                // Update user yang sudah ada (timpa data jika No KK sama)
                $userExists->name = $this->nama_sasaran_lansia;
                $userExists->password = Hash::make($this->no_kk_sasaran_lansia);
                $userExists->save();
                $userId = $userExists->id;
            } else {
                // Buat user baru
                $user = User::create([
                    'name' => $this->nama_sasaran_lansia,
                    'email' => $email,
                    'password' => Hash::make($this->no_kk_sasaran_lansia),
                    'email_verified_at' => now(),
                ]);
                $userId = $user->id;

                // Assign role orangtua jika belum punya
                if (!$user->hasRole('orangtua')) {
                    $user->assignRole('orangtua');
                }
            }
        }

        // Gunakan id_posyandu_sasaran jika ada, jika tidak gunakan posyanduId dari kader
        $posyanduId = $this->id_posyandu_sasaran ?? $this->setPosyanduFromKader();

        $data = [
            'id_users' => $userId,
            'id_posyandu' => $posyanduId,
            'nama_sasaran' => $this->nama_sasaran_lansia,
            'nik_sasaran' => $this->nik_sasaran_lansia,
            'no_kk_sasaran' => $this->no_kk_sasaran_lansia ?: null,
            'tempat_lahir' => $this->tempat_lahir_lansia ?: null,
            'tanggal_lahir' => $this->tanggal_lahir_lansia,
            'jenis_kelamin' => $this->jenis_kelamin_lansia,
            'umur_sasaran' => $umur,
            'pekerjaan' => $this->pekerjaan_lansia ?: null,
            'pendidikan' => $this->pendidikan_lansia ?: null,
            'nik_orangtua' => $nik_orangtua,
            'alamat_sasaran' => $this->alamat_sasaran_lansia,
            'rt' => $this->rt_lansia ?: null,
            'rw' => $this->rw_lansia ?: null,
            'kepersertaan_bpjs' => $this->kepersertaan_bpjs_lansia ?: null,
            'nomor_bpjs' => $this->nomor_bpjs_lansia ?: null,
            'nomor_telepon' => $this->nomor_telepon_lansia ?: null,
        ];

        if ($this->id_sasaran_lansia) {
            // UPDATE
            if (!$lansia) {
                $lansia = SasaranLansia::findOrFail($this->id_sasaran_lansia);
            }
            $lansia->update($data);
            session()->flash('message', 'Data Lansia berhasil diperbarui.');
        } else {
            // CREATE
            SasaranLansia::create($data);
            session()->flash('message', 'Data Lansia berhasil ditambahkan.');
        }

        $this->refreshPosyandu();
        $this->closeLansiaModal();
    }

    /**
     * Inisialisasi form edit lansia
     */
    public function editLansia($id)
    {
        $this->searchUser = ''; // Reset search user
        $lansia = SasaranLansia::findOrFail($id);

        $this->id_sasaran_lansia = $lansia->id_sasaran_lansia;
        $this->nama_sasaran_lansia = $lansia->nama_sasaran;
        $this->nik_sasaran_lansia = $lansia->nik_sasaran;
        $this->no_kk_sasaran_lansia = $lansia->no_kk_sasaran ?? '';
        $this->tempat_lahir_lansia = $lansia->tempat_lahir ?? '';
        $this->tanggal_lahir_lansia = $lansia->tanggal_lahir;
        // Split tanggal lahir menjadi hari, bulan, tahun
        if ($lansia->tanggal_lahir) {
            $date = Carbon::parse($lansia->tanggal_lahir);
            $this->hari_lahir_lansia = $date->day;
            $this->bulan_lahir_lansia = $date->month;
            $this->tahun_lahir_lansia = $date->year;
        } else {
            $this->hari_lahir_lansia = '';
            $this->bulan_lahir_lansia = '';
            $this->tahun_lahir_lansia = '';
        }
        $this->jenis_kelamin_lansia = $lansia->jenis_kelamin;
        $this->umur_sasaran_lansia = $lansia->tanggal_lahir
            ? Carbon::parse($lansia->tanggal_lahir)->age
            : $lansia->umur_sasaran;
        $this->pekerjaan_lansia = $lansia->pekerjaan ?? '';
        $this->pendidikan_lansia = $lansia->pendidikan ?? '';
        $this->alamat_sasaran_lansia = $lansia->alamat_sasaran ?? '';
        $this->rt_lansia = $lansia->rt ?? '';
        $this->rw_lansia = $lansia->rw ?? '';
        $this->kepersertaan_bpjs_lansia = $lansia->kepersertaan_bpjs ?? '';
        $this->nomor_bpjs_lansia = $lansia->nomor_bpjs ?? '';
        $this->nomor_telepon_lansia = $lansia->nomor_telepon ?? '';
        $this->id_users_sasaran_lansia = $lansia->id_users ?? '';
        $this->id_posyandu_sasaran = $lansia->id_posyandu ?? '';

        $this->isSasaranLansiaModalOpen = true;
    }

    /**
     * Hapus data lansia
     */
    public function deleteLansia($id)
    {
        $lansia = SasaranLansia::findOrFail($id);
        $lansia->delete();
        $this->refreshPosyandu();
        session()->flash('message', 'Data Lansia berhasil dihapus.');
    }

    /**
     * Combine hari, bulan, tahun menjadi tanggal lahir
     */
    private function combineTanggalLahirLansia()
    {
        if ($this->hari_lahir_lansia && $this->bulan_lahir_lansia && $this->tahun_lahir_lansia) {
            try {
                $this->tanggal_lahir_lansia = Carbon::create(
                    $this->tahun_lahir_lansia,
                    $this->bulan_lahir_lansia,
                    $this->hari_lahir_lansia
                )->format('Y-m-d');
            } catch (\Exception $e) {
                $this->tanggal_lahir_lansia = null;
            }
        } else {
            $this->tanggal_lahir_lansia = null;
        }
    }

    /**
     * Hitung umur otomatis ketika hari, bulan, atau tahun lahir berubah
     */
    public function updatedHariLahirLansia()
    {
        $this->calculateUmurLansia();
    }

    public function updatedBulanLahirLansia()
    {
        $this->calculateUmurLansia();
    }

    public function updatedTahunLahirLansia()
    {
        $this->calculateUmurLansia();
    }

    /**
     * Calculate umur dari hari, bulan, tahun lahir
     */
    private function calculateUmurLansia()
    {
        if ($this->hari_lahir_lansia && $this->bulan_lahir_lansia && $this->tahun_lahir_lansia) {
            try {
                $tanggalLahir = Carbon::create(
                    $this->tahun_lahir_lansia,
                    $this->bulan_lahir_lansia,
                    $this->hari_lahir_lansia
                );
                $this->umur_sasaran_lansia = $tanggalLahir->age;
                $this->tanggal_lahir_lansia = $tanggalLahir->format('Y-m-d');
            } catch (\Exception $e) {
                $this->umur_sasaran_lansia = '';
                $this->tanggal_lahir_lansia = null;
            }
        } else {
            $this->umur_sasaran_lansia = '';
            $this->tanggal_lahir_lansia = null;
        }
    }
}

