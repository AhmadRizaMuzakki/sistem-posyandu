<?php

namespace App\Helpers;

use Carbon\Carbon;
use Closure;

class TanggalInput
{
    public const PLACEHOLDER = 'tanggal/bulan/tahun';

    public const DISPLAY_FORMAT = 'd/m/Y';

    public const STORAGE_FORMAT = 'Y-m-d';

    /**
     * Format nilai DB (Y-m-d / Carbon) ke tampilan d/m/Y.
     */
    public static function toDisplay(null|string|Carbon $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        try {
            $date = $value instanceof Carbon ? $value : Carbon::parse($value);

            return $date->format(self::DISPLAY_FORMAT);
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * Parse input d/m/Y menjadi Y-m-d. Mengembalikan null jika tidak valid.
     */
    public static function toYmd(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        // Normalisasi spasi; terima juga pemisah - atau .
        $value = str_replace(['-', '.', ' '], '/', $value);

        if (! preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $value, $matches)) {
            return null;
        }

        $day = (int) $matches[1];
        $month = (int) $matches[2];
        $year = (int) $matches[3];

        if (! checkdate($month, $day, $year)) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }

    /**
     * Aturan validasi Livewire/Laravel untuk input d/m/Y.
     *
     * @return array<int, mixed>
     */
    public static function rules(bool $required = true, ?int $minYear = 1900, ?int $maxYear = null): array
    {
        $maxYear ??= (int) date('Y');

        return array_values(array_filter([
            $required ? 'required' : 'nullable',
            'string',
            self::formatRule(),
            self::yearRangeRule($minYear, $maxYear),
        ]));
    }

    public static function formatRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if ($value === null || $value === '') {
                return;
            }

            if (self::toYmd((string) $value) === null) {
                $fail('Format tanggal harus tanggal/bulan/tahun, contoh: 08/09/2026.');
            }
        };
    }

    public static function yearRangeRule(?int $minYear, ?int $maxYear): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($minYear, $maxYear): void {
            $ymd = self::toYmd((string) $value);
            if ($ymd === null) {
                return;
            }

            $year = (int) substr($ymd, 0, 4);

            if ($minYear !== null && $year < $minYear) {
                $fail("Tahun minimal {$minYear}.");
            }

            if ($maxYear !== null && $year > $maxYear) {
                $fail("Tahun maksimal {$maxYear}.");
            }
        };
    }

    /**
     * @return array<string, string>
     */
    public static function messages(string $field, string $label = 'Tanggal'): array
    {
        return [
            "{$field}.required" => "{$label} wajib diisi.",
            "{$field}.string" => "{$label} tidak valid.",
        ];
    }

    /**
     * Nilai hari/bulan/tahun & Y-m-d untuk hari ini (default form).
     *
     * @return array{hari:int,bulan:int,tahun:int,ymd:string,display:string}
     */
    public static function todayParts(): array
    {
        $now = now();

        return [
            'hari' => (int) $now->day,
            'bulan' => (int) $now->month,
            'tahun' => (int) $now->year,
            'ymd' => $now->format(self::STORAGE_FORMAT),
            'display' => $now->format(self::DISPLAY_FORMAT),
        ];
    }
}
