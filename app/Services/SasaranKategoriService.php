<?php

namespace App\Services;

use App\Models\Imunisasi;
use App\Models\Pendidikan;
use App\Models\SasaranBayibalita;
use App\Models\SasaranDewasa;
use App\Models\SasaranLansia;
use App\Models\SasaranPralansia;
use App\Models\SasaranRemaja;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SasaranKategoriService
{
    private const KATEGORI_ORDER = ['bayibalita', 'remaja', 'dewasa', 'pralansia', 'lansia'];

    private const MODEL_MAP = [
        'bayibalita' => [SasaranBayibalita::class, 'id_sasaran_bayibalita'],
        'remaja' => [SasaranRemaja::class, 'id_sasaran_remaja'],
        'dewasa' => [SasaranDewasa::class, 'id_sasaran_dewasa'],
        'pralansia' => [SasaranPralansia::class, 'id_sasaran_pralansia'],
        'lansia' => [SasaranLansia::class, 'id_sasaran_lansia'],
    ];

    /**
     * Tentukan kategori sasaran berdasarkan umur (tahun).
     *
     * - 0–4 tahun: Bayi dan Balita
     * - 5–17 tahun: Remaja
     * - 18–44 tahun: Dewasa
     * - 45–59 tahun: Pralansia
     * - 60+ tahun: Lansia
     */
    public function getKategoriByAge(?int $ageYears): ?string
    {
        if ($ageYears === null) {
            return null;
        }

        if ($ageYears >= 60) {
            return 'lansia';
        }

        if ($ageYears >= 45) {
            return 'pralansia';
        }

        if ($ageYears >= 18) {
            return 'dewasa';
        }

        if ($ageYears >= 5) {
            return 'remaja';
        }

        return 'bayibalita';
    }

    public function getAgeYears(object $sasaran): ?int
    {
        if (! empty($sasaran->tanggal_lahir)) {
            return (int) Carbon::parse($sasaran->tanggal_lahir)->age;
        }

        if (! is_null($sasaran->umur_sasaran ?? null)) {
            return (int) $sasaran->umur_sasaran;
        }

        return null;
    }

    /**
     * Pindahkan sasaran ke kategori yang sesuai umur untuk satu posyandu.
     */
    public function syncForPosyandu(int $posyanduId): int
    {
        $migrated = 0;

        foreach (self::KATEGORI_ORDER as $currentKategori) {
            [$modelClass] = self::MODEL_MAP[$currentKategori];
            $sasarans = $modelClass::where('id_posyandu', $posyanduId)->get();

            foreach ($sasarans as $sasaran) {
                $targetKategori = $this->getKategoriByAge($this->getAgeYears($sasaran));

                if ($targetKategori === null || $targetKategori === $currentKategori) {
                    continue;
                }

                if ($this->migrate($sasaran, $currentKategori, $targetKategori)) {
                    $migrated++;
                }
            }
        }

        return $migrated;
    }

    /**
     * Sinkronisasi kategori sasaran di semua posyandu.
     */
    public function syncAll(): int
    {
        $posyanduIds = collect();

        foreach (self::MODEL_MAP as [$modelClass]) {
            $posyanduIds = $posyanduIds->merge(
                $modelClass::query()->distinct()->pluck('id_posyandu')
            );
        }

        $migrated = 0;

        foreach ($posyanduIds->unique()->filter() as $posyanduId) {
            $migrated += $this->syncForPosyandu((int) $posyanduId);
        }

        return $migrated;
    }

    private function migrate(Model $sasaran, string $fromKategori, string $toKategori): bool
    {
        return (bool) DB::transaction(function () use ($sasaran, $fromKategori, $toKategori) {
            [$targetModelClass, $targetPk] = self::MODEL_MAP[$toKategori];
            [$sourceModelClass, $sourcePk] = self::MODEL_MAP[$fromKategori];

            $oldId = $sasaran->getKey();
            $sourceData = $sasaran->getAttributes();

            $target = new $targetModelClass;
            $data = [];

            foreach ($target->getFillable() as $field) {
                if (array_key_exists($field, $sourceData)) {
                    $data[$field] = $sourceData[$field];
                }
            }

            $age = $this->getAgeYears($sasaran);

            if ($age !== null) {
                $data['umur_sasaran'] = $age;
            }

            $existing = null;

            if (! empty($data['nik_sasaran']) && ! empty($data['id_posyandu'])) {
                $existing = $targetModelClass::where('id_posyandu', $data['id_posyandu'])
                    ->where('nik_sasaran', $data['nik_sasaran'])
                    ->first();
            }

            if ($existing) {
                $existing->update($data);
                $newId = $existing->getKey();
            } else {
                $newId = $targetModelClass::create($data)->getKey();
            }

            Imunisasi::where('kategori_sasaran', $fromKategori)
                ->where('id_sasaran', $oldId)
                ->update([
                    'kategori_sasaran' => $toKategori,
                    'id_sasaran' => $newId,
                ]);

            Pendidikan::where('kategori_sasaran', $fromKategori)
                ->where('id_sasaran', $oldId)
                ->update([
                    'kategori_sasaran' => $toKategori,
                    'id_sasaran' => $newId,
                ]);

            $sourceModelClass::where($sourcePk, $oldId)->delete();

            return true;
        });
    }
}
