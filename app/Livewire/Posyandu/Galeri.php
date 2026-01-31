<?php

namespace App\Livewire\Posyandu;

use App\Models\Galeri as GaleriModel;
use App\Livewire\Posyandu\Traits\PosyanduHelper;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;

class Galeri extends Component
{
    use WithFileUploads;
    use PosyanduHelper;

    #[Layout('layouts.posyandudashboard')]

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

    public function mount()
    {
        $this->initializePosyandu();
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
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        $saved = 0;
        foreach ($this->fotoFiles as $file) {
            $ext = $file->getClientOriginalExtension();
            $safeName = 'galeri_' . $this->posyanduId . '_' . Str::random(8) . '.' . $ext;
            $destFile = $dir . DIRECTORY_SEPARATOR . $safeName;
            if (!File::copy($file->getRealPath(), $destFile)) {
                session()->flash('messageType', 'error');
                session()->flash('message', 'Gagal menyimpan file ke ' . $dir);
                return;
            }
            $path = 'galeri/' . $safeName;
            GaleriModel::create([
                'path' => $path,
                'caption' => $caption,
                'id_posyandu' => $this->posyanduId,
            ]);
            $saved++;
        }
        $this->closeUploadModal();
        session()->flash('message', $saved > 1 ? "{$saved} foto berhasil ditambahkan." : 'Foto berhasil ditambahkan.');
    }

    public function deleteFoto($id)
    {
        $galeri = GaleriModel::where('id_posyandu', $this->posyanduId)->findOrFail($id);
        $fullPath = $galeri->path ? uploads_base_path('uploads/' . $galeri->path) : null;
        if ($fullPath && File::exists($fullPath)) {
            File::delete($fullPath);
        }
        $galeri->delete();
        session()->flash('message', 'Foto berhasil dihapus.');
    }

    public function render()
    {
        $items = GaleriModel::where('id_posyandu', $this->posyanduId)->latest()->paginate(24);
        return view('livewire.posyandu.galeri', [
            'items' => $items,
            'title' => 'Galeri - ' . $this->posyandu->nama_posyandu,
        ]);
    }
}
