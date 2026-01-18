<?php

namespace App\Livewire\SuperAdmin\Traits;

use App\Models\SasaranBayibalita;
use App\Models\Orangtua;
use App\Models\User;
use App\Models\SasaranDewasa;
use App\Models\SasaranPralansia;
use App\Models\SasaranLansia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

trait BalitaCrud
{
    // Modal State
    public $isSasaranBalitaModalOpen = false;

    // Field Form Sasaran Bayi & Balita
    public $id_sasaran_bayi_balita = null;
    public $id_posyandu_sasaran;
    public $nama_sasaran;
    public $nik_sasaran;
    public $no_kk_sasaran;
    public $tempat_lahir;
    public $tanggal_lahir_sasaran;
    public $hari_lahir_sasaran;
    public $bulan_lahir_sasaran;
    public $tahun_lahir_sasaran;
    public $jenis_kelamin;
    public $status_keluarga;
    public $umur_sasaran;
    public $nik_orangtua;
    public $alamat_sasaran;
    public $rt_sasaran;
    public $rw_sasaran;
    public $kepersertaan_bpjs;
    public $nomor_bpjs;

    // Field Form Orangtua
    public $nama_orangtua;
    public $tempat_lahir_orangtua;
    public $tanggal_lahir_orangtua;
    public $hari_lahir_orangtua;
    public $bulan_lahir_orangtua;
    public $tahun_lahir_orangtua;
    public $pekerjaan_orangtua;
    public $pendidikan_orangtua;
    public $kelamin_orangtua;
    public $kepersertaan_bpjs_orangtua;
    public $nomor_bpjs_orangtua;
    public $nomor_telepon_orangtua;

    /**
     * Buka modal tambah/edit Sasaran Balita
     */
    public function openBalitaModal($id = null)
    {
        if ($id) {
            $this->editBalita($id);
        } else {
            $this->resetBalitaFields();
            $this->isSasaranBalitaModalOpen = true;
        }
    }

    /**
     * Tutup modal dan reset field
     */
    public function closeBalitaModal()
    {
        $this->resetBalitaFields();
        $this->isSasaranBalitaModalOpen = false;
    }

    /**
     * Reset semua field form Sasaran Bayi Balita
     */
    private function resetBalitaFields()
    {
        $this->id_sasaran_bayi_balita = null;
        $this->id_posyandu_sasaran = $this->posyanduId ?? '';
        $this->nama_sasaran = '';
        $this->nik_sasaran = '';
        $this->no_kk_sasaran = '';
        $this->tempat_lahir = '';
        $this->tanggal_lahir_sasaran = '';
        $this->hari_lahir_sasaran = '';
        $this->bulan_lahir_sasaran = '';
        $this->tahun_lahir_sasaran = '';
        $this->jenis_kelamin = '';
        $this->status_keluarga = '';
        $this->umur_sasaran = '';
        $this->nik_orangtua = '';
        $this->alamat_sasaran = '';
        $this->rt_sasaran = '';
        $this->rw_sasaran = '';
        $this->kepersertaan_bpjs = '';
        $this->nomor_bpjs = '';
        // Reset field orangtua
        $this->nama_orangtua = '';
        $this->tempat_lahir_orangtua = '';
        $this->tanggal_lahir_orangtua = '';
        $this->hari_lahir_orangtua = '';
        $this->bulan_lahir_orangtua = '';
        $this->tahun_lahir_orangtua = '';
        $this->pekerjaan_orangtua = '';
        $this->pendidikan_orangtua = '';
        $this->kelamin_orangtua = '';
        $this->kepersertaan_bpjs_orangtua = '';
        $this->nomor_bpjs_orangtua = '';
        $this->nomor_telepon_orangtua = '';
    }

    /**
     * Proses simpan data balita, tambah/edit
     */
    public function storeBalita()
    {
        // Combine hari, bulan, tahun menjadi tanggal lahir
        $this->combineTanggalLahirSasaran();
        $this->combineTanggalLahirOrangtuaBalita();

        $this->validate([
            'id_posyandu_sasaran' => 'required|exists:posyandu,id_posyandu',
            'nama_sasaran' => 'required|string|max:100',
            'nik_sasaran' => 'required|numeric',
            'no_kk_sasaran' => 'required|numeric',
            'hari_lahir_sasaran' => 'required|numeric|min:1|max:31',
            'bulan_lahir_sasaran' => 'required|numeric|min:1|max:12',
            'tahun_lahir_sasaran' => 'required|numeric|min:1900|max:' . date('Y'),
            'tanggal_lahir_sasaran' => 'required|date',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'status_keluarga' => 'nullable|in:kepala keluarga,istri,anak',
            'alamat_sasaran' => 'required|string|max:225',
            'nik_orangtua' => 'required|numeric',
            'nama_orangtua' => 'required|string|max:100',
            'tempat_lahir_orangtua' => 'required|string|max:100',
            'hari_lahir_orangtua' => 'required|numeric|min:1|max:31',
            'bulan_lahir_orangtua' => 'required|numeric|min:1|max:12',
            'tahun_lahir_orangtua' => 'required|numeric|min:1900|max:' . date('Y'),
            'tanggal_lahir_orangtua' => 'required|date',
            'pekerjaan_orangtua' => 'required|string',
            'pendidikan_orangtua' => 'nullable|string',
            'kelamin_orangtua' => 'required|in:Laki-laki,Perempuan',
            'kepersertaan_bpjs_orangtua' => 'nullable|in:PBI,NON PBI',
            'nomor_bpjs_orangtua' => 'nullable|string|max:50',
            'nomor_telepon_orangtua' => 'nullable|string|max:20',
        ], [
            'id_posyandu_sasaran.required' => 'Posyandu wajib dipilih.',
            'id_posyandu_sasaran.exists' => 'Posyandu yang dipilih tidak valid.',
            'nama_sasaran.required' => 'Nama sasaran wajib diisi.',
            'nik_sasaran.required' => 'NIK wajib diisi.',
            'nik_sasaran.numeric' => 'NIK harus berupa angka.',
            'no_kk_sasaran.required' => 'No KK wajib diisi.',
            'no_kk_sasaran.numeric' => 'No KK harus berupa angka.',
            'hari_lahir_sasaran.required' => 'Hari lahir wajib diisi.',
            'hari_lahir_sasaran.numeric' => 'Hari harus berupa angka.',
            'hari_lahir_sasaran.min' => 'Hari minimal 1.',
            'hari_lahir_sasaran.max' => 'Hari maksimal 31.',
            'bulan_lahir_sasaran.required' => 'Bulan lahir wajib diisi.',
            'bulan_lahir_sasaran.numeric' => 'Bulan harus berupa angka.',
            'bulan_lahir_sasaran.min' => 'Bulan minimal 1.',
            'bulan_lahir_sasaran.max' => 'Bulan maksimal 12.',
            'tahun_lahir_sasaran.required' => 'Tahun lahir wajib diisi.',
            'tahun_lahir_sasaran.numeric' => 'Tahun harus berupa angka.',
            'tahun_lahir_sasaran.min' => 'Tahun minimal 1900.',
            'tahun_lahir_sasaran.max' => 'Tahun maksimal ' . date('Y') . '.',
            'tanggal_lahir_sasaran.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir_sasaran.date' => 'Tanggal lahir harus berupa tanggal yang valid.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin harus Laki-laki atau Perempuan.',
            'alamat_sasaran.required' => 'Alamat wajib diisi.',
            'alamat_sasaran.max' => 'Alamat maksimal 225 karakter.',
            'nik_orangtua.required' => 'NIK orangtua wajib diisi.',
            'nik_orangtua.numeric' => 'NIK orangtua harus berupa angka.',
            'nama_orangtua.required' => 'Nama orangtua wajib diisi.',
            'tempat_lahir_orangtua.required' => 'Tempat lahir orangtua wajib diisi.',
            'hari_lahir_orangtua.required' => 'Hari lahir orangtua wajib diisi.',
            'hari_lahir_orangtua.numeric' => 'Hari lahir orangtua harus berupa angka.',
            'hari_lahir_orangtua.min' => 'Hari lahir orangtua minimal 1.',
            'hari_lahir_orangtua.max' => 'Hari lahir orangtua maksimal 31.',
            'bulan_lahir_orangtua.required' => 'Bulan lahir orangtua wajib diisi.',
            'bulan_lahir_orangtua.numeric' => 'Bulan lahir orangtua harus berupa angka.',
            'bulan_lahir_orangtua.min' => 'Bulan lahir orangtua minimal 1.',
            'bulan_lahir_orangtua.max' => 'Bulan lahir orangtua maksimal 12.',
            'tahun_lahir_orangtua.required' => 'Tahun lahir orangtua wajib diisi.',
            'tahun_lahir_orangtua.numeric' => 'Tahun lahir orangtua harus berupa angka.',
            'tahun_lahir_orangtua.min' => 'Tahun lahir orangtua minimal 1900.',
            'tahun_lahir_orangtua.max' => 'Tahun lahir orangtua maksimal ' . date('Y') . '.',
            'tanggal_lahir_orangtua.required' => 'Tanggal lahir orangtua wajib diisi.',
            'tanggal_lahir_orangtua.date' => 'Tanggal lahir orangtua harus berupa tanggal yang valid.',
            'pekerjaan_orangtua.required' => 'Pekerjaan orangtua wajib dipilih.',
            'pendidikan_orangtua.string' => 'Pendidikan orangtua harus berupa teks.',
            'kelamin_orangtua.required' => 'Jenis kelamin orangtua wajib dipilih.',
            'kelamin_orangtua.in' => 'Jenis kelamin orangtua harus Laki-laki atau Perempuan.',
            'kepersertaan_bpjs_orangtua.in' => 'Kepersertaan BPJS orangtua harus PBI atau NON PBI.',
            'nomor_bpjs_orangtua.max' => 'Nomor BPJS orangtua maksimal 50 karakter.',
            'nomor_telepon_orangtua.max' => 'Nomor telepon orangtua maksimal 20 karakter.',
        ]);

        // Hitung umur dari tanggal_lahir_sasaran
        $umur = null;
        if ($this->tanggal_lahir_sasaran) {
            $umur = Carbon::parse($this->tanggal_lahir_sasaran)->age;
        }

        // Simpan/Update data orangtua
        // Cari orangtua berdasarkan no_kk dan alamat dari balita
        $orangtuaData = [
            'nik' => $this->nik_orangtua,
            'nama' => $this->nama_orangtua,
            'no_kk' => $this->no_kk_sasaran, // Gunakan no_kk dari balita
            'tempat_lahir' => $this->tempat_lahir_orangtua,
            'tanggal_lahir' => $this->tanggal_lahir_orangtua,
            'pekerjaan' => $this->pekerjaan_orangtua,
            'pendidikan' => $this->pendidikan_orangtua ?: null,
            'kelamin' => $this->kelamin_orangtua,
            'alamat' => $this->alamat_sasaran, // Gunakan alamat dari balita
            'kepersertaan_bpjs' => $this->kepersertaan_bpjs_orangtua ?: null,
            'nomor_bpjs' => $this->nomor_bpjs_orangtua ?: null,
            'nomor_telepon' => $this->nomor_telepon_orangtua ?: null,
        ];

        // Update atau create orangtua berdasarkan nik, no_kk, dan alamat
        // Jika ada balita dengan no_kk dan alamat yang sama, gunakan data orangtua dari balita tersebut
        $existingBalita = SasaranBayibalita::where('no_kk_sasaran', $this->no_kk_sasaran)
            ->where('alamat_sasaran', $this->alamat_sasaran)
            ->whereNotNull('nik_orangtua')
            ->first();

        if ($existingBalita && $existingBalita->nik_orangtua) {
            // Gunakan nik orangtua yang sudah ada dari balita dengan no_kk dan alamat yang sama
            $orangtuaData['nik'] = $existingBalita->nik_orangtua;
            $this->nik_orangtua = $existingBalita->nik_orangtua;

            // Update data orangtua jika ada perubahan
            $existingOrangtua = Orangtua::find($existingBalita->nik_orangtua);
            if ($existingOrangtua) {
                $orangtuaData['nama'] = $existingOrangtua->nama ?? $this->nama_orangtua;
                $orangtuaData['tempat_lahir'] = $existingOrangtua->tempat_lahir ?? $this->tempat_lahir_orangtua;
                $orangtuaData['tanggal_lahir'] = $existingOrangtua->tanggal_lahir ?? $this->tanggal_lahir_orangtua;
                $orangtuaData['pekerjaan'] = $existingOrangtua->pekerjaan ?? $this->pekerjaan_orangtua;
                $orangtuaData['pendidikan'] = $existingOrangtua->pendidikan ?? ($this->pendidikan_orangtua ?: null);
                $orangtuaData['kelamin'] = $existingOrangtua->kelamin ?? $this->kelamin_orangtua;
                $orangtuaData['kepersertaan_bpjs'] = $existingOrangtua->kepersertaan_bpjs ?? ($this->kepersertaan_bpjs_orangtua ?: null);
                $orangtuaData['nomor_bpjs'] = $existingOrangtua->nomor_bpjs ?? ($this->nomor_bpjs_orangtua ?: null);
                $orangtuaData['nomor_telepon'] = $existingOrangtua->nomor_telepon ?? ($this->nomor_telepon_orangtua ?: null);
            }
        }

        DB::transaction(function () use ($orangtuaData, $umur, &$user, &$orangtua) {
            // Update atau create orangtua
            $orangtua = Orangtua::updateOrCreate(
                ['nik' => $this->nik_orangtua],
                $orangtuaData
            );

            // Buat atau update user untuk orangtua berdasarkan No KK
            // Pastikan no_kk tersedia
            if (empty($this->no_kk_sasaran)) {
                throw new \Exception('No KK wajib diisi untuk membuat akun.');
            }

            $email = $this->no_kk_sasaran . '@gmail.com';
            $userExists = User::where('email', $email)->first();

            if ($userExists) {
                // Update user yang sudah ada (timpa data jika No KK sama)
                $userExists->name = $this->nama_orangtua;
                $userExists->password = Hash::make($this->no_kk_sasaran);
                $userExists->save();
                $user = $userExists;
            } else {
                // Buat user baru
                $user = User::create([
                    'name' => $this->nama_orangtua,
                    'email' => $email,
                    'password' => Hash::make($this->no_kk_sasaran),
                    'email_verified_at' => now(),
                ]);
            }

            // Assign role orangtua jika belum punya
            if (!$user->hasRole('orangtua')) {
                $user->assignRole('orangtua');
            }

            // Buat atau update record sasaran dewasa/pralansia/lansia berdasarkan umur orangtua
            // Panggil setelah user dibuat agar user sudah tersedia
            $this->createOrUpdateSasaranFromOrangtua(
                $orangtua, 
                $this->id_posyandu_sasaran ?? $this->posyanduId,
                $this->rt_sasaran,
                $this->rw_sasaran
            );

            $data = [
                'id_users' => $user->id,
                'id_posyandu' => $this->id_posyandu_sasaran ?? $this->posyanduId,
                'nama_sasaran' => $this->nama_sasaran,
                'nik_sasaran' => $this->nik_sasaran,
                'no_kk_sasaran' => $this->no_kk_sasaran ?: null,
                'tempat_lahir' => $this->tempat_lahir ?: null,
                'tanggal_lahir' => $this->tanggal_lahir_sasaran,
                'jenis_kelamin' => $this->jenis_kelamin,
                'status_keluarga' => $this->status_keluarga ?: null,
                'umur_sasaran' => $umur,
                'nik_orangtua' => $this->nik_orangtua,
                'alamat_sasaran' => $this->alamat_sasaran,
                'rt' => $this->rt_sasaran ?: null,
                'rw' => $this->rw_sasaran ?: null,
                'kepersertaan_bpjs' => $this->kepersertaan_bpjs ?: null,
                'nomor_bpjs' => $this->nomor_bpjs ?: null,
            ];

            if ($this->id_sasaran_bayi_balita) {
                // UPDATE
                $balita = SasaranBayibalita::findOrFail($this->id_sasaran_bayi_balita);
                $balita->update($data);
            } else {
                // CREATE
                SasaranBayibalita::create($data);
            }
        });
        
        if ($this->id_sasaran_bayi_balita) {
            session()->flash('message', 'Data Balita berhasil diperbarui.');
        } else {
            session()->flash('message', 'Data Balita berhasil ditambahkan.');
        }

        $this->refreshPosyandu();
        $this->closeBalitaModal();
    }

    /**
     * Inisialisasi form edit balita
     */
    public function editBalita($id)
    {
        $balita = SasaranBayibalita::findOrFail($id);

        $this->id_sasaran_bayi_balita = $balita->id_sasaran_bayibalita;
        $this->id_posyandu_sasaran = $balita->id_posyandu ?? $this->posyanduId;
        $this->nama_sasaran = $balita->nama_sasaran;
        $this->nik_sasaran = $balita->nik_sasaran;
        $this->no_kk_sasaran = $balita->no_kk_sasaran ?? '';
        $this->tempat_lahir = $balita->tempat_lahir ?? '';
        $this->tanggal_lahir_sasaran = $balita->tanggal_lahir;
        // Split tanggal lahir menjadi hari, bulan, tahun
        if ($balita->tanggal_lahir) {
            $date = Carbon::parse($balita->tanggal_lahir);
            $this->hari_lahir_sasaran = $date->day;
            $this->bulan_lahir_sasaran = $date->month;
            $this->tahun_lahir_sasaran = $date->year;
        } else {
            $this->hari_lahir_sasaran = '';
            $this->bulan_lahir_sasaran = '';
            $this->tahun_lahir_sasaran = '';
        }
        $this->jenis_kelamin = $balita->jenis_kelamin;
        $this->status_keluarga = $balita->status_keluarga ?? '';
        $this->umur_sasaran = $balita->tanggal_lahir
            ? Carbon::parse($balita->tanggal_lahir)->age
            : $balita->umur_sasaran;
        $this->nik_orangtua = $balita->nik_orangtua ?? '';
        $this->alamat_sasaran = $balita->alamat_sasaran ?? '';
        $this->rt_sasaran = $balita->rt ?? '';
        $this->rw_sasaran = $balita->rw ?? '';
        $this->kepersertaan_bpjs = $balita->kepersertaan_bpjs ?? '';
        $this->nomor_bpjs = $balita->nomor_bpjs ?? '';

        // Load data orangtua jika ada
        if ($balita->nik_orangtua) {
            $orangtua = Orangtua::find($balita->nik_orangtua);
            if ($orangtua) {
                $this->nama_orangtua = $orangtua->nama;
                $this->tempat_lahir_orangtua = $orangtua->tempat_lahir;
                $this->tanggal_lahir_orangtua = $orangtua->tanggal_lahir ? $orangtua->tanggal_lahir->format('Y-m-d') : '';
                if ($orangtua->tanggal_lahir) {
                    $dateOrtu = Carbon::parse($orangtua->tanggal_lahir);
                    $this->hari_lahir_orangtua = $dateOrtu->day;
                    $this->bulan_lahir_orangtua = $dateOrtu->month;
                    $this->tahun_lahir_orangtua = $dateOrtu->year;
                } else {
                    $this->hari_lahir_orangtua = '';
                    $this->bulan_lahir_orangtua = '';
                    $this->tahun_lahir_orangtua = '';
                }
                $this->pekerjaan_orangtua = $orangtua->pekerjaan;
                $this->pendidikan_orangtua = $orangtua->pendidikan ?? '';
                $this->kelamin_orangtua = $orangtua->kelamin;
                $this->kepersertaan_bpjs_orangtua = $orangtua->kepersertaan_bpjs ?? '';
                $this->nomor_bpjs_orangtua = $orangtua->nomor_bpjs ?? '';
                $this->nomor_telepon_orangtua = $orangtua->nomor_telepon ?? '';
            } else {
                $this->nama_orangtua = '';
                $this->tempat_lahir_orangtua = '';
                $this->tanggal_lahir_orangtua = '';
                $this->hari_lahir_orangtua = '';
                $this->bulan_lahir_orangtua = '';
                $this->tahun_lahir_orangtua = '';
                $this->pekerjaan_orangtua = '';
                $this->pendidikan_orangtua = '';
                $this->kelamin_orangtua = '';
                $this->kepersertaan_bpjs_orangtua = '';
                $this->nomor_bpjs_orangtua = '';
                $this->nomor_telepon_orangtua = '';
            }
        } else {
            $this->nama_orangtua = '';
            $this->tempat_lahir_orangtua = '';
            $this->tanggal_lahir_orangtua = '';
            $this->pekerjaan_orangtua = '';
            $this->pendidikan_orangtua = '';
            $this->kelamin_orangtua = '';
            $this->hari_lahir_orangtua = '';
            $this->bulan_lahir_orangtua = '';
            $this->tahun_lahir_orangtua = '';
            $this->kepersertaan_bpjs_orangtua = '';
            $this->nomor_bpjs_orangtua = '';
            $this->nomor_telepon_orangtua = '';
        }

        $this->isSasaranBalitaModalOpen = true;
    }

    /**
     * Hapus data balita
     */
    public function deleteBalita($id)
    {
        $balita = SasaranBayibalita::findOrFail($id);
        $balita->delete();
        $this->refreshPosyandu();
        session()->flash('message', 'Data Balita berhasil dihapus.');
    }

    /**
     * Combine hari, bulan, tahun menjadi tanggal lahir
     */
    private function combineTanggalLahirSasaran()
    {
        if ($this->hari_lahir_sasaran && $this->bulan_lahir_sasaran && $this->tahun_lahir_sasaran) {
            try {
                $this->tanggal_lahir_sasaran = Carbon::create(
                    $this->tahun_lahir_sasaran,
                    $this->bulan_lahir_sasaran,
                    $this->hari_lahir_sasaran
                )->format('Y-m-d');
            } catch (\Exception $e) {
                $this->tanggal_lahir_sasaran = null;
            }
        } else {
            $this->tanggal_lahir_sasaran = null;
        }
    }

    /**
     * Combine hari, bulan, tahun menjadi tanggal lahir orangtua
     */
    private function combineTanggalLahirOrangtuaBalita()
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

    /**
     * Hitung umur otomatis ketika hari, bulan, atau tahun lahir berubah
     */
    public function updatedHariLahirSasaran()
    {
        $this->calculateUmurSasaran();
    }

    public function updatedBulanLahirSasaran()
    {
        $this->calculateUmurSasaran();
    }

    public function updatedTahunLahirSasaran()
    {
        $this->calculateUmurSasaran();
    }

    /**
     * Calculate umur dari hari, bulan, tahun lahir
     */
    private function calculateUmurSasaran()
    {
        if ($this->hari_lahir_sasaran && $this->bulan_lahir_sasaran && $this->tahun_lahir_sasaran) {
            try {
                $tanggalLahir = Carbon::create(
                    $this->tahun_lahir_sasaran,
                    $this->bulan_lahir_sasaran,
                    $this->hari_lahir_sasaran
                );
                $this->umur_sasaran = $tanggalLahir->age;
                $this->tanggal_lahir_sasaran = $tanggalLahir->format('Y-m-d');
            } catch (\Exception $e) {
                $this->umur_sasaran = '';
                $this->tanggal_lahir_sasaran = null;
            }
        } else {
            $this->umur_sasaran = '';
            $this->tanggal_lahir_sasaran = null;
        }
    }

    /**
     * Get list of existing No KK with detailed information for autocomplete
     */
    public function getNoKkList()
    {
        $posyanduId = $this->id_posyandu_sasaran ?? $this->posyanduId ?? null;
        
        if (!$posyanduId) {
            return [];
        }
        
        // Get all no_kk from balita and remaja in the same posyandu with orangtua info
        $noKkList = [];
        
        // Get from balita
        $balitaList = SasaranBayibalita::where('id_posyandu', $posyanduId)
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
                $countBalita = SasaranBayibalita::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $countRemaja = \App\Models\SasaranRemaja::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $countDewasa = SasaranDewasa::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $countPralansia = SasaranPralansia::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $countLansia = SasaranLansia::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $totalAnggota = $countBalita + $countRemaja + $countDewasa + $countPralansia + $countLansia;
                
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
        $remajaList = \App\Models\SasaranRemaja::where('id_posyandu', $posyanduId)
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
                $countBalita = SasaranBayibalita::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $countRemaja = \App\Models\SasaranRemaja::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $countDewasa = SasaranDewasa::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $countPralansia = SasaranPralansia::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $countLansia = SasaranLansia::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $totalAnggota = $countBalita + $countRemaja + $countDewasa + $countPralansia + $countLansia;
                
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
        
        // Get from dewasa (dewasa/pralansia/lansia adalah orangtua itu sendiri)
        $dewasaList = SasaranDewasa::where('id_posyandu', $posyanduId)
            ->whereNotNull('no_kk_sasaran')
            ->get();
            
        foreach ($dewasaList as $dewasa) {
            $noKk = $dewasa->no_kk_sasaran;
            if (!isset($noKkList[$noKk])) {
                // Count anggota keluarga dengan no_kk yang sama
                $countBalita = SasaranBayibalita::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $countRemaja = \App\Models\SasaranRemaja::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $countDewasa = SasaranDewasa::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $countPralansia = SasaranPralansia::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $countLansia = SasaranLansia::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $totalAnggota = $countBalita + $countRemaja + $countDewasa + $countPralansia + $countLansia;
                
                $noKkList[$noKk] = [
                    'no_kk' => $noKk,
                    'nama_orangtua' => $dewasa->nama_sasaran,
                    'jumlah_anggota' => $totalAnggota,
                    'nik_orangtua' => $dewasa->nik_sasaran,
                    'orangtua_data' => [
                        'nik' => $dewasa->nik_sasaran,
                        'nama' => $dewasa->nama_sasaran,
                        'tempat_lahir' => $dewasa->tempat_lahir,
                        'tanggal_lahir' => $dewasa->tanggal_lahir ? (is_string($dewasa->tanggal_lahir) ? Carbon::parse($dewasa->tanggal_lahir)->format('Y-m-d') : $dewasa->tanggal_lahir->format('Y-m-d')) : null,
                        'hari_lahir' => $dewasa->tanggal_lahir ? (is_string($dewasa->tanggal_lahir) ? Carbon::parse($dewasa->tanggal_lahir)->day : $dewasa->tanggal_lahir->day) : null,
                        'bulan_lahir' => $dewasa->tanggal_lahir ? (is_string($dewasa->tanggal_lahir) ? Carbon::parse($dewasa->tanggal_lahir)->month : $dewasa->tanggal_lahir->month) : null,
                        'tahun_lahir' => $dewasa->tanggal_lahir ? (is_string($dewasa->tanggal_lahir) ? Carbon::parse($dewasa->tanggal_lahir)->year : $dewasa->tanggal_lahir->year) : null,
                        'pekerjaan' => $dewasa->pekerjaan,
                        'pendidikan' => $dewasa->pendidikan,
                        'kelamin' => $dewasa->jenis_kelamin,
                        'kepersertaan_bpjs' => $dewasa->kepersertaan_bpjs,
                        'nomor_bpjs' => $dewasa->nomor_bpjs,
                        'nomor_telepon' => $dewasa->nomor_telepon,
                    ]
                ];
            }
        }
        
        // Get from pralansia
        $pralansiaList = SasaranPralansia::where('id_posyandu', $posyanduId)
            ->whereNotNull('no_kk_sasaran')
            ->get();
            
        foreach ($pralansiaList as $pralansia) {
            $noKk = $pralansia->no_kk_sasaran;
            if (!isset($noKkList[$noKk])) {
                // Count anggota keluarga dengan no_kk yang sama
                $countBalita = SasaranBayibalita::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $countRemaja = \App\Models\SasaranRemaja::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $countDewasa = SasaranDewasa::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $countPralansia = SasaranPralansia::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $countLansia = SasaranLansia::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $totalAnggota = $countBalita + $countRemaja + $countDewasa + $countPralansia + $countLansia;
                
                $noKkList[$noKk] = [
                    'no_kk' => $noKk,
                    'nama_orangtua' => $pralansia->nama_sasaran,
                    'jumlah_anggota' => $totalAnggota,
                    'nik_orangtua' => $pralansia->nik_sasaran,
                    'orangtua_data' => [
                        'nik' => $pralansia->nik_sasaran,
                        'nama' => $pralansia->nama_sasaran,
                        'tempat_lahir' => $pralansia->tempat_lahir,
                        'tanggal_lahir' => $pralansia->tanggal_lahir ? (is_string($pralansia->tanggal_lahir) ? Carbon::parse($pralansia->tanggal_lahir)->format('Y-m-d') : $pralansia->tanggal_lahir->format('Y-m-d')) : null,
                        'hari_lahir' => $pralansia->tanggal_lahir ? (is_string($pralansia->tanggal_lahir) ? Carbon::parse($pralansia->tanggal_lahir)->day : $pralansia->tanggal_lahir->day) : null,
                        'bulan_lahir' => $pralansia->tanggal_lahir ? (is_string($pralansia->tanggal_lahir) ? Carbon::parse($pralansia->tanggal_lahir)->month : $pralansia->tanggal_lahir->month) : null,
                        'tahun_lahir' => $pralansia->tanggal_lahir ? (is_string($pralansia->tanggal_lahir) ? Carbon::parse($pralansia->tanggal_lahir)->year : $pralansia->tanggal_lahir->year) : null,
                        'pekerjaan' => $pralansia->pekerjaan,
                        'pendidikan' => $pralansia->pendidikan,
                        'kelamin' => $pralansia->jenis_kelamin,
                        'kepersertaan_bpjs' => $pralansia->kepersertaan_bpjs,
                        'nomor_bpjs' => $pralansia->nomor_bpjs,
                        'nomor_telepon' => $pralansia->nomor_telepon,
                    ]
                ];
            }
        }
        
        // Get from lansia
        $lansiaList = SasaranLansia::where('id_posyandu', $posyanduId)
            ->whereNotNull('no_kk_sasaran')
            ->get();
            
        foreach ($lansiaList as $lansia) {
            $noKk = $lansia->no_kk_sasaran;
            if (!isset($noKkList[$noKk])) {
                // Count anggota keluarga dengan no_kk yang sama
                $countBalita = SasaranBayibalita::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $countRemaja = \App\Models\SasaranRemaja::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $countDewasa = SasaranDewasa::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $countPralansia = SasaranPralansia::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $countLansia = SasaranLansia::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $totalAnggota = $countBalita + $countRemaja + $countDewasa + $countPralansia + $countLansia;
                
                $noKkList[$noKk] = [
                    'no_kk' => $noKk,
                    'nama_orangtua' => $lansia->nama_sasaran,
                    'jumlah_anggota' => $totalAnggota,
                    'nik_orangtua' => $lansia->nik_sasaran,
                    'orangtua_data' => [
                        'nik' => $lansia->nik_sasaran,
                        'nama' => $lansia->nama_sasaran,
                        'tempat_lahir' => $lansia->tempat_lahir,
                        'tanggal_lahir' => $lansia->tanggal_lahir ? (is_string($lansia->tanggal_lahir) ? Carbon::parse($lansia->tanggal_lahir)->format('Y-m-d') : $lansia->tanggal_lahir->format('Y-m-d')) : null,
                        'hari_lahir' => $lansia->tanggal_lahir ? (is_string($lansia->tanggal_lahir) ? Carbon::parse($lansia->tanggal_lahir)->day : $lansia->tanggal_lahir->day) : null,
                        'bulan_lahir' => $lansia->tanggal_lahir ? (is_string($lansia->tanggal_lahir) ? Carbon::parse($lansia->tanggal_lahir)->month : $lansia->tanggal_lahir->month) : null,
                        'tahun_lahir' => $lansia->tanggal_lahir ? (is_string($lansia->tanggal_lahir) ? Carbon::parse($lansia->tanggal_lahir)->year : $lansia->tanggal_lahir->year) : null,
                        'pekerjaan' => $lansia->pekerjaan,
                        'pendidikan' => $lansia->pendidikan,
                        'kelamin' => $lansia->jenis_kelamin,
                        'kepersertaan_bpjs' => $lansia->kepersertaan_bpjs,
                        'nomor_bpjs' => $lansia->nomor_bpjs,
                        'nomor_telepon' => $lansia->nomor_telepon,
                    ]
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
    public function updatedNoKkSasaran($value)
    {
        if ($value) {
            $this->loadOrangtuaByNoKk($value);
        }
    }

    /**
     * Load data orangtua berdasarkan No KK
     */
    public function loadOrangtuaByNoKk($noKk)
    {
        if (!$noKk) {
            return;
        }

        // Pastikan posyanduId tersedia
        $posyanduId = $this->id_posyandu_sasaran ?? $this->posyanduId ?? null;
        
        if (!$posyanduId) {
            return;
        }

        // Cari NIK orangtua dari balita atau remaja dengan no_kk yang sama
        $sasaran = SasaranBayibalita::where('id_posyandu', $posyanduId)
            ->where('no_kk_sasaran', $noKk)
            ->whereNotNull('nik_orangtua')
            ->first();

        if (!$sasaran) {
            // Coba dari remaja
            $sasaran = \App\Models\SasaranRemaja::where('id_posyandu', $posyanduId)
                ->where('no_kk_sasaran', $noKk)
                ->whereNotNull('nik_orangtua')
                ->first();
        }

        if ($sasaran && $sasaran->nik_orangtua) {
            // Cari data orangtua langsung dari tabel orangtua
            $orangtua = Orangtua::find($sasaran->nik_orangtua);
            
            if ($orangtua) {
                $this->nik_orangtua = $orangtua->nik;
                $this->nama_orangtua = $orangtua->nama;
                $this->tempat_lahir_orangtua = $orangtua->tempat_lahir ?? '';
                $this->tanggal_lahir_orangtua = $orangtua->tanggal_lahir ? $orangtua->tanggal_lahir->format('Y-m-d') : '';
                
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
                $this->kelamin_orangtua = $orangtua->kelamin ?? '';
                $this->kepersertaan_bpjs_orangtua = $orangtua->kepersertaan_bpjs ?? '';
                $this->nomor_bpjs_orangtua = $orangtua->nomor_bpjs ?? '';
                $this->nomor_telepon_orangtua = $orangtua->nomor_telepon ?? '';
                
                // Update alamat sasaran jika kosong
                if (empty($this->alamat_sasaran) && $sasaran->alamat_sasaran) {
                    $this->alamat_sasaran = $sasaran->alamat_sasaran;
                }
                
                // Update RT dan RW jika kosong
                if (empty($this->rt_sasaran) && $sasaran->rt) {
                    $this->rt_sasaran = $sasaran->rt;
                }
                if (empty($this->rw_sasaran) && $sasaran->rw) {
                    $this->rw_sasaran = $sasaran->rw;
                }
            }
        } else {
            // Jika tidak ditemukan di balita/remaja, coba dari dewasa/pralansia/lansia
            // (mereka adalah orangtua itu sendiri)
            $sasaranDewasa = SasaranDewasa::where('id_posyandu', $posyanduId)
                ->where('no_kk_sasaran', $noKk)
                ->first();
            
            if (!$sasaranDewasa) {
                $sasaranDewasa = SasaranPralansia::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->first();
            }
            
            if (!$sasaranDewasa) {
                $sasaranDewasa = SasaranLansia::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->first();
            }
            
            if ($sasaranDewasa) {
                // Gunakan data sasaran dewasa/pralansia/lansia sebagai data orangtua
                $this->nik_orangtua = $sasaranDewasa->nik_sasaran;
                $this->nama_orangtua = $sasaranDewasa->nama_sasaran;
                $this->tempat_lahir_orangtua = $sasaranDewasa->tempat_lahir ?? '';
                
                if ($sasaranDewasa->tanggal_lahir) {
                    $date = is_string($sasaranDewasa->tanggal_lahir) ? Carbon::parse($sasaranDewasa->tanggal_lahir) : $sasaranDewasa->tanggal_lahir;
                    $this->tanggal_lahir_orangtua = $date->format('Y-m-d');
                    $this->hari_lahir_orangtua = $date->day;
                    $this->bulan_lahir_orangtua = $date->month;
                    $this->tahun_lahir_orangtua = $date->year;
                } else {
                    $this->tanggal_lahir_orangtua = '';
                    $this->hari_lahir_orangtua = '';
                    $this->bulan_lahir_orangtua = '';
                    $this->tahun_lahir_orangtua = '';
                }
                
                $this->pekerjaan_orangtua = $sasaranDewasa->pekerjaan ?? '';
                $this->pendidikan_orangtua = $sasaranDewasa->pendidikan ?? '';
                $this->kelamin_orangtua = $sasaranDewasa->jenis_kelamin ?? '';
                $this->kepersertaan_bpjs_orangtua = $sasaranDewasa->kepersertaan_bpjs ?? '';
                $this->nomor_bpjs_orangtua = $sasaranDewasa->nomor_bpjs ?? '';
                $this->nomor_telepon_orangtua = $sasaranDewasa->nomor_telepon ?? '';
                
                // Update alamat sasaran jika kosong
                if (empty($this->alamat_sasaran) && $sasaranDewasa->alamat_sasaran) {
                    $this->alamat_sasaran = $sasaranDewasa->alamat_sasaran;
                }
                
                // Update RT dan RW jika kosong
                if (empty($this->rt_sasaran) && $sasaranDewasa->rt) {
                    $this->rt_sasaran = $sasaranDewasa->rt;
                }
                if (empty($this->rw_sasaran) && $sasaranDewasa->rw) {
                    $this->rw_sasaran = $sasaranDewasa->rw;
                }
            }
        }
        
        // Dispatch event untuk memastikan UI ter-update
        $this->dispatch('orangtua-loaded');
    }

    /**
     * Buat atau update record sasaran dewasa/pralansia/lansia dari data orangtua
     */
    private function createOrUpdateSasaranFromOrangtua($orangtua, $idPosyandu, $rt = null, $rw = null)
    {
        if (!$orangtua || !$orangtua->tanggal_lahir || !$idPosyandu) {
            return;
        }

        $umur = $orangtua->umur;
        // Gunakan No KK untuk email, bukan NIK
        if (empty($orangtua->no_kk)) {
            return; // Skip jika tidak ada No KK
        }
        $email = $orangtua->no_kk . '@gmail.com';
        $user = User::where('email', $email)->first();

        if (!$user) {
            return; // User harus sudah dibuat sebelumnya
        }

        // Data dasar untuk sasaran
        $sasaranData = [
            'id_users' => $user->id,
            'id_posyandu' => $idPosyandu,
            'nama_sasaran' => $orangtua->nama,
            'nik_sasaran' => $orangtua->nik,
            'no_kk_sasaran' => $orangtua->no_kk,
            'tempat_lahir' => $orangtua->tempat_lahir,
            'tanggal_lahir' => $orangtua->tanggal_lahir,
            'jenis_kelamin' => $orangtua->kelamin,
            'umur_sasaran' => $umur,
            'pekerjaan' => $orangtua->pekerjaan,
            'alamat_sasaran' => $orangtua->alamat,
            'rt' => $rt ?: null,
            'rw' => $rw ?: null,
            'kepersertaan_bpjs' => $orangtua->kepersertaan_bpjs,
            'nomor_bpjs' => $orangtua->nomor_bpjs,
            'nomor_telepon' => $orangtua->nomor_telepon,
            'nik_orangtua' => null, // Orangtua tidak punya orangtua
        ];

        // Tentukan kategori berdasarkan umur
        if ($umur >= 18 && $umur <= 45) {
            // Dewasa (18-45 tahun)
            $existing = SasaranDewasa::where('nik_sasaran', $orangtua->nik)
                ->where('id_posyandu', $idPosyandu)
                ->first();
            
            if ($existing) {
                $sasaranData['pendidikan'] = $orangtua->pendidikan;
                $existing->update($sasaranData);
            } else {
                $sasaranData['pendidikan'] = $orangtua->pendidikan;
                SasaranDewasa::create($sasaranData);
            }
        } elseif ($umur >= 46 && $umur <= 59) {
            // Pralansia (46-59 tahun)
            $existing = SasaranPralansia::where('nik_sasaran', $orangtua->nik)
                ->where('id_posyandu', $idPosyandu)
                ->first();
            
            if ($existing) {
                $existing->update($sasaranData);
            } else {
                SasaranPralansia::create($sasaranData);
            }
        } elseif ($umur >= 60) {
            // Lansia (60+ tahun)
            $existing = SasaranLansia::where('nik_sasaran', $orangtua->nik)
                ->where('id_posyandu', $idPosyandu)
                ->first();
            
            if ($existing) {
                $existing->update($sasaranData);
            } else {
                SasaranLansia::create($sasaranData);
            }
        }
    }
}

