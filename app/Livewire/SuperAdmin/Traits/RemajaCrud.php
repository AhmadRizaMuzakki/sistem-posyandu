<?php

namespace App\Livewire\SuperAdmin\Traits;

use App\Models\sasaran_remaja;
use App\Models\Orangtua;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

trait RemajaCrud
{
    // Modal State
    public $isSasaranRemajaModalOpen = false;

    // Field Form Sasaran Remaja
    public $id_sasaran_remaja = null;
    public $nama_sasaran_remaja;
    public $nik_sasaran_remaja;
    public $no_kk_sasaran_remaja;
    public $tempat_lahir_remaja;
    public $tanggal_lahir_remaja;
    public $hari_lahir_remaja;
    public $bulan_lahir_remaja;
    public $tahun_lahir_remaja;
    public $jenis_kelamin_remaja;
    public $umur_sasaran_remaja;
    public $nik_orangtua_remaja;
    public $alamat_sasaran_remaja;
    public $kepersertaan_bpjs_remaja;
    public $nomor_bpjs_remaja;
    public $nomor_telepon_remaja;

    // Field Form Orangtua
    public $nama_orangtua_remaja;
    public $tempat_lahir_orangtua_remaja;
    public $tanggal_lahir_orangtua_remaja;
    public $pekerjaan_orangtua_remaja;
    public $kelamin_orangtua_remaja;

    /**
     * Buka modal tambah/edit Sasaran Remaja
     */
    public function openRemajaModal($id = null)
    {
        if ($id) {
            $this->editRemaja($id);
        } else {
            $this->resetRemajaFields();
            $this->isSasaranRemajaModalOpen = true;
        }
    }

    /**
     * Tutup modal dan reset field
     */
    public function closeRemajaModal()
    {
        $this->resetRemajaFields();
        $this->isSasaranRemajaModalOpen = false;
    }

    /**
     * Reset semua field form Sasaran Remaja
     */
    private function resetRemajaFields()
    {
        $this->id_sasaran_remaja = null;
        $this->nama_sasaran_remaja = '';
        $this->nik_sasaran_remaja = '';
        $this->no_kk_sasaran_remaja = '';
        $this->tempat_lahir_remaja = '';
        $this->tanggal_lahir_remaja = '';
        $this->jenis_kelamin_remaja = '';
        $this->umur_sasaran_remaja = '';
        $this->nik_orangtua_remaja = '';
        $this->alamat_sasaran_remaja = '';
        $this->kepersertaan_bpjs_remaja = '';
        $this->nomor_bpjs_remaja = '';
        $this->nomor_telepon_remaja = '';
        // Reset field orangtua
        $this->nama_orangtua_remaja = '';
        $this->tempat_lahir_orangtua_remaja = '';
        $this->tanggal_lahir_orangtua_remaja = '';
        $this->pekerjaan_orangtua_remaja = '';
        $this->kelamin_orangtua_remaja = '';
    }

    /**
     * Proses simpan data remaja, tambah/edit
     */
    public function storeRemaja()
    {
        // Combine hari, bulan, tahun menjadi tanggal lahir
        $this->combineTanggalLahirRemaja();

        $this->validate([
            'nama_sasaran_remaja' => 'required|string|max:100',
            'nik_sasaran_remaja' => 'required|numeric',
            'hari_lahir_remaja' => 'required|numeric|min:1|max:31',
            'bulan_lahir_remaja' => 'required|numeric|min:1|max:12',
            'tahun_lahir_remaja' => 'required|numeric|min:1900|max:' . date('Y'),
            'tanggal_lahir_remaja' => 'required|date',
            'jenis_kelamin_remaja' => 'required|in:Laki-laki,Perempuan',
            'alamat_sasaran_remaja' => 'required|string|max:225',
            'nik_orangtua_remaja' => 'required|numeric',
            'nama_orangtua_remaja' => 'required|string|max:100',
            'tempat_lahir_orangtua_remaja' => 'required|string|max:100',
            'tanggal_lahir_orangtua_remaja' => 'required|date',
            'pekerjaan_orangtua_remaja' => 'required|string',
            'kelamin_orangtua_remaja' => 'required|in:Laki-laki,Perempuan',
        ], [
            'nama_sasaran_remaja.required' => 'Nama sasaran wajib diisi.',
            'nik_sasaran_remaja.required' => 'NIK wajib diisi.',
            'nik_sasaran_remaja.numeric' => 'NIK harus berupa angka.',
            'hari_lahir_remaja.required' => 'Hari lahir wajib diisi.',
            'hari_lahir_remaja.numeric' => 'Hari harus berupa angka.',
            'hari_lahir_remaja.min' => 'Hari minimal 1.',
            'hari_lahir_remaja.max' => 'Hari maksimal 31.',
            'bulan_lahir_remaja.required' => 'Bulan lahir wajib diisi.',
            'bulan_lahir_remaja.numeric' => 'Bulan harus berupa angka.',
            'bulan_lahir_remaja.min' => 'Bulan minimal 1.',
            'bulan_lahir_remaja.max' => 'Bulan maksimal 12.',
            'tahun_lahir_remaja.required' => 'Tahun lahir wajib diisi.',
            'tahun_lahir_remaja.numeric' => 'Tahun harus berupa angka.',
            'tahun_lahir_remaja.min' => 'Tahun minimal 1900.',
            'tahun_lahir_remaja.max' => 'Tahun maksimal ' . date('Y') . '.',
            'tanggal_lahir_remaja.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir_remaja.date' => 'Tanggal lahir harus berupa tanggal yang valid.',
            'jenis_kelamin_remaja.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin_remaja.in' => 'Jenis kelamin harus Laki-laki atau Perempuan.',
            'alamat_sasaran_remaja.required' => 'Alamat wajib diisi.',
            'alamat_sasaran_remaja.max' => 'Alamat maksimal 225 karakter.',
            'nik_orangtua_remaja.required' => 'NIK orangtua wajib diisi.',
            'nik_orangtua_remaja.numeric' => 'NIK orangtua harus berupa angka.',
            'nama_orangtua_remaja.required' => 'Nama orangtua wajib diisi.',
            'tempat_lahir_orangtua_remaja.required' => 'Tempat lahir orangtua wajib diisi.',
            'tanggal_lahir_orangtua_remaja.required' => 'Tanggal lahir orangtua wajib diisi.',
            'tanggal_lahir_orangtua_remaja.date' => 'Tanggal lahir orangtua harus berupa tanggal yang valid.',
            'pekerjaan_orangtua_remaja.required' => 'Pekerjaan orangtua wajib dipilih.',
            'kelamin_orangtua_remaja.required' => 'Jenis kelamin orangtua wajib dipilih.',
            'kelamin_orangtua_remaja.in' => 'Jenis kelamin orangtua harus Laki-laki atau Perempuan.',
        ]);

        // Simpan/Update data orangtua
        $orangtuaData = [
            'nik' => $this->nik_orangtua_remaja,
            'nama' => $this->nama_orangtua_remaja,
            'tempat_lahir' => $this->tempat_lahir_orangtua_remaja,
            'tanggal_lahir' => $this->tanggal_lahir_orangtua_remaja,
            'pekerjaan' => $this->pekerjaan_orangtua_remaja,
            'kelamin' => $this->kelamin_orangtua_remaja,
        ];

        // Update atau create orangtua
        Orangtua::updateOrCreate(
            ['nik' => $this->nik_orangtua_remaja],
            $orangtuaData
        );

        // Buat atau update user untuk orangtua
        $email = $this->nik_orangtua_remaja . '@gmail.com';
        $userExists = User::where('email', $email)->first();

        if ($userExists) {
            // Update user yang sudah ada
            $userExists->name = $this->nama_orangtua_remaja;
            $userExists->password = Hash::make($this->nik_orangtua_remaja);
            $userExists->save();
            $user = $userExists;
        } else {
            // Buat user baru
            $user = User::create([
                'name' => $this->nama_orangtua_remaja,
                'email' => $email,
                'password' => Hash::make($this->nik_orangtua_remaja),
                'email_verified_at' => now(),
            ]);
        }

        // Assign role orangtua jika belum punya
        if (!$user->hasRole('orangtua')) {
            $user->assignRole('orangtua');
        }

        // Hitung umur dari tanggal_lahir_remaja
        $umur = null;
        if ($this->tanggal_lahir_remaja) {
            $umur = Carbon::parse($this->tanggal_lahir_remaja)->age;
        }

        $data = [
            'id_users' => $user->id,
            'id_posyandu' => $this->posyanduId,
            'nama_sasaran' => $this->nama_sasaran_remaja,
            'nik_sasaran' => $this->nik_sasaran_remaja,
            'no_kk_sasaran' => $this->no_kk_sasaran_remaja ?: null,
            'tempat_lahir' => $this->tempat_lahir_remaja ?: null,
            'tanggal_lahir' => $this->tanggal_lahir_remaja,
            'jenis_kelamin' => $this->jenis_kelamin_remaja,
            'umur_sasaran' => $umur,
            'nik_orangtua' => $this->nik_orangtua_remaja,
            'alamat_sasaran' => $this->alamat_sasaran_remaja,
            'kepersertaan_bpjs' => $this->kepersertaan_bpjs_remaja ?: null,
            'nomor_bpjs' => $this->nomor_bpjs_remaja ?: null,
            'nomor_telepon' => $this->nomor_telepon_remaja ?: null,
        ];

        if ($this->id_sasaran_remaja) {
            // UPDATE
            $remaja = sasaran_remaja::findOrFail($this->id_sasaran_remaja);
            $remaja->update($data);
            session()->flash('message', 'Data Remaja berhasil diperbarui.');
        } else {
            // CREATE
            sasaran_remaja::create($data);
            session()->flash('message', 'Data Remaja berhasil ditambahkan.');
        }

        $this->refreshPosyandu();
        $this->closeRemajaModal();
    }

    /**
     * Inisialisasi form edit remaja
     */
    public function editRemaja($id)
    {
        $remaja = sasaran_remaja::findOrFail($id);

        $this->id_sasaran_remaja = $remaja->id_sasaran_remaja;
        $this->nama_sasaran_remaja = $remaja->nama_sasaran;
        $this->nik_sasaran_remaja = $remaja->nik_sasaran;
        $this->no_kk_sasaran_remaja = $remaja->no_kk_sasaran ?? '';
        $this->tempat_lahir_remaja = $remaja->tempat_lahir ?? '';
        $this->tanggal_lahir_remaja = $remaja->tanggal_lahir;
        $this->jenis_kelamin_remaja = $remaja->jenis_kelamin;
        $this->umur_sasaran_remaja = $remaja->tanggal_lahir
            ? Carbon::parse($remaja->tanggal_lahir)->age
            : $remaja->umur_sasaran;
        $this->nik_orangtua_remaja = $remaja->nik_orangtua ?? '';
        $this->alamat_sasaran_remaja = $remaja->alamat_sasaran ?? '';
        $this->kepersertaan_bpjs_remaja = $remaja->kepersertaan_bpjs ?? '';
        $this->nomor_bpjs_remaja = $remaja->nomor_bpjs ?? '';
        $this->nomor_telepon_remaja = $remaja->nomor_telepon ?? '';

        // Load data orangtua jika ada
        if ($remaja->nik_orangtua) {
            $orangtua = Orangtua::find($remaja->nik_orangtua);
            if ($orangtua) {
                $this->nama_orangtua_remaja = $orangtua->nama;
                $this->tempat_lahir_orangtua_remaja = $orangtua->tempat_lahir;
                $this->tanggal_lahir_orangtua_remaja = $orangtua->tanggal_lahir ? $orangtua->tanggal_lahir->format('Y-m-d') : '';
                $this->pekerjaan_orangtua_remaja = $orangtua->pekerjaan;
                $this->kelamin_orangtua_remaja = $orangtua->kelamin;
            } else {
                $this->nama_orangtua_remaja = '';
                $this->tempat_lahir_orangtua_remaja = '';
                $this->tanggal_lahir_orangtua_remaja = '';
                $this->pekerjaan_orangtua_remaja = '';
                $this->kelamin_orangtua_remaja = '';
            }
        } else {
            $this->nama_orangtua_remaja = '';
            $this->tempat_lahir_orangtua_remaja = '';
            $this->tanggal_lahir_orangtua_remaja = '';
            $this->pekerjaan_orangtua_remaja = '';
            $this->kelamin_orangtua_remaja = '';
        }

        $this->isSasaranRemajaModalOpen = true;
    }

    /**
     * Hapus data remaja
     */
    public function deleteRemaja($id)
    {
        $remaja = sasaran_remaja::findOrFail($id);
        $remaja->delete();
        $this->refreshPosyandu();
        session()->flash('message', 'Data Remaja berhasil dihapus.');
    }

    /**
     * Combine hari, bulan, tahun menjadi tanggal lahir
     */
    private function combineTanggalLahirRemaja()
    {
        if ($this->hari_lahir_remaja && $this->bulan_lahir_remaja && $this->tahun_lahir_remaja) {
            try {
                $this->tanggal_lahir_remaja = Carbon::create(
                    $this->tahun_lahir_remaja,
                    $this->bulan_lahir_remaja,
                    $this->hari_lahir_remaja
                )->format('Y-m-d');
            } catch (\Exception $e) {
                $this->tanggal_lahir_remaja = null;
            }
        } else {
            $this->tanggal_lahir_remaja = null;
        }
    }

    /**
     * Hitung umur otomatis ketika hari, bulan, atau tahun lahir berubah
     */
    public function updatedHariLahirRemaja()
    {
        $this->calculateUmurRemaja();
    }

    public function updatedBulanLahirRemaja()
    {
        $this->calculateUmurRemaja();
    }

    public function updatedTahunLahirRemaja()
    {
        $this->calculateUmurRemaja();
    }

    /**
     * Calculate umur dari hari, bulan, tahun lahir
     */
    private function calculateUmurRemaja()
    {
        if ($this->hari_lahir_remaja && $this->bulan_lahir_remaja && $this->tahun_lahir_remaja) {
            try {
                $tanggalLahir = Carbon::create(
                    $this->tahun_lahir_remaja,
                    $this->bulan_lahir_remaja,
                    $this->hari_lahir_remaja
                );
                $this->umur_sasaran_remaja = $tanggalLahir->age;
                $this->tanggal_lahir_remaja = $tanggalLahir->format('Y-m-d');
            } catch (\Exception $e) {
                $this->umur_sasaran_remaja = '';
                $this->tanggal_lahir_remaja = null;
            }
        } else {
            $this->umur_sasaran_remaja = '';
            $this->tanggal_lahir_remaja = null;
        }
    }
}

