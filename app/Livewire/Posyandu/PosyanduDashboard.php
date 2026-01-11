<?php

namespace App\Livewire\Posyandu;

use App\Livewire\Posyandu\Traits\PosyanduHelper;
use App\Livewire\Posyandu\Traits\DashboardHelper;
use App\Models\Kader;
use App\Models\Imunisasi;
use App\Models\Pendidikan;
use App\Models\SasaranRemaja;
use App\Models\SasaranDewasa;
use App\Models\SasaranPralansia;
use App\Models\SasaranLansia;
use App\Models\SasaranIbuhamil;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PosyanduDashboard extends Component
{
    use PosyanduHelper, DashboardHelper, WithFileUploads;

    public $skFile;
    public $showUploadModal = false;
    
    // Pendidikan properties
    public $showPendidikanModal = false;
    public $pendidikan_terakhir = '';

    #[Layout('layouts.posyandudashboard')]

    public function mount()
    {
        $this->initializePosyandu();
    }

    public function render()
    {
        $totalKader = Kader::where('id_posyandu', $this->posyanduId)->count();
        $totalSasaran = $this->getTotalSasaran($this->posyanduId);
        $sasaranByCategory = $this->getSasaranCountsByCategory($this->posyanduId);
        $pendidikanData = $this->getPendidikanData($this->posyanduId);

        // Data untuk dropdown filter imunisasi
        $kategoriSasaranList = Imunisasi::where('id_posyandu', $this->posyanduId)
            ->distinct()
            ->orderBy('kategori_sasaran')
            ->pluck('kategori_sasaran')
            ->toArray();

        $jenisVaksinList = Imunisasi::where('id_posyandu', $this->posyanduId)
            ->distinct()
            ->orderBy('jenis_imunisasi')
            ->pluck('jenis_imunisasi')
            ->toArray();

        $imunisasiList = Imunisasi::where('id_posyandu', $this->posyanduId)->get();
        $namaSasaranList = collect();
        foreach ($imunisasiList as $imunisasi) {
            $sasaran = $imunisasi->sasaran;
            if ($sasaran && $sasaran->nama_sasaran) {
                $namaSasaranList->push($sasaran->nama_sasaran);
            }
        }
        $namaSasaranList = $namaSasaranList->unique()->sort()->values()->toArray();

        // Data untuk dropdown filter pendidikan
        $kategoriPendidikanList = Pendidikan::where('id_posyandu', $this->posyanduId)
            ->distinct()
            ->orderBy('pendidikan_terakhir')
            ->pluck('pendidikan_terakhir')
            ->toArray();

        // Mapping label kategori
        $kategoriLabels = [
            'bayibalita' => 'Bayi dan Balita',
            'remaja' => 'Remaja',
            'dewasa' => 'Dewasa',
            'pralansia' => 'Pralansia',
            'lansia' => 'Lansia',
            'ibuhamil' => 'Ibu Hamil',
        ];

        return view('livewire.posyandu.admin-posyandu', [
            'posyandu' => $this->posyandu,
            'totalKader' => $totalKader,
            'totalSasaran' => $totalSasaran,
            'sasaranByCategory' => $sasaranByCategory,
            'pendidikanData' => $pendidikanData,
            'kategoriSasaranList' => $kategoriSasaranList,
            'kategoriLabels' => $kategoriLabels,
            'jenisVaksinList' => $jenisVaksinList,
            'namaSasaranList' => $namaSasaranList,
            'kategoriPendidikanList' => $kategoriPendidikanList,
        ]);
    }

    /**
     * Upload SK Posyandu dengan validasi keamanan
     */
    public function uploadSk()
    {
        $this->validate([
            'skFile' => [
                'required',
                'file',
                'mimes:pdf,doc,docx',
                'max:5120', // 5MB max
                function ($attribute, $value, $fail) {
                    // Validasi MIME type tambahan untuk keamanan
                    $allowedMimes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                    $fileMime = $value->getMimeType();

                    if (!in_array($fileMime, $allowedMimes)) {
                        $fail('Format file tidak diizinkan. Hanya PDF dan DOC/DOCX yang diperbolehkan.');
                    }

                    // Validasi ekstensi file
                    $extension = strtolower($value->getClientOriginalExtension());
                    $allowedExtensions = ['pdf', 'doc', 'docx'];

                    if (!in_array($extension, $allowedExtensions)) {
                        $fail('Ekstensi file tidak diizinkan.');
                    }

                    // Validasi ukuran file (5MB)
                    if ($value->getSize() > 5242880) {
                        $fail('Ukuran file maksimal 5MB.');
                    }

                    // Validasi nama file (prevent path traversal)
                    $filename = $value->getClientOriginalName();
                    if (preg_match('/\.\./', $filename) || preg_match('/[\/\\\\]/', $filename)) {
                        $fail('Nama file tidak valid.');
                    }
                }
            ],
        ], [
            'skFile.required' => 'File SK harus diupload.',
            'skFile.file' => 'File yang diupload tidak valid.',
            'skFile.mimes' => 'Format file harus PDF, DOC, atau DOCX.',
            'skFile.max' => 'Ukuran file maksimal 5MB.',
        ]);

        try {
            // Generate nama file yang aman
            $originalName = $this->skFile->getClientOriginalName();
            $extension = $this->skFile->getClientOriginalExtension();
            $safeName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '_' . time() . '.' . $extension;

            // Simpan file ke storage
            $path = $this->skFile->storeAs('sk_posyandu', $safeName, 'public');

            // Hapus file lama jika ada
            if ($this->posyandu->sk_posyandu) {
                $oldPath = str_replace('/storage/', '', $this->posyandu->sk_posyandu);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Update database dengan path file
            $this->posyandu->update([
                'sk_posyandu' => '/storage/' . $path
            ]);

            // Refresh data
            $this->refreshPosyandu();

            // Reset
            $this->skFile = null;
            $this->showUploadModal = false;

            session()->flash('message', 'File SK berhasil diupload.');
            session()->flash('messageType', 'success');
        } catch (\Exception $e) {
            session()->flash('message', 'Gagal mengupload file SK: ' . $e->getMessage());
            session()->flash('messageType', 'error');
        }
    }

    /**
     * Hapus file SK
     */
    public function deleteSk()
    {
        try {
            if ($this->posyandu->sk_posyandu) {
                $oldPath = str_replace('/storage/', '', $this->posyandu->sk_posyandu);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $this->posyandu->update([
                'sk_posyandu' => null
            ]);

            $this->refreshPosyandu();

            session()->flash('message', 'File SK berhasil dihapus.');
            session()->flash('messageType', 'success');
        } catch (\Exception $e) {
            session()->flash('message', 'Gagal menghapus file SK: ' . $e->getMessage());
            session()->flash('messageType', 'error');
        }
    }

    /**
     * Buka modal input pendidikan
     */
    public function openPendidikanModal()
    {
        $this->pendidikan_terakhir = '';
        $this->showPendidikanModal = true;
    }

    /**
     * Tutup modal pendidikan
     */
    public function closePendidikanModal()
    {
        $this->showPendidikanModal = false;
        $this->pendidikan_terakhir = '';
    }

    /**
     * Update pendidikan ke semua sasaran di posyandu ini
     */
    public function updatePendidikanSemuaSasaran()
    {
        $this->validate([
            'pendidikan_terakhir' => 'required|in:Tidak/Belum Sekolah,PAUD,TK,Tidak Tamat SD/Sederajat,Tamat SD/Sederajat,SLTP/Sederajat,SLTA/Sederajat,Diploma I/II,Akademi/Diploma III/Sarjana Muda,Diploma IV/Strata I,Strata II,Strata III',
        ], [
            'pendidikan_terakhir.required' => 'Pendidikan terakhir wajib dipilih.',
            'pendidikan_terakhir.in' => 'Pendidikan terakhir tidak valid.',
        ]);

        try {
            $posyanduId = $this->posyanduId;
            $pendidikanValue = $this->pendidikan_terakhir;
            $userId = Auth::id();
            $updatedCount = 0;

            // Update Remaja
            $remajaList = SasaranRemaja::where('id_posyandu', $posyanduId)->get();
            foreach ($remajaList as $remaja) {
                $remaja->update(['pendidikan' => $pendidikanValue]);
                
                // Simpan ke tabel pendidikan
                Pendidikan::updateOrCreate(
                    [
                        'id_posyandu' => $posyanduId,
                        'id_sasaran' => $remaja->id_sasaran_remaja,
                        'kategori_sasaran' => 'remaja',
                    ],
                    [
                        'id_users' => $userId,
                        'nik' => $remaja->nik_sasaran,
                        'nama' => $remaja->nama_sasaran,
                        'tanggal_lahir' => $remaja->tanggal_lahir,
                        'jenis_kelamin' => $remaja->jenis_kelamin,
                        'umur' => $remaja->umur_sasaran,
                        'pendidikan_terakhir' => $pendidikanValue,
                    ]
                );
                $updatedCount++;
            }

            // Update Dewasa
            $dewasaList = SasaranDewasa::where('id_posyandu', $posyanduId)->get();
            foreach ($dewasaList as $dewasa) {
                $dewasa->update(['pendidikan' => $pendidikanValue]);
                
                // Simpan ke tabel pendidikan
                Pendidikan::updateOrCreate(
                    [
                        'id_posyandu' => $posyanduId,
                        'id_sasaran' => $dewasa->id_sasaran_dewasa,
                        'kategori_sasaran' => 'dewasa',
                    ],
                    [
                        'id_users' => $userId,
                        'nik' => $dewasa->nik_sasaran,
                        'nama' => $dewasa->nama_sasaran,
                        'tanggal_lahir' => $dewasa->tanggal_lahir,
                        'jenis_kelamin' => $dewasa->jenis_kelamin,
                        'umur' => $dewasa->umur_sasaran,
                        'pendidikan_terakhir' => $pendidikanValue,
                    ]
                );
                $updatedCount++;
            }

            // Update Pralansia
            $pralansiaList = SasaranPralansia::where('id_posyandu', $posyanduId)->get();
            foreach ($pralansiaList as $pralansia) {
                $pralansia->update(['pendidikan' => $pendidikanValue]);
                
                // Simpan ke tabel pendidikan
                Pendidikan::updateOrCreate(
                    [
                        'id_posyandu' => $posyanduId,
                        'id_sasaran' => $pralansia->id_sasaran_pralansia,
                        'kategori_sasaran' => 'pralansia',
                    ],
                    [
                        'id_users' => $userId,
                        'nik' => $pralansia->nik_sasaran,
                        'nama' => $pralansia->nama_sasaran,
                        'tanggal_lahir' => $pralansia->tanggal_lahir,
                        'jenis_kelamin' => $pralansia->jenis_kelamin,
                        'umur' => $pralansia->umur_sasaran,
                        'pendidikan_terakhir' => $pendidikanValue,
                    ]
                );
                $updatedCount++;
            }

            // Update Lansia
            $lansiaList = SasaranLansia::where('id_posyandu', $posyanduId)->get();
            foreach ($lansiaList as $lansia) {
                $lansia->update(['pendidikan' => $pendidikanValue]);
                
                // Simpan ke tabel pendidikan
                Pendidikan::updateOrCreate(
                    [
                        'id_posyandu' => $posyanduId,
                        'id_sasaran' => $lansia->id_sasaran_lansia,
                        'kategori_sasaran' => 'lansia',
                    ],
                    [
                        'id_users' => $userId,
                        'nik' => $lansia->nik_sasaran,
                        'nama' => $lansia->nama_sasaran,
                        'tanggal_lahir' => $lansia->tanggal_lahir,
                        'jenis_kelamin' => $lansia->jenis_kelamin,
                        'umur' => $lansia->umur_sasaran,
                        'pendidikan_terakhir' => $pendidikanValue,
                    ]
                );
                $updatedCount++;
            }

            // Update Ibu Hamil
            $ibuhamilList = SasaranIbuhamil::where('id_posyandu', $posyanduId)->get();
            foreach ($ibuhamilList as $ibuhamil) {
                $ibuhamil->update(['pendidikan' => $pendidikanValue]);
                
                // Simpan ke tabel pendidikan
                Pendidikan::updateOrCreate(
                    [
                        'id_posyandu' => $posyanduId,
                        'id_sasaran' => $ibuhamil->id_sasaran_ibuhamil,
                        'kategori_sasaran' => 'ibuhamil',
                    ],
                    [
                        'id_users' => $userId,
                        'nik' => $ibuhamil->nik_sasaran,
                        'nama' => $ibuhamil->nama_sasaran,
                        'tanggal_lahir' => $ibuhamil->tanggal_lahir,
                        'jenis_kelamin' => $ibuhamil->jenis_kelamin,
                        'umur' => $ibuhamil->umur_sasaran,
                        'pendidikan_terakhir' => $pendidikanValue,
                    ]
                );
                $updatedCount++;
            }

            $this->closePendidikanModal();
            $this->refreshPosyandu();

            session()->flash('message', "Pendidikan berhasil diupdate ke {$updatedCount} sasaran (Remaja, Dewasa, Pralansia, Lansia, Ibu Hamil) dan tersimpan di menu Pendidikan.");
            session()->flash('messageType', 'success');
        } catch (\Exception $e) {
            session()->flash('message', 'Gagal mengupdate pendidikan: ' . $e->getMessage());
            session()->flash('messageType', 'error');
        }
    }
}
