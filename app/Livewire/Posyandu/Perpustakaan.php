<?php

namespace App\Livewire\Posyandu;

use App\Models\Perpustakaan as PerpustakaanModel;
use App\Models\Posyandu;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Perpustakaan extends Component
{
    use WithFileUploads, WithPagination;

    public $posyandu;
    public $posyanduId;

    // Form fields
    public $judul = '';
    public $deskripsi = '';
    public $penulis = '';
    public $kategori = '';
    public $coverImage;
    public $halamanFiles = [];
    public $pdfFile;
    public $uploadType = 'images'; // 'images' or 'pdf'

    // Modal states
    public $showAddModal = false;
    public $showEditModal = false;
    public $showFlipbookModal = false;
    public $editingId = null;
    public $viewingBook = null;

    // Kategori options
    public $kategoriOptions = [
        'kesehatan' => 'Kesehatan',
        'gizi' => 'Gizi & Nutrisi',
        'parenting' => 'Parenting',
        'ibu_hamil' => 'Ibu Hamil',
        'bayi_balita' => 'Bayi & Balita',
        'lansia' => 'Lansia',
        'umum' => 'Umum',
    ];

    #[Layout('layouts.posyandudashboard')]

    protected function rules()
    {
        $rules = [
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:1000',
            'penulis' => 'nullable|string|max:255',
            'kategori' => 'nullable|string|max:100',
            'coverImage' => 'nullable|image|max:2048',
            'uploadType' => 'required|in:images,pdf',
        ];

        if ($this->uploadType === 'pdf') {
            $rules['pdfFile'] = 'required|mimes:pdf|max:20480'; // Max 20MB for PDF
        } else {
            $rules['halamanFiles'] = 'required';
            $rules['halamanFiles.*'] = 'image|max:5120';
        }

        return $rules;
    }

    protected function messages()
    {
        return [
            'judul.required' => 'Judul buku wajib diisi.',
            'judul.max' => 'Judul maksimal 255 karakter.',
            'coverImage.image' => 'Cover harus berupa gambar.',
            'coverImage.max' => 'Cover maksimal 2 MB.',
            'halamanFiles.required' => 'Upload minimal satu halaman.',
            'halamanFiles.*.image' => 'Setiap halaman harus berupa gambar (JPG, PNG, GIF, WebP).',
            'halamanFiles.*.max' => 'Setiap halaman maksimal 5 MB.',
            'pdfFile.required' => 'Upload file PDF.',
            'pdfFile.mimes' => 'File harus berupa PDF.',
            'pdfFile.max' => 'Ukuran PDF maksimal 20 MB.',
        ];
    }

    public function mount()
    {
        $user = Auth::user();
        $kader = $user->kader;
        
        if (!$kader) {
            abort(403, 'Akses ditolak. Anda bukan kader.');
        }

        $this->posyanduId = $kader->id_posyandu;
        $this->loadPosyandu();
    }

    private function loadPosyandu()
    {
        $posyandu = Posyandu::find($this->posyanduId);
        if (!$posyandu) {
            abort(404, 'Posyandu tidak ditemukan');
        }
        $this->posyandu = $posyandu;
    }

    public function openAddModal()
    {
        $this->resetForm();
        $this->showAddModal = true;
    }

    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->resetForm();
    }

    public function openEditModal($id)
    {
        $book = PerpustakaanModel::where('id_posyandu', $this->posyanduId)->findOrFail($id);
        $this->editingId = $book->id;
        $this->judul = $book->judul;
        $this->deskripsi = $book->deskripsi;
        $this->penulis = $book->penulis;
        $this->kategori = $book->kategori;
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->resetForm();
    }

    public function openFlipbook($id)
    {
        $this->viewingBook = PerpustakaanModel::where('id_posyandu', $this->posyanduId)->findOrFail($id);
        $this->showFlipbookModal = true;
    }

    public function closeFlipbook()
    {
        $this->showFlipbookModal = false;
        $this->viewingBook = null;
    }

    private function resetForm()
    {
        $this->reset(['judul', 'deskripsi', 'penulis', 'kategori', 'coverImage', 'halamanFiles', 'pdfFile', 'uploadType', 'editingId']);
        $this->uploadType = 'images';
        $this->resetErrorBag();
    }

    public function saveBook()
    {
        $this->validate();

        $dir = uploads_base_path('uploads/perpustakaan/' . $this->posyanduId);
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $allowedImageMimes = ['image/jpeg' => 'jpg', 'image/pjpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];

        // Save cover image
        $coverPath = null;
        if ($this->coverImage) {
            $ext = safe_upload_extension($this->coverImage, $allowedImageMimes) ?? 'jpg';
            $coverName = 'cover_' . Str::random(8) . '.' . $ext;
            $destFile = $dir . DIRECTORY_SEPARATOR . $coverName;
            if (File::copy($this->coverImage->getRealPath(), $destFile)) {
                $coverPath = 'perpustakaan/' . $this->posyanduId . '/' . $coverName;
            }
        }

        $halamanPaths = [];
        $pdfPath = null;
        $jumlahHalaman = 0;

        if ($this->uploadType === 'pdf' && $this->pdfFile) {
            // Save PDF file
            $pdfName = 'book_' . Str::random(8) . '.pdf';
            $destFile = $dir . DIRECTORY_SEPARATOR . $pdfName;
            if (File::copy($this->pdfFile->getRealPath(), $destFile)) {
                $pdfPath = 'perpustakaan/' . $this->posyanduId . '/' . $pdfName;
                // We'll set page count to 0 for PDF (will be determined by PDF.js on client)
                $jumlahHalaman = 0;
            }
        } else {
            // Save halaman images
            $pageNumber = 1;
            foreach ($this->halamanFiles as $file) {
                $ext = safe_upload_extension($file, $allowedImageMimes) ?? 'jpg';
                $pageName = 'page_' . str_pad($pageNumber, 3, '0', STR_PAD_LEFT) . '_' . Str::random(6) . '.' . $ext;
                $destFile = $dir . DIRECTORY_SEPARATOR . $pageName;
                if (File::copy($file->getRealPath(), $destFile)) {
                    $halamanPaths[] = 'perpustakaan/' . $this->posyanduId . '/' . $pageName;
                }
                $pageNumber++;
            }
            $jumlahHalaman = count($halamanPaths);

            // Use first page as cover if no cover uploaded
            if (!$coverPath && count($halamanPaths) > 0) {
                $coverPath = $halamanPaths[0];
            }
        }

        PerpustakaanModel::create([
            'id_posyandu' => $this->posyanduId,
            'judul' => $this->judul,
            'deskripsi' => $this->deskripsi,
            'penulis' => $this->penulis,
            'kategori' => $this->kategori,
            'cover_image' => $coverPath,
            'file_path' => $pdfPath,
            'halaman_images' => count($halamanPaths) > 0 ? $halamanPaths : null,
            'jumlah_halaman' => $jumlahHalaman,
            'is_active' => true,
        ]);

        $this->closeAddModal();
        session()->flash('message', 'Buku berhasil ditambahkan ke perpustakaan.');
    }

    public function updateBook()
    {
        $this->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:1000',
            'penulis' => 'nullable|string|max:255',
            'kategori' => 'nullable|string|max:100',
        ]);

        $book = PerpustakaanModel::where('id_posyandu', $this->posyanduId)->findOrFail($this->editingId);
        $book->update([
            'judul' => $this->judul,
            'deskripsi' => $this->deskripsi,
            'penulis' => $this->penulis,
            'kategori' => $this->kategori,
        ]);

        $this->closeEditModal();
        session()->flash('message', 'Buku berhasil diperbarui.');
    }

    public function deleteBook($id)
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if ($id === false) {
            abort(404);
        }

        $book = PerpustakaanModel::where('id_posyandu', $this->posyanduId)->findOrFail($id);

        // Delete cover image
        if ($book->cover_image) {
            $fullPath = uploads_safe_full_path($book->cover_image);
            if ($fullPath && File::exists($fullPath)) {
                File::delete($fullPath);
            }
        }

        // Delete PDF file
        if ($book->file_path) {
            $fullPath = uploads_safe_full_path($book->file_path);
            if ($fullPath && File::exists($fullPath)) {
                File::delete($fullPath);
            }
        }

        // Delete halaman images
        if ($book->halaman_images && is_array($book->halaman_images)) {
            foreach ($book->halaman_images as $path) {
                $fullPath = uploads_safe_full_path($path);
                if ($fullPath && File::exists($fullPath)) {
                    File::delete($fullPath);
                }
            }
        }

        $book->delete();
        session()->flash('message', 'Buku berhasil dihapus dari perpustakaan.');
    }

    public function toggleActive($id)
    {
        $book = PerpustakaanModel::where('id_posyandu', $this->posyanduId)->findOrFail($id);
        $book->update(['is_active' => !$book->is_active]);
        session()->flash('message', $book->is_active ? 'Buku diaktifkan.' : 'Buku dinonaktifkan.');
    }

    public function render()
    {
        $items = PerpustakaanModel::where('id_posyandu', $this->posyanduId)
            ->latest()
            ->paginate(12);

        return view('livewire.posyandu.perpustakaan', [
            'items' => $items,
            'title' => 'Perpustakaan - ' . $this->posyandu->nama_posyandu,
        ]);
    }
}
