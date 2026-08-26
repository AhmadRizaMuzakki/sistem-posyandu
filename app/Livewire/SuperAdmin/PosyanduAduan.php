<?php

namespace App\Livewire\SuperAdmin;

use App\Helpers\AduanOptions;
use App\Livewire\Posyandu\KaderAduan;
use App\Models\Orangtua;
use App\Models\Posyandu;
use Livewire\Attributes\Layout;

#[Layout('layouts.superadmindashboard')]
class PosyanduAduan extends KaderAduan
{
    public function mount(): void
    {
        $id = request()->route('id');

        if (! $id) {
            abort(404, 'ID tidak ditemukan');
        }

        try {
            $decryptedId = decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID tidak valid');
        }

        $posyandu = Posyandu::find($decryptedId);
        if (! $posyandu) {
            abort(404, 'Posyandu tidak ditemukan');
        }

        $this->posyanduId = $posyandu->id_posyandu;
        $this->posyandu = $posyandu;
        app(\App\Services\SasaranKategoriService::class)->syncForPosyandu($this->posyanduId);
    }

    public function render()
    {
        $aduanList = $this->applyFilters(
            $this->baseQuery()->orderByDesc('tanggal_aduan')
        )->paginate(10);

        $noKkList = $aduanList->pluck('no_kk')->unique()->filter()->values();
        $orangtuaMap = Orangtua::whereIn('no_kk', $noKkList)
            ->get()
            ->keyBy('no_kk');

        $selectedAduan = null;
        $detailOrangtua = null;
        if ($this->showDetailModal && $this->selectedAduanId) {
            $selectedAduan = $this->baseQuery()
                ->where('id_aduan', $this->selectedAduanId)
                ->first();
            if ($selectedAduan) {
                $detailOrangtua = Orangtua::where('no_kk', $selectedAduan->no_kk)->first();
            }
        }

        return view('livewire.super-admin.posyandu-aduan', [
            'title' => 'SPM - '.$this->posyandu->nama_posyandu,
            'posyandu' => $this->posyandu,
            'aduanList' => $aduanList,
            'orangtuaMap' => $orangtuaMap,
            'selectedAduan' => $selectedAduan,
            'detailOrangtua' => $detailOrangtua,
            'keluargaList' => $this->getKeluargaList(),
            'filteredKeluargaList' => $this->getFilteredKeluargaList(),
            'statusOptions' => AduanOptions::statusOptions(),
            'kategoriOptions' => AduanOptions::kategoriOptions(),
        ]);
    }
}
