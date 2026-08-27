<?php

namespace App\Services;

use App\Helpers\SasaranFilterOptions;
use App\Models\Pendidikan;
use App\Models\SasaranDewasa;
use App\Models\SasaranIbuhamil;
use App\Models\SasaranLansia;
use App\Models\SasaranPralansia;
use App\Models\SasaranRemaja;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PendidikanListService
{
    /**
     * Kategori sasaran yang memiliki kolom pendidikan.
     *
     * @return array<string, array{class-string, string, string}>
     */
    public static function sasaranCategories(): array
    {
        return PendidikanChartService::sasaranCategories();
    }

    /**
     * Query UNION semua sasaran dengan pendidikan terisi.
     */
    public static function query(?int $posyanduId = null, ?string $search = null): Builder
    {
        $union = self::buildUnionQuery($posyanduId);

        $query = DB::query()->fromSub($union, 'pendidikan_sasaran');

        if ($search) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                    ->orWhere('nik', 'like', '%' . $search . '%')
                    ->orWhere('pendidikan_terakhir', 'like', '%' . $search . '%');
            });
        }

        return $query->orderByDesc('tanggal_lahir');
    }

    /**
     * Pagination untuk Livewire.
     */
    public static function paginate(?int $posyanduId, ?string $search = null, int $perPage = 10, ?int $page = null): LengthAwarePaginator
    {
        $page = $page ?: (int) request()->input('page', 1);
        $query = self::query($posyanduId, $search);

        $total = (clone $query)->count();
        $items = $query
            ->forPage($page, $perPage)
            ->get();

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /**
     * Ambil semua data (untuk PDF/laporan).
     */
    public static function getAll(?int $posyanduId = null, array $filters = []): \Illuminate\Support\Collection
    {
        $query = self::query($posyanduId);
        self::applyFilters($query, $filters);

        return $query->get();
    }

    /**
     * Terapkan filter laporan ke query builder.
     *
     * @param  array<string, mixed>  $filters
     */
    public static function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['kategori_sasaran'])) {
            $query->where('kategori_sasaran', $filters['kategori_sasaran']);
        }

        if (! empty($filters['pendidikan_terakhir'])) {
            $query->where('pendidikan_terakhir', $filters['pendidikan_terakhir']);
        }

        if (! empty($filters['nama'])) {
            $query->where('nama', 'like', '%' . $filters['nama'] . '%');
        }

        if (! empty($filters['filter_sasaran'])) {
            SasaranFilterOptions::applyToPendidikanQuery($query, $filters['filter_sasaran']);
        }

        return $query;
    }

    /**
     * Cari satu baris berdasarkan kategori + id sasaran.
     */
    public static function find(?int $posyanduId, string $kategori, int $idSasaran): ?object
    {
        return self::query($posyanduId)
            ->where('kategori_sasaran', $kategori)
            ->where('id_sasaran', $idSasaran)
            ->first();
    }

    /**
     * Update pendidikan di tabel sasaran (+ sync cache pendidikans).
     */
    public static function updatePendidikan(string $kategori, int $idSasaran, string $pendidikan, ?int $posyanduId = null): bool
    {
        $sasaran = self::getSasaranModel($kategori, $idSasaran);

        if (! $sasaran) {
            return false;
        }

        if ($posyanduId !== null && (int) $sasaran->id_posyandu !== $posyanduId) {
            return false;
        }

        if (! self::kategoriHasPendidikanColumn($kategori)) {
            return false;
        }

        $sasaran->update(['pendidikan' => $pendidikan]);
        self::syncPendidikanCache($sasaran, $kategori, $idSasaran, $pendidikan);

        return true;
    }

    /**
     * Kosongkan pendidikan sasaran (hapus dari daftar).
     */
    public static function clearPendidikan(string $kategori, int $idSasaran, ?int $posyanduId = null): bool
    {
        $sasaran = self::getSasaranModel($kategori, $idSasaran);

        if (! $sasaran) {
            return false;
        }

        if ($posyanduId !== null && (int) $sasaran->id_posyandu !== $posyanduId) {
            return false;
        }

        if (! self::kategoriHasPendidikanColumn($kategori)) {
            return false;
        }

        $sasaran->update(['pendidikan' => null]);

        Pendidikan::query()
            ->where('id_sasaran', $idSasaran)
            ->where('kategori_sasaran', $kategori)
            ->when($posyanduId, fn ($q) => $q->where('id_posyandu', $posyanduId))
            ->delete();

        return true;
    }

    public static function kategoriHasPendidikanColumn(string $kategori): bool
    {
        $categories = self::sasaranCategories();

        if (! isset($categories[$kategori])) {
            return false;
        }

        [, , $table] = $categories[$kategori];

        return Schema::hasTable($table) && Schema::hasColumn($table, 'pendidikan');
    }

    /**
     * @return object|null
     */
    public static function getSasaranModel(string $kategori, int $idSasaran)
    {
        $categories = self::sasaranCategories();

        if (! isset($categories[$kategori])) {
            return null;
        }

        [$modelClass, $primaryKey] = $categories[$kategori];

        return $modelClass::where($primaryKey, $idSasaran)->first();
    }

    private static function buildUnionQuery(?int $posyanduId): Builder
    {
        $parts = [];

        foreach (self::sasaranCategories() as $kategori => [, $idColumn, $table]) {
            $part = self::buildSasaranPart($kategori, $table, $idColumn, $posyanduId);

            if ($part !== null) {
                $parts[] = $part;
            }
        }

        if (empty($parts)) {
            return DB::query()->fromSub(
                DB::table(DB::raw('(SELECT NULL as kategori_sasaran, NULL as id_sasaran, NULL as id_posyandu, NULL as nik, NULL as nama, NULL as tanggal_lahir, NULL as jenis_kelamin, NULL as umur, NULL as rt, NULL as rw, NULL as pendidikan_terakhir WHERE 1=0) as empty_pendidikan')),
                'pendidikan_sasaran'
            );
        }

        $union = array_shift($parts);

        foreach ($parts as $part) {
            $union->unionAll($part);
        }

        return $union;
    }

    private static function buildSasaranPart(string $kategori, string $table, string $idColumn, ?int $posyanduId): ?Builder
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'pendidikan')) {
            return null;
        }

        $query = DB::table($table)
            ->whereNotNull('pendidikan')
            ->where('pendidikan', '!=', '');

        if ($posyanduId !== null) {
            $query->where('id_posyandu', $posyanduId);
        }

        return $query->select([
            DB::raw("'{$kategori}' as kategori_sasaran"),
            "{$idColumn} as id_sasaran",
            'id_posyandu',
            'nik_sasaran as nik',
            'nama_sasaran as nama',
            'tanggal_lahir',
            'jenis_kelamin',
            'umur_sasaran as umur',
            'rt',
            'rw',
            'pendidikan as pendidikan_terakhir',
        ]);
    }

    /**
     * @param  object  $sasaran
     */
    private static function syncPendidikanCache($sasaran, string $kategori, int $idSasaran, string $pendidikan): void
    {
        if (! Schema::hasTable('pendidikans')) {
            return;
        }

        Pendidikan::updateOrCreate(
            [
                'id_posyandu' => $sasaran->id_posyandu,
                'id_sasaran' => $idSasaran,
                'kategori_sasaran' => $kategori,
            ],
            [
                'id_users' => Auth::id(),
                'nik' => $sasaran->nik_sasaran ?? null,
                'nama' => $sasaran->nama_sasaran ?? null,
                'tanggal_lahir' => $sasaran->tanggal_lahir ?? null,
                'jenis_kelamin' => $sasaran->jenis_kelamin ?? null,
                'umur' => $sasaran->umur_sasaran ?? null,
                'pendidikan_terakhir' => $pendidikan,
                'rt' => $sasaran->rt ?? null,
                'rw' => $sasaran->rw ?? null,
            ]
        );
    }
}
