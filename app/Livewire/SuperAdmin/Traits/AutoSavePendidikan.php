<?php

namespace App\Livewire\SuperAdmin\Traits;

use App\Models\Pendidikan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait AutoSavePendidikan
{
    /**
     * Auto-save pendidikan ke tabel pendidikan setelah sasaran disimpan
     */
    protected function autoSavePendidikan($sasaranId, $kategoriSasaran, $posyanduId, $pendidikanValue, $sasaranData = [])
    {
        try {
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

            // Siapkan data untuk insert/update
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

            // Gunakan updateOrCreate untuk mengurangi query (1 query instead of 2)
            Pendidikan::updateOrCreate(
                [
                    'id_sasaran' => $sasaranId,
                    'kategori_sasaran' => $kategoriSasaran,
                    'id_posyandu' => $posyanduId,
                ],
                $data
            );
        } catch (\Exception $e) {
            // Log error tetapi jangan ganggu proses penyimpanan sasaran
            Log::error('Error auto-saving pendidikan: ' . $e->getMessage(), [
                'sasaran_id' => $sasaranId,
                'kategori' => $kategoriSasaran,
                'posyandu_id' => $posyanduId,
            ]);
        }
    }
}

