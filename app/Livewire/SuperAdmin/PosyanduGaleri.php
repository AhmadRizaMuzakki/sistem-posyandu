<?php

namespace App\Livewire\SuperAdmin;

use App\Livewire\Traits\GaleriTanggalFotoTrait;
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
    use GaleriTanggalFotoTrait;

    public $posyandu;
    public $posyanduId;

    public $fotoFiles = [];
    public $caption = '';
    public $showUploadModal = false;
    public $editingId = null;
    public $editingPreviewPath = null;

    #[Layout('layouts.superadmindashboard')]

    protected function rules()
    {
        $rules = array_merge([
            'caption' => 'nullable|string|max:255',
        ], $this->galeriTanggalRules());

        if ($this->editingId) {
            return $rules;
        }

        return array_merge($rules, [
            'fotoFiles' => 'required',
            'fotoFiles.*' => 'image|max:2048',
        ]);
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
        if (! $posyandu) {
            abort(404, 'Posyandu tidak ditemukan');
        }
        $this->posyandu = $posyandu;
    }

    public function openUploadModal()
    {
        $this->reset(['fotoFiles', 'caption', 'editingId', 'editingPreviewPath']);
        $this->resetGaleriTanggalFields();
        $this->showUploadModal = true;
    }

    public function openEditModal($id)
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if ($id === false) {
            abort(404);
        }

        $galeri = GaleriModel::where('id_posyandu', $this->posyanduId)->findOrFail($id);
        $this->editingId = $galeri->id;
        $this->editingPreviewPath = $galeri->path;
        $this->caption = $galeri->caption ?? '';
        $this->tanggal_foto = $galeri->tanggal_foto
            ? $galeri->tanggal_foto->format('Y-m-d')
            : '';
        $this->fotoFiles = [];
        $this->showUploadModal = true;
    }

    public function closeUploadModal()
    {
        $this->showUploadModal = false;
        $this->reset(['fotoFiles', 'caption', 'editingId', 'editingPreviewPath']);
        $this->resetGaleriTanggalFields();
    }

    public function saveFoto()
    {
        if ($this->editingId) {
            $this->updateFoto();

            return;
        }

        $this->validate();
        $tanggalFoto = $this->resolveTanggalFoto();
        $caption = $this->caption ?: null;
        $dir = uploads_base_path('uploads/galeri');
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        $allowedImageMimes = ['image/jpeg' => 'jpg', 'image/pjpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
        $saved = 0;
        foreach ($this->fotoFiles as $file) {
            $ext = safe_upload_extension($file, $allowedImageMimes) ?? 'jpg';
            $safeName = 'galeri_'.$this->posyanduId.'_'.Str::random(8).'.'.$ext;
            $destFile = $dir.DIRECTORY_SEPARATOR.$safeName;
            if (! File::copy($file->getRealPath(), $destFile)) {
                session()->flash('messageType', 'error');
                session()->flash('message', 'Gagal menyimpan file ke '.$dir);

                return;
            }
            $path = 'galeri/'.$safeName;
            GaleriModel::create([
                'path' => $path,
                'caption' => $caption,
                'tanggal_foto' => $tanggalFoto,
                'id_posyandu' => $this->posyanduId,
            ]);
            $saved++;
        }
        $this->closeUploadModal();
        session()->flash('message', $saved > 1 ? "{$saved} foto berhasil ditambahkan." : 'Foto berhasil ditambahkan.');
    }

    public function updateFoto()
    {
        $this->validate();

        $galeri = GaleriModel::where('id_posyandu', $this->posyanduId)->findOrFail($this->editingId);
        $galeri->update([
            'caption' => $this->caption ?: null,
            'tanggal_foto' => $this->resolveTanggalFoto(),
        ]);

        $this->closeUploadModal();
        session()->flash('message', 'Data foto berhasil diperbarui.');
    }

    public function deleteFoto($id)
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if ($id === false) {
            abort(404);
        }
        $galeri = GaleriModel::where('id_posyandu', $this->posyanduId)->findOrFail($id);
        $fullPath = $galeri->path ? uploads_safe_full_path($galeri->path) : null;
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
            'title' => 'Galeri - '.$this->posyandu->nama_posyandu,
        ]);
    }
}
