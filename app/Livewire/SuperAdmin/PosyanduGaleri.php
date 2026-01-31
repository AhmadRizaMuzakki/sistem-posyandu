<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Galeri as GaleriModel;
use App\Models\Posyandu;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;

class PosyanduGaleri extends Component
{
    use WithFileUploads;

    public $posyandu;
    public $posyanduId;

    public $fotoFiles = [];
    public $caption = '';
    public $showUploadModal = false;

    #[Layout('layouts.superadmindashboard')]

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

    private function loadPosyandu()
    {
        $posyandu = Posyandu::find($this->posyanduId);
        if (!$posyandu) {
            abort(404, 'Posyandu tidak ditemukan');
        }
        $this->posyandu = $posyandu;
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
        $dir = public_path('uploads/galeri');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        $saved = 0;
        foreach ($this->fotoFiles as $file) {
            $ext = $file->getClientOriginalExtension();
            $safeName = 'galeri_' . $this->posyanduId . '_' . Str::random(8) . '.' . $ext;
            $file->move($dir, $safeName);
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
        $fullPath = $galeri->path ? public_path('uploads/' . $galeri->path) : null;
        if ($fullPath && File::exists($fullPath)) {
            File::delete($fullPath);
        }
        $galeri->delete();
        session()->flash('message', 'Foto berhasil dihapus.');
    }

    public function render()
    {
        $items = GaleriModel::where('id_posyandu', $this->posyanduId)->latest()->paginate(24);
        return view('livewire.super-admin.posyandu-galeri', [
            'items' => $items,
            'title' => 'Galeri - ' . $this->posyandu->nama_posyandu,
        ]);
    }
}
