<?php

namespace App\Livewire\SuperAdmin\Traits;

use App\Models\SasaranRemaja;
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
    public $pendidikan_remaja;
    public $nik_orangtua_remaja;
    public $alamat_sasaran_remaja;
    public $rt_remaja;
    public $rw_remaja;
    public $kepersertaan_bpjs_remaja;
    public $nomor_bpjs_remaja;
    public $nomor_telepon_remaja;

    // Field Form Orangtua
    public $nama_orangtua_remaja;
    public $tempat_lahir_orangtua_remaja;
    public $tanggal_lahir_orangtua_remaja;
    public $hari_lahir_orangtua_remaja;
    public $bulan_lahir_orangtua_remaja;
    public $tahun_lahir_orangtua_remaja;
    public $pekerjaan_orangtua_remaja;
    public $pendidikan_orangtua_remaja;
    public $kelamin_orangtua_remaja;
    public $kepersertaan_bpjs_orangtua_remaja;
    public $nomor_bpjs_orangtua_remaja;
    public $nomor_telepon_orangtua_remaja;

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
        $this->hari_lahir_remaja = '';
        $this->bulan_lahir_remaja = '';
        $this->tahun_lahir_remaja = '';
        $this->jenis_kelamin_remaja = '';
        $this->umur_sasaran_remaja = '';
        $this->pendidikan_remaja = '';
        $this->nik_orangtua_remaja = '';
        $this->alamat_sasaran_remaja = '';
        $this->rt_remaja = '';
        $this->rw_remaja = '';
        $this->kepersertaan_bpjs_remaja = '';
        $this->nomor_bpjs_remaja = '';
        $this->nomor_telepon_remaja = '';
        // Reset field orangtua
        $this->nama_orangtua_remaja = '';
        $this->tempat_lahir_orangtua_remaja = '';
        $this->tanggal_lahir_orangtua_remaja = '';
        $this->hari_lahir_orangtua_remaja = '';
        $this->bulan_lahir_orangtua_remaja = '';
        $this->tahun_lahir_orangtua_remaja = '';
        $this->pekerjaan_orangtua_remaja = '';
        $this->pendidikan_orangtua_remaja = '';
        $this->kelamin_orangtua_remaja = '';
        $this->kepersertaan_bpjs_orangtua_remaja = '';
        $this->nomor_bpjs_orangtua_remaja = '';
        $this->nomor_telepon_orangtua_remaja = '';
    }

    /**
     * Proses simpan data remaja, tambah/edit
     */
    public function storeRemaja()
    {
        // Combine hari, bulan, tahun menjadi tanggal lahir
        $this->combineTanggalLahirRemaja();
        $this->combineTanggalLahirOrangtuaRemaja();

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
            'hari_lahir_orangtua_remaja' => 'required|numeric|min:1|max:31',
            'bulan_lahir_orangtua_remaja' => 'required|numeric|min:1|max:12',
            'tahun_lahir_orangtua_remaja' => 'required|numeric|min:1900|max:' . date('Y'),
            'tanggal_lahir_orangtua_remaja' => 'required|date',
            'pekerjaan_orangtua_remaja' => 'required|string',
            'pendidikan_orangtua_remaja' => 'nullable|string',
            'kelamin_orangtua_remaja' => 'required|in:Laki-laki,Perempuan',
            'kepersertaan_bpjs_orangtua_remaja' => 'nullable|in:PBI,NON PBI',
            'nomor_bpjs_orangtua_remaja' => 'nullable|string|max:50',
            'nomor_telepon_orangtua_remaja' => 'nullable|string|max:20',
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
            'hari_lahir_orangtua_remaja.required' => 'Hari lahir orangtua wajib diisi.',
            'hari_lahir_orangtua_remaja.numeric' => 'Hari lahir orangtua harus berupa angka.',
            'hari_lahir_orangtua_remaja.min' => 'Hari lahir orangtua minimal 1.',
            'hari_lahir_orangtua_remaja.max' => 'Hari lahir orangtua maksimal 31.',
            'bulan_lahir_orangtua_remaja.required' => 'Bulan lahir orangtua wajib diisi.',
            'bulan_lahir_orangtua_remaja.numeric' => 'Bulan lahir orangtua harus berupa angka.',
            'bulan_lahir_orangtua_remaja.min' => 'Bulan lahir orangtua minimal 1.',
            'bulan_lahir_orangtua_remaja.max' => 'Bulan lahir orangtua maksimal 12.',
            'tahun_lahir_orangtua_remaja.required' => 'Tahun lahir orangtua wajib diisi.',
            'tahun_lahir_orangtua_remaja.numeric' => 'Tahun lahir orangtua harus berupa angka.',
            'tahun_lahir_orangtua_remaja.min' => 'Tahun lahir orangtua minimal 1900.',
            'tahun_lahir_orangtua_remaja.max' => 'Tahun lahir orangtua maksimal ' . date('Y') . '.',
            'tanggal_lahir_orangtua_remaja.required' => 'Tanggal lahir orangtua wajib diisi.',
            'tanggal_lahir_orangtua_remaja.date' => 'Tanggal lahir orangtua harus berupa tanggal yang valid.',
            'pekerjaan_orangtua_remaja.required' => 'Pekerjaan orangtua wajib dipilih.',
            'pendidikan_orangtua_remaja.string' => 'Pendidikan orangtua harus berupa teks.',
            'kelamin_orangtua_remaja.required' => 'Jenis kelamin orangtua wajib dipilih.',
            'kelamin_orangtua_remaja.in' => 'Jenis kelamin orangtua harus Laki-laki atau Perempuan.',
            'kepersertaan_bpjs_orangtua_remaja.in' => 'Kepersertaan BPJS orangtua harus PBI atau NON PBI.',
            'nomor_bpjs_orangtua_remaja.max' => 'Nomor BPJS orangtua maksimal 50 karakter.',
            'nomor_telepon_orangtua_remaja.max' => 'Nomor telepon orangtua maksimal 20 karakter.',
        ]);

        // Simpan/Update data orangtua
        // Cari orangtua berdasarkan no_kk dan alamat dari remaja
        $orangtuaData = [
            'nik' => $this->nik_orangtua_remaja,
            'nama' => $this->nama_orangtua_remaja,
            'no_kk' => $this->no_kk_sasaran_remaja, // Gunakan no_kk dari remaja
            'tempat_lahir' => $this->tempat_lahir_orangtua_remaja,
            'tanggal_lahir' => $this->tanggal_lahir_orangtua_remaja,
            'pekerjaan' => $this->pekerjaan_orangtua_remaja,
            'pendidikan' => $this->pendidikan_orangtua_remaja ?: null,
            'kelamin' => $this->kelamin_orangtua_remaja,
            'alamat' => $this->alamat_sasaran_remaja, // Gunakan alamat dari remaja
            'kepersertaan_bpjs' => $this->kepersertaan_bpjs_orangtua_remaja ?: null,
            'nomor_bpjs' => $this->nomor_bpjs_orangtua_remaja ?: null,
            'nomor_telepon' => $this->nomor_telepon_orangtua_remaja ?: null,
        ];

        // Update atau create orangtua berdasarkan nik, no_kk, dan alamat
        // Jika ada remaja dengan no_kk dan alamat yang sama, gunakan data orangtua dari remaja tersebut
        $existingRemaja = SasaranRemaja::where('no_kk_sasaran', $this->no_kk_sasaran_remaja)
            ->where('alamat_sasaran', $this->alamat_sasaran_remaja)
            ->whereNotNull('nik_orangtua')
            ->first();

        if ($existingRemaja && $existingRemaja->nik_orangtua) {
            // Gunakan nik orangtua yang sudah ada dari remaja dengan no_kk dan alamat yang sama
            $orangtuaData['nik'] = $existingRemaja->nik_orangtua;
            $this->nik_orangtua_remaja = $existingRemaja->nik_orangtua;
            
            // Pastikan no_kk selalu diambil dari remaja
            $orangtuaData['no_kk'] = $existingRemaja->no_kk_sasaran ?? $this->no_kk_sasaran_remaja;
            $orangtuaData['alamat'] = $existingRemaja->alamat_sasaran ?? $this->alamat_sasaran_remaja;

            // Update data orangtua jika ada perubahan
            $existingOrangtua = Orangtua::find($existingRemaja->nik_orangtua);
            if ($existingOrangtua) {
                $orangtuaData['nama'] = $existingOrangtua->nama ?? $this->nama_orangtua_remaja;
                $orangtuaData['tempat_lahir'] = $existingOrangtua->tempat_lahir ?? $this->tempat_lahir_orangtua_remaja;
                $orangtuaData['tanggal_lahir'] = $existingOrangtua->tanggal_lahir ?? $this->tanggal_lahir_orangtua_remaja;
                $orangtuaData['pekerjaan'] = $existingOrangtua->pekerjaan ?? $this->pekerjaan_orangtua_remaja;
                $orangtuaData['pendidikan'] = $existingOrangtua->pendidikan ?? ($this->pendidikan_orangtua_remaja ?: null);
                $orangtuaData['kelamin'] = $existingOrangtua->kelamin ?? $this->kelamin_orangtua_remaja;
                $orangtuaData['kepersertaan_bpjs'] = $existingOrangtua->kepersertaan_bpjs ?? ($this->kepersertaan_bpjs_orangtua_remaja ?: null);
                $orangtuaData['nomor_bpjs'] = $existingOrangtua->nomor_bpjs ?? ($this->nomor_bpjs_orangtua_remaja ?: null);
                $orangtuaData['nomor_telepon'] = $existingOrangtua->nomor_telepon ?? ($this->nomor_telepon_orangtua_remaja ?: null);
            }
        }

        // Update atau create orangtua
        $orangtua = Orangtua::updateOrCreate(
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

        // Buat atau update record sasaran dewasa/pralansia/lansia dari data orangtua
        // Panggil setelah user dibuat agar user sudah tersedia
        if (method_exists($this, 'createOrUpdateSasaranFromOrangtua')) {
            $this->createOrUpdateSasaranFromOrangtua(
                $orangtua,
                $this->posyanduId,
                $this->rt_remaja,
                $this->rw_remaja
            );
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
            'pendidikan' => $this->pendidikan_remaja ?: null,
            'nik_orangtua' => $this->nik_orangtua_remaja,
            'alamat_sasaran' => $this->alamat_sasaran_remaja,
            'rt' => $this->rt_remaja ?: null,
            'rw' => $this->rw_remaja ?: null,
            'kepersertaan_bpjs' => $this->kepersertaan_bpjs_remaja ?: null,
            'nomor_bpjs' => $this->nomor_bpjs_remaja ?: null,
            'nomor_telepon' => $this->nomor_telepon_remaja ?: null,
        ];

        if ($this->id_sasaran_remaja) {
            // UPDATE
            $remaja = SasaranRemaja::findOrFail($this->id_sasaran_remaja);
            $remaja->update($data);
            session()->flash('message', 'Data Remaja berhasil diperbarui.');
        } else {
            // CREATE
            SasaranRemaja::create($data);
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
        $remaja = SasaranRemaja::findOrFail($id);

        $this->id_sasaran_remaja = $remaja->id_sasaran_remaja;
        $this->nama_sasaran_remaja = $remaja->nama_sasaran;
        $this->nik_sasaran_remaja = $remaja->nik_sasaran;
        $this->no_kk_sasaran_remaja = $remaja->no_kk_sasaran ?? '';
        $this->tempat_lahir_remaja = $remaja->tempat_lahir ?? '';
        $this->tanggal_lahir_remaja = $remaja->tanggal_lahir;
        // Split tanggal lahir menjadi hari, bulan, tahun
        if ($remaja->tanggal_lahir) {
            $date = Carbon::parse($remaja->tanggal_lahir);
            $this->hari_lahir_remaja = $date->day;
            $this->bulan_lahir_remaja = $date->month;
            $this->tahun_lahir_remaja = $date->year;
        } else {
            $this->hari_lahir_remaja = '';
            $this->bulan_lahir_remaja = '';
            $this->tahun_lahir_remaja = '';
        }
        $this->jenis_kelamin_remaja = $remaja->jenis_kelamin;
        $this->umur_sasaran_remaja = $remaja->tanggal_lahir
            ? Carbon::parse($remaja->tanggal_lahir)->age
            : $remaja->umur_sasaran;
        $this->pendidikan_remaja = $remaja->pendidikan ?? '';
        $this->nik_orangtua_remaja = $remaja->nik_orangtua ?? '';
        $this->alamat_sasaran_remaja = $remaja->alamat_sasaran ?? '';
        $this->rt_remaja = $remaja->rt ?? '';
        $this->rw_remaja = $remaja->rw ?? '';
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
                if ($orangtua->tanggal_lahir) {
                    $dateOrtu = Carbon::parse($orangtua->tanggal_lahir);
                    $this->hari_lahir_orangtua_remaja = $dateOrtu->day;
                    $this->bulan_lahir_orangtua_remaja = $dateOrtu->month;
                    $this->tahun_lahir_orangtua_remaja = $dateOrtu->year;
                } else {
                    $this->hari_lahir_orangtua_remaja = '';
                    $this->bulan_lahir_orangtua_remaja = '';
                    $this->tahun_lahir_orangtua_remaja = '';
                }
                $this->pekerjaan_orangtua_remaja = $orangtua->pekerjaan;
                $this->pendidikan_orangtua_remaja = $orangtua->pendidikan ?? '';
                $this->kelamin_orangtua_remaja = $orangtua->kelamin;
                $this->kepersertaan_bpjs_orangtua_remaja = $orangtua->kepersertaan_bpjs ?? '';
                $this->nomor_bpjs_orangtua_remaja = $orangtua->nomor_bpjs ?? '';
                $this->nomor_telepon_orangtua_remaja = $orangtua->nomor_telepon ?? '';
            } else {
                $this->nama_orangtua_remaja = '';
                $this->tempat_lahir_orangtua_remaja = '';
                $this->tanggal_lahir_orangtua_remaja = '';
                $this->hari_lahir_orangtua_remaja = '';
                $this->bulan_lahir_orangtua_remaja = '';
                $this->tahun_lahir_orangtua_remaja = '';
                $this->pekerjaan_orangtua_remaja = '';
                $this->pendidikan_orangtua_remaja = '';
                $this->kelamin_orangtua_remaja = '';
                $this->kepersertaan_bpjs_orangtua_remaja = '';
                $this->nomor_bpjs_orangtua_remaja = '';
                $this->nomor_telepon_orangtua_remaja = '';
            }
        } else {
            $this->nama_orangtua_remaja = '';
            $this->tempat_lahir_orangtua_remaja = '';
            $this->tanggal_lahir_orangtua_remaja = '';
            $this->pekerjaan_orangtua_remaja = '';
            $this->pendidikan_orangtua_remaja = '';
            $this->kelamin_orangtua_remaja = '';
            $this->hari_lahir_orangtua_remaja = '';
            $this->bulan_lahir_orangtua_remaja = '';
            $this->tahun_lahir_orangtua_remaja = '';
            $this->kepersertaan_bpjs_orangtua_remaja = '';
            $this->nomor_bpjs_orangtua_remaja = '';
            $this->nomor_telepon_orangtua_remaja = '';
        }

        $this->isSasaranRemajaModalOpen = true;
    }

    /**
     * Hapus data remaja
     */
    public function deleteRemaja($id)
    {
        $remaja = SasaranRemaja::findOrFail($id);
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
     * Combine hari, bulan, tahun menjadi tanggal lahir orangtua
     */
    private function combineTanggalLahirOrangtuaRemaja()
    {
        if ($this->hari_lahir_orangtua_remaja && $this->bulan_lahir_orangtua_remaja && $this->tahun_lahir_orangtua_remaja) {
            try {
                $this->tanggal_lahir_orangtua_remaja = Carbon::create(
                    $this->tahun_lahir_orangtua_remaja,
                    $this->bulan_lahir_orangtua_remaja,
                    $this->hari_lahir_orangtua_remaja
                )->format('Y-m-d');
            } catch (\Exception $e) {
                $this->tanggal_lahir_orangtua_remaja = null;
            }
        } else {
            $this->tanggal_lahir_orangtua_remaja = null;
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
     * Get list of existing No KK with detailed information for autocomplete
     */
    public function getNoKkListRemaja()
    {
        $posyanduId = $this->posyanduId ?? null;
        
        if (!$posyanduId) {
            return [];
        }
        
        // Get all no_kk from balita and remaja in the same posyandu with orangtua info
        $noKkList = [];
        
        // Get from balita
        $balitaList = \App\Models\SasaranBayibalita::where('id_posyandu', $posyanduId)
            ->whereNotNull('no_kk_sasaran')
            ->whereNotNull('nik_orangtua')
            ->with('orangtua')
            ->get();
            
        foreach ($balitaList as $balita) {
            $noKk = $balita->no_kk_sasaran;
            if (!isset($noKkList[$noKk])) {
                $orangtua = $balita->orangtua;
                $namaOrtu = $orangtua ? $orangtua->nama : '-';
                
                // Count anggota keluarga dengan no_kk yang sama
                $countBalita = \App\Models\SasaranBayibalita::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $countRemaja = SasaranRemaja::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $totalAnggota = $countBalita + $countRemaja;
                
                $noKkList[$noKk] = [
                    'no_kk' => $noKk,
                    'nama_orangtua' => $namaOrtu,
                    'jumlah_anggota' => $totalAnggota,
                    'nik_orangtua' => $orangtua ? $orangtua->nik : null,
                    'orangtua_data' => $orangtua ? [
                        'nik' => $orangtua->nik,
                        'nama' => $orangtua->nama,
                        'tempat_lahir' => $orangtua->tempat_lahir,
                        'tanggal_lahir' => $orangtua->tanggal_lahir ? $orangtua->tanggal_lahir->format('Y-m-d') : null,
                        'hari_lahir' => $orangtua->tanggal_lahir ? $orangtua->tanggal_lahir->day : null,
                        'bulan_lahir' => $orangtua->tanggal_lahir ? $orangtua->tanggal_lahir->month : null,
                        'tahun_lahir' => $orangtua->tanggal_lahir ? $orangtua->tanggal_lahir->year : null,
                        'pekerjaan' => $orangtua->pekerjaan,
                        'pendidikan' => $orangtua->pendidikan,
                        'kelamin' => $orangtua->kelamin,
                        'kepersertaan_bpjs' => $orangtua->kepersertaan_bpjs,
                        'nomor_bpjs' => $orangtua->nomor_bpjs,
                        'nomor_telepon' => $orangtua->nomor_telepon,
                    ] : null
                ];
            }
        }
        
        // Get from remaja
        $remajaList = SasaranRemaja::where('id_posyandu', $posyanduId)
            ->whereNotNull('no_kk_sasaran')
            ->whereNotNull('nik_orangtua')
            ->with('orangtua')
            ->get();
            
        foreach ($remajaList as $remaja) {
            $noKk = $remaja->no_kk_sasaran;
            if (!isset($noKkList[$noKk])) {
                $orangtua = $remaja->orangtua;
                $namaOrtu = $orangtua ? $orangtua->nama : '-';
                
                // Count anggota keluarga dengan no_kk yang sama
                $countBalita = \App\Models\SasaranBayibalita::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $countRemaja = SasaranRemaja::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $totalAnggota = $countBalita + $countRemaja;
                
                $noKkList[$noKk] = [
                    'no_kk' => $noKk,
                    'nama_orangtua' => $namaOrtu,
                    'jumlah_anggota' => $totalAnggota,
                    'nik_orangtua' => $orangtua ? $orangtua->nik : null,
                    'orangtua_data' => $orangtua ? [
                        'nik' => $orangtua->nik,
                        'nama' => $orangtua->nama,
                        'tempat_lahir' => $orangtua->tempat_lahir,
                        'tanggal_lahir' => $orangtua->tanggal_lahir ? $orangtua->tanggal_lahir->format('Y-m-d') : null,
                        'hari_lahir' => $orangtua->tanggal_lahir ? $orangtua->tanggal_lahir->day : null,
                        'bulan_lahir' => $orangtua->tanggal_lahir ? $orangtua->tanggal_lahir->month : null,
                        'tahun_lahir' => $orangtua->tanggal_lahir ? $orangtua->tanggal_lahir->year : null,
                        'pekerjaan' => $orangtua->pekerjaan,
                        'pendidikan' => $orangtua->pendidikan,
                        'kelamin' => $orangtua->kelamin,
                        'kepersertaan_bpjs' => $orangtua->kepersertaan_bpjs,
                        'nomor_bpjs' => $orangtua->nomor_bpjs,
                        'nomor_telepon' => $orangtua->nomor_telepon,
                    ] : null
                ];
            }
        }
        
        // Sort by no_kk
        ksort($noKkList);
        
        return array_values($noKkList);
    }

    /**
     * Auto-fill data orangtua ketika No KK berubah
     */
    public function updatedNoKkSasaranRemaja($value)
    {
        if ($value) {
            $this->loadOrangtuaByNoKkRemaja($value);
        }
    }

    /**
     * Load data orangtua berdasarkan No KK
     */
    public function loadOrangtuaByNoKkRemaja($noKk)
    {
        if (!$noKk) {
            return;
        }

        // Pastikan posyanduId tersedia
        $posyanduId = $this->id_posyandu_sasaran_remaja ?? $this->posyanduId ?? null;
        
        if (!$posyanduId) {
            return;
        }

        // Cari NIK orangtua dari balita atau remaja dengan no_kk yang sama
        $sasaran = \App\Models\SasaranBayibalita::where('id_posyandu', $posyanduId)
            ->where('no_kk_sasaran', $noKk)
            ->whereNotNull('nik_orangtua')
            ->first();

        if (!$sasaran) {
            // Coba dari remaja
            $sasaran = SasaranRemaja::where('id_posyandu', $posyanduId)
                ->where('no_kk_sasaran', $noKk)
                ->whereNotNull('nik_orangtua')
                ->first();
        }

        if ($sasaran && $sasaran->nik_orangtua) {
            // Cari data orangtua langsung dari tabel orangtua
            $orangtua = \App\Models\Orangtua::find($sasaran->nik_orangtua);
            
            if ($orangtua) {
                $this->nik_orangtua_remaja = $orangtua->nik;
                $this->nama_orangtua_remaja = $orangtua->nama;
                $this->tempat_lahir_orangtua_remaja = $orangtua->tempat_lahir ?? '';
                $this->tanggal_lahir_orangtua_remaja = $orangtua->tanggal_lahir ? $orangtua->tanggal_lahir->format('Y-m-d') : '';
                
                if ($orangtua->tanggal_lahir) {
                    $date = Carbon::parse($orangtua->tanggal_lahir);
                    $this->hari_lahir_orangtua_remaja = $date->day;
                    $this->bulan_lahir_orangtua_remaja = $date->month;
                    $this->tahun_lahir_orangtua_remaja = $date->year;
                } else {
                    $this->hari_lahir_orangtua_remaja = '';
                    $this->bulan_lahir_orangtua_remaja = '';
                    $this->tahun_lahir_orangtua_remaja = '';
                }
                
                $this->pekerjaan_orangtua_remaja = $orangtua->pekerjaan ?? '';
                $this->pendidikan_orangtua_remaja = $orangtua->pendidikan ?? '';
                $this->kelamin_orangtua_remaja = $orangtua->kelamin ?? '';
                $this->kepersertaan_bpjs_orangtua_remaja = $orangtua->kepersertaan_bpjs ?? '';
                $this->nomor_bpjs_orangtua_remaja = $orangtua->nomor_bpjs ?? '';
                $this->nomor_telepon_orangtua_remaja = $orangtua->nomor_telepon ?? '';
                
                // Update alamat sasaran jika kosong
                if (empty($this->alamat_sasaran_remaja) && $sasaran->alamat_sasaran) {
                    $this->alamat_sasaran_remaja = $sasaran->alamat_sasaran;
                }
            }
        }
        
        // Dispatch event untuk memastikan UI ter-update
        $this->dispatch('orangtua-loaded');
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

