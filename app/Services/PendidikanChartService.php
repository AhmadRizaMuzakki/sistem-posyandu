<?php

namespace App\Services;

use App\Models\Pendidikan;
use App\Models\SasaranBayibalita;
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
            'bayibalita' => [SasaranBayibalita::class, 'id_sasaran_bayibalita', 'sasaran_bayibalita'],
            'remaja' => [SasaranRemaja::class, 'id_sasaran_remaja', 'sasaran_remajas'],
            'dewasa' => [SasaranDewasa::class, 'id_sasaran_dewasa', 'sasaran_dewasas'],
            'pralansia' => [SasaranPralansia::class, 'id_sasaran_pralansia', 'sasaran_pralansias'],
            'lansia' => [SasaranLansia::class, 'id_sasaran_lansia', 'sasaran_lansias'],
            'ibuhamil' => [SasaranIbuhamil::class, 'id_sasaran_ibuhamil', 'sasaran_ibuhamils'],
        ];
    }

    /**
     * Data grafik: utamakan tabel pendidikans (selaras menu/export),
     * fallback ke kolom pendidikan sasaran jika tabel kosong.
     */
    public static function getChartData(?int $posyanduId = null): array
    {
        $fromPendidikan = self::getChartDataFromPendidikanTable($posyanduId);

        if (array_sum($fromPendidikan['data']) > 0) {
            return $fromPendidikan;
        }

        return self::getChartDataFromSasaran($posyanduId);
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
     * Data dari tabel pendidikans — record terbaru per sasaran (hindari duplikat tambah manual).
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

        // Ambil entry terbaru per sasaran agar tidak double-count
        $latestPerSasaran = $query
            ->orderByDesc('id_pendidikan')
            ->get()
            ->unique(fn ($row) => $row->id_posyandu . '|' . $row->kategori_sasaran . '|' . $row->id_sasaran);

        foreach ($latestPerSasaran as $row) {
            if (isset($counts[$row->pendidikan_terakhir])) {
                $counts[$row->pendidikan_terakhir]++;
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
     * Sinkronkan sasaran → tabel pendidikans.
     * - Record baru: isi dari sasaran (termasuk pendidikan).
     * - Record lama: update biodata saja, JANGAN timpa pendidikan_terakhir
     *   (mencegah sisa bulk update "Tamat SD" menimpa data yang sudah benar).
     */
    public static function syncFromSasaran(?int $posyanduId = null, ?int $userId = null): int
    {
        $userId = $userId ?? Auth::id();
        $synced = 0;

        foreach (self::sasaranCategories() as $kategori => [$modelClass, $primaryKey, $table]) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'pendidikan')) {
                continue;
            }

            $query = $modelClass::query()
                ->whereNotNull('pendidikan')
                ->where('pendidikan', '!=', '')
                ->select($primaryKey, 'id_posyandu', 'nik_sasaran', 'nama_sasaran', 'tanggal_lahir', 'jenis_kelamin', 'umur_sasaran', 'pendidikan', 'rt', 'rw');

            if ($posyanduId !== null) {
                $query->where('id_posyandu', $posyanduId);
            }

            foreach ($query->cursor() as $sasaran) {
                $keys = [
                    'id_posyandu' => $sasaran->id_posyandu,
                    'id_sasaran' => $sasaran->$primaryKey,
                    'kategori_sasaran' => $kategori,
                ];

                $existing = Pendidikan::query()->where($keys)->first();

                $biodata = [
                    'id_users' => $userId,
                    'nik' => $sasaran->nik_sasaran,
                    'nama' => $sasaran->nama_sasaran,
                    'tanggal_lahir' => $sasaran->tanggal_lahir,
                    'jenis_kelamin' => $sasaran->jenis_kelamin,
                    'umur' => $sasaran->umur_sasaran,
                    'rt' => $sasaran->rt ?? null,
                    'rw' => $sasaran->rw ?? null,
                ];

                if ($existing) {
                    $existing->update($biodata);
                } else {
                    Pendidikan::create($keys + $biodata + [
                        'pendidikan_terakhir' => $sasaran->pendidikan,
                    ]);
                }

                $synced++;
            }
        }

        self::cleanupDuplicatePendidikanRecords($posyanduId);
        self::repairYoungChildrenPendidikan($posyanduId);

        return $synced;
    }

    /**
     * Balita & anak kecil (umur <= 6) yang salah terisi "Tamat SD" dll → Tidak/Belum Sekolah.
     */
    public static function repairYoungChildrenPendidikan(?int $posyanduId = null): int
    {
        if (! Schema::hasTable('pendidikans')) {
            return 0;
        }

        $wrongLevels = [
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

        $fixed = 0;

        // 1) Semua bayibalita → Tidak/Belum Sekolah (kecuali sudah PAUD/TK)
        if (Schema::hasTable('sasaran_bayibalita') && Schema::hasColumn('sasaran_bayibalita', 'pendidikan')) {
            $balitaQuery = SasaranBayibalita::query()
                ->where(function ($q) {
                    $q->whereNull('pendidikan')
                        ->orWhere('pendidikan', '')
                        ->orWhereIn('pendidikan', [
                            'Tidak Tamat SD/Sederajat',
                            'Tamat SD/Sederajat',
                            'SLTP/Sederajat',
                            'SLTA/Sederajat',
                            'Diploma I/II',
                            'Akademi/Diploma III/Sarjana Muda',
                            'Diploma IV/Strata I',
                            'Strata II',
                            'Strata III',
                        ]);
                });

            if ($posyanduId !== null) {
                $balitaQuery->where('id_posyandu', $posyanduId);
            }

            foreach ($balitaQuery->cursor() as $balita) {
                $balita->update(['pendidikan' => 'Tidak/Belum Sekolah']);

                Pendidikan::updateOrCreate(
                    [
                        'id_posyandu' => $balita->id_posyandu,
                        'id_sasaran' => $balita->id_sasaran_bayibalita,
                        'kategori_sasaran' => 'bayibalita',
                    ],
                    [
                        'id_users' => Auth::id(),
                        'nik' => $balita->nik_sasaran,
                        'nama' => $balita->nama_sasaran,
                        'tanggal_lahir' => $balita->tanggal_lahir,
                        'jenis_kelamin' => $balita->jenis_kelamin,
                        'umur' => $balita->umur_sasaran,
                        'rt' => $balita->rt ?? null,
                        'rw' => $balita->rw ?? null,
                        'pendidikan_terakhir' => 'Tidak/Belum Sekolah',
                    ]
                );
                $fixed++;
            }
        }

        // 2) Baris pendidikans umur <= 6 dengan jenjang SD ke atas → Tidak/Belum Sekolah
        $youngQuery = Pendidikan::query()
            ->whereNotNull('umur')
            ->where('umur', '<=', 6)
            ->whereIn('pendidikan_terakhir', $wrongLevels);

        if ($posyanduId !== null) {
            $youngQuery->where('id_posyandu', $posyanduId);
        }

        foreach ($youngQuery->cursor() as $row) {
            $row->update(['pendidikan_terakhir' => 'Tidak/Belum Sekolah']);

            // Samakan kolom pendidikan di tabel sasaran (jika ada)
            if (isset(self::sasaranCategories()[$row->kategori_sasaran])) {
                [$modelClass, $primaryKey] = self::sasaranCategories()[$row->kategori_sasaran];
                $modelClass::where($primaryKey, $row->id_sasaran)
                    ->update(['pendidikan' => 'Tidak/Belum Sekolah']);
            }

            $fixed++;
        }

        return $fixed;
    }

    /**
     * Hapus duplikat lama — simpan record terbaru per sasaran.
     */
    public static function cleanupDuplicatePendidikanRecords(?int $posyanduId = null): int
    {
        if (! Schema::hasTable('pendidikans')) {
            return 0;
        }

        $keepQuery = Pendidikan::query()
            ->selectRaw('MAX(id_pendidikan) as id_pendidikan')
            ->groupBy('id_posyandu', 'id_sasaran', 'kategori_sasaran');

        if ($posyanduId !== null) {
            $keepQuery->where('id_posyandu', $posyanduId);
        }

        $keepIds = $keepQuery->pluck('id_pendidikan');

        $deleteQuery = Pendidikan::query()->whereNotIn('id_pendidikan', $keepIds);

        if ($posyanduId !== null) {
            $deleteQuery->where('id_posyandu', $posyanduId);
        }

        return $deleteQuery->delete();
    }
}
