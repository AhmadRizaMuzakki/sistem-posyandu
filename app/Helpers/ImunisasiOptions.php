<?php

namespace App\Helpers;

class ImunisasiOptions
{
    /**
     * Jadwal imunisasi dasar bayi/balita (usia → jenis imunisasi).
     *
     * @return array<string, array<int, string>>
     */
    public static function jadwalBayiBalita(): array
    {
        return [
            '0 Bulan' => ['Hepatitis 0'],
            '1 Bulan' => ['BCG', 'Polio 1'],
            '2 Bulan' => ['DPT-HB-Hib 1', 'Polio 2', 'PCV 1', 'Rotarix 1'],
            '3 Bulan' => ['DPT-HB-Hib 2', 'Polio 3', 'PCV 2', 'Rotarix 2'],
            '4 Bulan' => ['DPT-HB-Hib 3', 'Polio 4', 'IPV 1'],
            '9 Bulan' => ['Campak MR', 'IPV 2'],
            '12 Bulan' => ['PCV 3'],
            '18 Bulan' => ['DPT-HB-Hib Lanjutan', 'Campak MR Lanjutan'],
        ];
    }

    /**
     * Pemetaan nama lama ke nama standar baru.
     *
     * @return array<string, string>
     */
    public static function legacyNameMap(): array
    {
        return [
            'Hepatitis B 0' => 'Hepatitis 0',
            'Campak 1' => 'Campak MR',
            'Campak 2' => 'Campak MR Lanjutan',
            'Campak Booster' => 'Campak MR Lanjutan',
            'DPT-HB-Hib Booster' => 'DPT-HB-Hib Lanjutan',
        ];
    }

    /**
     * Semua nilai opsi standar (untuk validasi tampilan data lama).
     *
     * @return array<int, string>
     */
    public static function allOptionValues(): array
    {
        $values = [];

        foreach (self::jadwalBayiBalita() as $items) {
            $values = array_merge($values, $items);
        }

        return array_values(array_unique($values));
    }

    /**
     * Nilai untuk filter/query — mencakup alias nama lama di database.
     *
     * @return array<int, string>
     */
    public static function valuesForFilter(string $jenisImunisasi): array
    {
        $values = [$jenisImunisasi];

        foreach (self::legacyNameMap() as $old => $new) {
            if ($new === $jenisImunisasi) {
                $values[] = $old;
            }
        }

        return array_values(array_unique($values));
    }
}
