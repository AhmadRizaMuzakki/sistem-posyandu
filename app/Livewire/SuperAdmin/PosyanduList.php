<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Posyandu;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosyanduList extends Component
{
    use WithFileUploads;

    public $posyanduList = [];
    public $showModal = false;
    public $search = '';
    public $isEditMode = false;
    public $editingId = null;

    // Form properties
    public $nama_posyandu = '';
    public $alamat_posyandu = '';
    public $domisili_posyandu = '';
    public $link_maps = '';
    public $skFile = null;
    public $logoFile = null;
    public $currentSkPath = null;
    public $currentLogoPath = null;

    #[Layout('layouts.superadmindashboard')]

    public function mount()
    {
        $this->loadPosyandu();
    }

    /**
     * Load semua posyandu
     */
    public function loadPosyandu()
    {
        $query = Posyandu::with([
            'sasaran_bayibalita',
            'sasaran_remaja',
            'sasaran_dewasa',
            'sasaran_ibuhamil',
            'sasaran_pralansia',
            'sasaran_lansia',
        ]);

        if (!empty($this->search)) {
            $query->where('nama_posyandu', 'like', '%' . $this->search . '%')
                  ->orWhere('alamat_posyandu', 'like', '%' . $this->search . '%')
                  ->orWhere('domisili_posyandu', 'like', '%' . $this->search . '%');
        }

        $this->posyanduList = $query->orderBy('nama_posyandu')->get();
    }

    /**
     * Reset form
     */
    public function resetForm()
    {
        $this->nama_posyandu = '';
        $this->alamat_posyandu = '';
        $this->domisili_posyandu = '';
        $this->link_maps = '';
        $this->skFile = null;
        $this->logoFile = null;
        $this->currentSkPath = null;
        $this->currentLogoPath = null;
        $this->showModal = false;
        $this->isEditMode = false;
        $this->editingId = null;
    }

    /**
     * Buka modal tambah
     */
    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    /**
     * Tutup modal
     */
    public function closeModal()
    {
        $this->resetForm();
    }

    /**
     * Simpan posyandu baru atau update
     */
    public function store()
    {
        $this->validate([
            'nama_posyandu' => 'required|string|max:255',
            'alamat_posyandu' => 'nullable|string',
            'domisili_posyandu' => 'nullable|string|max:255',
            'link_maps' => 'nullable|string|max:2048',
            'skFile' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'logoFile' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'nama_posyandu.required' => 'Nama posyandu wajib diisi.',
            'nama_posyandu.max' => 'Nama posyandu maksimal 255 karakter.',
            'domisili_posyandu.max' => 'Domisili maksimal 255 karakter.',
            'skFile.file' => 'File SK harus berupa file.',
            'skFile.mimes' => 'File SK harus berformat PDF, DOC, atau DOCX.',
            'skFile.max' => 'Ukuran file SK maksimal 5MB.',
            'logoFile.image' => 'Logo harus berupa gambar.',
            'logoFile.mimes' => 'Logo harus berformat JPEG, PNG, atau JPG.',
            'logoFile.max' => 'Ukuran logo maksimal 2MB.',
        ]);

        try {
            $data = [
                'nama_posyandu' => $this->nama_posyandu,
                'alamat_posyandu' => $this->alamat_posyandu ?: null,
                'domisili_posyandu' => $this->domisili_posyandu ?: null,
                'link_maps' => $this->link_maps ?: null,
            ];

            // Upload SK ke public/uploads/sk_posyandu
            if ($this->skFile) {
                if ($this->currentSkPath) {
                    $rel = ltrim(str_replace('/storage/', '', $this->currentSkPath), '/');
                    $oldFull = uploads_base_path('uploads/' . $rel);
                    if (File::exists($oldFull)) {
                        File::delete($oldFull);
                    }
                }
                $dir = public_path('uploads/sk_posyandu');
                if (!File::isDirectory($dir)) {
                    File::makeDirectory($dir, 0755, true);
                }
                $allowedDocMimes = ['application/pdf' => 'pdf', 'application/msword' => 'doc', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx'];
                $extension = safe_upload_extension($this->skFile, $allowedDocMimes);
                if (!$extension) {
                    throw new \InvalidArgumentException('Format file SK tidak valid.');
                }
                $safeName = Str::random(12) . '_' . time() . '.' . $extension;
                File::copy($this->skFile->getRealPath(), $dir . DIRECTORY_SEPARATOR . $safeName);
                $data['sk_posyandu'] = 'sk_posyandu/' . $safeName;
            } elseif ($this->isEditMode && !$this->skFile) {
                $data['sk_posyandu'] = $this->currentSkPath;
            }

            // Upload logo ke public/uploads/logo_posyandu
            if ($this->logoFile) {
                if ($this->currentLogoPath) {
                    $rel = ltrim(str_replace('/storage/', '', $this->currentLogoPath), '/');
                    $oldFull = uploads_base_path('uploads/' . $rel);
                    if (File::exists($oldFull)) {
                        File::delete($oldFull);
                    }
                }
                $dir = public_path('uploads/logo_posyandu');
                if (!File::isDirectory($dir)) {
                    File::makeDirectory($dir, 0755, true);
                }
                $allowedImageMimes = ['image/jpeg' => 'jpg', 'image/pjpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
                $extension = safe_upload_extension($this->logoFile, $allowedImageMimes) ?? 'jpg';
                $safeName = Str::random(12) . '_' . time() . '.' . $extension;
                File::copy($this->logoFile->getRealPath(), $dir . DIRECTORY_SEPARATOR . $safeName);
                $data['logo_posyandu'] = 'logo_posyandu/' . $safeName;
            } elseif ($this->isEditMode && !$this->logoFile) {
                $data['logo_posyandu'] = $this->currentLogoPath;
            }

            DB::transaction(function () use ($data, &$message) {
                if ($this->isEditMode && $this->editingId) {
                    // Update existing
                    $posyandu = Posyandu::find($this->editingId);
                    if (!$posyandu) {
                        throw new \Exception('Posyandu tidak ditemukan.');
                    }
                    $posyandu->update($data);
                    $message = 'Posyandu berhasil diperbarui.';
                } else {
                    // Create new
                    Posyandu::create($data);
                    $message = 'Posyandu berhasil ditambahkan.';
                }
            });

            $this->loadPosyandu();
            $this->resetForm();

            session()->flash('message', $message);
            session()->flash('messageType', 'success');
        } catch (\Exception $e) {
            session()->flash('message', 'Gagal menyimpan posyandu: ' . $e->getMessage());
            session()->flash('messageType', 'error');
        }
    }

    /**
     * Edit posyandu
     */
    public function edit($id)
    {
        try {
            $posyandu = Posyandu::find($id);
            if (!$posyandu) {
                session()->flash('message', 'Posyandu tidak ditemukan.');
                session()->flash('messageType', 'error');
                return;
            }

            $this->editingId = $id;
            $this->isEditMode = true;
            $this->nama_posyandu = $posyandu->nama_posyandu;
            $this->alamat_posyandu = $posyandu->alamat_posyandu ?? '';
            $this->domisili_posyandu = $posyandu->domisili_posyandu ?? '';
            $this->link_maps = $posyandu->link_maps ?? '';
            $this->currentSkPath = $posyandu->sk_posyandu;
            $this->currentLogoPath = $posyandu->logo_posyandu;
            $this->skFile = null;
            $this->logoFile = null;
            $this->showModal = true;
        } catch (\Exception $e) {
            session()->flash('message', 'Gagal memuat data posyandu: ' . $e->getMessage());
            session()->flash('messageType', 'error');
        }
    }

    /**
     * Hapus posyandu
     */
    public function delete($id)
    {
        try {
            $posyandu = Posyandu::find($id);
            if (!$posyandu) {
                session()->flash('message', 'Posyandu tidak ditemukan.');
                session()->flash('messageType', 'error');
                return;
            }

            DB::transaction(function () use ($posyandu) {
                if ($posyandu->sk_posyandu) {
                    $rel = ltrim(str_replace('/storage/', '', $posyandu->sk_posyandu), '/');
                    $full = uploads_base_path('uploads/' . $rel);
                    if (File::exists($full)) {
                        File::delete($full);
                    }
                }
                if ($posyandu->logo_posyandu) {
                    $rel = ltrim(str_replace('/storage/', '', $posyandu->logo_posyandu), '/');
                    $full = uploads_base_path('uploads/' . $rel);
                    if (File::exists($full)) {
                        File::delete($full);
                    }
                }
                $posyandu->delete();
            });
            $this->loadPosyandu();

            session()->flash('message', 'Posyandu berhasil dihapus.');
            session()->flash('messageType', 'success');
        } catch (\Exception $e) {
            session()->flash('message', 'Gagal menghapus posyandu: ' . $e->getMessage());
            session()->flash('messageType', 'error');
        }
    }

    /**
     * Hapus logo posyandu saja (tanpa menghapus data posyandu)
     */
    public function deleteLogo($id)
    {
        try {
            $posyandu = Posyandu::find($id);
            if (!$posyandu) {
                session()->flash('message', 'Posyandu tidak ditemukan.');
                session()->flash('messageType', 'error');
                return;
            }

            if ($posyandu->logo_posyandu) {
                $rel = ltrim(str_replace('/storage/', '', $posyandu->logo_posyandu), '/');
                $full = uploads_base_path('uploads/' . $rel);
                if (File::exists($full)) {
                    File::delete($full);
                }
            }

            $posyandu->update(['logo_posyandu' => null]);

            $this->loadPosyandu();

            session()->flash('message', 'Logo posyandu berhasil dihapus.');
            session()->flash('messageType', 'success');
        } catch (\Exception $e) {
            session()->flash('message', 'Gagal menghapus logo posyandu: ' . $e->getMessage());
            session()->flash('messageType', 'error');
        }
    }

    /**
     * Updated search - reload data
     */
    public function updatedSearch()
    {
        $this->loadPosyandu();
    }

    public function render()
    {
        $daftarPosyandu = Posyandu::select('id_posyandu', 'nama_posyandu')->get();

        return view('livewire.super-admin.posyandu-list', [
            'title' => 'Daftar Posyandu',
            'daftarPosyandu' => $daftarPosyandu,
        ]);
    }
}

