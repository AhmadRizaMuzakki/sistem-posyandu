<?php

namespace App\Livewire\Posyandu;

use App\Livewire\Posyandu\Traits\PosyanduHelper;
use App\Livewire\Posyandu\Traits\DashboardHelper;
use App\Livewire\Traits\NotificationModal;
use App\Models\IbuMenyusui;
use App\Models\Jadwal;
use App\Models\Kader;
use App\Models\KunjunganIbuMenyusui;
use App\Models\Imunisasi;
use App\Models\Pendidikan;
use App\Models\SasaranBayibalita;
use App\Models\SasaranRemaja;
use App\Models\SasaranDewasa;
use App\Models\SasaranPralansia;
use App\Models\SasaranLansia;
use App\Models\SasaranIbuhamil;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosyanduDashboard extends Component
{
    use PosyanduHelper, DashboardHelper, WithFileUploads, NotificationModal;

    public $skFile;
    public $showUploadModal = false;
    
    // Pendidikan properties
    public $showPendidikanModal = false;
    public $pendidikan_terakhir = '';
    
    // Modal konfirmasi
    public $showConfirmModal = false;
    public $confirmMessage = '';
    public $confirmAction = '';

    #[Layout('layouts.posyandudashboard')]

    public function mount()
    {
        $this->initializePosyandu();
    }

    public function render()
    {
        // Optimasi: Gunakan cache untuk data yang jarang berubah
        $cacheKey = "posyandu_dashboard_{$this->posyanduId}";
        $cachedData = cache()->remember($cacheKey, 60, function () {
            $totalKader = Kader::where('id_posyandu', $this->posyanduId)->count();
            $sasaranByCategory = $this->getSasaranCountsByCategory($this->posyanduId);
            $totalSasaran = array_sum($sasaranByCategory);
            $pendidikanData = $this->getPendidikanData($this->posyanduId);

            // Data untuk dropdown filter imunisasi - optimasi dengan single query
            $imunisasiData = Imunisasi::where('id_posyandu', $this->posyanduId)
                ->select('kategori_sasaran', 'jenis_imunisasi', 'id_sasaran', 'kategori_sasaran as kategori')
                ->get();

            $kategoriSasaranList = $imunisasiData->pluck('kategori_sasaran')->unique()->sort()->values()->toArray();
            $jenisVaksinList = $imunisasiData->pluck('jenis_imunisasi')->unique()->sort()->values()->toArray();

            // Optimasi: Ambil semua sasaran sekaligus untuk menghindari N+1
            $namaSasaranList = $this->getNamaSasaranFromImunisasi($imunisasiData);

            // Data untuk dropdown filter pendidikan
            $kategoriPendidikanList = Pendidikan::where('id_posyandu', $this->posyanduId)
                ->distinct()
                ->orderBy('pendidikan_terakhir')
                ->pluck('pendidikan_terakhir')
                ->toArray();

            // Ringkasan absen petugas (bulan ini)
            $bulanIni = (int) date('n');
            $tahunIni = (int) date('Y');
            $jadwalBulanIni = Jadwal::where('id_posyandu', $this->posyanduId)
                ->whereMonth('tanggal', $bulanIni)
                ->whereYear('tanggal', $tahunIni)
                ->get();
            $absenPetugas = [
                'total' => $jadwalBulanIni->count(),
                'hadir' => $jadwalBulanIni->where('presensi', 'hadir')->count(),
                'tidak_hadir' => $jadwalBulanIni->where('presensi', 'tidak_hadir')->count(),
                'belum_hadir' => $jadwalBulanIni->where('presensi', 'belum_hadir')->count(),
            ];

            // Ringkasan absen bayi/balita (bulan ini - kunjungan)
            $totalBayi = IbuMenyusui::where('id_posyandu', $this->posyanduId)->count();
            $bayiHadirBulanIni = KunjunganIbuMenyusui::whereHas('ibuMenyusui', fn ($q) => $q->where('id_posyandu', $this->posyanduId))
                ->where('bulan', $bulanIni)
                ->where('tahun', $tahunIni)
                ->where('status', 'success')
                ->count();
            $absenBayi = [
                'total' => $totalBayi,
                'hadir' => $bayiHadirBulanIni,
                'tidak_hadir' => $totalBayi - $bayiHadirBulanIni,
            ];

            return compact('totalKader', 'totalSasaran', 'sasaranByCategory', 'pendidikanData',
                'kategoriSasaranList', 'jenisVaksinList', 'namaSasaranList', 'kategoriPendidikanList',
                'absenPetugas', 'absenBayi');
        });

        // Mapping label kategori
        $kategoriLabels = [
            'bayibalita' => 'Bayi dan Balita',
            'remaja' => 'Remaja',
            'dewasa' => 'Dewasa',
            'pralansia' => 'Pralansia',
            'lansia' => 'Lansia',
            'ibuhamil' => 'Ibu Hamil',
        ];

        return view('livewire.posyandu.admin-posyandu', array_merge($cachedData, [
            'posyandu' => $this->posyandu,
            'kategoriLabels' => $kategoriLabels,
        ]));
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
            $dir = public_path('uploads/sk_posyandu');
            if (!File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            if ($this->posyandu->sk_posyandu) {
                $rel = ltrim(str_replace('/storage/', '', $this->posyandu->sk_posyandu), '/');
                $oldFull = uploads_base_path('uploads/' . $rel);
                if (File::exists($oldFull)) {
                    File::delete($oldFull);
                }
            }
            $allowedDocMimes = ['application/pdf' => 'pdf', 'application/msword' => 'doc', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx'];
            $extension = safe_upload_extension($this->skFile, $allowedDocMimes);
            if (!$extension) {
                throw new \InvalidArgumentException('Format file SK tidak valid.');
            }
            $safeName = Str::random(12) . '_' . time() . '.' . $extension;
            File::copy($this->skFile->getRealPath(), $dir . DIRECTORY_SEPARATOR . $safeName);

            $this->posyandu->update([
                'sk_posyandu' => 'sk_posyandu/' . $safeName
            ]);

            // Refresh data
            $this->refreshPosyandu();

            // Clear cache
            cache()->forget("posyandu_dashboard_{$this->posyanduId}");

            // Reset
            $this->skFile = null;
            $this->showUploadModal = false;

            $this->showSuccessNotification('File SK berhasil diupload.');
        } catch (\Exception $e) {
            $this->showErrorNotification('Gagal mengupload file SK: ' . $e->getMessage());
        }
    }

    /**
     * Buka modal konfirmasi
     */
    public function openConfirmModal($action, $message)
    {
        $this->confirmAction = $action;
        $this->confirmMessage = $message;
        $this->showConfirmModal = true;
    }

    /**
     * Tutup modal konfirmasi
     */
    public function closeConfirmModal()
    {
        $this->showConfirmModal = false;
        $this->confirmMessage = '';
        $this->confirmAction = '';
    }

    /**
     * Eksekusi action setelah konfirmasi
     */
    public function executeConfirmAction()
    {
        if ($this->confirmAction === 'deleteSk') {
            $this->deleteSk();
        }
        $this->closeConfirmModal();
    }

    /**
     * Hapus file SK
     */
    public function deleteSk()
    {
        try {
            if ($this->posyandu->sk_posyandu) {
                $rel = ltrim(str_replace('/storage/', '', $this->posyandu->sk_posyandu), '/');
                $full = uploads_base_path('uploads/' . $rel);
                if (File::exists($full)) {
                    File::delete($full);
                }
            }

            $this->posyandu->update([
                'sk_posyandu' => null
            ]);

            $this->refreshPosyandu();

            // Clear cache
            cache()->forget("posyandu_dashboard_{$this->posyanduId}");

            $this->showSuccessNotification('File SK berhasil dihapus.');
        } catch (\Exception $e) {
            $this->showErrorNotification('Gagal menghapus file SK: ' . $e->getMessage());
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

            // Optimasi: Gunakan bulk update dan bulk insert untuk performa maksimal
            DB::transaction(function () use ($posyanduId, $pendidikanValue, $userId, &$updatedCount) {
                // Bulk update untuk semua kategori sekaligus
                $updatedCount += SasaranRemaja::where('id_posyandu', $posyanduId)
                    ->update(['pendidikan' => $pendidikanValue]);
                
                $updatedCount += SasaranDewasa::where('id_posyandu', $posyanduId)
                    ->update(['pendidikan' => $pendidikanValue]);
                
                $updatedCount += SasaranPralansia::where('id_posyandu', $posyanduId)
                    ->update(['pendidikan' => $pendidikanValue]);
                
                $updatedCount += SasaranLansia::where('id_posyandu', $posyanduId)
                    ->update(['pendidikan' => $pendidikanValue]);
                
                $updatedCount += SasaranIbuhamil::where('id_posyandu', $posyanduId)
                    ->update(['pendidikan' => $pendidikanValue]);

                // Bulk insert/update untuk tabel pendidikan
                $this->bulkUpdatePendidikanTable($posyanduId, $pendidikanValue, $userId);
            });

            // Clear cache setelah update
            cache()->forget("posyandu_dashboard_{$posyanduId}");

            $this->closePendidikanModal();
            $this->refreshPosyandu();

            $this->showSuccessNotification("Pendidikan berhasil diupdate ke {$updatedCount} sasaran (Remaja, Dewasa, Pralansia, Lansia, Ibu Hamil) dan tersimpan di menu Pendidikan.");
        } catch (\Exception $e) {
            $this->showErrorNotification('Gagal mengupdate pendidikan: ' . $e->getMessage());
        }
    }

    /**
     * Bulk update tabel pendidikan untuk performa optimal
     */
    private function bulkUpdatePendidikanTable($posyanduId, $pendidikanValue, $userId)
    {
        $pendidikanData = [];

        // Ambil semua sasaran sekaligus untuk batch insert
        $categories = [
            'remaja' => [SasaranRemaja::class, 'id_sasaran_remaja'],
            'dewasa' => [SasaranDewasa::class, 'id_sasaran_dewasa'],
            'pralansia' => [SasaranPralansia::class, 'id_sasaran_pralansia'],
            'lansia' => [SasaranLansia::class, 'id_sasaran_lansia'],
            'ibuhamil' => [SasaranIbuhamil::class, 'id_sasaran_ibuhamil'],
        ];

        foreach ($categories as $kategori => [$modelClass, $primaryKey]) {
            $sasarans = $modelClass::where('id_posyandu', $posyanduId)
                ->select($primaryKey, 'nik_sasaran', 'nama_sasaran', 'tanggal_lahir', 'jenis_kelamin', 'umur_sasaran')
                ->get();

            foreach ($sasarans as $sasaran) {
                $pendidikanData[] = [
                    'id_posyandu' => $posyanduId,
                    'id_users' => $userId,
                    'id_sasaran' => $sasaran->$primaryKey,
                    'kategori_sasaran' => $kategori,
                    'nik' => $sasaran->nik_sasaran,
                    'nama' => $sasaran->nama_sasaran,
                    'tanggal_lahir' => $sasaran->tanggal_lahir,
                    'jenis_kelamin' => $sasaran->jenis_kelamin,
                    'umur' => $sasaran->umur_sasaran,
                    'pendidikan_terakhir' => $pendidikanValue,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Bulk insert dengan ON DUPLICATE KEY UPDATE (MySQL) atau upsert (Laravel)
        if (!empty($pendidikanData)) {
            Pendidikan::upsert(
                $pendidikanData,
                ['id_posyandu', 'id_sasaran', 'kategori_sasaran'],
                ['pendidikan_terakhir', 'updated_at']
            );
        }
    }

    /**
     * Optimasi: Ambil nama sasaran dari imunisasi tanpa N+1 query
     */
    private function getNamaSasaranFromImunisasi($imunisasiData)
    {
        // Group by kategori untuk batch query
        $grouped = $imunisasiData->groupBy('kategori_sasaran');
        $namaSasaranList = collect();

        $modelMap = [
            'bayibalita' => [SasaranBayibalita::class, 'id_sasaran_bayibalita'],
            'remaja' => [SasaranRemaja::class, 'id_sasaran_remaja'],
            'dewasa' => [SasaranDewasa::class, 'id_sasaran_dewasa'],
            'pralansia' => [SasaranPralansia::class, 'id_sasaran_pralansia'],
            'lansia' => [SasaranLansia::class, 'id_sasaran_lansia'],
        ];

        foreach ($grouped as $kategori => $items) {
            if (!isset($modelMap[$kategori])) continue;

            [$modelClass, $primaryKey] = $modelMap[$kategori];
            $ids = $items->pluck('id_sasaran')->unique()->filter();

            if ($ids->isEmpty()) continue;

            // Batch query untuk menghindari N+1
            $sasarans = $modelClass::whereIn($primaryKey, $ids)
                ->where('id_posyandu', $this->posyanduId)
                ->pluck('nama_sasaran')
                ->filter();

            $namaSasaranList = $namaSasaranList->merge($sasarans);
        }

        return $namaSasaranList->unique()->sort()->values()->toArray();
    }
}
