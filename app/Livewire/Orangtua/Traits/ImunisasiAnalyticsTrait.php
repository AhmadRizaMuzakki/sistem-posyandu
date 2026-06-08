<?php

namespace App\Livewire\Orangtua\Traits;

use App\Models\Imunisasi;
use App\Models\SasaranBayibalita;
use App\Models\SasaranDewasa;
use App\Models\SasaranLansia;
use App\Models\SasaranPralansia;
use App\Models\SasaranRemaja;
use App\Services\AntropometriService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

trait ImunisasiAnalyticsTrait
{
    protected function resolveNoKk(): ?string
    {
        $user = Auth::user();
        if ($user->email && str_ends_with($user->email, '@gmail.com')) {
            return str_replace('@gmail.com', '', $user->email);
        }

        return null;
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    protected function getSasaranUntukAnalytics(): \Illuminate\Support\Collection
    {
        $noKk = $this->resolveNoKk();
        $allSasaran = collect();

        if (!$noKk) {
            return $allSasaran;
        }

        $kategoriMap = [
            ['model' => SasaranBayibalita::class, 'id' => 'id_sasaran_bayibalita', 'label' => 'Bayi/Balita', 'slug' => 'bayibalita'],
            ['model' => SasaranRemaja::class, 'id' => 'id_sasaran_remaja', 'label' => 'Remaja', 'slug' => 'remaja'],
            ['model' => SasaranDewasa::class, 'id' => 'id_sasaran_dewasa', 'label' => 'Dewasa', 'slug' => 'dewasa'],
            ['model' => SasaranPralansia::class, 'id' => 'id_sasaran_pralansia', 'label' => 'Pralansia', 'slug' => 'pralansia'],
            ['model' => SasaranLansia::class, 'id' => 'id_sasaran_lansia', 'label' => 'Lansia', 'slug' => 'lansia'],
        ];

        foreach ($kategoriMap as $cfg) {
            $sasaranList = $cfg['model']::where('no_kk_sasaran', $noKk)->get();
            foreach ($sasaranList as $sasaran) {
                $allSasaran->push([
                    'id' => $sasaran->{$cfg['id']},
                    'kategori' => $cfg['label'],
                    'kategori_slug' => $cfg['slug'],
                    'nama' => $sasaran->nama_sasaran,
                    'nik' => $sasaran->nik_sasaran,
                    'tanggal_lahir' => $sasaran->tanggal_lahir,
                    'jenis_kelamin' => $sasaran->jenis_kelamin,
                ]);
            }
        }

        return $allSasaran;
    }

    /**
     * @param  \Illuminate\Support\Collection  $allSasaran
     */
    protected function getImunisasiAnalytics($allSasaran, AntropometriService $antropometri): array
    {
        if ($allSasaran->isEmpty()) {
            return [
                'grafikPertumbuhan' => [],
                'grafikImunisasiJenis' => ['labels' => [], 'data' => []],
                'penilaianPerKategori' => [],
                'totalImunisasi' => 0,
            ];
        }

        $conditions = $allSasaran->map(fn ($s) => [
            'id' => $s['id'],
            'kategori' => $s['kategori_slug'],
        ])->toArray();

        $query = Imunisasi::where(function ($q) use ($conditions) {
            foreach ($conditions as $cond) {
                $q->orWhere(function ($sub) use ($cond) {
                    $sub->where('id_sasaran', $cond['id'])
                        ->where('kategori_sasaran', $cond['kategori']);
                });
            }
        });

        $semuaImunisasi = $query->orderBy('tanggal_imunisasi', 'asc')->get();
        $totalImunisasi = $semuaImunisasi->count();

        $jenisCount = $semuaImunisasi->groupBy('jenis_imunisasi')
            ->map->count()
            ->sortDesc();

        $grafikImunisasiJenis = [
            'labels' => $jenisCount->keys()->values()->toArray(),
            'data' => $jenisCount->values()->toArray(),
        ];

        $sasaranByKey = $allSasaran->keyBy(fn ($s) => $s['kategori_slug'] . '_' . $s['id']);
        $grafikPertumbuhan = [];
        $penilaianPerKategori = [];

        $kategoriLabels = [
            'bayibalita' => 'Bayi/Balita',
            'remaja' => 'Remaja',
            'dewasa' => 'Dewasa',
            'pralansia' => 'Pralansia',
            'lansia' => 'Lansia',
        ];

        foreach ($kategoriLabels as $slug => $label) {
            $penilaianPerKategori[$slug] = [
                'label' => $label,
                'slug' => $slug,
                'sasaran' => [],
            ];
        }

        $grouped = $semuaImunisasi->groupBy(fn ($im) => $im->kategori_sasaran . '_' . $im->id_sasaran);

        foreach ($grouped as $key => $records) {
            $sasaran = $sasaranByKey->get($key);
            if (!$sasaran) {
                continue;
            }

            $tanggalLahir = $sasaran['tanggal_lahir'] ? Carbon::parse($sasaran['tanggal_lahir']) : null;
            $labels = [];
            $berat = [];
            $tinggi = [];
            $imt = [];

            foreach ($records as $im) {
                if ($im->tanggal_imunisasi === null) {
                    continue;
                }
                $labels[] = $im->tanggal_imunisasi->format('d/m/Y');
                $berat[] = $im->berat_badan !== null ? (float) $im->berat_badan : null;
                $tinggi[] = $im->tinggi_badan !== null ? (float) $im->tinggi_badan : null;
                $imt[] = $antropometri->hitungImt(
                    $im->berat_badan !== null ? (float) $im->berat_badan : null,
                    $im->tinggi_badan !== null ? (float) $im->tinggi_badan : null
                );
            }

            if (count($labels) > 0) {
                $grafikPertumbuhan[] = [
                    'nama' => $sasaran['nama'],
                    'kategori' => $sasaran['kategori'],
                    'kategori_slug' => $sasaran['kategori_slug'],
                    'labels' => $labels,
                    'berat' => $berat,
                    'tinggi' => $tinggi,
                    'imt' => $imt,
                ];
            }

            $terakhir = $records->sortByDesc('tanggal_imunisasi')->first();
            $penilaian = null;

            if ($terakhir && $terakhir->berat_badan !== null && $terakhir->tinggi_badan !== null) {
                $penilaian = $antropometri->evaluasi(
                    (float) $terakhir->berat_badan,
                    (float) $terakhir->tinggi_badan,
                    $tanggalLahir,
                    $terakhir->tanggal_imunisasi ? Carbon::parse($terakhir->tanggal_imunisasi) : null,
                    $sasaran['jenis_kelamin'],
                    $terakhir->tekanan_darah,
                    $terakhir->gula_darah !== null ? (float) $terakhir->gula_darah : null
                );
            }

            $slug = $sasaran['kategori_slug'];
            $penilaianPerKategori[$slug]['sasaran'][] = [
                'nama' => $sasaran['nama'],
                'nik' => $sasaran['nik'],
                'jenis_kelamin' => $sasaran['jenis_kelamin'],
                'tanggal_imunisasi_terakhir' => $terakhir?->tanggal_imunisasi?->format('d/m/Y'),
                'jenis_imunisasi_terakhir' => $terakhir?->jenis_imunisasi,
                'total_imunisasi' => $records->count(),
                'berat_badan' => $terakhir?->berat_badan,
                'tinggi_badan' => $terakhir?->tinggi_badan,
                'penilaian' => $penilaian,
            ];
        }

        return [
            'grafikPertumbuhan' => $grafikPertumbuhan,
            'grafikImunisasiJenis' => $grafikImunisasiJenis,
            'penilaianPerKategori' => array_values($penilaianPerKategori),
            'totalImunisasi' => $totalImunisasi,
        ];
    }
}
