<?php

namespace App\Livewire\SuperAdmin\Traits;

use App\Models\Orangtua;
use App\Models\SasaranBayibalita;
use App\Models\SasaranDewasa;
use App\Models\SasaranIbuhamil;
use App\Models\SasaranLansia;
use App\Models\SasaranPralansia;
use App\Models\SasaranRemaja;

trait SasaranViewTrait
{
    public $isSasaranViewModalOpen = false;
    public $viewSasaranKategori = '';
    public $viewSasaranId = null;

    public function viewSasaran(string $kategori, $id)
    {
        $model = $this->resolveSasaranForView($kategori, $id);
        $this->applyViewSasaran($kategori, $model);
    }

    public function viewOrangtua($nik)
    {
        $model = Orangtua::where('nik', $nik)->firstOrFail();
        $this->applyViewSasaran('orangtua', $model);
    }

    public function closeSasaranViewModal()
    {
        $this->isSasaranViewModalOpen = false;
        $this->viewSasaranKategori = '';
        $this->viewSasaranId = null;
    }

    protected function resolveSasaranForView(string $kategori, $id)
    {
        return match ($kategori) {
            'bayibalita' => SasaranBayibalita::with(['orangtua', 'user'])->findOrFail($id),
            'remaja' => SasaranRemaja::with(['orangtua', 'user'])->findOrFail($id),
            'dewasa' => SasaranDewasa::with(['orangtua', 'user'])->findOrFail($id),
            'pralansia' => SasaranPralansia::with(['orangtua', 'user'])->findOrFail($id),
            'lansia' => SasaranLansia::with(['orangtua', 'user'])->findOrFail($id),
            'ibuhamil' => SasaranIbuhamil::findOrFail($id),
            default => abort(404, 'Kategori sasaran tidak valid'),
        };
    }

    protected function applyViewSasaran(string $kategori, $model): void
    {
        $this->viewSasaranKategori = $kategori;
        $this->viewSasaranId = $model->getKey();
        $this->isSasaranViewModalOpen = true;
    }

    public function getViewSasaranProperty()
    {
        if (!$this->isSasaranViewModalOpen || $this->viewSasaranId === null || $this->viewSasaranId === '') {
            return null;
        }

        if ($this->viewSasaranKategori === 'orangtua') {
            return Orangtua::where('nik', $this->viewSasaranId)->first();
        }

        try {
            return $this->resolveSasaranForView($this->viewSasaranKategori, $this->viewSasaranId);
        } catch (\Throwable) {
            return null;
        }
    }

    public function getViewSasaranOrangtuaProperty()
    {
        $sasaran = $this->viewSasaran;

        if (!$sasaran || !in_array($this->viewSasaranKategori, ['bayibalita', 'remaja'], true)) {
            return null;
        }

        return $sasaran->orangtua ?? null;
    }

    public function getViewSasaranTitleProperty(): string
    {
        return match ($this->viewSasaranKategori) {
            'bayibalita' => 'Detail Sasaran Bayi/Balita',
            'remaja' => 'Detail Sasaran Remaja',
            'dewasa' => 'Detail Sasaran Dewasa',
            'ibuhamil' => 'Detail Sasaran Ibu Hamil',
            'pralansia' => 'Detail Sasaran Pralansia',
            'lansia' => 'Detail Sasaran Lansia',
            'orangtua' => 'Detail Data Orang Tua',
            default => 'Detail Sasaran',
        };
    }
}
