<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Posyandu;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
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
            ];

            // Upload SK jika ada file baru
            if ($this->skFile) {
                // Hapus file lama jika ada
                if ($this->currentSkPath) {
                    $oldPath = str_replace('/storage/', '', $this->currentSkPath);
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }

                $originalName = $this->skFile->getClientOriginalName();
                $extension = $this->skFile->getClientOriginalExtension();
                $safeName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '_' . time() . '.' . $extension;
                $path = $this->skFile->storeAs('sk_posyandu', $safeName, 'public');
                $data['sk_posyandu'] = '/storage/' . $path;
            } elseif ($this->isEditMode && !$this->skFile) {
                // Jika edit mode dan tidak ada file baru, pertahankan file lama
                $data['sk_posyandu'] = $this->currentSkPath;
            }

            // Upload logo jika ada file baru
            if ($this->logoFile) {
                // Hapus file lama jika ada
                if ($this->currentLogoPath) {
                    $oldPath = str_replace('/storage/', '', $this->currentLogoPath);
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }

                $originalName = $this->logoFile->getClientOriginalName();
                $extension = $this->logoFile->getClientOriginalExtension();
                $safeName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '_' . time() . '.' . $extension;
                $path = $this->logoFile->storeAs('logo_posyandu', $safeName, 'public');
                $data['logo_posyandu'] = '/storage/' . $path;
            } elseif ($this->isEditMode && !$this->logoFile) {
                // Jika edit mode dan tidak ada file baru, pertahankan file lama
                $data['logo_posyandu'] = $this->currentLogoPath;
            }

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

            // Hapus file SK jika ada
            if ($posyandu->sk_posyandu) {
                $oldPath = str_replace('/storage/', '', $posyandu->sk_posyandu);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Hapus logo jika ada
            if ($posyandu->logo_posyandu) {
                $oldPath = str_replace('/storage/', '', $posyandu->logo_posyandu);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $posyandu->delete();
            $this->loadPosyandu();

            session()->flash('message', 'Posyandu berhasil dihapus.');
            session()->flash('messageType', 'success');
        } catch (\Exception $e) {
            session()->flash('message', 'Gagal menghapus posyandu: ' . $e->getMessage());
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

