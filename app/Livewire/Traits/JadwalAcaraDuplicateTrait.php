<?php

namespace App\Livewire\Traits;

use App\Models\JadwalKegiatan;
use Carbon\Carbon;

trait JadwalAcaraDuplicateTrait
{
    /**
     * Cek apakah acara dengan nama & tanggal yang sama sudah ada di posyandu ini.
     */
    protected function acaraSudahTerdaftar(?int $exceptId = null): bool
    {
        $nama = trim((string) ($this->nama_kegiatan ?? ''));
        $tanggal = $this->tanggal_acara ?? '';

        if ($nama === '' || $tanggal === '' || empty($this->posyanduId)) {
            return false;
        }

        $query = JadwalKegiatan::query()
            ->where('id_posyandu', $this->posyanduId)
            ->whereDate('tanggal', $tanggal)
            ->whereRaw('LOWER(TRIM(nama_kegiatan)) = ?', [mb_strtolower($nama)]);

        if ($exceptId !== null) {
            $query->where('id_jadwal_kegiatan', '!=', $exceptId);
        }

        return $query->exists();
    }

    protected function pesanAcaraDuplikat(): string
    {
        $nama = trim((string) $this->nama_kegiatan);
        $tanggalLabel = $this->tanggal_acara
            ? Carbon::parse($this->tanggal_acara)->locale('id')->translatedFormat('d F Y')
            : 'tanggal tersebut';

        return "Acara \"{$nama}\" pada {$tanggalLabel} sudah ditambahkan. Silakan gunakan nama atau tanggal yang berbeda.";
    }

    public function getAcaraDuplicatePesanProperty(): ?string
    {
        if ($this->id_jadwal_kegiatan_edit) {
            return null;
        }

        if (! $this->acaraSudahTerdaftar()) {
            return null;
        }

        return $this->pesanAcaraDuplikat();
    }

    protected function tampilkanNotifikasiAcaraDuplikat(): void
    {
        $message = $this->pesanAcaraDuplikat();

        session()->flash('message', $message);
        session()->flash('messageType', 'error');

        if (method_exists($this, 'showWarningNotification')) {
            $this->showWarningNotification($message, 'Acara Sudah Ditambahkan');
        }
    }
}
