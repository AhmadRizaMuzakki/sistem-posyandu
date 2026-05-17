<?php

namespace App\Livewire\Traits;

trait GaleriTanggalFotoTrait
{
    public $tanggal_foto = '';

    protected function galeriTanggalRules(): array
    {
        return [
            'tanggal_foto' => 'nullable|date|before_or_equal:today',
        ];
    }

    protected function resetGaleriTanggalFields(): void
    {
        $this->tanggal_foto = '';
    }

    protected function resolveTanggalFoto(): ?string
    {
        return $this->tanggal_foto !== '' && $this->tanggal_foto !== null
            ? $this->tanggal_foto
            : null;
    }
}
