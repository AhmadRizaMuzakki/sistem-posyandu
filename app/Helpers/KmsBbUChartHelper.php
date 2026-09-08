<?php

namespace App\Helpers;

use App\Services\AntropometriService;
use Carbon\Carbon;

/**
 * Kartu Menuju Sehat (KMS) — grafik BB/U & TB/U dengan zona SD.
 * Kurva 0–11 bulan: WHO/PMK (melengkapi config yang mulai dari 12 bulan).
 * Kurva 12–60 bulan: config/antropometri.php.
 */
class KmsBbUChartHelper
{
    /** @var array<string, array<int, array{sd3_min: float, min: float, sd1_min: float, median: float, sd1_max: float, max: float, sd3_max: float}>> */
    private static array $infantCurvesBbU = [
        'L' => [
            0 => ['sd3_min' => 2.1, 'min' => 2.5, 'sd1_min' => 2.9, 'median' => 3.3, 'sd1_max' => 3.9, 'max' => 4.4, 'sd3_max' => 5.0],
            1 => ['sd3_min' => 2.9, 'min' => 3.4, 'sd1_min' => 3.9, 'median' => 4.5, 'sd1_max' => 5.1, 'max' => 5.8, 'sd3_max' => 6.6],
            2 => ['sd3_min' => 3.8, 'min' => 4.3, 'sd1_min' => 4.9, 'median' => 5.6, 'sd1_max' => 6.3, 'max' => 7.1, 'sd3_max' => 8.0],
            3 => ['sd3_min' => 4.4, 'min' => 5.0, 'sd1_min' => 5.7, 'median' => 6.4, 'sd1_max' => 7.2, 'max' => 8.0, 'sd3_max' => 9.0],
            4 => ['sd3_min' => 4.9, 'min' => 5.6, 'sd1_min' => 6.2, 'median' => 7.0, 'sd1_max' => 7.8, 'max' => 8.7, 'sd3_max' => 9.8],
            5 => ['sd3_min' => 5.3, 'min' => 6.0, 'sd1_min' => 6.7, 'median' => 7.5, 'sd1_max' => 8.4, 'max' => 9.3, 'sd3_max' => 10.5],
            6 => ['sd3_min' => 5.7, 'min' => 6.4, 'sd1_min' => 7.1, 'median' => 7.9, 'sd1_max' => 8.8, 'max' => 9.8, 'sd3_max' => 11.0],
            7 => ['sd3_min' => 5.9, 'min' => 6.7, 'sd1_min' => 7.4, 'median' => 8.3, 'sd1_max' => 9.2, 'max' => 10.3, 'sd3_max' => 11.4],
            8 => ['sd3_min' => 6.2, 'min' => 6.9, 'sd1_min' => 7.7, 'median' => 8.6, 'sd1_max' => 9.6, 'max' => 10.7, 'sd3_max' => 11.9],
            9 => ['sd3_min' => 6.4, 'min' => 7.1, 'sd1_min' => 8.0, 'median' => 8.9, 'sd1_max' => 9.9, 'max' => 11.0, 'sd3_max' => 12.3],
            10 => ['sd3_min' => 6.6, 'min' => 7.4, 'sd1_min' => 8.2, 'median' => 9.2, 'sd1_max' => 10.2, 'max' => 11.4, 'sd3_max' => 12.7],
            11 => ['sd3_min' => 6.8, 'min' => 7.6, 'sd1_min' => 8.4, 'median' => 9.4, 'sd1_max' => 10.5, 'max' => 11.7, 'sd3_max' => 13.0],
        ],
        'P' => [
            0 => ['sd3_min' => 2.0, 'min' => 2.4, 'sd1_min' => 2.8, 'median' => 3.2, 'sd1_max' => 3.7, 'max' => 4.2, 'sd3_max' => 4.8],
            1 => ['sd3_min' => 2.7, 'min' => 3.2, 'sd1_min' => 3.6, 'median' => 4.2, 'sd1_max' => 4.8, 'max' => 5.5, 'sd3_max' => 6.2],
            2 => ['sd3_min' => 3.4, 'min' => 3.9, 'sd1_min' => 4.5, 'median' => 5.1, 'sd1_max' => 5.8, 'max' => 6.6, 'sd3_max' => 7.5],
            3 => ['sd3_min' => 4.0, 'min' => 4.5, 'sd1_min' => 5.2, 'median' => 5.8, 'sd1_max' => 6.6, 'max' => 7.5, 'sd3_max' => 8.5],
            4 => ['sd3_min' => 4.4, 'min' => 5.0, 'sd1_min' => 5.7, 'median' => 6.4, 'sd1_max' => 7.3, 'max' => 8.2, 'sd3_max' => 9.3],
            5 => ['sd3_min' => 4.8, 'min' => 5.4, 'sd1_min' => 6.1, 'median' => 6.9, 'sd1_max' => 7.8, 'max' => 8.8, 'sd3_max' => 10.0],
            6 => ['sd3_min' => 5.1, 'min' => 5.7, 'sd1_min' => 6.5, 'median' => 7.3, 'sd1_max' => 8.2, 'max' => 9.3, 'sd3_max' => 10.6],
            7 => ['sd3_min' => 5.3, 'min' => 6.0, 'sd1_min' => 6.8, 'median' => 7.6, 'sd1_max' => 8.6, 'max' => 9.8, 'sd3_max' => 11.1],
            8 => ['sd3_min' => 5.6, 'min' => 6.3, 'sd1_min' => 7.0, 'median' => 7.9, 'sd1_max' => 9.0, 'max' => 10.2, 'sd3_max' => 11.6],
            9 => ['sd3_min' => 5.8, 'min' => 6.5, 'sd1_min' => 7.3, 'median' => 8.2, 'sd1_max' => 9.3, 'max' => 10.5, 'sd3_max' => 12.0],
            10 => ['sd3_min' => 5.9, 'min' => 6.7, 'sd1_min' => 7.5, 'median' => 8.5, 'sd1_max' => 9.6, 'max' => 10.9, 'sd3_max' => 12.4],
            11 => ['sd3_min' => 6.1, 'min' => 6.9, 'sd1_min' => 7.7, 'median' => 8.7, 'sd1_max' => 9.9, 'max' => 11.2, 'sd3_max' => 12.8],
        ],
    ];

    /** @var array<string, array<int, array{sd3_min: float, min: float, sd1_min: float, median: float, sd1_max: float, max: float, sd3_max: float}>> */
    private static array $infantCurvesTbU = [
        'L' => [
            0 => ['sd3_min' => 44.2, 'min' => 46.1, 'sd1_min' => 48.0, 'median' => 49.9, 'sd1_max' => 51.8, 'max' => 53.7, 'sd3_max' => 55.6],
            1 => ['sd3_min' => 48.9, 'min' => 50.8, 'sd1_min' => 52.8, 'median' => 54.7, 'sd1_max' => 56.7, 'max' => 58.6, 'sd3_max' => 60.6],
            2 => ['sd3_min' => 52.4, 'min' => 54.4, 'sd1_min' => 56.4, 'median' => 58.4, 'sd1_max' => 60.4, 'max' => 62.4, 'sd3_max' => 64.4],
            3 => ['sd3_min' => 55.3, 'min' => 57.3, 'sd1_min' => 59.4, 'median' => 61.4, 'sd1_max' => 63.5, 'max' => 65.5, 'sd3_max' => 67.6],
            4 => ['sd3_min' => 57.6, 'min' => 59.7, 'sd1_min' => 61.8, 'median' => 63.9, 'sd1_max' => 66.0, 'max' => 68.0, 'sd3_max' => 70.1],
            5 => ['sd3_min' => 59.6, 'min' => 61.7, 'sd1_min' => 63.8, 'median' => 65.9, 'sd1_max' => 68.0, 'max' => 70.1, 'sd3_max' => 72.2],
            6 => ['sd3_min' => 61.2, 'min' => 63.3, 'sd1_min' => 65.5, 'median' => 67.6, 'sd1_max' => 69.8, 'max' => 71.9, 'sd3_max' => 74.0],
            7 => ['sd3_min' => 62.7, 'min' => 64.8, 'sd1_min' => 67.0, 'median' => 69.2, 'sd1_max' => 71.3, 'max' => 73.5, 'sd3_max' => 75.7],
            8 => ['sd3_min' => 64.0, 'min' => 66.2, 'sd1_min' => 68.4, 'median' => 70.6, 'sd1_max' => 72.8, 'max' => 75.0, 'sd3_max' => 77.2],
            9 => ['sd3_min' => 65.2, 'min' => 67.5, 'sd1_min' => 69.7, 'median' => 72.0, 'sd1_max' => 74.2, 'max' => 76.5, 'sd3_max' => 78.7],
            10 => ['sd3_min' => 66.4, 'min' => 68.7, 'sd1_min' => 71.0, 'median' => 73.3, 'sd1_max' => 75.6, 'max' => 77.9, 'sd3_max' => 80.1],
            11 => ['sd3_min' => 67.6, 'min' => 69.9, 'sd1_min' => 72.2, 'median' => 74.5, 'sd1_max' => 76.8, 'max' => 79.2, 'sd3_max' => 81.5],
        ],
        'P' => [
            0 => ['sd3_min' => 43.6, 'min' => 45.4, 'sd1_min' => 47.3, 'median' => 49.1, 'sd1_max' => 51.0, 'max' => 52.9, 'sd3_max' => 54.7],
            1 => ['sd3_min' => 47.8, 'min' => 49.8, 'sd1_min' => 51.7, 'median' => 53.7, 'sd1_max' => 55.6, 'max' => 57.6, 'sd3_max' => 59.5],
            2 => ['sd3_min' => 51.0, 'min' => 53.0, 'sd1_min' => 55.0, 'median' => 57.1, 'sd1_max' => 59.1, 'max' => 61.1, 'sd3_max' => 63.2],
            3 => ['sd3_min' => 53.5, 'min' => 55.6, 'sd1_min' => 57.7, 'median' => 59.8, 'sd1_max' => 61.9, 'max' => 64.0, 'sd3_max' => 66.1],
            4 => ['sd3_min' => 55.6, 'min' => 57.8, 'sd1_min' => 59.9, 'median' => 62.1, 'sd1_max' => 64.3, 'max' => 66.4, 'sd3_max' => 68.6],
            5 => ['sd3_min' => 57.4, 'min' => 59.6, 'sd1_min' => 61.8, 'median' => 64.0, 'sd1_max' => 66.2, 'max' => 68.5, 'sd3_max' => 70.7],
            6 => ['sd3_min' => 58.9, 'min' => 61.2, 'sd1_min' => 63.5, 'median' => 65.7, 'sd1_max' => 68.0, 'max' => 70.3, 'sd3_max' => 72.5],
            7 => ['sd3_min' => 60.3, 'min' => 62.7, 'sd1_min' => 65.0, 'median' => 67.3, 'sd1_max' => 69.6, 'max' => 71.9, 'sd3_max' => 74.2],
            8 => ['sd3_min' => 61.7, 'min' => 64.0, 'sd1_min' => 66.4, 'median' => 68.7, 'sd1_max' => 71.1, 'max' => 73.5, 'sd3_max' => 75.8],
            9 => ['sd3_min' => 62.9, 'min' => 65.3, 'sd1_min' => 67.7, 'median' => 70.1, 'sd1_max' => 72.6, 'max' => 75.0, 'sd3_max' => 77.4],
            10 => ['sd3_min' => 64.1, 'min' => 66.5, 'sd1_min' => 69.0, 'median' => 71.5, 'sd1_max' => 73.9, 'max' => 76.4, 'sd3_max' => 78.9],
            11 => ['sd3_min' => 65.2, 'min' => 67.7, 'sd1_min' => 70.3, 'median' => 72.8, 'sd1_max' => 75.3, 'max' => 77.8, 'sd3_max' => 80.3],
        ],
    ];

    /**
     * @param  array<int, array{umur_bulan: int|float, berat: float, tanggal?: string|null}>  $points
     * @return array<string, mixed>|null
     */
    public static function buildPayload(
        string $nama,
        ?string $jenisKelamin,
        ?Carbon $tanggalLahir,
        array $points,
        string $kategoriSlug = 'bayibalita'
    ): ?array {
        $nilaiPoints = [];
        foreach ($points as $point) {
            if (! isset($point['berat'], $point['umur_bulan'])) {
                continue;
            }
            $nilaiPoints[] = [
                'umur_bulan' => $point['umur_bulan'],
                'nilai' => $point['berat'],
                'tanggal' => $point['tanggal'] ?? null,
            ];
        }

        return self::buildIndeksPayload(
            'bb_u',
            $nama,
            $jenisKelamin,
            $tanggalLahir,
            $nilaiPoints,
            $kategoriSlug
        );
    }

    /**
     * @param  array<int, array{umur_bulan: int|float, tinggi: float, tanggal?: string|null}>  $points
     * @return array<string, mixed>|null
     */
    public static function buildTbUPayload(
        string $nama,
        ?string $jenisKelamin,
        ?Carbon $tanggalLahir,
        array $points,
        string $kategoriSlug = 'bayibalita'
    ): ?array {
        $nilaiPoints = [];
        foreach ($points as $point) {
            if (! isset($point['tinggi'], $point['umur_bulan'])) {
                continue;
            }
            $nilaiPoints[] = [
                'umur_bulan' => $point['umur_bulan'],
                'nilai' => $point['tinggi'],
                'tanggal' => $point['tanggal'] ?? null,
            ];
        }

        return self::buildIndeksPayload(
            'tb_u',
            $nama,
            $jenisKelamin,
            $tanggalLahir,
            $nilaiPoints,
            $kategoriSlug
        );
    }

    /**
     * @param  array<int, array{umur_bulan: int|float, nilai: float, tanggal?: string|null}>  $points
     * @return array<string, mixed>|null
     */
    private static function buildIndeksPayload(
        string $indeks,
        string $nama,
        ?string $jenisKelamin,
        ?Carbon $tanggalLahir,
        array $points,
        string $kategoriSlug
    ): ?array {
        if ($kategoriSlug !== 'bayibalita' || $tanggalLahir === null || empty($points)) {
            return null;
        }

        $antropometri = app(AntropometriService::class);
        $jk = $antropometri->normalizeJenisKelamin($jenisKelamin);
        $jkLabel = $antropometri->labelJenisKelamin($jk);

        $validPoints = [];
        foreach ($points as $point) {
            if (! isset($point['nilai'], $point['umur_bulan'])) {
                continue;
            }
            $umur = (float) $point['umur_bulan'];
            $nilai = (float) $point['nilai'];
            if ($umur < 0 || $nilai <= 0) {
                continue;
            }
            $row = [
                'umur_bulan' => round($umur, 1),
                'nilai' => round($nilai, 2),
                'tanggal' => $point['tanggal'] ?? null,
            ];
            // Kompatibilitas callout/chart lama
            if ($indeks === 'bb_u') {
                $row['berat'] = $row['nilai'];
            } else {
                $row['tinggi'] = $row['nilai'];
            }
            $validPoints[] = $row;
        }

        if ($validPoints === []) {
            return null;
        }

        usort($validPoints, fn ($a, $b) => $a['umur_bulan'] <=> $b['umur_bulan']);

        $maxAge = (float) max(array_column($validPoints, 'umur_bulan'));
        if ($maxAge > 60) {
            return null;
        }

        $curves = self::curvesFor($jk, $indeks);
        $yMaxCurve = max(array_column($curves['sd3_max'], 'y'));
        $yMaxData = max(array_column($validPoints, 'nilai'));
        $lahirLabel = $tanggalLahir->locale('id')->translatedFormat('d F Y');

        if ($indeks === 'tb_u') {
            $yMin = 40;
            $yMax = (int) max(130, ceil(max($yMaxCurve, $yMaxData) / 5) * 5);
            $unit = 'cm';
            $yAxis = 'Tinggi Badan (cm)';
            $mode = 'kms_tb_u';
            $title = 'Kartu Menuju Sehat (KMS) - Tinggi Badan Menurut Umur (TB/U)';
            $legend = [
                ['color' => '#fecaca', 'range' => '< −3 SD', 'status' => 'Sangat Pendek'],
                ['color' => '#fde047', 'range' => '−3 s/d −2 SD', 'status' => 'Pendek'],
                ['color' => '#bbf7d0', 'range' => '−2 s/d −1 & +1 s/d +2', 'status' => 'Normal'],
                ['color' => '#4ade80', 'range' => '−1 s/d +1 SD', 'status' => 'Normal Ideal'],
                ['color' => '#fdba74', 'range' => '> +2 SD', 'status' => 'Tinggi'],
            ];
            $pdfLegend = [
                ['color' => '#fecaca', 'label' => '< -3 SD (Sangat Pendek / Merah)'],
                ['color' => '#fde047', 'label' => '-3 s/d -2 SD (Pendek / Kuning)'],
                ['color' => '#bbf7d0', 'label' => '-2 s/d -1 & +1 s/d +2 SD (Normal / Hijau Muda)'],
                ['color' => '#4ade80', 'label' => '-1 s/d +1 SD (Normal Ideal / Hijau Tua)'],
                ['color' => '#fdba74', 'label' => '> +2 SD (Tinggi / Oranye)'],
            ];
        } else {
            $yMin = 2;
            $yMax = (int) max(26, ceil(max($yMaxCurve, $yMaxData) / 2) * 2);
            $unit = 'kg';
            $yAxis = 'Berat Badan (kg)';
            $mode = 'kms_bb_u';
            $title = 'Kartu Menuju Sehat (KMS) - Berat Badan Menurut Umur (BB/U)';
            $legend = [
                ['color' => '#fecaca', 'range' => '< −3 SD', 'status' => 'Sangat Kurang'],
                ['color' => '#fde047', 'range' => '−3 s/d −2 SD', 'status' => 'BB Kurang'],
                ['color' => '#bbf7d0', 'range' => '−2 s/d −1 & +1 s/d +2', 'status' => 'Normal'],
                ['color' => '#4ade80', 'range' => '−1 s/d +1 SD', 'status' => 'Normal Ideal'],
                ['color' => '#fdba74', 'range' => '> +2 SD', 'status' => 'Risiko Lebih'],
            ];
            $pdfLegend = [
                ['color' => '#fecaca', 'label' => '< -3 SD (BB Sangat Kurang / Merah)'],
                ['color' => '#fde047', 'label' => '-3 s/d -2 SD (BB Kurang / Kuning)'],
                ['color' => '#bbf7d0', 'label' => '-2 s/d -1 & +1 s/d +2 SD (Normal / Hijau Muda)'],
                ['color' => '#4ade80', 'label' => '-1 s/d +1 SD (Normal Ideal / Hijau Tua)'],
                ['color' => '#fdba74', 'label' => '> +2 SD (Risiko BB Lebih / Oranye)'],
            ];
        }

        return [
            'mode' => $mode,
            'indeks' => $indeks,
            'unit' => $unit,
            'y_axis_label' => $yAxis,
            'title' => $title,
            'subtitle' => sprintf('%s (%s | Lahir: %s)', $nama, $jkLabel, $lahirLabel),
            'nama' => $nama,
            'jenis_kelamin' => $jkLabel,
            'jenis_kelamin_kode' => $jk,
            'tanggal_lahir' => $tanggalLahir->format('d/m/Y'),
            'tanggal_lahir_label' => $lahirLabel,
            'x_min' => 0,
            'x_max' => 60,
            'y_min' => $yMin,
            'y_max' => $yMax,
            'curves' => $curves,
            'points' => $validPoints,
            'legend' => $legend,
            'pdf_legend' => $pdfLegend,
        ];
    }

    /**
     * @return array<string, array<int, array{x: int, y: float}>>
     */
    public static function curvesFor(string $jk, string $indeks = 'bb_u'): array
    {
        $jk = $jk === 'P' ? 'P' : 'L';
        $indeks = $indeks === 'tb_u' ? 'tb_u' : 'bb_u';
        $config = config('antropometri.'.$indeks.'.'.$jk, []);
        $infant = $indeks === 'tb_u' ? self::$infantCurvesTbU : self::$infantCurvesBbU;
        $keys = ['sd3_min', 'min', 'sd1_min', 'median', 'sd1_max', 'max', 'sd3_max'];
        $out = array_fill_keys($keys, []);

        for ($bulan = 0; $bulan <= 60; $bulan++) {
            $row = null;
            if ($bulan <= 11) {
                $row = $infant[$jk][$bulan] ?? null;
            } else {
                $row = $config[$bulan] ?? null;
            }
            if ($row === null) {
                continue;
            }
            foreach ($keys as $key) {
                $out[$key][] = ['x' => $bulan, 'y' => (float) $row[$key]];
            }
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function pdfDataUri(array $payload, int $width = 1600, int $height = 1000): ?string
    {
        if (! function_exists('imagecreatetruecolor') || ! in_array(($payload['mode'] ?? ''), ['kms_bb_u', 'kms_tb_u'], true)) {
            return null;
        }

        $img = imagecreatetruecolor($width, $height);
        if ($img === false) {
            return null;
        }

        imagealphablending($img, true);
        imagesavealpha($img, true);

        $white = imagecolorallocate($img, 255, 255, 255);
        $dark = imagecolorallocate($img, 17, 24, 39);
        $muted = imagecolorallocate($img, 75, 85, 99);
        $grid = imagecolorallocate($img, 209, 213, 219);
        $gridSoft = imagecolorallocate($img, 243, 244, 246);
        $medianColor = imagecolorallocate($img, 22, 101, 52);
        $childColor = imagecolorallocate($img, 37, 99, 235);
        $boundary = imagecolorallocate($img, 120, 53, 15);
        $calloutBorder = imagecolorallocate($img, 37, 99, 235);
        $calloutBg = imagecolorallocate($img, 239, 246, 255);
        $legendBorder = imagecolorallocate($img, 156, 163, 175);

        imagefilledrectangle($img, 0, 0, $width, $height, $white);

        $font = self::fontPath();
        $padL = 78;
        $padR = 100;
        $padT = 128;
        $padB = 72;
        $chartW = $width - $padL - $padR;
        $chartH = $height - $padT - $padB;

        $xMin = (float) ($payload['x_min'] ?? 0);
        $xMax = (float) ($payload['x_max'] ?? 60);
        $yMin = (float) ($payload['y_min'] ?? 2);
        $yMax = (float) ($payload['y_max'] ?? 26);
        $xSpan = max(0.001, $xMax - $xMin);
        $ySpan = max(0.001, $yMax - $yMin);

        $mapX = static fn (float $x) => (int) round($padL + (($x - $xMin) / $xSpan) * $chartW);
        $mapY = static fn (float $y) => (int) round($padT + $chartH - (($y - $yMin) / $ySpan) * $chartH);

        $curves = $payload['curves'] ?? [];
        $zonePairs = [
            ['bottom' => $yMin, 'topKey' => 'sd3_min', 'rgb' => [254, 202, 202]],
            ['bottomKey' => 'sd3_min', 'topKey' => 'min', 'rgb' => [254, 240, 138]],
            ['bottomKey' => 'min', 'topKey' => 'sd1_min', 'rgb' => [187, 247, 208]],
            ['bottomKey' => 'sd1_min', 'topKey' => 'sd1_max', 'rgb' => [74, 222, 128]],
            ['bottomKey' => 'sd1_max', 'topKey' => 'max', 'rgb' => [187, 247, 208]],
            ['bottomKey' => 'max', 'top' => $yMax, 'rgb' => [253, 186, 116]],
        ];

        foreach ($zonePairs as $zone) {
            $bottomSeries = isset($zone['bottomKey']) ? ($curves[$zone['bottomKey']] ?? []) : null;
            $topSeries = isset($zone['topKey']) ? ($curves[$zone['topKey']] ?? []) : null;
            $points = [];

            if ($topSeries) {
                foreach ($topSeries as $pt) {
                    $points[] = $mapX((float) $pt['x']);
                    $points[] = $mapY((float) $pt['y']);
                }
            } else {
                $points[] = $mapX($xMin);
                $points[] = $mapY((float) $zone['top']);
                $points[] = $mapX($xMax);
                $points[] = $mapY((float) $zone['top']);
            }

            if ($bottomSeries) {
                for ($i = count($bottomSeries) - 1; $i >= 0; $i--) {
                    $pt = $bottomSeries[$i];
                    $points[] = $mapX((float) $pt['x']);
                    $points[] = $mapY((float) $pt['y']);
                }
            } else {
                $points[] = $mapX($xMax);
                $points[] = $mapY((float) $zone['bottom']);
                $points[] = $mapX($xMin);
                $points[] = $mapY((float) $zone['bottom']);
            }

            if (count($points) >= 6) {
                $color = imagecolorallocatealpha($img, $zone['rgb'][0], $zone['rgb'][1], $zone['rgb'][2], 40);
                imagefilledpolygon($img, $points, $color);
            }
        }

        // Grid
        $yStep = (($payload['indeks'] ?? 'bb_u') === 'tb_u') ? 5 : 2;
        for ($val = (int) $yMin; $val <= (int) $yMax; $val += $yStep) {
            $y = $mapY((float) $val);
            imageline($img, $padL, $y, $padL + $chartW, $y, $val % ($yStep * 2) === 0 ? $grid : $gridSoft);
            self::text($img, $font, 11, $padL - 40, $y + 4, (string) $val, $muted);
        }
        for ($bln = 0; $bln <= 60; $bln += 6) {
            $x = $mapX((float) $bln);
            imageline($img, $x, $padT, $x, $padT + $chartH, $bln % 12 === 0 ? $grid : $gridSoft);
            self::text($img, $font, 11, $x - ($bln >= 10 ? 8 : 4), $padT + $chartH + 20, (string) $bln, $muted);
        }

        // Boundary dashed curves
        $boundaryKeys = ['sd3_min', 'min', 'sd1_min', 'sd1_max', 'max', 'sd3_max'];
        if (function_exists('imagesetstyle')) {
            $dash = [$boundary, $boundary, $boundary, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT];
            imagesetstyle($img, $dash);
        }
        foreach ($boundaryKeys as $key) {
            self::drawPolyline($img, $curves[$key] ?? [], $mapX, $mapY, IMG_COLOR_STYLED, 1);
        }

        // Median tebal
        if (function_exists('imagesetthickness')) {
            imagesetthickness($img, 3);
        }
        self::drawPolyline($img, $curves['median'] ?? [], $mapX, $mapY, $medianColor, 3);
        if (function_exists('imagesetthickness')) {
            imagesetthickness($img, 1);
        }

        // Data anak
        $unit = (string) ($payload['unit'] ?? 'kg');
        $yAxisLabel = (string) ($payload['y_axis_label'] ?? 'Berat Badan (kg)');
        $childPts = $payload['points'] ?? [];
        $mapped = [];
        foreach ($childPts as $pt) {
            $nilai = $pt['nilai'] ?? ($pt['berat'] ?? ($pt['tinggi'] ?? null));
            if ($nilai === null) {
                continue;
            }
            $mapped[] = [
                'x' => $mapX((float) $pt['umur_bulan']),
                'y' => $mapY((float) $nilai),
                'umur' => $pt['umur_bulan'],
                'nilai' => $nilai,
                'tanggal' => $pt['tanggal'] ?? null,
            ];
        }
        if (function_exists('imagesetthickness')) {
            imagesetthickness($img, 3);
        }
        for ($i = 1; $i < count($mapped); $i++) {
            imageline($img, $mapped[$i - 1]['x'], $mapped[$i - 1]['y'], $mapped[$i]['x'], $mapped[$i]['y'], $childColor);
        }
        if (function_exists('imagesetthickness')) {
            imagesetthickness($img, 1);
        }
        foreach ($mapped as $pt) {
            imagefilledellipse($img, $pt['x'], $pt['y'], 13, 13, $childColor);
            imageellipse($img, $pt['x'], $pt['y'], 13, 13, $white);
        }

        // Callout: nilai utama ditonjolkan
        $callouts = array_slice($mapped, -3);
        $offsetY = [-64, -86, -50];
        $offsetX = [-50, 8, 40];
        $fontBold = self::fontBoldPath() ?? $font;
        foreach ($callouts as $i => $pt) {
            $nilaiText = number_format((float) $pt['nilai'], 1, '.', '').' '.$unit;
            $metaText = ((int) round((float) $pt['umur'])).' bln';
            if (! empty($pt['tanggal'])) {
                $metaText .= ' · '.$pt['tanggal'];
            }

            $boxW = max(92, (int) (strlen($metaText) * 6.4) + 20);
            $boxH = 40;
            $bx = min(
                $padL + $chartW - $boxW - 6,
                max($padL + 6, $pt['x'] - (int) ($boxW / 2) + ($offsetX[$i % 3]))
            );
            $by = max($padT + 6, $pt['y'] + ($offsetY[$i % 3]));
            if ($by + $boxH + 10 > $pt['y']) {
                $by = max($padT + 6, $pt['y'] - $boxH - 18);
            }
            imagefilledrectangle($img, $bx, $by, $bx + $boxW, $by + $boxH, $calloutBg);
            imagerectangle($img, $bx, $by, $bx + $boxW, $by + $boxH, $calloutBorder);
            imageline($img, (int) (($bx + $bx + $boxW) / 2), $by + $boxH, $pt['x'], $pt['y'] - 7, $calloutBorder);
            self::text($img, $fontBold, 13, $bx + 10, $by + 17, $nilaiText, $childColor);
            self::text($img, $font, 9, $bx + 10, $by + 32, $metaText, $muted);
        }

        // Label SD kanan
        $endLabels = [
            'sd3_max' => ['+3 SD', [220, 38, 38]],
            'max' => ['+2 SD', [234, 88, 12]],
            'median' => ['0 (Median)', [22, 101, 52]],
            'min' => ['-2 SD', [202, 138, 4]],
            'sd3_min' => ['-3 SD', [220, 38, 38]],
        ];
        foreach ($endLabels as $key => [$text, $rgb]) {
            $series = $curves[$key] ?? [];
            if ($series === []) {
                continue;
            }
            $last = $series[count($series) - 1];
            $color = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
            self::text($img, $font, 11, $mapX((float) $last['x']) + 8, $mapY((float) $last['y']) + 4, $text, $color);
        }

        // Axes
        imageline($img, $padL, $padT, $padL, $padT + $chartH, $dark);
        imageline($img, $padL, $padT + $chartH, $padL + $chartW, $padT + $chartH, $dark);
        self::textCentered($img, $font, 13, (int) ($width / 2), $height - 18, 'Umur (Bulan)', $dark);
        self::textRotated($img, $font, 13, 24, (int) ($height / 2) + 55, $yAxisLabel, $dark);

        // Judul tengah (mirip foto)
        $title = (string) ($payload['title'] ?? 'Kartu Menuju Sehat (KMS) - Berat Badan Menurut Umur (BB/U)');
        $subtitle = (string) ($payload['subtitle'] ?? '');
        self::textCentered($img, $font, 17, (int) ($width / 2), 28, $title, $dark);
        if ($subtitle !== '') {
            self::textCentered($img, $font, 12, (int) ($width / 2), 50, $subtitle, $muted);
        }

        // Legend kotak kiri atas (mirip foto)
        $pdfLegend = $payload['pdf_legend'] ?? [];
        if ($pdfLegend === [] && ! empty($payload['legend'])) {
            foreach ($payload['legend'] as $item) {
                $pdfLegend[] = [
                    'color' => $item['color'] ?? '#cccccc',
                    'label' => trim(($item['range'] ?? '').' '.($item['status'] ?? ($item['label'] ?? ''))),
                ];
            }
        }

        $legendRows = count($pdfLegend) + 1;
        $legendBoxW = 360;
        $legendBoxH = 18 + ($legendRows * 18);
        $legendX = $padL + 8;
        $legendY = $padT + 8;
        imagefilledrectangle($img, $legendX, $legendY, $legendX + $legendBoxW, $legendY + $legendBoxH, $white);
        imagerectangle($img, $legendX, $legendY, $legendX + $legendBoxW, $legendY + $legendBoxH, $legendBorder);

        $ly = $legendY + 16;
        foreach ($pdfLegend as $item) {
            $hex = ltrim((string) ($item['color'] ?? '#cccccc'), '#');
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            $c = imagecolorallocate($img, $r, $g, $b);
            imagefilledrectangle($img, $legendX + 10, $ly - 9, $legendX + 22, $ly + 3, $c);
            imagerectangle($img, $legendX + 10, $ly - 9, $legendX + 22, $ly + 3, $muted);
            self::text($img, $font, 10, $legendX + 28, $ly, (string) ($item['label'] ?? ''), $dark);
            $ly += 18;
        }

        imageline($img, $legendX + 10, $ly - 4, $legendX + 28, $ly - 4, $childColor);
        imagefilledellipse($img, $legendX + 19, $ly - 4, 8, 8, $childColor);
        $childName = (string) ($payload['nama'] ?? 'Data Anak');
        self::text($img, $font, 10, $legendX + 34, $ly, 'Data Anak ('.$childName.')', $dark);

        ob_start();
        imagepng($img);
        $png = ob_get_clean();
        imagedestroy($img);

        if ($png === false || $png === '') {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode($png);
    }

    /**
     * @param  array<int, array{x: int|float, y: float}>  $series
     * @param  callable(float): int  $mapX
     * @param  callable(float): int  $mapY
     */
    private static function drawPolyline($img, array $series, callable $mapX, callable $mapY, int $color, int $thickness = 1): void
    {
        if (count($series) < 2) {
            return;
        }
        if (function_exists('imagesetthickness') && $thickness > 1 && $color !== IMG_COLOR_STYLED) {
            imagesetthickness($img, $thickness);
        }
        for ($i = 1; $i < count($series); $i++) {
            imageline(
                $img,
                $mapX((float) $series[$i - 1]['x']),
                $mapY((float) $series[$i - 1]['y']),
                $mapX((float) $series[$i]['x']),
                $mapY((float) $series[$i]['y']),
                $color
            );
        }
        if (function_exists('imagesetthickness') && $thickness > 1 && $color !== IMG_COLOR_STYLED) {
            imagesetthickness($img, 1);
        }
    }

    private static function fontPath(): ?string
    {
        $candidates = [
            'C:\\Windows\\Fonts\\segoeui.ttf',
            'C:\\Windows\\Fonts\\arial.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
        ];
        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    private static function fontBoldPath(): ?string
    {
        $candidates = [
            'C:\\Windows\\Fonts\\segoeuib.ttf',
            'C:\\Windows\\Fonts\\arialbd.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
        ];
        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return self::fontPath();
    }

    private static function text($img, ?string $font, float $size, int $x, int $y, string $text, int $color): void
    {
        if ($font && function_exists('imagettftext')) {
            imagettftext($img, $size, 0, $x, $y, $color, $font, $text);

            return;
        }
        imagestring($img, 2, $x, max(0, $y - 10), $text, $color);
    }

    private static function textCentered($img, ?string $font, float $size, int $centerX, int $y, string $text, int $color): void
    {
        if ($font && function_exists('imagettfbbox') && function_exists('imagettftext')) {
            $box = imagettfbbox($size, 0, $font, $text);
            if ($box !== false) {
                $textW = abs($box[2] - $box[0]);
                imagettftext($img, $size, 0, (int) ($centerX - ($textW / 2)), $y, $color, $font, $text);

                return;
            }
        }
        self::text($img, $font, $size, max(0, $centerX - (int) (strlen($text) * 3)), $y, $text, $color);
    }

    private static function textRotated($img, ?string $font, float $size, int $x, int $y, string $text, int $color): void
    {
        if ($font && function_exists('imagettftext')) {
            imagettftext($img, $size, 90, $x, $y, $color, $font, $text);

            return;
        }
        imagestring($img, 2, $x, $y, $text, $color);
    }
}
