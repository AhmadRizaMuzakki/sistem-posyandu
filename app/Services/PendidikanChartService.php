<?php

namespace App\Services;

use App\Models\Pendidikan;
use App\Models\SasaranDewasa;
use App\Models\SasaranIbuhamil;
use App\Models\SasaranLansia;
use App\Models\SasaranPralansia;
use App\Models\SasaranRemaja;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class PendidikanChartService
{
    /**
     * Urutan standar tingkat pendidikan untuk grafik.
     */
    public static function levels(): array
    {
        return [
            'Tidak/Belum Sekolah',
            'PAUD',
            'TK',
            'Tidak Tamat SD/Sederajat',
            'Tamat SD/Sederajat',
            'SLTP/Sederajat',
            'SLTA/Sederajat',
            'Diploma I/II',
            'Akademi/Diploma III/Sarjana Muda',
            'Diploma IV/Strata I',
            'Strata II',
            'Strata III',
        ];
    }

    /**
     * Mapping kategori sasaran → [model, primary key, tabel].
     */
    public static function sasaranCategories(): array
    {
        return [
            'remaja' => [SasaranRemaja::class, 'id_sasaran_remaja', 'sasaran_remajas'],
            'dewasa' => [SasaranDewasa::class, 'id_sasaran_dewasa', 'sasaran_dewasas'],
            'pralansia' => [SasaranPralansia::class, 'id_sasaran_pralansia', 'sasaran_pralansias'],
            'lansia' => [SasaranLansia::class, 'id_sasaran_lansia', 'sasaran_lansias'],
            'ibuhamil' => [SasaranIbuhamil::class, 'id_sasaran_ibuhamil', 'sasaran_ibuhamils'],
        ];
    }

    /**
     * Data grafik: utamakan kolom pendidikan sasaran, fallback ke tabel pendidikans.
     */
    public static function getChartData(?int $posyanduId = null): array
    {
        $data = self::getChartDataFromSasaran($posyanduId);

        if (array_sum($data['data']) === 0) {
            $data = self::getChartDataFromPendidikanTable($posyanduId);
        }

        return $data;
    }

    /**
     * Apakah ada data pendidikan untuk ditampilkan di grafik.
     */
    public static function hasChartData(?int $posyanduId = null): bool
    {
        return array_sum(self::getChartData($posyanduId)['data']) > 0;
    }

    /**
     * Data grafik dari kolom pendidikan di tabel sasaran.
     */
    private static function getChartDataFromSasaran(?int $posyanduId = null): array
    {
        $levels = self::levels();
        $counts = array_fill_keys($levels, 0);

        foreach (self::sasaranCategories() as [$modelClass, $primaryKey, $table]) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'pendidikan')) {
                continue;
            }

            $query = $modelClass::query()
                ->whereNotNull('pendidikan')
                ->where('pendidikan', '!=', '');

            if ($posyanduId !== null) {
                $query->where('id_posyandu', $posyanduId);
            }

            $grouped = $query
                ->selectRaw('pendidikan, COUNT(*) as jumlah')
                ->groupBy('pendidikan')
                ->pluck('jumlah', 'pendidikan');

            foreach ($grouped as $level => $jumlah) {
                if (isset($counts[$level])) {
                    $counts[$level] += (int) $jumlah;
                }
            }
        }

        return self::formatChartData($counts);
    }

    /**
     * Fallback: data dari tabel pendidikans (data lama / hasil sync).
     */
    private static function getChartDataFromPendidikanTable(?int $posyanduId = null): array
    {
        $levels = self::levels();
        $counts = array_fill_keys($levels, 0);

        if (!Schema::hasTable('pendidikans')) {
            return self::formatChartData($counts);
        }

        $query = Pendidikan::query()
            ->whereNotNull('pendidikan_terakhir')
            ->where('pendidikan_terakhir', '!=', '')
            ->whereIn('kategori_sasaran', array_keys(self::sasaranCategories()));

        if ($posyanduId !== null) {
            $query->where('id_posyandu', $posyanduId);
        }

        $grouped = $query
            ->selectRaw('pendidikan_terakhir, COUNT(*) as jumlah')
            ->groupBy('pendidikan_terakhir')
            ->pluck('jumlah', 'pendidikan_terakhir');

        foreach ($grouped as $level => $jumlah) {
            if (isset($counts[$level])) {
                $counts[$level] += (int) $jumlah;
            }
        }

        return self::formatChartData($counts);
    }

    private static function formatChartData(array $counts): array
    {
        return [
            'labels' => array_keys($counts),
            'data' => array_values($counts),
        ];
    }

    /**
     * Sinkronkan pendidikan tiap sasaran ke tabel pendidikans (untuk menu/laporan).
     * Mempertahankan nilai pendidikan masing-masing sasaran, bukan satu nilai untuk semua.
     */
    public static function syncFromSasaran(?int $posyanduId = null, ?int $userId = null): int
    {
        $userId = $userId ?? Auth::id();
        $synced = 0;

        foreach (self::sasaranCategories() as $kategori => [$modelClass, $primaryKey, $table]) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'pendidikan')) {
                continue;
            }

            $query = $modelClass::query()
                ->whereNotNull('pendidikan')
                ->where('pendidikan', '!=', '')
                ->select($primaryKey, 'id_posyandu', 'nik_sasaran', 'nama_sasaran', 'tanggal_lahir', 'jenis_kelamin', 'umur_sasaran', 'pendidikan');

            if ($posyanduId !== null) {
                $query->where('id_posyandu', $posyanduId);
            }

            foreach ($query->cursor() as $sasaran) {
                Pendidikan::updateOrCreate(
                    [
                        'id_posyandu' => $sasaran->id_posyandu,
                        'id_sasaran' => $sasaran->$primaryKey,
                        'kategori_sasaran' => $kategori,
                    ],
                    [
                        'id_users' => $userId,
                        'nik' => $sasaran->nik_sasaran,
                        'nama' => $sasaran->nama_sasaran,
                        'tanggal_lahir' => $sasaran->tanggal_lahir,
                        'jenis_kelamin' => $sasaran->jenis_kelamin,
                        'umur' => $sasaran->umur_sasaran,
                        'pendidikan_terakhir' => $sasaran->pendidikan,
                    ]
                );
                $synced++;
            }
        }

        return $synced;
    }
}
