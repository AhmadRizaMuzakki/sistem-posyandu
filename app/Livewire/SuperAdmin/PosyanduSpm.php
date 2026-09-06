<?php

namespace App\Livewire\SuperAdmin;

use App\Helpers\SpmOptions;
use App\Livewire\Posyandu\KaderSpm;
use App\Models\Orangtua;
use App\Models\Posyandu;
use Livewire\Attributes\Layout;

#[Layout('layouts.superadmindashboard')]
class PosyanduSpm extends KaderSpm
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
        $spmList = $this->applyFilters(
            $this->baseQuery()->orderByDesc('tanggal_aduan')
        )->paginate(10);

        $noKkList = $spmList->pluck('no_kk')->unique()->filter()->values();
        $orangtuaMap = Orangtua::whereIn('no_kk', $noKkList)
            ->get()
            ->keyBy('no_kk');

        $selectedSpm = null;
        $detailOrangtua = null;
        if ($this->showDetailModal && $this->selectedSpmId) {
            $selectedSpm = $this->baseQuery()
                ->where('id_aduan', $this->selectedSpmId)
                ->first();
            if ($selectedSpm) {
                $detailOrangtua = Orangtua::where('no_kk', $selectedSpm->no_kk)->first();
            }
        }

        return view('livewire.super-admin.posyandu-spm', [
            'title' => '6 SPM - '.$this->posyandu->nama_posyandu,
            'posyandu' => $this->posyandu,
            'spmList' => $spmList,
            'orangtuaMap' => $orangtuaMap,
            'selectedSpm' => $selectedSpm,
            'detailOrangtua' => $detailOrangtua,
            'keluargaList' => $this->getKeluargaList(),
            'filteredKeluargaList' => $this->getFilteredKeluargaList(),
            'statusOptions' => SpmOptions::statusOptions(),
            'kategoriOptions' => SpmOptions::kategoriOptions(),
        ]);
    }
}
