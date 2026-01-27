<?php

namespace App\Livewire\SuperAdmin\Traits;

use App\Models\Pendidikan as PendidikanModel;
use App\Models\SasaranBayibalita;
use App\Models\SasaranRemaja;
use App\Models\SasaranDewasa;
use App\Models\SasaranPralansia;
use App\Models\SasaranLansia;
use App\Models\SasaranIbuhamil;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

trait PendidikanCrud
{
    // Modal State
    public $isPendidikanModalOpen = false;

    // Field Form Pendidikan
    public $id_pendidikan = null;
    public $id_posyandu_pendidikan;
    public $id_sasaran_pendidikan;
    public $kategori_sasaran_pendidikan = '';
    public $nik_pendidikan = '';
    public $nama_pendidikan = '';
    public $tanggal_lahir_pendidikan = '';
    public $hari_lahir_pendidikan;
    public $bulan_lahir_pendidikan;
    public $tahun_lahir_pendidikan;
    public $jenis_kelamin_pendidikan = '';
    public $umur_pendidikan;
    public $pendidikan_terakhir_pendidikan = '';
    public $rt_pendidikan = '';
    public $rw_pendidikan = '';

    // Untuk dropdown sasaran
    public $sasaranList = [];

    /**
     * Buka modal tambah/edit Pendidikan
     */
    public function openPendidikanModal($id = null)
    {
        if ($id) {
            $this->editPendidikan($id);
        } else {
            $this->resetPendidikanFields();
            // Pre-fill dengan posyandu saat ini
            if (isset($this->posyandu) && $this->posyandu && isset($this->posyandu->id_posyandu)) {
                $this->id_posyandu_pendidikan = $this->posyandu->id_posyandu;
                $this->loadSasaranList();
            }
            $this->isPendidikanModalOpen = true;
        }
    }

    /**
     * Tutup modal dan reset field
     */
    public function closePendidikanModal()
    {
        $this->resetPendidikanFields();
        $this->isPendidikanModalOpen = false;
    }

    /**
     * Reset semua field form Pendidikan
     */
    private function resetPendidikanFields()
    {
        $fields = [
            'id_pendidikan' => null,
            'id_posyandu_pendidikan' => '',
            'id_sasaran_pendidikan' => '',
            'kategori_sasaran_pendidikan' => '',
            'nik_pendidikan' => '',
            'nama_pendidikan' => '',
            'tanggal_lahir_pendidikan' => '',
            'hari_lahir_pendidikan' => '',
            'bulan_lahir_pendidikan' => '',
            'tahun_lahir_pendidikan' => '',
            'jenis_kelamin_pendidikan' => '',
            'umur_pendidikan' => '',
            'pendidikan_terakhir_pendidikan' => '',
            'rt_pendidikan' => '',
            'rw_pendidikan' => '',
            'sasaranList' => [],
        ];

        foreach ($fields as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Load sasaran list berdasarkan posyandu dan kategori
     */
    public function loadSasaranList()
    {
        if (!$this->id_posyandu_pendidikan) {
            $this->sasaranList = [];
            return;
        }

        $sasaranConfig = [
            [SasaranBayibalita::class, 'id_sasaran_bayibalita', 'bayibalita'],
            [SasaranRemaja::class, 'id_sasaran_remaja', 'remaja'],
            [SasaranDewasa::class, 'id_sasaran_dewasa', 'dewasa'],
            [SasaranPralansia::class, 'id_sasaran_pralansia', 'pralansia'],
            [SasaranLansia::class, 'id_sasaran_lansia', 'lansia'],
            [SasaranIbuhamil::class, 'id_sasaran_ibuhamil', 'ibuhamil'],
        ];

        $sasaranList = collect();

        foreach ($sasaranConfig as [$modelClass, $idField, $kategori]) {
            $sasarans = $modelClass::where('id_posyandu', $this->id_posyandu_pendidikan)->get();
            
            foreach ($sasarans as $s) {
                $tanggalLahir = null;
                if ($s->tanggal_lahir) {
                    try {
                        if ($s->tanggal_lahir instanceof \Carbon\Carbon) {
                            $tanggalLahir = $s->tanggal_lahir->format('Y-m-d');
                        } elseif (is_string($s->tanggal_lahir)) {
                            // Jika sudah string format Y-m-d, gunakan langsung
                            // Jika format lain, parse dulu
                            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s->tanggal_lahir)) {
                                $tanggalLahir = $s->tanggal_lahir;
                            } else {
                                $tanggalLahir = Carbon::parse($s->tanggal_lahir)->format('Y-m-d');
                            }
                        } else {
                            $tanggalLahir = Carbon::parse($s->tanggal_lahir)->format('Y-m-d');
                        }
                    } catch (\Exception $e) {
                        // Jika parsing gagal, set null
                        $tanggalLahir = null;
                    }
                }
                
                $sasaranList->push([
                    'id' => $s->$idField,
                    'kategori' => $kategori,
                    'nama' => $s->nama_sasaran,
                    'nik' => $s->nik_sasaran ?? '',
                    'tanggal_lahir' => $tanggalLahir,
                    'jenis_kelamin' => $s->jenis_kelamin ?? '',
                    'umur' => $s->umur_sasaran ?? null,
                    'pendidikan' => $s->pendidikan ?? '',
                    'rt' => $s->rt ?? '',
                    'rw' => $s->rw ?? '',
                ]);
            }
        }

        $this->sasaranList = $sasaranList->toArray();
    }

    /**
     * Update data saat sasaran dipilih
     * Method ini dipanggil otomatis oleh Livewire ketika id_sasaran_pendidikan berubah
     */
    public function updatedIdSasaranPendidikan($value)
    {
        if (!$value || empty($this->sasaranList)) {
            $this->kategori_sasaran_pendidikan = '';
            $this->nik_pendidikan = '';
            $this->nama_pendidikan = '';
            $this->tanggal_lahir_pendidikan = '';
            $this->hari_lahir_pendidikan = '';
            $this->bulan_lahir_pendidikan = '';
            $this->tahun_lahir_pendidikan = '';
            $this->jenis_kelamin_pendidikan = '';
            $this->umur_pendidikan = '';
            $this->pendidikan_terakhir_pendidikan = '';
            $this->rt_pendidikan = '';
            $this->rw_pendidikan = '';
            return;
        }

        // Cari sasaran dari list berdasarkan ID dan kategori (jika sudah ada)
        // Ini penting untuk menghindari konflik ID antar kategori
        $sasaran = null;
        
        // Jika sudah ada kategori, cari yang sesuai dengan ID dan kategori
        if ($this->kategori_sasaran_pendidikan) {
            $sasaran = collect($this->sasaranList)->first(function($s) use ($value) {
                return $s['id'] == $value && $s['kategori'] == $this->kategori_sasaran_pendidikan;
            });
        }
        
        // Jika tidak ditemukan dengan kategori, cari berdasarkan ID saja
        if (!$sasaran) {
            $sasaran = collect($this->sasaranList)->firstWhere('id', $value);
        }
        
        if ($sasaran) {
            // Set kategori langsung dari list untuk menghindari konflik ID
            $this->kategori_sasaran_pendidikan = $sasaran['kategori'] ?? '';
            $this->nik_pendidikan = $sasaran['nik'] ?? '';
            $this->nama_pendidikan = $sasaran['nama'] ?? '';
            
            if (!empty($sasaran['tanggal_lahir'])) {
                $tanggalLahir = Carbon::parse($sasaran['tanggal_lahir']);
                $this->tanggal_lahir_pendidikan = $tanggalLahir->format('Y-m-d');
                $this->hari_lahir_pendidikan = $tanggalLahir->day;
                $this->bulan_lahir_pendidikan = $tanggalLahir->month;
                $this->tahun_lahir_pendidikan = $tanggalLahir->year;
                $this->calculateUmur();
            } else {
                $this->tanggal_lahir_pendidikan = '';
                $this->hari_lahir_pendidikan = '';
                $this->bulan_lahir_pendidikan = '';
                $this->tahun_lahir_pendidikan = '';
                $this->umur_pendidikan = '';
            }
            
            $this->jenis_kelamin_pendidikan = $sasaran['jenis_kelamin'] ?? '';
            
            if (isset($sasaran['umur']) && $sasaran['umur'] !== null) {
                $this->umur_pendidikan = $sasaran['umur'];
            } elseif (empty($this->umur_pendidikan) && $this->tanggal_lahir_pendidikan) {
                $this->calculateUmur();
            }
            
            $this->pendidikan_terakhir_pendidikan = $sasaran['pendidikan'] ?? '';
            $this->rt_pendidikan = $sasaran['rt'] ?? '';
            $this->rw_pendidikan = $sasaran['rw'] ?? '';
        }
    }

    /**
     * Update sasaran list saat posyandu berubah
     */
    public function updatedIdPosyanduPendidikan()
    {
        $this->id_sasaran_pendidikan = '';
        $this->kategori_sasaran_pendidikan = '';
        $this->nik_pendidikan = '';
        $this->nama_pendidikan = '';
        $this->tanggal_lahir_pendidikan = '';
        $this->hari_lahir_pendidikan = '';
        $this->bulan_lahir_pendidikan = '';
        $this->tahun_lahir_pendidikan = '';
            $this->jenis_kelamin_pendidikan = '';
            $this->umur_pendidikan = '';
            $this->pendidikan_terakhir_pendidikan = '';
            $this->rt_pendidikan = '';
            $this->rw_pendidikan = '';
            $this->loadSasaranList();
        }

    /**
     * Calculate umur dari tanggal lahir
     */
    public function calculateUmur()
    {
        if ($this->tanggal_lahir_pendidikan) {
            try {
                $tanggalLahir = Carbon::parse($this->tanggal_lahir_pendidikan);
                $this->umur_pendidikan = $tanggalLahir->age;
            } catch (\Exception $e) {
                $this->umur_pendidikan = '';
            }
        } else {
            $this->umur_pendidikan = '';
        }
    }

    /**
     * Updated tanggal lahir
     */
    public function updatedTanggalLahirPendidikan()
    {
        $this->calculateUmur();
    }

    /**
     * Updated hari, bulan, atau tahun lahir
     */
    public function updatedHariLahirPendidikan()
    {
        $this->combineTanggalLahir();
    }

    public function updatedBulanLahirPendidikan()
    {
        $this->combineTanggalLahir();
    }

    public function updatedTahunLahirPendidikan()
    {
        $this->combineTanggalLahir();
    }

    /**
     * Combine hari, bulan, tahun menjadi tanggal lahir
     */
    protected function combineTanggalLahir(): void
    {
        if ($this->hari_lahir_pendidikan && $this->bulan_lahir_pendidikan && $this->tahun_lahir_pendidikan) {
            try {
                $this->tanggal_lahir_pendidikan = Carbon::create(
                    $this->tahun_lahir_pendidikan,
                    $this->bulan_lahir_pendidikan,
                    $this->hari_lahir_pendidikan
                )->format('Y-m-d');
                $this->calculateUmur();
            } catch (\Exception $e) {
                $this->tanggal_lahir_pendidikan = null;
            }
        } else {
            $this->tanggal_lahir_pendidikan = null;
        }
    }

    /**
     * Get validation rules untuk pendidikan
     */
    protected function getPendidikanValidationRules()
    {
        return [
            'id_posyandu_pendidikan' => 'required|exists:posyandu,id_posyandu',
            'id_sasaran_pendidikan' => 'required',
            'kategori_sasaran_pendidikan' => 'required|in:bayibalita,remaja,dewasa,pralansia,lansia,ibuhamil',
            'nama_pendidikan' => 'required|string|max:255',
            'nik_pendidikan' => 'nullable|string|max:255',
            'hari_lahir_pendidikan' => 'required|numeric|min:1|max:31',
            'bulan_lahir_pendidikan' => 'required|numeric|min:1|max:12',
            'tahun_lahir_pendidikan' => 'required|numeric|min:1900|max:' . date('Y'),
            'tanggal_lahir_pendidikan' => 'required|date',
            'jenis_kelamin_pendidikan' => 'required|in:Laki-laki,Perempuan',
            'umur_pendidikan' => 'nullable|integer|min:0|max:150',
            'pendidikan_terakhir_pendidikan' => 'required|in:Tidak/Belum Sekolah,PAUD,TK,Tidak Tamat SD/Sederajat,Tamat SD/Sederajat,SLTP/Sederajat,SLTA/Sederajat,Diploma I/II,Akademi/Diploma III/Sarjana Muda,Diploma IV/Strata I,Strata II,Strata III',
        ];
    }

    /**
     * Get validation messages untuk pendidikan
     */
    protected function getPendidikanValidationMessages()
    {
        return [
            'id_posyandu_pendidikan.required' => 'Posyandu wajib dipilih.',
            'id_posyandu_pendidikan.exists' => 'Posyandu yang dipilih tidak valid.',
            'id_sasaran_pendidikan.required' => 'Sasaran wajib dipilih.',
            'kategori_sasaran_pendidikan.required' => 'Kategori sasaran wajib dipilih.',
            'kategori_sasaran_pendidikan.in' => 'Kategori sasaran tidak valid.',
            'nama_pendidikan.required' => 'Nama wajib diisi.',
            'nama_pendidikan.max' => 'Nama maksimal 255 karakter.',
            'hari_lahir_pendidikan.required' => 'Hari lahir wajib diisi.',
            'bulan_lahir_pendidikan.required' => 'Bulan lahir wajib diisi.',
            'tahun_lahir_pendidikan.required' => 'Tahun lahir wajib diisi.',
            'tanggal_lahir_pendidikan.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir_pendidikan.date' => 'Tanggal lahir tidak valid.',
            'jenis_kelamin_pendidikan.required' => 'Jenis kelamin wajib dipilih.',
            'pendidikan_terakhir_pendidikan.required' => 'Pendidikan terakhir wajib dipilih.',
        ];
    }

    /**
     * Proses simpan data pendidikan, tambah/edit
     * Data diambil dari sasaran yang dipilih
     */
    public function storePendidikan()
    {
        $this->combineTanggalLahir();
        $this->validate($this->getPendidikanValidationRules(), $this->getPendidikanValidationMessages());

        // Ambil data dari sasaran yang dipilih
        $sasaran = collect($this->sasaranList)->firstWhere('id', $this->id_sasaran_pendidikan);
        
        if (!$sasaran) {
            session()->flash('message', 'Sasaran tidak ditemukan.');
            session()->flash('messageType', 'error');
            return;
        }

        // Ambil data dari sasaran, dengan prioritas inputan user jika ada
        $data = [
            'id_posyandu' => $this->id_posyandu_pendidikan,
            'id_users' => Auth::id(),
            'id_sasaran' => $this->id_sasaran_pendidikan,
            'kategori_sasaran' => $this->kategori_sasaran_pendidikan,
            'nik' => $this->nik_pendidikan ?: ($sasaran['nik'] ?? null),
            'nama' => $this->nama_pendidikan ?: ($sasaran['nama'] ?? ''),
            'tanggal_lahir' => $this->tanggal_lahir_pendidikan ?: ($sasaran['tanggal_lahir'] ?? null),
            'jenis_kelamin' => $this->jenis_kelamin_pendidikan ?: ($sasaran['jenis_kelamin'] ?? null),
            'umur' => $this->umur_pendidikan !== '' ? $this->umur_pendidikan : ($sasaran['umur'] ?? null),
            'pendidikan_terakhir' => $this->pendidikan_terakhir_pendidikan ?: ($sasaran['pendidikan'] ?? null),
            'rt' => $this->rt_pendidikan ?: ($sasaran['rt'] ?? null),
            'rw' => $this->rw_pendidikan ?: ($sasaran['rw'] ?? null),
        ];

        DB::transaction(function () use ($data) {
            if ($this->id_pendidikan) {
                PendidikanModel::findOrFail($this->id_pendidikan)->update($data);
            } else {
                PendidikanModel::create($data);
            }
        });
        
        if ($this->id_pendidikan) {
            session()->flash('message', 'Data Pendidikan berhasil diperbarui.');
        } else {
            session()->flash('message', 'Data Pendidikan berhasil ditambahkan.');
        }

        if (method_exists($this, 'refreshPosyandu')) {
            $this->refreshPosyandu();
        }
        $this->closePendidikanModal();
    }

    /**
     * Inisialisasi form edit pendidikan
     */
    public function editPendidikan($id = null)
    {
        if (!$id) {
            return;
        }

        $pendidikan = PendidikanModel::findOrFail($id);

        $this->id_pendidikan = $pendidikan->id_pendidikan;
        $this->id_posyandu_pendidikan = $pendidikan->id_posyandu;
        $this->loadSasaranList();
        $this->kategori_sasaran_pendidikan = $pendidikan->kategori_sasaran;
        $this->id_sasaran_pendidikan = $pendidikan->id_sasaran;
        $this->nik_pendidikan = $pendidikan->nik ?? '';
        $this->nama_pendidikan = $pendidikan->nama ?? '';
        
        if ($pendidikan->tanggal_lahir) {
            $tanggalLahir = $pendidikan->tanggal_lahir instanceof \Carbon\Carbon 
                ? $pendidikan->tanggal_lahir 
                : Carbon::parse($pendidikan->tanggal_lahir);
            
            $this->tanggal_lahir_pendidikan = $tanggalLahir->format('Y-m-d');
            $this->hari_lahir_pendidikan = $tanggalLahir->day;
            $this->bulan_lahir_pendidikan = $tanggalLahir->month;
            $this->tahun_lahir_pendidikan = $tanggalLahir->year;
        } else {
            $this->tanggal_lahir_pendidikan = '';
            $this->hari_lahir_pendidikan = '';
            $this->bulan_lahir_pendidikan = '';
            $this->tahun_lahir_pendidikan = '';
        }
        
        $this->jenis_kelamin_pendidikan = $pendidikan->jenis_kelamin ?? '';
        $this->umur_pendidikan = $pendidikan->umur ?? '';
        $this->pendidikan_terakhir_pendidikan = $pendidikan->pendidikan_terakhir ?? '';
        $this->rt_pendidikan = $pendidikan->rt ?? '';
        $this->rw_pendidikan = $pendidikan->rw ?? '';
        $this->isPendidikanModalOpen = true;
    }

    /**
     * Hapus data pendidikan
     */
    public function deletePendidikan($id = null)
    {
        if (!$id) {
            return;
        }

        PendidikanModel::findOrFail($id)->delete();

        if (method_exists($this, 'refreshPosyandu')) {
            $this->refreshPosyandu();
        }
        session()->flash('message', 'Data Pendidikan berhasil dihapus.');
    }

    /**
     * Get query pendidikan dengan filter search
     * Filter berdasarkan posyandu (konteks), data berasal dari sasaran
     */
    protected function getPendidikanQuery($posyanduId)
    {
        // Query berdasarkan posyandu (hanya sebagai filter/konteks)
        // Data pendidikan sendiri berasal dari sasaran yang dipilih
        $query = PendidikanModel::where('id_posyandu', $posyanduId);

        if (!empty($this->search ?? '')) {
            $query->where(function ($q) {
                $q->where('nama', 'like', '%' . $this->search . '%')
                  ->orWhere('nik', 'like', '%' . $this->search . '%')
                  ->orWhere('pendidikan_terakhir', 'like', '%' . $this->search . '%');
            });
        }

        return $query->orderBy('tanggal_lahir', 'desc');
    }
}

