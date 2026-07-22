<?php

namespace App\Helpers;

class ImunisasiOptions
{
    /**
     * Jadwal imunisasi dasar bayi/balita sesuai jadwal Nasional Kemenkes.
     *
     * @return array<string, array<int, string>>
     */
    public static function jadwalBayiBalita(): array
    {
        return [
            '< 24 Jam' => ['Hepatitis B (HB-0)'],
            '1 Bulan' => ['Polio Tetes 1 (OPV 1)', 'BCG'],
            '2 Bulan' => ['DPT-HB-Hib 1', 'Polio Tetes 2 (OPV 2)', 'PCV 1', 'RV 1'],
            '3 Bulan' => ['DPT-HB-Hib 2', 'Polio Tetes 3 (OPV 3)', 'PCV 2', 'RV 2'],
            '4 Bulan' => ['DPT-HB-Hib 3', 'Polio Tetes 4 (OPV 4)', 'Polio Suntik 1 (IPV 1)', 'RV 3'],
            '9 Bulan' => ['Campak Rubela 1', 'Polio Suntik 2 (IPV 2)'],
            '10 Bulan' => ['JE'],
            '12 Bulan' => ['PCV 3'],
            '18 Bulan' => ['DPT-HB-Hib 4', 'Campak Rubela 2'],
        ];
    }

    /**
     * Pemetaan nama lama ke nama standar jadwal Nasional.
     *
     * @return array<string, string>
     */
    public static function legacyNameMap(): array
    {
        return [
            // Hepatitis B lahir
            'Hepatitis 0' => 'Hepatitis B (HB-0)',
            'Hepatitis B 0' => 'Hepatitis B (HB-0)',
            'HB-0' => 'Hepatitis B (HB-0)',

            // Polio tetes (OPV)
            'Polio 1' => 'Polio Tetes 1 (OPV 1)',
            'OPV 1' => 'Polio Tetes 1 (OPV 1)',
            'Polio 2' => 'Polio Tetes 2 (OPV 2)',
            'OPV 2' => 'Polio Tetes 2 (OPV 2)',
            'Polio 3' => 'Polio Tetes 3 (OPV 3)',
            'OPV 3' => 'Polio Tetes 3 (OPV 3)',
            'Polio 4' => 'Polio Tetes 4 (OPV 4)',
            'OPV 4' => 'Polio Tetes 4 (OPV 4)',

            // Polio suntik (IPV)
            'IPV 1' => 'Polio Suntik 1 (IPV 1)',
            'IPV 2' => 'Polio Suntik 2 (IPV 2)',

            // Rotavirus
            'Rotarix 1' => 'RV 1',
            'Rotarix 2' => 'RV 2',
            'Rotavirus 1' => 'RV 1',
            'Rotavirus 2' => 'RV 2',
            'Rotavirus 3' => 'RV 3',

            // Campak Rubela
            'Campak MR' => 'Campak Rubela 1',
            'Campak 1' => 'Campak Rubela 1',
            'Campak Rubela' => 'Campak Rubela 1',
            'Campak MR Lanjutan' => 'Campak Rubela 2',
            'Campak 2' => 'Campak Rubela 2',
            'Campak Booster' => 'Campak Rubela 2',

            // DPT-HB-Hib lanjutan
            'DPT-HB-Hib Lanjutan' => 'DPT-HB-Hib 4',
            'DPT-HB-Hib Booster' => 'DPT-HB-Hib 4',
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
