<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Perpustakaan as PerpustakaanModel;
use App\Models\Posyandu;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class PerpustakaanSemua extends Component
{
    use WithFileUploads, WithPagination;

    #[Layout('layouts.superadmindashboard')]

    public $viewingBook = null;
    public $showFlipbookModal = false;
    public $showAddModal = false;

    public $posyanduId = '';
    public $judul = '';
    public $deskripsi = '';
    public $penulis = '';
    public $kategori = '';
    public $coverImage;
    public $halamanFiles = [];
    public $pdfFile;
    public $uploadType = 'images';

    public $kategoriOptions = [
        'kesehatan' => 'Kesehatan',
        'gizi' => 'Gizi & Nutrisi',
        'parenting' => 'Parenting',
        'ibu_hamil' => 'Ibu Hamil',
        'bayi_balita' => 'Bayi & Balita',
        'lansia' => 'Lansia',
        'umum' => 'Umum',
    ];

    protected function rules()
    {
        $rules = [
            'posyanduId' => 'required|exists:posyandu,id_posyandu',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:1000',
            'penulis' => 'nullable|string|max:255',
            'kategori' => 'nullable|string|max:100',
            'coverImage' => 'nullable|image|max:2048',
            'uploadType' => 'required|in:images,pdf',
        ];
        if ($this->uploadType === 'pdf') {
            $rules['pdfFile'] = 'required|mimes:pdf|max:20480';
        } else {
            $rules['halamanFiles'] = 'required';
            $rules['halamanFiles.*'] = 'image|max:5120';
        }
        return $rules;
    }

    protected function messages()
    {
        return [
            'posyanduId.required' => 'Pilih posyandu untuk menambahkan buku.',
            'judul.required' => 'Judul buku wajib diisi.',
            'halamanFiles.required' => 'Upload minimal satu halaman.',
            'pdfFile.required' => 'Upload file PDF.',
        ];
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

    private function resetForm()
    {
        $this->reset(['posyanduId', 'judul', 'deskripsi', 'penulis', 'kategori', 'coverImage', 'halamanFiles', 'pdfFile', 'uploadType']);
        $this->uploadType = 'images';
        $this->resetErrorBag();
    }

    public function saveBook()
    {
        $this->validate();
        $posyanduId = $this->posyanduId;

        $dir = uploads_base_path('uploads/perpustakaan/' . $posyanduId);
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $allowedImageMimes = ['image/jpeg' => 'jpg', 'image/pjpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];

        $coverPath = null;
        if ($this->coverImage) {
            $ext = safe_upload_extension($this->coverImage, $allowedImageMimes) ?? 'jpg';
            $coverName = 'cover_' . Str::random(8) . '.' . $ext;
            $destFile = $dir . DIRECTORY_SEPARATOR . $coverName;
            if (File::copy($this->coverImage->getRealPath(), $destFile)) {
                $coverPath = 'perpustakaan/' . $posyanduId . '/' . $coverName;
            }
        }

        $halamanPaths = [];
        $pdfPath = null;
        $jumlahHalaman = 0;

        if ($this->uploadType === 'pdf' && $this->pdfFile) {
            $pdfName = 'book_' . Str::random(8) . '.pdf';
            $destFile = $dir . DIRECTORY_SEPARATOR . $pdfName;
            if (File::copy($this->pdfFile->getRealPath(), $destFile)) {
                $pdfPath = 'perpustakaan/' . $posyanduId . '/' . $pdfName;
            }
        } else {
            $pageNumber = 1;
            foreach ($this->halamanFiles as $file) {
                $ext = safe_upload_extension($file, $allowedImageMimes) ?? 'jpg';
                $pageName = 'page_' . str_pad($pageNumber, 3, '0', STR_PAD_LEFT) . '_' . Str::random(6) . '.' . $ext;
                $destFile = $dir . DIRECTORY_SEPARATOR . $pageName;
                if (File::copy($file->getRealPath(), $destFile)) {
                    $halamanPaths[] = 'perpustakaan/' . $posyanduId . '/' . $pageName;
                }
                $pageNumber++;
            }
            $jumlahHalaman = count($halamanPaths);
            if (!$coverPath && count($halamanPaths) > 0) {
                $coverPath = $halamanPaths[0];
            }
        }

        PerpustakaanModel::create([
            'id_posyandu' => $posyanduId,
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

    public function openFlipbook($id)
    {
        $this->viewingBook = PerpustakaanModel::with('posyandu')->findOrFail($id);
        $this->showFlipbookModal = true;
    }

    public function closeFlipbook()
    {
        $this->showFlipbookModal = false;
        $this->viewingBook = null;
    }

    public function render()
    {
        $items = PerpustakaanModel::with('posyandu:id_posyandu,nama_posyandu')
            ->latest()
            ->paginate(24);

        $daftarPosyandu = Posyandu::orderBy('nama_posyandu')->get(['id_posyandu', 'nama_posyandu']);

        return view('livewire.super-admin.perpustakaan', [
            'items' => $items,
            'daftarPosyandu' => $daftarPosyandu,
            'title' => 'Perpustakaan',
        ]);
    }
}
