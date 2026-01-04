<?php

namespace App\Livewire\SuperAdmin\Traits;

use App\Models\Pendidikan;
use Illuminate\Support\Facades\Auth;

trait AutoSavePendidikan
{
    /**
     * Auto-save pendidikan ke tabel pendidikan setelah sasaran disimpan
     */
    protected function autoSavePendidikan($sasaranId, $kategoriSasaran, $posyanduId, $pendidikanValue, $sasaranData = [])
    {
        if (empty($pendidikanValue)) {
            return;
        }

        // Tentukan primary key berdasarkan kategori
        $primaryKeyMap = [
            'remaja' => 'id_sasaran_remaja',
            'dewasa' => 'id_sasaran_dewasa',
            'pralansia' => 'id_sasaran_pralansia',
            'lansia' => 'id_sasaran_lansia',
        ];

        $primaryKey = $primaryKeyMap[$kategoriSasaran] ?? null;
        if (!$primaryKey) {
            return;
        }

        // Cari atau buat record pendidikan
        $pendidikan = Pendidikan::where('id_sasaran', $sasaranId)
            ->where('kategori_sasaran', $kategoriSasaran)
            ->where('id_posyandu', $posyanduId)
            ->first();

        $data = [
            'id_posyandu' => $posyanduId,
            'id_users' => Auth::id(),
            'id_sasaran' => $sasaranId,
            'kategori_sasaran' => $kategoriSasaran,
            'pendidikan_terakhir' => $pendidikanValue,
        ];

        // Tambahkan data tambahan jika tersedia
        if (!empty($sasaranData)) {
            $data['nik'] = $sasaranData['nik'] ?? null;
            $data['nama'] = $sasaranData['nama'] ?? null;
            $data['tanggal_lahir'] = $sasaranData['tanggal_lahir'] ?? null;
            $data['jenis_kelamin'] = $sasaranData['jenis_kelamin'] ?? null;
            $data['umur'] = $sasaranData['umur'] ?? null;
        }

        if ($pendidikan) {
            $pendidikan->update($data);
        } else {
            Pendidikan::create($data);
        }
    }
}

