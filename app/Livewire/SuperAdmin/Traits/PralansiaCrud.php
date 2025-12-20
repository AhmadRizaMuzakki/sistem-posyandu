<?php

namespace App\Livewire\SuperAdmin\Traits;

use App\Models\SasaranPralansia;
use App\Models\SasaranBayibalita;
use App\Models\SasaranRemaja;
use App\Models\SasaranDewasa;
use App\Models\SasaranLansia;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
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
    public $pendidikan_pralansia;
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
        $this->pendidikan_pralansia = '';
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
        // Combine hari, bulan, tahun menjadi tanggal lahir
        $this->combineTanggalLahirPralansia();

        $this->validate([
            'nama_sasaran_pralansia' => 'required|string|max:100',
            'nik_sasaran_pralansia' => 'required|numeric',
            'no_kk_sasaran_pralansia' => 'required|numeric',
            'hari_lahir_pralansia' => 'required|numeric|min:1|max:31',
            'bulan_lahir_pralansia' => 'required|numeric|min:1|max:12',
            'tahun_lahir_pralansia' => 'required|numeric|min:1900|max:' . date('Y'),
            'tanggal_lahir_pralansia' => 'required|date',
            'jenis_kelamin_pralansia' => 'required|in:Laki-laki,Perempuan',
            'alamat_sasaran_pralansia' => 'required|string|max:225',
            'pendidikan_pralansia' => 'nullable|string',
        ], [
            'nama_sasaran_pralansia.required' => 'Nama sasaran wajib diisi.',
            'nik_sasaran_pralansia.required' => 'NIK wajib diisi.',
            'nik_sasaran_pralansia.numeric' => 'NIK harus berupa angka.',
            'no_kk_sasaran_pralansia.required' => 'No KK wajib diisi.',
            'no_kk_sasaran_pralansia.numeric' => 'No KK harus berupa angka.',
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
            'pendidikan_pralansia.string' => 'Pendidikan harus berupa teks.',
        ]);

        // Hitung umur dari tanggal_lahir_pralansia
        $umur = null;
        if ($this->tanggal_lahir_pralansia) {
            $umur = Carbon::parse($this->tanggal_lahir_pralansia)->age;
        }

        // Cari nik_orangtua dari sasaran balita jika ada no_kk dan alamat yang sama
        $nik_orangtua = null;
        $pralansia = null;
        
        // Jika update, cek apakah sudah ada nik_orangtua
        if ($this->id_sasaran_pralansia) {
            $pralansia = SasaranPralansia::find($this->id_sasaran_pralansia);
            if ($pralansia && $pralansia->nik_orangtua) {
                $nik_orangtua = $pralansia->nik_orangtua;
            }
        }
        
        // Jika belum ada nik_orangtua, cari dari sasaran balita
        if (!$nik_orangtua && $this->no_kk_sasaran_pralansia && $this->alamat_sasaran_pralansia) {
            $existingBalita = SasaranBayibalita::where('no_kk_sasaran', $this->no_kk_sasaran_pralansia)
                ->where('alamat_sasaran', $this->alamat_sasaran_pralansia)
                ->whereNotNull('nik_orangtua')
                ->first();
            
            if ($existingBalita && $existingBalita->nik_orangtua) {
                $nik_orangtua = $existingBalita->nik_orangtua;
            }
        }

        // Buat atau update user untuk sasaran pralansia berdasarkan No KK
        $userId = null;
        if ($this->id_users_sasaran_pralansia !== '') {
            // Jika user sudah dipilih manual, gunakan itu
            $userId = $this->id_users_sasaran_pralansia;
        } else {
            // Pastikan no_kk tersedia
            if (empty($this->no_kk_sasaran_pralansia)) {
                throw new \Exception('No KK wajib diisi untuk membuat akun.');
            }

            // Buat akun otomatis berdasarkan No KK sasaran
            $email = $this->no_kk_sasaran_pralansia . '@gmail.com';
            $userExists = User::where('email', $email)->first();

            if ($userExists) {
                // Update user yang sudah ada (timpa data jika No KK sama)
                $userExists->name = $this->nama_sasaran_pralansia;
                $userExists->password = Hash::make($this->no_kk_sasaran_pralansia);
                $userExists->save();
                $userId = $userExists->id;
            } else {
                // Buat user baru
                $user = User::create([
                    'name' => $this->nama_sasaran_pralansia,
                    'email' => $email,
                    'password' => Hash::make($this->no_kk_sasaran_pralansia),
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
            'nama_sasaran' => $this->nama_sasaran_pralansia,
            'nik_sasaran' => $this->nik_sasaran_pralansia,
            'no_kk_sasaran' => $this->no_kk_sasaran_pralansia ?: null,
            'tempat_lahir' => $this->tempat_lahir_pralansia ?: null,
            'tanggal_lahir' => $this->tanggal_lahir_pralansia,
            'jenis_kelamin' => $this->jenis_kelamin_pralansia,
            'umur_sasaran' => $umur,
            'pekerjaan' => $this->pekerjaan_pralansia ?: null,
            'pendidikan' => $this->pendidikan_pralansia ?: null,
            'nik_orangtua' => $nik_orangtua,
            'alamat_sasaran' => $this->alamat_sasaran_pralansia,
            'rt' => $this->rt_pralansia ?: null,
            'rw' => $this->rw_pralansia ?: null,
            'kepersertaan_bpjs' => $this->kepersertaan_bpjs_pralansia ?: null,
            'nomor_bpjs' => $this->nomor_bpjs_pralansia ?: null,
            'nomor_telepon' => $this->nomor_telepon_pralansia ?: null,
        ];

        if ($this->id_sasaran_pralansia) {
            // UPDATE
            if (!$pralansia) {
                $pralansia = SasaranPralansia::findOrFail($this->id_sasaran_pralansia);
            }
            $pralansia->update($data);
            session()->flash('message', 'Data Pralansia berhasil diperbarui.');
        } else {
            // CREATE
            SasaranPralansia::create($data);
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
        $pralansia = SasaranPralansia::findOrFail($id);

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
        $this->pendidikan_pralansia = $pralansia->pendidikan ?? '';
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
        $pralansia = SasaranPralansia::findOrFail($id);
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

    /**
     * Get list of No KK untuk autocomplete
     */
    public function getNoKkListPralansia()
    {
        $posyanduId = $this->posyanduId ?? null;
        
        if (!$posyanduId) {
            return [];
        }

        $noKkList = [];
        
        // Get from balita
        $balitaList = SasaranBayibalita::where('id_posyandu', $posyanduId)
            ->whereNotNull('no_kk_sasaran')
            ->get();
            
        foreach ($balitaList as $balita) {
            $noKk = $balita->no_kk_sasaran;
            if (!isset($noKkList[$noKk])) {
                $countBalita = SasaranBayibalita::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $countRemaja = SasaranRemaja::where('id_posyandu', $posyanduId)
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
                    'nama_orangtua' => '-',
                    'jumlah_anggota' => $totalAnggota,
                ];
            }
        }
        
        // Get from remaja
        $remajaList = SasaranRemaja::where('id_posyandu', $posyanduId)
            ->whereNotNull('no_kk_sasaran')
            ->get();
            
        foreach ($remajaList as $remaja) {
            $noKk = $remaja->no_kk_sasaran;
            if (!isset($noKkList[$noKk])) {
                $countBalita = SasaranBayibalita::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $countRemaja = SasaranRemaja::where('id_posyandu', $posyanduId)
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
                    'nama_orangtua' => '-',
                    'jumlah_anggota' => $totalAnggota,
                ];
            }
        }
        
        // Get from dewasa
        $dewasaList = SasaranDewasa::where('id_posyandu', $posyanduId)
            ->whereNotNull('no_kk_sasaran')
            ->get();
            
        foreach ($dewasaList as $dewasa) {
            $noKk = $dewasa->no_kk_sasaran;
            if (!isset($noKkList[$noKk])) {
                $countBalita = SasaranBayibalita::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $countRemaja = SasaranRemaja::where('id_posyandu', $posyanduId)
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
                $countBalita = SasaranBayibalita::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $countRemaja = SasaranRemaja::where('id_posyandu', $posyanduId)
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
                $countBalita = SasaranBayibalita::where('id_posyandu', $posyanduId)
                    ->where('no_kk_sasaran', $noKk)
                    ->count();
                $countRemaja = SasaranRemaja::where('id_posyandu', $posyanduId)
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
                ];
            }
        }
        
        // Sort by no_kk
        ksort($noKkList);
        
        return array_values($noKkList);
    }

    /**
     * Auto-fill data ketika No KK berubah
     */
    public function updatedNoKkSasaranPralansia($value)
    {
        if ($value) {
            $this->loadDataByNoKkPralansia($value);
        }
    }

    /**
     * Load data berdasarkan No KK
     */
    public function loadDataByNoKkPralansia($noKk)
    {
        if (!$noKk) {
            return;
        }

        $posyanduId = $this->posyanduId ?? null;
        
        if (!$posyanduId) {
            return;
        }

        // Cari sasaran dengan no_kk yang sama
        $sasaran = SasaranPralansia::where('id_posyandu', $posyanduId)
            ->where('no_kk_sasaran', $noKk)
            ->first();

        if (!$sasaran) {
            // Coba dari lansia
            $sasaran = SasaranLansia::where('id_posyandu', $posyanduId)
                ->where('no_kk_sasaran', $noKk)
                ->first();
        }

        if (!$sasaran) {
            // Coba dari dewasa
            $sasaran = SasaranDewasa::where('id_posyandu', $posyanduId)
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
            if (empty($this->alamat_sasaran_pralansia) && $sasaran->alamat_sasaran) {
                $this->alamat_sasaran_pralansia = $sasaran->alamat_sasaran;
            }
            
            if (empty($this->rt_pralansia) && $sasaran->rt) {
                $this->rt_pralansia = $sasaran->rt;
            }
            
            if (empty($this->rw_pralansia) && $sasaran->rw) {
                $this->rw_pralansia = $sasaran->rw;
            }
        }
    }
}

