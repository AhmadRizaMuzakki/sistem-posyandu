<?php

namespace App\Helpers;

class AduanOptions
{
    public const STATUS_MENUNGGU = 'menunggu';

    public const STATUS_DIPROSES = 'diproses';

    public const STATUS_SELESAI = 'selesai';

    public const STATUS_DITOLAK = 'ditolak';

    public const SPM_KESEHATAN = 'kesehatan';

    public const SPM_PENDIDIKAN = 'pendidikan';

    public const SPM_PEKERJAAN_UMUM = 'pekerjaan_umum';

    public const SPM_PERUMAHAN_RAKYAT = 'perumahan_rakyat';

    public const SPM_TRANTIBUMLINMAS = 'trantibumlinmas';

    public const SPM_SOSIAL = 'sosial';

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_MENUNGGU => 'Menunggu',
            self::STATUS_DIPROSES => 'Diproses',
            self::STATUS_SELESAI => 'Selesai',
            self::STATUS_DITOLAK => 'Ditolak',
        ];
    }

    /**
     * 6 Standar Pelayanan Minimal (SPM) Posyandu — Permendagri No. 13 Tahun 2024.
     *
     * @return array<string, string>
     */
    public static function kategoriOptions(): array
    {
        return [
            self::SPM_KESEHATAN => 'Kesehatan',
            self::SPM_PENDIDIKAN => 'Pendidikan',
            self::SPM_PEKERJAAN_UMUM => 'Pekerjaan Umum',
            self::SPM_PERUMAHAN_RAKYAT => 'Perumahan Rakyat',
            self::SPM_TRANTIBUMLINMAS => 'Ketenteraman & Ketertiban Umum',
            self::SPM_SOSIAL => 'Sosial',
        ];
    }

    public static function statusLabel(?string $status): string
    {
        if ($status === null || $status === '') {
            return '-';
        }

        return self::statusOptions()[$status] ?? '-';
    }

    public static function kategoriLabel(?string $kategori): string
    {
        if ($kategori === null || $kategori === '') {
            return '-';
        }

        return self::kategoriOptions()[$kategori] ?? '-';
    }

    public static function statusBadgeClasses(?string $status): string
    {
        return match ($status) {
            self::STATUS_MENUNGGU => 'bg-amber-100 text-amber-800',
            self::STATUS_DIPROSES => 'bg-blue-100 text-blue-800',
            self::STATUS_SELESAI => 'bg-green-100 text-green-800',
            self::STATUS_DITOLAK => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    /**
     * Persentase progres aduan berdasarkan status (Opsi A).
     */
    public static function statusProgress(?string $status): int
    {
        return match ($status) {
            self::STATUS_MENUNGGU => 25,
            self::STATUS_DIPROSES => 50,
            self::STATUS_SELESAI => 100,
            self::STATUS_DITOLAK => 0,
            default => 0,
        };
    }

    public static function statusProgressBarClasses(?string $status): string
    {
        return match ($status) {
            self::STATUS_MENUNGGU => 'bg-amber-500',
            self::STATUS_DIPROSES => 'bg-blue-500',
            self::STATUS_SELESAI => 'bg-green-500',
            self::STATUS_DITOLAK => 'bg-red-500',
            default => 'bg-gray-400',
        };
    }

    public static function statusIcon(?string $status): string
    {
        return match ($status) {
            self::STATUS_MENUNGGU => 'ph-clock',
            self::STATUS_DIPROSES => 'ph-gear-six',
            self::STATUS_SELESAI => 'ph-check-circle',
            self::STATUS_DITOLAK => 'ph-x-circle',
            default => 'ph-megaphone',
        };
    }

    public static function statusCardBorderClass(?string $status): string
    {
        return match ($status) {
            self::STATUS_MENUNGGU => 'border-l-amber-400',
            self::STATUS_DIPROSES => 'border-l-blue-500',
            self::STATUS_SELESAI => 'border-l-green-500',
            self::STATUS_DITOLAK => 'border-l-red-500',
            default => 'border-l-gray-300',
        };
    }

    public static function statusIconWrapClasses(?string $status): string
    {
        return match ($status) {
            self::STATUS_MENUNGGU => 'bg-amber-50 text-amber-600 ring-amber-100',
            self::STATUS_DIPROSES => 'bg-blue-50 text-blue-600 ring-blue-100',
            self::STATUS_SELESAI => 'bg-green-50 text-green-600 ring-green-100',
            self::STATUS_DITOLAK => 'bg-red-50 text-red-600 ring-red-100',
            default => 'bg-gray-50 text-gray-500 ring-gray-100',
        };
    }

    public static function progressBarColorClass(int $progress): string
    {
        if ($progress >= 100) {
            return 'bg-green-500';
        }
        if ($progress >= 50) {
            return 'bg-blue-500';
        }
        if ($progress >= 25) {
            return 'bg-amber-500';
        }

        return 'bg-red-500';
    }

    /**
     * @param  iterable<int, object{status: string|null}>  $aduans
     */
    public static function averageProgress(iterable $aduans): int
    {
        $items = collect($aduans);
        if ($items->isEmpty()) {
            return 0;
        }

        return (int) round($items->avg(fn ($aduan) => self::statusProgress($aduan->status)));
    }

    public static function kategoriBadgeClasses(?string $kategori): string
    {
        return match ($kategori) {
            self::SPM_KESEHATAN => 'bg-teal-50 text-teal-700',
            self::SPM_PENDIDIKAN => 'bg-blue-50 text-blue-700',
            self::SPM_PEKERJAAN_UMUM => 'bg-amber-50 text-amber-700',
            self::SPM_PERUMAHAN_RAKYAT => 'bg-indigo-50 text-indigo-700',
            self::SPM_TRANTIBUMLINMAS => 'bg-purple-50 text-purple-700',
            self::SPM_SOSIAL => 'bg-rose-50 text-rose-700',
            default => 'bg-gray-50 text-gray-700',
        };
    }
}
