<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Posyandu;
use App\Models\GambarPosyandu;
use App\Models\Orangtua;
use App\Models\Pendidikan;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PosyanduInfo extends Component
{
    use WithFileUploads;

    public $posyandu;
    public $posyanduId;
    
    // Modal upload SK (untuk kompatibilitas dengan view)
    public $skFile;
    public $showUploadModal = false;

    // Multi gambar upload
    public $gambarFiles = [];
    public $gambarCaption = '';
    public $showGambarModal = false;
    
    // Modal konfirmasi (untuk kompatibilitas dengan confirm-modal)
    public $showConfirmModal = false;
    public $confirmMessage = '';
    public $confirmAction = '';

    #[Layout('layouts.superadmindashboard')]

    public function mount($id)
    {
        try {
            $decryptedId = decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID tidak valid');
        }

        $this->posyanduId = $decryptedId;
        $this->loadPosyandu();
    }

    /**
     * Load data posyandu dengan relasi
     */
    private function loadPosyandu()
    {
        $relations = [
            'kader.user',
            'sasaran_bayibalita.user',
            'sasaran_remaja.user',
            'sasaran_dewasa.user',
            'sasaran_pralansia.user',
            'sasaran_lansia.user',
            'sasaran_ibuhamil',
            'gambarPosyandu',
        ];

        $posyandu = Posyandu::with($relations)->find($this->posyanduId);

        if (!$posyandu) {
            abort(404, 'Posyandu tidak ditemukan');
        }

        $this->posyandu = $posyandu;
    }


    /**
     * Get count of orangtua by age range (for statistics)
     */
    public function getOrangtuaCountByUmur($minAge, $maxAge = null)
    {
        $query = Orangtua::query();

        // Filter by age
        if ($maxAge !== null) {
            $query->byAgeRange($minAge, $maxAge);
        } else {
            $query->byMinAge($minAge);
        }

        return $query->count();
    }

    /**
     * Buka modal konfirmasi (untuk kompatibilitas dengan view)
     */
    public function openConfirmModal($action, $message)
    {
        $this->confirmAction = $action;
        $this->confirmMessage = $message;
        $this->showConfirmModal = true;
    }

    /**
     * Tutup modal konfirmasi (untuk kompatibilitas dengan confirm-modal)
     */
    public function closeConfirmModal()
    {
        $this->showConfirmModal = false;
        $this->confirmMessage = '';
        $this->confirmAction = '';
    }

    /**
     * Upload SK Posyandu
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
            $this->loadPosyandu();

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
     * Delete SK Posyandu
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

            // Refresh data
            $this->loadPosyandu();

            // Close modal
            $this->showConfirmModal = false;

            session()->flash('message', 'File SK berhasil dihapus.');
            session()->flash('messageType', 'success');
        } catch (\Exception $e) {
            session()->flash('message', 'Gagal menghapus file SK: ' . $e->getMessage());
            session()->flash('messageType', 'error');
        }
    }

    /**
     * Eksekusi action setelah konfirmasi (untuk kompatibilitas dengan confirm-modal)
     */
    public function executeConfirmAction()
    {
        if ($this->confirmAction === 'deleteSk') {
            $this->deleteSk();
        } elseif ($this->confirmAction === 'deleteGambar') {
            $this->deleteGambar();
        } else {
            $this->closeConfirmModal();
        }
    }

    /**
     * Upload Gambar Posyandu (multiple) - tampil di halaman detail publik sebagai galeri
     */
    public function uploadGambar()
    {
        $this->validate([
            'gambarFiles' => 'required|array|min:1',
            'gambarFiles.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'gambarCaption' => 'nullable|string|max:255',
        ], [
            'gambarFiles.required' => 'Pilih minimal satu gambar untuk diupload.',
            'gambarFiles.min' => 'Pilih minimal satu gambar untuk diupload.',
            'gambarFiles.*.image' => 'File harus berupa gambar.',
            'gambarFiles.*.mimes' => 'Format: JPEG, PNG, JPG.',
            'gambarFiles.*.max' => 'Maksimal 2MB per file.',
        ]);

        try {
            $dir = public_path('uploads/gambar_posyandu');
            if (!File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true);
            }

            $allowedImageMimes = ['image/jpeg' => 'jpg', 'image/pjpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
            $maxUrutan = GambarPosyandu::where('id_posyandu', $this->posyanduId)->max('urutan') ?? 0;

            foreach ($this->gambarFiles as $file) {
                $extension = safe_upload_extension($file, $allowedImageMimes) ?? 'jpg';
                $safeName = Str::random(12) . '_' . time() . '_' . Str::random(4) . '.' . $extension;
                File::copy($file->getRealPath(), $dir . DIRECTORY_SEPARATOR . $safeName);

                $maxUrutan++;
                GambarPosyandu::create([
                    'id_posyandu' => $this->posyanduId,
                    'path' => 'gambar_posyandu/' . $safeName,
                    'caption' => $this->gambarCaption ?: null,
                    'urutan' => $maxUrutan,
                ]);
            }

            $this->loadPosyandu();
            $this->gambarFiles = [];
            $this->gambarCaption = '';
            $this->showGambarModal = false;

            $count = count($this->gambarFiles) ?: 'Gambar';
            session()->flash('message', 'Gambar berhasil diupload ke galeri posyandu.');
            session()->flash('messageType', 'success');
        } catch (\Exception $e) {
            session()->flash('message', 'Gagal mengupload gambar: ' . $e->getMessage());
            session()->flash('messageType', 'error');
        }
    }

    /**
     * Hapus satu gambar dari galeri
     */
    public function deleteGambarPosyandu($id)
    {
        try {
            $gambar = GambarPosyandu::where('id', $id)
                ->where('id_posyandu', $this->posyanduId)
                ->first();

            if ($gambar) {
                $full = uploads_base_path('uploads/' . $gambar->path);
                if (File::exists($full)) {
                    File::delete($full);
                }
                $gambar->delete();
            }

            $this->loadPosyandu();
            session()->flash('message', 'Gambar berhasil dihapus.');
            session()->flash('messageType', 'success');
        } catch (\Exception $e) {
            session()->flash('message', 'Gagal menghapus gambar: ' . $e->getMessage());
            session()->flash('messageType', 'error');
        }
    }

    /**
     * Hapus semua gambar posyandu (legacy method untuk backward compatibility)
     */
    public function deleteGambar()
    {
        try {
            $gambarList = GambarPosyandu::where('id_posyandu', $this->posyanduId)->get();
            foreach ($gambarList as $gambar) {
                $full = uploads_base_path('uploads/' . $gambar->path);
                if (File::exists($full)) {
                    File::delete($full);
                }
                $gambar->delete();
            }

            $this->loadPosyandu();
            $this->showConfirmModal = false;
            session()->flash('message', 'Semua gambar berhasil dihapus.');
            session()->flash('messageType', 'success');
        } catch (\Exception $e) {
            session()->flash('message', 'Gagal menghapus gambar: ' . $e->getMessage());
            session()->flash('messageType', 'error');
        }
    }

    /**
     * Get pendidikan data untuk chart
     */
    public function getPendidikanChartData()
    {
        $pendidikanData = Pendidikan::where('id_posyandu', $this->posyanduId)
            ->selectRaw('pendidikan_terakhir, COUNT(*) as jumlah')
            ->groupBy('pendidikan_terakhir')
            ->orderByRaw('
                CASE 
                    WHEN pendidikan_terakhir = "Tidak/Belum Sekolah" THEN 1
                    WHEN pendidikan_terakhir = "PAUD" THEN 2
                    WHEN pendidikan_terakhir = "TK" THEN 3
                    WHEN pendidikan_terakhir = "Tidak Tamat SD/Sederajat" THEN 4
                    WHEN pendidikan_terakhir = "Tamat SD/Sederajat" THEN 5
                    WHEN pendidikan_terakhir = "SLTP/Sederajat" THEN 6
                    WHEN pendidikan_terakhir = "SLTA/Sederajat" THEN 7
                    WHEN pendidikan_terakhir = "Diploma I/II" THEN 8
                    WHEN pendidikan_terakhir = "Akademi/Diploma III/Sarjana Muda" THEN 9
                    WHEN pendidikan_terakhir = "Diploma IV/Strata I" THEN 10
                    WHEN pendidikan_terakhir = "Strata II" THEN 11
                    WHEN pendidikan_terakhir = "Strata III" THEN 12
                    ELSE 13
                END
            ')
            ->get();

        return [
            'labels' => $pendidikanData->pluck('pendidikan_terakhir')->toArray(),
            'data' => $pendidikanData->pluck('jumlah')->toArray(),
        ];
    }

    public function render()
    {
        $daftarPosyandu = Posyandu::select('id_posyandu', 'nama_posyandu')->get();
        $pendidikanChartData = $this->getPendidikanChartData();

        return view('livewire.super-admin.posyandu-info', [
            'title' => 'Info Posyandu - ' . $this->posyandu->nama_posyandu,
            'daftarPosyandu' => $daftarPosyandu,
            'pendidikanChartData' => $pendidikanChartData,
        ]);
    }
}

