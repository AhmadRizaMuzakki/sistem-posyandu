<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Galeri as GaleriModel;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Component;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;

class Galeri extends Component
{
    use WithFileUploads;

    #[Layout('layouts.superadmindashboard')]

    public $fotoFiles = [];
    public $caption = '';
    public $showUploadModal = false;

    protected function rules()
    {
        return [
            'fotoFiles' => 'required',
            'fotoFiles.*' => 'image|max:2048',
            'caption' => 'nullable|string|max:255',
        ];
    }

    protected function messages()
    {
        return [
            'fotoFiles.required' => 'Pilih minimal satu foto.',
            'fotoFiles.*.image' => 'Setiap file harus berupa gambar (JPG, PNG, GIF, WebP).',
            'fotoFiles.*.max' => 'Setiap foto maksimal 2 MB.',
        ];
    }

    public function openUploadModal()
    {
        $this->reset(['fotoFiles', 'caption']);
        $this->showUploadModal = true;
    }

    public function closeUploadModal()
    {
        $this->showUploadModal = false;
        $this->reset(['fotoFiles', 'caption']);
    }

    public function saveFoto()
    {
        $this->validate();
        $caption = $this->caption ?: null;
        $dir = uploads_base_path('uploads/galeri');
        try {
            if (!File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            if (!File::isWritable($dir)) {
                session()->flash('messageType', 'error');
                session()->flash('message', 'Folder uploads/galeri tidak dapat ditulis. Di server buat folder public/uploads/galeri dan set permission 775 atau 755.');
                return;
            }
        } catch (\Throwable $e) {
            session()->flash('messageType', 'error');
            session()->flash('message', 'Folder uploads/galeri tidak bisa dibuat. Buat manual: public/uploads/galeri dengan permission 775.');
            return;
        }
        $saved = 0;
        try {
            foreach ($this->fotoFiles as $file) {
                $ext = $file->getClientOriginalExtension();
                $safeName = 'galeri_' . Str::random(8) . '.' . $ext;
                $destFile = $dir . DIRECTORY_SEPARATOR . $safeName;
                // Pakai copy agar jalan saat temp (storage) dan public/uploads beda filesystem/symlink
                if (!File::copy($file->getRealPath(), $destFile)) {
                    throw new FileException('Copy gagal ke: ' . $destFile);
                }
                $path = 'galeri/' . $safeName;
                GaleriModel::create([
                    'path' => $path,
                    'caption' => $caption,
                    'id_posyandu' => null,
                ]);
                $saved++;
            }
        } catch (FileException $e) {
            session()->flash('messageType', 'error');
            session()->flash('message', 'Gagal menyimpan file. Pastikan folder public/uploads/galeri ada dan bisa ditulis (chmod 775). Path: ' . $dir);
            return;
        } catch (\Throwable $e) {
            session()->flash('messageType', 'error');
            session()->flash('message', 'Gagal upload: ' . $e->getMessage());
            return;
        }
        $this->closeUploadModal();
        session()->flash('message', $saved > 1 ? "{$saved} foto berhasil ditambahkan." : 'Foto berhasil ditambahkan.');
    }

    public function deleteFoto($id)
    {
        $galeri = GaleriModel::findOrFail($id);
        $fullPath = $galeri->path ? uploads_base_path('uploads/' . $galeri->path) : null;
        if ($fullPath && File::exists($fullPath)) {
            File::delete($fullPath);
        }
        $galeri->delete();
        session()->flash('message', 'Foto berhasil dihapus.');
    }

    public function render()
    {
        // Tampilkan foto dari seluruh galeri: global + semua posyandu
        $items = GaleriModel::with('posyandu')->latest()->paginate(24);
        return view('livewire.super-admin.galeri', [
            'items' => $items,
            'title' => 'Galeri',
        ]);
    }
}
