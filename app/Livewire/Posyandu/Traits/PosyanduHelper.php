<?php

namespace App\Livewire\Posyandu\Traits;

use App\Models\Kader;
use App\Models\Posyandu;
use Illuminate\Support\Facades\Auth;

trait PosyanduHelper
{
    public $posyandu;
    public $posyanduId;

    /**
     * Initialize posyandu from logged-in kader
     */
    protected function initializePosyandu()
    {
        $user = Auth::user();
        $kader = Kader::where('id_users', $user->id)->first();

        if (!$kader) {
            abort(403, 'Anda bukan kader terdaftar.');
        }

        $this->posyanduId = $kader->id_posyandu;
        $this->loadPosyandu();
    }

    /**
     * Load posyandu data
     */
    protected function loadPosyandu()
    {
        $posyandu = Posyandu::find($this->posyanduId);

        if (!$posyandu) {
            abort(404, 'Posyandu tidak ditemukan');
        }

        $this->posyandu = $posyandu;
    }

    /**
     * Load posyandu with relations
     */
    protected function loadPosyanduWithRelations(array $relations = [])
    {
        $defaultRelations = [
            'sasaran_bayibalita.user',
            'sasaran_bayibalita.orangtua',
            'sasaran_remaja.user',
            'sasaran_remaja.orangtua',
            'sasaran_dewasa.user',
            'sasaran_pralansia.user',
            'sasaran_lansia.user',
            'sasaran_ibuhamil',
        ];

        $relations = !empty($relations) ? $relations : $defaultRelations;

        $posyandu = Posyandu::with($relations)->find($this->posyanduId);

        if (!$posyandu) {
            abort(404, 'Posyandu tidak ditemukan');
        }

        $this->posyandu = $posyandu;
    }

    /**
     * Refresh posyandu data
     */
    protected function refreshPosyandu()
    {
        $this->loadPosyandu();
    }

    /**
     * Refresh posyandu with relations
     */
    protected function refreshPosyanduWithRelations(array $relations = [])
    {
        $this->loadPosyanduWithRelations($relations);
    }
}

