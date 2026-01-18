<?php

namespace App\Livewire\SuperAdmin\Traits;

use App\Models\SasaranDewasa;
use App\Models\SasaranBayibalita;
use App\Models\SasaranRemaja;
use App\Models\SasaranPralansia;
use App\Models\SasaranLansia;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Livewire\SuperAdmin\Traits\AutoSavePendidikan;

trait DewasaCrud
{
    use AutoSavePendidikan;
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
    public $status_keluarga_dewasa;
    public $umur_sasaran_dewasa;
    public $pekerjaan_dewasa;
    public $pendidikan_dewasa;
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
        $this->status_keluarga_dewasa = '';
        $this->umur_sasaran_dewasa = '';
        $this->pekerjaan_dewasa = '';
        $this->pendidikan_dewasa = '';
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
            'no_kk_sasaran_dewasa' => 'required|numeric',
            'hari_lahir_dewasa' => 'required|numeric|min:1|max:31',
            'bulan_lahir_dewasa' => 'required|numeric|min:1|max:12',
            'tahun_lahir_dewasa' => 'required|numeric|min:1900|max:' . date('Y'),
            'tanggal_lahir_dewasa' => 'required|date',
            'jenis_kelamin_dewasa' => 'required|in:Laki-laki,Perempuan',
            'status_keluarga_dewasa' => 'nullable|in:kepala keluarga,istri,anak',
            'alamat_sasaran_dewasa' => 'required|string|max:225',
        ], [
            'nama_sasaran_dewasa.required' => 'Nama sasaran wajib diisi.',
            'nik_sasaran_dewasa.required' => 'NIK wajib diisi.',
            'nik_sasaran_dewasa.numeric' => 'NIK harus berupa angka.',
            'no_kk_sasaran_dewasa.required' => 'No KK wajib diisi.',
            'no_kk_sasaran_dewasa.numeric' => 'No KK harus berupa angka.',
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

        // Cari nik_orangtua dari sasaran balita jika ada no_kk dan alamat yang sama
        $nik_orangtua = null;
        $dewasa = null;
        
        // Jika update, cek apakah sudah ada nik_orangtua
        if ($this->id_sasaran_dewasa) {
            $dewasa = SasaranDewasa::find($this->id_sasaran_dewasa);
            if ($dewasa && $dewasa->nik_orangtua) {
                $nik_orangtua = $dewasa->nik_orangtua;
            }
        }
        
        // Jika belum ada nik_orangtua, cari dari sasaran balita
        if (!$nik_orangtua && $this->no_kk_sasaran_dewasa && $this->alamat_sasaran_dewasa) {
            $existingBalita = SasaranBayibalita::where('no_kk_sasaran', $this->no_kk_sasaran_dewasa)
                ->where('alamat_sasaran', $this->alamat_sasaran_dewasa)
                ->whereNotNull('nik_orangtua')
                ->first();
            
            if ($existingBalita && $existingBalita->nik_orangtua) {
                $nik_orangtua = $existingBalita->nik_orangtua;
            }
        }

        // Buat atau update user untuk sasaran dewasa berdasarkan No KK
        $userId = null;
        if ($this->id_users_sasaran_dewasa !== '') {
            // Jika user sudah dipilih manual, gunakan itu
            $userId = $this->id_users_sasaran_dewasa;
        } else {
            // Pastikan no_kk tersedia
            if (empty($this->no_kk_sasaran_dewasa)) {
                throw new \Exception('No KK wajib diisi untuk membuat akun.');
            }

            // Buat akun otomatis berdasarkan No KK sasaran
            $email = $this->no_kk_sasaran_dewasa . '@gmail.com';
            $userExists = User::where('email', $email)->first();

            if ($userExists) {
                // Update user yang sudah ada (timpa data jika No KK sama)
                $userExists->name = $this->nama_sasaran_dewasa;
                $userExists->password = Hash::make($this->no_kk_sasaran_dewasa);
                $userExists->save();
                $userId = $userExists->id;
            } else {
                // Buat user baru
                $user = User::create([
                    'name' => $this->nama_sasaran_dewasa,
                    'email' => $email,
                    'password' => Hash::make($this->no_kk_sasaran_dewasa),
                    'email_verified_at' => now(),
                ]);
                $userId = $user->id;

                // Assign role orangtua jika belum punya
                if (!$user->hasRole('orangtua')) {
                    $user->assignRole('orangtua');
                }
            }
        }

        $data = [
            'id_users' => $userId,
            'id_posyandu' => $this->posyanduId,
            'nama_sasaran' => $this->nama_sasaran_dewasa,
            'nik_sasaran' => $this->nik_sasaran_dewasa,
            'no_kk_sasaran' => $this->no_kk_sasaran_dewasa ?: null,
            'tempat_lahir' => $this->tempat_lahir_dewasa ?: null,
            'tanggal_lahir' => $this->tanggal_lahir_dewasa,
            'jenis_kelamin' => $this->jenis_kelamin_dewasa,
            'status_keluarga' => $this->status_keluarga_dewasa ?: null,
            'umur_sasaran' => $umur,
            'pekerjaan' => $this->pekerjaan_dewasa ?: null,
            'pendidikan' => $this->pendidikan_dewasa ?: null,
            'nik_orangtua' => $nik_orangtua,
            'alamat_sasaran' => $this->alamat_sasaran_dewasa,
            'rt' => $this->rt_dewasa ?: null,
            'rw' => $this->rw_dewasa ?: null,
            'kepersertaan_bpjs' => $this->kepersertaan_bpjs_dewasa ?: null,
            'nomor_bpjs' => $this->nomor_bpjs_dewasa ?: null,
            'nomor_telepon' => $this->nomor_telepon_dewasa ?: null,
        ];

        DB::transaction(function () use ($data, &$dewasa) {
            if ($this->id_sasaran_dewasa) {
                // UPDATE
                if (!$dewasa) {
                    $dewasa = SasaranDewasa::findOrFail($this->id_sasaran_dewasa);
                }
                $dewasa->update($data);
                
                // Auto-save pendidikan
                if (!empty($this->pendidikan_dewasa)) {
                    $this->autoSavePendidikan(
                        $dewasa->id_sasaran_dewasa,
                        'dewasa',
                        $this->posyanduId,
                        $this->pendidikan_dewasa,
                        [
                            'nik' => $dewasa->nik_sasaran,
                            'nama' => $dewasa->nama_sasaran,
                            'tanggal_lahir' => $dewasa->tanggal_lahir,
                            'jenis_kelamin' => $dewasa->jenis_kelamin,
                            'umur' => $dewasa->umur_sasaran,
                        ]
                    );
                }
            } else {
                // CREATE
                $dewasa = SasaranDewasa::create($data);
                
                // Auto-save pendidikan
                if (!empty($this->pendidikan_dewasa)) {
                    $this->autoSavePendidikan(
                        $dewasa->id_sasaran_dewasa,
                        'dewasa',
                        $this->posyanduId,
                        $this->pendidikan_dewasa,
                        [
                            'nik' => $dewasa->nik_sasaran,
                            'nama' => $dewasa->nama_sasaran,
                            'tanggal_lahir' => $dewasa->tanggal_lahir,
                            'jenis_kelamin' => $dewasa->jenis_kelamin,
                            'umur' => $dewasa->umur_sasaran,
                        ]
                    );
                }
            }
        });
        
        if ($this->id_sasaran_dewasa) {
            session()->flash('message', 'Data Dewasa berhasil diperbarui.');
        } else {
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
        $dewasa = SasaranDewasa::findOrFail($id);

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
        $this->status_keluarga_dewasa = $dewasa->status_keluarga ?? '';
        $this->umur_sasaran_dewasa = $dewasa->tanggal_lahir
            ? Carbon::parse($dewasa->tanggal_lahir)->age
            : $dewasa->umur_sasaran;
        $this->pekerjaan_dewasa = $dewasa->pekerjaan ?? '';
        $this->pendidikan_dewasa = $dewasa->pendidikan ?? '';
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
        $dewasa = SasaranDewasa::findOrFail($id);
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

    /**
     * Get list of No KK untuk autocomplete (Optimized dengan aggregate query)
     */
    public function getNoKkListDewasa()
    {
        $posyanduId = $this->posyanduId ?? null;
        
        if (!$posyanduId) {
            return [];
        }

        // Ambil semua no_kk unik dari semua tabel sasaran sekaligus
        $allNoKk = collect();
        
        $allNoKk = $allNoKk->merge(
            SasaranBayibalita::where('id_posyandu', $posyanduId)
                ->whereNotNull('no_kk_sasaran')
                ->select('no_kk_sasaran')
                ->distinct()
                ->pluck('no_kk_sasaran')
        );
        
        $allNoKk = $allNoKk->merge(
            SasaranRemaja::where('id_posyandu', $posyanduId)
                ->whereNotNull('no_kk_sasaran')
                ->select('no_kk_sasaran')
                ->distinct()
                ->pluck('no_kk_sasaran')
        );
        
        $allNoKk = $allNoKk->merge(
            SasaranDewasa::where('id_posyandu', $posyanduId)
                ->whereNotNull('no_kk_sasaran')
                ->select('no_kk_sasaran')
                ->distinct()
                ->pluck('no_kk_sasaran')
        );
        
        $allNoKk = $allNoKk->merge(
            SasaranPralansia::where('id_posyandu', $posyanduId)
                ->whereNotNull('no_kk_sasaran')
                ->select('no_kk_sasaran')
                ->distinct()
                ->pluck('no_kk_sasaran')
        );
        
        $allNoKk = $allNoKk->merge(
            SasaranLansia::where('id_posyandu', $posyanduId)
                ->whereNotNull('no_kk_sasaran')
                ->select('no_kk_sasaran')
                ->distinct()
                ->pluck('no_kk_sasaran')
        );
        
        $uniqueNoKk = $allNoKk->unique()->values();
        
        if ($uniqueNoKk->isEmpty()) {
            return [];
        }
        
        // Hitung jumlah anggota per no_kk dengan aggregate query (lebih efisien)
        $countBalita = SasaranBayibalita::where('id_posyandu', $posyanduId)
            ->whereIn('no_kk_sasaran', $uniqueNoKk)
            ->whereNotNull('no_kk_sasaran')
            ->selectRaw('no_kk_sasaran, COUNT(*) as count')
            ->groupBy('no_kk_sasaran')
            ->pluck('count', 'no_kk_sasaran');
        
        $countRemaja = SasaranRemaja::where('id_posyandu', $posyanduId)
            ->whereIn('no_kk_sasaran', $uniqueNoKk)
            ->whereNotNull('no_kk_sasaran')
            ->selectRaw('no_kk_sasaran, COUNT(*) as count')
            ->groupBy('no_kk_sasaran')
            ->pluck('count', 'no_kk_sasaran');
        
        $countDewasa = SasaranDewasa::where('id_posyandu', $posyanduId)
            ->whereIn('no_kk_sasaran', $uniqueNoKk)
            ->whereNotNull('no_kk_sasaran')
            ->selectRaw('no_kk_sasaran, COUNT(*) as count')
            ->groupBy('no_kk_sasaran')
            ->pluck('count', 'no_kk_sasaran');
        
        $countPralansia = SasaranPralansia::where('id_posyandu', $posyanduId)
            ->whereIn('no_kk_sasaran', $uniqueNoKk)
            ->whereNotNull('no_kk_sasaran')
            ->selectRaw('no_kk_sasaran, COUNT(*) as count')
            ->groupBy('no_kk_sasaran')
            ->pluck('count', 'no_kk_sasaran');
        
        $countLansia = SasaranLansia::where('id_posyandu', $posyanduId)
            ->whereIn('no_kk_sasaran', $uniqueNoKk)
            ->whereNotNull('no_kk_sasaran')
            ->selectRaw('no_kk_sasaran, COUNT(*) as count')
            ->groupBy('no_kk_sasaran')
            ->pluck('count', 'no_kk_sasaran');
        
        // Ambil nama orangtua dari dewasa/pralansia/lansia (prioritas)
        $namaOrangtua = SasaranDewasa::where('id_posyandu', $posyanduId)
            ->whereIn('no_kk_sasaran', $uniqueNoKk)
            ->whereNotNull('no_kk_sasaran')
            ->select('no_kk_sasaran', 'nama_sasaran')
            ->get()
            ->keyBy('no_kk_sasaran');
        
        // Jika tidak ada di dewasa, ambil dari pralansia
        $namaOrangtuaPralansia = SasaranPralansia::where('id_posyandu', $posyanduId)
            ->whereIn('no_kk_sasaran', $uniqueNoKk)
            ->whereNotNull('no_kk_sasaran')
            ->whereNotIn('no_kk_sasaran', $namaOrangtua->keys())
            ->select('no_kk_sasaran', 'nama_sasaran')
            ->get()
            ->keyBy('no_kk_sasaran');
        
        $namaOrangtua = $namaOrangtua->merge($namaOrangtuaPralansia);
        
        // Jika masih tidak ada, ambil dari lansia
        $namaOrangtuaLansia = SasaranLansia::where('id_posyandu', $posyanduId)
            ->whereIn('no_kk_sasaran', $uniqueNoKk)
            ->whereNotNull('no_kk_sasaran')
            ->whereNotIn('no_kk_sasaran', $namaOrangtua->keys())
            ->select('no_kk_sasaran', 'nama_sasaran')
            ->get()
            ->keyBy('no_kk_sasaran');
        
        $namaOrangtua = $namaOrangtua->merge($namaOrangtuaLansia);
        
        // Build hasil
        $noKkList = [];
        foreach ($uniqueNoKk as $noKk) {
            $totalAnggota = ($countBalita[$noKk] ?? 0) + 
                          ($countRemaja[$noKk] ?? 0) + 
                          ($countDewasa[$noKk] ?? 0) + 
                          ($countPralansia[$noKk] ?? 0) + 
                          ($countLansia[$noKk] ?? 0);
            
            $namaOrtu = '-';
            if (isset($namaOrangtua[$noKk])) {
                $namaOrtu = $namaOrangtua[$noKk]->nama_sasaran;
            }
            
            $noKkList[$noKk] = [
                'no_kk' => $noKk,
                'nama_orangtua' => $namaOrtu,
                'jumlah_anggota' => $totalAnggota,
            ];
        }
        
        // Sort by no_kk
        ksort($noKkList);
        
        return array_values($noKkList);
    }

    /**
     * Auto-fill data ketika No KK berubah
     */
    public function updatedNoKkSasaranDewasa($value)
    {
        if ($value) {
            $this->loadDataByNoKkDewasa($value);
        }
    }

    /**
     * Load data berdasarkan No KK
     */
    public function loadDataByNoKkDewasa($noKk)
    {
        if (!$noKk) {
            return;
        }

        $posyanduId = $this->posyanduId ?? null;
        
        if (!$posyanduId) {
            return;
        }

        // Cari sasaran dengan no_kk yang sama
        $sasaran = SasaranDewasa::where('id_posyandu', $posyanduId)
            ->where('no_kk_sasaran', $noKk)
            ->first();

        if (!$sasaran) {
            // Coba dari pralansia
            $sasaran = SasaranPralansia::where('id_posyandu', $posyanduId)
                ->where('no_kk_sasaran', $noKk)
                ->first();
        }

        if (!$sasaran) {
            // Coba dari lansia
            $sasaran = SasaranLansia::where('id_posyandu', $posyanduId)
                ->where('no_kk_sasaran', $noKk)
                ->first();
        }

        if (!$sasaran) {
            // Coba dari balita
            $sasaran = SasaranBayibalita::where('id_posyandu', $posyanduId)
                ->where('no_kk_sasaran', $noKk)
                ->first();
        }

        if (!$sasaran) {
            // Coba dari remaja
            $sasaran = SasaranRemaja::where('id_posyandu', $posyanduId)
                ->where('no_kk_sasaran', $noKk)
                ->first();
        }

        if ($sasaran) {
            // Auto-fill data jika field kosong
            if (empty($this->alamat_sasaran_dewasa) && $sasaran->alamat_sasaran) {
                $this->alamat_sasaran_dewasa = $sasaran->alamat_sasaran;
            }
            
            if (empty($this->rt_dewasa) && $sasaran->rt) {
                $this->rt_dewasa = $sasaran->rt;
            }
            
            if (empty($this->rw_dewasa) && $sasaran->rw) {
                $this->rw_dewasa = $sasaran->rw;
            }
        }
    }
}

