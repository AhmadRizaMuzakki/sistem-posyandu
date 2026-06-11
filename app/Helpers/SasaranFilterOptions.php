<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class SasaranFilterOptions
{
    /**
     * Label kategori sasaran standar.
     *
     * @return array<string, string>
     */
    public static function kategoriLabels(): array
    {
        return [
            'bayibalita' => 'Bayi dan Balita',
            'remaja' => 'Remaja',
            'dewasa' => 'Dewasa',
            'pralansia' => 'Pralansia',
            'lansia' => 'Lansia',
            'ibuhamil' => 'Ibu Hamil',
        ];
    }

    /**
     * Opsi filter usia & tahun lahir (statis).
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function extendedOptions(): array
    {
        $options = [];

        $usiaRanges = [
            [0, 4],
            [5, 9],
            [10, 14],
            [15, 19],
            [20, 24],
            [25, 29],
            [30, 34],
            [35, 39],
            [40, 44],
            [45, 49],
            [50, 54],
            [55, 59],
            [60, 64],
            [65, 69],
            [70, 74],
        ];

        foreach ($usiaRanges as [$min, $max]) {
            $options[] = [
                'value' => "usia:{$min}-{$max}",
                'label' => "Usia {$min}-{$max} Tahun",
            ];
        }

        $options[] = [
            'value' => 'usia:75+',
            'label' => 'Usia 75 Tahun ke Atas',
        ];

        foreach ([2020, 2021, 2022, 2023, 2024] as $year) {
            $options[] = [
                'value' => "lahir:{$year}",
                'label' => "Lahir Tahun {$year}",
            ];
            $options[] = [
                'value' => "lahir:<{$year}",
                'label' => "Lahir Sebelum Tahun {$year}",
            ];
        }

        return $options;
    }

    public static function isKategori(string $filter): bool
    {
        return array_key_exists($filter, self::kategoriLabels());
    }

    public static function isUsiaFilter(string $filter): bool
    {
        return str_starts_with($filter, 'usia:');
    }

    public static function isLahirFilter(string $filter): bool
    {
        return str_starts_with($filter, 'lahir:');
    }

    public static function isExtendedFilter(string $filter): bool
    {
        return self::isUsiaFilter($filter) || self::isLahirFilter($filter);
    }

    public static function getLabel(?string $filter): string
    {
        if (! $filter) {
            return 'Semua Kategori';
        }

        if (self::isKategori($filter)) {
            return self::kategoriLabels()[$filter];
        }

        foreach (self::extendedOptions() as $option) {
            if ($option['value'] === $filter) {
                return $option['label'];
            }
        }

        return ucfirst($filter);
    }

    /**
     * Kategori imunisasi yang dipakai saat filter kategori klasik.
     *
     * @return array<int, string>
     */
    public static function imunisasiKategoris(): array
    {
        return ['bayibalita', 'remaja', 'dewasa', 'pralansia', 'lansia'];
    }

    /**
     * @return array<int, string>
     */
    public static function resolveImunisasiKategoris(?string $filter): array
    {
        $all = self::imunisasiKategoris();

        if (! $filter) {
            return $all;
        }

        if (self::isKategori($filter) && in_array($filter, $all, true)) {
            return [$filter];
        }

        if (self::isExtendedFilter($filter)) {
            return $all;
        }

        return $all;
    }

    public static function matchesTanggalLahir(mixed $tanggalLahir, string $filter): bool
    {
        if (! $tanggalLahir) {
            return false;
        }

        $date = $tanggalLahir instanceof Carbon
          ? $tanggalLahir->copy()
          : Carbon::parse($tanggalLahir);

        if (self::isUsiaFilter($filter)) {
            $age = $date->age;

            if ($filter === 'usia:75+') {
                return $age >= 75;
            }

            if (preg_match('/^usia:(\d+)-(\d+)$/', $filter, $matches)) {
                $min = (int) $matches[1];
                $max = (int) $matches[2];

                return $age >= $min && $age <= $max;
            }

            return false;
        }

        if (self::isLahirFilter($filter)) {
            $birthYear = (int) $date->format('Y');

            if (preg_match('/^lahir:<(\d{4})$/', $filter, $matches)) {
                return $birthYear < (int) $matches[1];
            }

            if (preg_match('/^lahir:(\d{4})$/', $filter, $matches)) {
                return $birthYear === (int) $matches[1];
            }

            return false;
        }

        return true;
    }

    public static function matchesSasaranFilter(mixed $sasaran, ?string $filter): bool
    {
        if (! $filter) {
            return true;
        }

        if (self::isExtendedFilter($filter)) {
            return self::matchesTanggalLahir($sasaran->tanggal_lahir ?? null, $filter);
        }

        return true;
    }

    public static function applyToPendidikanQuery(Builder $query, ?string $filter): Builder
    {
        if (! $filter) {
            return $query;
        }

        if (self::isKategori($filter)) {
            return $query->where('kategori_sasaran', $filter);
        }

        if (self::isExtendedFilter($filter)) {
            return $query->whereNotNull('tanggal_lahir')
                ->where(function (Builder $q) use ($filter) {
                    if (self::isUsiaFilter($filter)) {
                        self::applyUsiaWhere($q, $filter);
                    } elseif (self::isLahirFilter($filter)) {
                        self::applyLahirWhere($q, $filter);
                    }
                });
        }

        return $query->where('kategori_sasaran', $filter);
    }

    private static function applyUsiaWhere(Builder $query, string $filter): void
    {
        $today = Carbon::today();

        if ($filter === 'usia:75+') {
            $maxBirthDate = $today->copy()->subYears(75)->endOfDay();
            $query->whereDate('tanggal_lahir', '<=', $maxBirthDate->toDateString());

            return;
        }

        if (preg_match('/^usia:(\d+)-(\d+)$/', $filter, $matches)) {
            $minAge = (int) $matches[1];
            $maxAge = (int) $matches[2];
            $latestBirth = $today->copy()->subYears($minAge)->endOfDay();
            $earliestBirth = $today->copy()->subYears($maxAge + 1)->addDay()->startOfDay();
            $query->whereDate('tanggal_lahir', '<=', $latestBirth->toDateString())
                ->whereDate('tanggal_lahir', '>=', $earliestBirth->toDateString());
        }
    }

    private static function applyLahirWhere(Builder $query, string $filter): void
    {
        if (preg_match('/^lahir:<(\d{4})$/', $filter, $matches)) {
            $query->whereYear('tanggal_lahir', '<', (int) $matches[1]);

            return;
        }

        if (preg_match('/^lahir:(\d{4})$/', $filter, $matches)) {
            $query->whereYear('tanggal_lahir', (int) $matches[1]);
        }
    }
}
