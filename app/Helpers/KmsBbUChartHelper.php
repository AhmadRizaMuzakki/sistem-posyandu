<?php

namespace App\Helpers;

use App\Services\AntropometriService;
use Carbon\Carbon;

/**
 * Kartu Menuju Sehat (KMS) — grafik BB/U dengan zona SD.
 * Kurva 0–11 bulan: WHO/PMK (melengkapi config yang mulai dari 12 bulan).
 * Kurva 12–60 bulan: config/antropometri.php.
 */
class KmsBbUChartHelper
{
    /** @var array<string, array<int, array{sd3_min: float, min: float, sd1_min: float, median: float, sd1_max: float, max: float, sd3_max: float}>> */
    private static array $infantCurves = [
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
        if ($kategoriSlug !== 'bayibalita' || $tanggalLahir === null || empty($points)) {
            return null;
        }

        $antropometri = app(AntropometriService::class);
        $jk = $antropometri->normalizeJenisKelamin($jenisKelamin);
        $jkLabel = $antropometri->labelJenisKelamin($jk);

        $validPoints = [];
        foreach ($points as $point) {
            if (! isset($point['berat'], $point['umur_bulan'])) {
                continue;
            }
            $umur = (float) $point['umur_bulan'];
            $berat = (float) $point['berat'];
            if ($umur < 0 || $berat <= 0) {
                continue;
            }
            $validPoints[] = [
                'umur_bulan' => round($umur, 1),
                'berat' => round($berat, 2),
                'tanggal' => $point['tanggal'] ?? null,
            ];
        }

        if ($validPoints === []) {
            return null;
        }

        usort($validPoints, fn ($a, $b) => $a['umur_bulan'] <=> $b['umur_bulan']);

        $maxAge = (float) max(array_column($validPoints, 'umur_bulan'));
        if ($maxAge > 60) {
            return null;
        }

        $curves = self::curvesFor($jk);
        $yMaxCurve = max(array_column($curves['sd3_max'], 'y'));
        $yMaxData = max(array_column($validPoints, 'berat'));
        $yMax = (int) max(26, ceil(max($yMaxCurve, $yMaxData) / 2) * 2);
        $yMin = 2;

        $lahirLabel = $tanggalLahir->locale('id')->translatedFormat('d F Y');

        return [
            'mode' => 'kms_bb_u',
            'title' => 'Kartu Menuju Sehat (KMS) - Berat Badan Menurut Umur (BB/U)',
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
            'legend' => [
                ['color' => '#fecaca', 'range' => '< −3 SD', 'status' => 'Sangat Kurang'],
                ['color' => '#fde047', 'range' => '−3 s/d −2 SD', 'status' => 'BB Kurang'],
                ['color' => '#bbf7d0', 'range' => '−2 s/d −1 & +1 s/d +2', 'status' => 'Normal'],
                ['color' => '#4ade80', 'range' => '−1 s/d +1 SD', 'status' => 'Normal Ideal'],
                ['color' => '#fdba74', 'range' => '> +2 SD', 'status' => 'Risiko Lebih'],
            ],
            'pdf_legend' => [
                ['color' => '#fecaca', 'label' => '< -3 SD (BB Sangat Kurang / Merah)'],
                ['color' => '#fde047', 'label' => '-3 s/d -2 SD (BB Kurang / Kuning)'],
                ['color' => '#bbf7d0', 'label' => '-2 s/d -1 & +1 s/d +2 SD (Normal / Hijau Muda)'],
                ['color' => '#4ade80', 'label' => '-1 s/d +1 SD (Normal Ideal / Hijau Tua)'],
                ['color' => '#fdba74', 'label' => '> +2 SD (Risiko BB Lebih / Oranye)'],
            ],
        ];
    }

    /**
     * @return array<string, array<int, array{x: int, y: float}>>
     */
    public static function curvesFor(string $jk): array
    {
        $jk = $jk === 'P' ? 'P' : 'L';
        $config = config('antropometri.bb_u.'.$jk, []);
        $keys = ['sd3_min', 'min', 'sd1_min', 'median', 'sd1_max', 'max', 'sd3_max'];
        $out = array_fill_keys($keys, []);

        for ($bulan = 0; $bulan <= 60; $bulan++) {
            $row = null;
            if ($bulan <= 11) {
                $row = self::$infantCurves[$jk][$bulan] ?? null;
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
        if (! function_exists('imagecreatetruecolor') || ($payload['mode'] ?? '') !== 'kms_bb_u') {
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
        for ($kg = (int) $yMin; $kg <= (int) $yMax; $kg += 2) {
            $y = $mapY((float) $kg);
            imageline($img, $padL, $y, $padL + $chartW, $y, $kg % 4 === 0 ? $grid : $gridSoft);
            self::text($img, $font, 11, $padL - 36, $y + 4, (string) $kg, $muted);
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
        $childPts = $payload['points'] ?? [];
        $mapped = [];
        foreach ($childPts as $pt) {
            $mapped[] = [
                'x' => $mapX((float) $pt['umur_bulan']),
                'y' => $mapY((float) $pt['berat']),
                'umur' => $pt['umur_bulan'],
                'berat' => $pt['berat'],
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

        // Callout seperti foto awal (geser horizontal jika titik berdekatan)
        $callouts = array_slice($mapped, -3);
        $offsetY = [-68, -90, -52];
        $offsetX = [-50, 8, 40];
        foreach ($callouts as $i => $pt) {
            $label1 = ((int) round((float) $pt['umur'])).' bln';
            $label2 = $pt['tanggal'] ? '('.$pt['tanggal'].')' : '';
            $label3 = number_format((float) $pt['berat'], 1, '.', '').' kg';
            $boxW = 84;
            $boxH = $label2 !== '' ? 44 : 32;
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
            self::text($img, $font, 10, $bx + 8, $by + 15, $label1, $childColor);
            if ($label2 !== '') {
                self::text($img, $font, 9, $bx + 8, $by + 28, $label2, $muted);
                self::text($img, $font, 10, $bx + 8, $by + 40, $label3, $dark);
            } else {
                self::text($img, $font, 10, $bx + 8, $by + 28, $label3, $dark);
            }
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
        self::textRotated($img, $font, 13, 24, (int) ($height / 2) + 55, 'Berat Badan (kg)', $dark);

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
