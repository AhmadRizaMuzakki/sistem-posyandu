<?php

namespace App\Livewire\Posyandu\Traits;

use App\Models\Kader;
use Illuminate\Support\Facades\Auth;

trait PosyanduCrudTrait
{
    /**
     * Validasi bahwa posyandu yang dipilih adalah posyandu dari kader yang login
     */
    protected function validatePosyanduAccess($posyanduId = null)
    {
        $user = Auth::user();
        $kader = Kader::where('id_users', $user->id)->first();

        if (!$kader) {
            abort(403, 'Anda bukan kader terdaftar.');
        }

        $kaderPosyanduId = $kader->id_posyandu;

        // Jika posyanduId tidak diberikan, gunakan dari kader
        if ($posyanduId === null) {
            return $kaderPosyanduId;
        }

        // Validasi bahwa posyandu yang dipilih adalah posyandu kader
        if ($posyanduId != $kaderPosyanduId) {
            abort(403, 'Anda tidak memiliki akses ke posyandu ini.');
        }

        return $kaderPosyanduId;
    }

    /**
     * Set posyandu otomatis dari kader yang login
     */
    protected function setPosyanduFromKader()
    {
        $user = Auth::user();
        $kader = Kader::where('id_users', $user->id)->first();

        if (!$kader) {
            abort(403, 'Anda bukan kader terdaftar.');
        }

        return $kader->id_posyandu;
    }

    /**
     * Validasi akses untuk edit/delete berdasarkan posyandu
     */
    protected function validateSasaranPosyanduAccess($sasaran, $posyanduIdField = 'id_posyandu')
    {
        $kaderPosyanduId = $this->setPosyanduFromKader();

        if ($sasaran->$posyanduIdField != $kaderPosyanduId) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }
    }
}

