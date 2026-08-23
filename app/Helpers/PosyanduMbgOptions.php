<?php

namespace App\Helpers;

class PosyanduMbgOptions
{
    public const AKTIF = 'aktif';

    public const TIDAK_AKTIF = 'tidak_aktif';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::AKTIF => 'Aktif MBG',
            self::TIDAK_AKTIF => 'Tidak Aktif MBG',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_keys(self::options());
    }

    public static function label(?string $status): string
    {
        if ($status === null || $status === '') {
            return '-';
        }

        return self::options()[$status] ?? '-';
    }

    public static function badgeClasses(?string $status): string
    {
        return match ($status) {
            self::AKTIF => 'bg-green-100 text-green-800',
            self::TIDAK_AKTIF => 'bg-gray-100 text-gray-700',
            default => 'bg-yellow-50 text-yellow-700',
        };
    }
}
