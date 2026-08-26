<?php

namespace App\Helpers;

class PdfChartHelper
{
    /**
     * Generate bar chart PNG as data URI for DomPDF.
     *
     * @param  array<int, array{label: string, jumlah: int|float}>  $rows
     * @param  array<int, array{0: int, 1: int, 2: int}>  $colors
     */
    public static function barChartDataUri(array $rows, int $width = 900, int $height = 420, array $colors = []): ?string
    {
        if (! function_exists('imagecreatetruecolor') || empty($rows)) {
            return null;
        }

        $defaultColors = [
            [59, 130, 246],
            [16, 185, 129],
            [251, 191, 36],
            [239, 68, 68],
            [168, 85, 247],
            [236, 72, 153],
            [20, 184, 166],
            [99, 102, 241],
        ];
        $colors = $colors ?: $defaultColors;

        $img = imagecreatetruecolor($width, $height);
        if ($img === false) {
            return null;
        }

        $white = imagecolorallocate($img, 255, 255, 255);
        $gray = imagecolorallocate($img, 156, 163, 175);
        $dark = imagecolorallocate($img, 55, 65, 81);
        imagefilledrectangle($img, 0, 0, $width, $height, $white);

        $paddingLeft = 48;
        $paddingRight = 16;
        $paddingTop = max(18, (int) ($height * 0.09));
        $paddingBottom = max(44, (int) ($height * 0.20));
        $chartW = $width - $paddingLeft - $paddingRight;
        $chartH = max(80, $height - $paddingTop - $paddingBottom);

        $max = max(1, (int) max(array_column($rows, 'jumlah')));
        // Beri ruang headroom supaya batang tinggi tidak mepet ke atas
        $axisMax = (int) max($max, ceil($max * 1.15));
        $count = count($rows);
        $gap = max(8, (int) ($width * 0.02));
        $barW = max(14, (int) (($chartW - ($gap * ($count + 1))) / $count));

        // Grid lines
        for ($i = 0; $i <= 4; $i++) {
            $y = $paddingTop + (int) (($chartH / 4) * $i);
            imageline($img, $paddingLeft, $y, $width - $paddingRight, $y, $gray);
            $val = (int) round($axisMax * (1 - ($i / 4)));
            imagestring($img, 2, 8, $y - 6, (string) $val, $dark);
        }

        foreach ($rows as $index => $row) {
            $value = (float) ($row['jumlah'] ?? 0);
            $barH = (int) round(($value / $axisMax) * $chartH);
            $x = $paddingLeft + $gap + ($index * ($barW + $gap));
            $y = $paddingTop + $chartH - $barH;

            $rgb = $colors[$index % count($colors)];
            $color = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
            imagefilledrectangle($img, $x, $y, $x + $barW, $paddingTop + $chartH, $color);

            $label = self::truncateLabel((string) ($row['label'] ?? ''), 12);
            $labelX = $x + (int) (($barW - (strlen($label) * 6)) / 2);
            imagestring($img, 2, max($x, $labelX), $paddingTop + $chartH + 10, $label, $dark);

            $valueLabel = (string) (int) $value;
            $valueX = $x + (int) (($barW - (strlen($valueLabel) * 6)) / 2);
            imagestring($img, 3, max($x, $valueX), max($paddingTop, $y - 16), $valueLabel, $dark);
        }

        // Axes
        imageline($img, $paddingLeft, $paddingTop, $paddingLeft, $paddingTop + $chartH, $dark);
        imageline($img, $paddingLeft, $paddingTop + $chartH, $width - $paddingRight, $paddingTop + $chartH, $dark);

        return self::imageToDataUri($img);
    }

    /**
     * Generate pie chart PNG as data URI for DomPDF.
     *
     * @param  array<int, array{label: string, jumlah: int|float}>  $rows
     * @param  array<int, array{0: int, 1: int, 2: int}>  $colors
     */
    public static function pieChartDataUri(array $rows, int $width = 900, int $height = 420, array $colors = []): ?string
    {
        if (! function_exists('imagecreatetruecolor') || empty($rows)) {
            return null;
        }

        $rows = array_values(array_filter($rows, fn ($row) => (float) ($row['jumlah'] ?? 0) > 0));
        if (empty($rows)) {
            return null;
        }

        $defaultColors = [
            [59, 130, 246],
            [16, 185, 129],
            [251, 191, 36],
            [239, 68, 68],
            [168, 85, 247],
            [236, 72, 153],
            [20, 184, 166],
            [99, 102, 241],
            [245, 158, 11],
            [34, 197, 94],
            [14, 165, 233],
            [244, 63, 94],
        ];
        $colors = $colors ?: $defaultColors;

        $img = imagecreatetruecolor($width, $height);
        if ($img === false) {
            return null;
        }

        $white = imagecolorallocate($img, 255, 255, 255);
        $dark = imagecolorallocate($img, 55, 65, 81);
        imagefilledrectangle($img, 0, 0, $width, $height, $white);

        $total = array_sum(array_map(fn ($row) => (float) $row['jumlah'], $rows));
        if ($total <= 0) {
            imagedestroy($img);

            return null;
        }

        $cx = (int) ($width * 0.30);
        $cy = (int) ($height / 2);
        $radius = (int) min($height * 0.36, $width * 0.20);

        $start = 0.0;
        foreach ($rows as $index => $row) {
            $value = (float) $row['jumlah'];
            $slice = ($value / $total) * 360.0;
            $end = $start + $slice;
            $rgb = $colors[$index % count($colors)];
            $color = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
            imagefilledarc($img, $cx, $cy, $radius * 2, $radius * 2, (int) round($start), (int) round($end), $color, IMG_ARC_PIE);
            $start = $end;
        }

        // Legend
        $legendX = (int) ($width * 0.55);
        $legendY = 16;
        $legendStep = max(16, (int) (($height - 24) / max(1, count($rows))));
        foreach ($rows as $index => $row) {
            $rgb = $colors[$index % count($colors)];
            $color = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
            imagefilledrectangle($img, $legendX, $legendY, $legendX + 12, $legendY + 12, $color);

            $percent = round(((float) $row['jumlah'] / $total) * 100, 1);
            $text = self::truncateLabel((string) $row['label'], 24).' ('.(int) $row['jumlah']." / {$percent}%)";
            imagestring($img, 2, $legendX + 18, $legendY - 1, $text, $dark);
            $legendY += $legendStep;
            if ($legendY > $height - 14) {
                break;
            }
        }

        return self::imageToDataUri($img);
    }

    /**
     * Grafik pertumbuhan line chart (tinggi + berat) untuk DomPDF.
     *
     * @param  array<int, string|null>  $labels
     * @param  array<int, float|int|null>  $tinggi
     * @param  array<int, float|int|null>  $berat
     */
    public static function pertumbuhanChartDataUri(
        array $labels,
        array $tinggi,
        array $berat,
        int $width = 920,
        int $height = 360
    ): ?string {
        if (! function_exists('imagecreatetruecolor') || empty($labels)) {
            return null;
        }

        $count = count($labels);
        $tinggiVals = [];
        $beratVals = [];
        for ($i = 0; $i < $count; $i++) {
            $tinggiVals[] = isset($tinggi[$i]) && $tinggi[$i] !== null && $tinggi[$i] !== ''
                ? (float) $tinggi[$i]
                : null;
            $beratVals[] = isset($berat[$i]) && $berat[$i] !== null && $berat[$i] !== ''
                ? (float) $berat[$i]
                : null;
        }

        $tinggiPresent = array_values(array_filter($tinggiVals, fn ($v) => $v !== null));
        $beratPresent = array_values(array_filter($beratVals, fn ($v) => $v !== null));
        if (empty($tinggiPresent) && empty($beratPresent)) {
            return null;
        }

        $img = imagecreatetruecolor($width, $height);
        if ($img === false) {
            return null;
        }

        $white = imagecolorallocate($img, 255, 255, 255);
        $gray = imagecolorallocate($img, 229, 231, 235);
        $dark = imagecolorallocate($img, 55, 65, 81);
        $muted = imagecolorallocate($img, 107, 114, 128);
        $tinggiColor = imagecolorallocate($img, 16, 185, 129);
        $beratColor = imagecolorallocate($img, 59, 130, 246);
        imagefilledrectangle($img, 0, 0, $width, $height, $white);

        $paddingLeft = 56;
        $paddingRight = 56;
        $paddingTop = 40;
        $paddingBottom = 52;
        $chartW = $width - $paddingLeft - $paddingRight;
        $chartH = max(80, $height - $paddingTop - $paddingBottom);

        [$axisMinTinggi, $axisMaxTinggi] = self::axisRangeFromValues($tinggiPresent);
        [$axisMinBerat, $axisMaxBerat] = self::axisRangeFromValues($beratPresent);
        $spanTinggi = max(0.001, $axisMaxTinggi - $axisMinTinggi);
        $spanBerat = max(0.001, $axisMaxBerat - $axisMinBerat);

        // Legend
        imageline($img, $paddingLeft, 16, $paddingLeft + 16, 16, $tinggiColor);
        imagefilledellipse($img, $paddingLeft + 8, 16, 7, 7, $tinggiColor);
        imageellipse($img, $paddingLeft + 8, 16, 7, 7, $white);
        imagestring($img, 2, $paddingLeft + 22, 10, 'Tinggi (cm)', $dark);
        imageline($img, $paddingLeft + 118, 16, $paddingLeft + 134, 16, $beratColor);
        imagefilledellipse($img, $paddingLeft + 126, 16, 7, 7, $beratColor);
        imageellipse($img, $paddingLeft + 126, 16, 7, 7, $white);
        imagestring($img, 2, $paddingLeft + 140, 10, 'Berat (kg)', $dark);

        // Grid + dual axis labels
        for ($i = 0; $i <= 4; $i++) {
            $y = $paddingTop + (int) (($chartH / 4) * $i);
            imageline($img, $paddingLeft, $y, $width - $paddingRight, $y, $gray);
            $valT = $axisMaxTinggi - (($spanTinggi / 4) * $i);
            $valB = $axisMaxBerat - (($spanBerat / 4) * $i);
            imagestring($img, 2, 6, $y - 6, (string) (int) round($valT), $tinggiColor);
            imagestring($img, 2, $width - $paddingRight + 6, $y - 6, rtrim(rtrim(number_format($valB, 1, '.', ''), '0'), '.'), $beratColor);
        }

        $innerPadX = $count > 1 ? (int) ($chartW * 0.06) : (int) ($chartW / 2);
        $usableW = max(1, $chartW - (2 * $innerPadX));
        $stepX = $count > 1 ? $usableW / ($count - 1) : 0;
        $pointsTinggi = [];
        $pointsBerat = [];

        for ($index = 0; $index < $count; $index++) {
            $x = (int) round($paddingLeft + $innerPadX + ($count > 1 ? $index * $stepX : 0));

            // vertical guide
            imageline($img, $x, $paddingTop, $x, $paddingTop + $chartH, $gray);

            if ($tinggiVals[$index] !== null) {
                $ratio = ($tinggiVals[$index] - $axisMinTinggi) / $spanTinggi;
                $y = (int) round($paddingTop + $chartH - ($ratio * $chartH));
                $pointsTinggi[] = ['x' => $x, 'y' => $y, 'value' => $tinggiVals[$index]];
            }
            if ($beratVals[$index] !== null) {
                $ratio = ($beratVals[$index] - $axisMinBerat) / $spanBerat;
                $y = (int) round($paddingTop + $chartH - ($ratio * $chartH));
                $pointsBerat[] = ['x' => $x, 'y' => $y, 'value' => $beratVals[$index]];
            }

            $label = self::truncateLabel((string) ($labels[$index] ?? ''), 10);
            $labelX = $x - (int) ((strlen($label) * 5) / 2);
            imagestring($img, 2, max($paddingLeft - 4, $labelX), $paddingTop + $chartH + 10, $label, $muted);
        }

        if (function_exists('imagesetthickness')) {
            imagesetthickness($img, 2);
        }

        for ($i = 1; $i < count($pointsTinggi); $i++) {
            imageline($img, $pointsTinggi[$i - 1]['x'], $pointsTinggi[$i - 1]['y'], $pointsTinggi[$i]['x'], $pointsTinggi[$i]['y'], $tinggiColor);
        }
        for ($i = 1; $i < count($pointsBerat); $i++) {
            imageline($img, $pointsBerat[$i - 1]['x'], $pointsBerat[$i - 1]['y'], $pointsBerat[$i]['x'], $pointsBerat[$i]['y'], $beratColor);
        }

        if (function_exists('imagesetthickness')) {
            imagesetthickness($img, 1);
        }

        foreach ($pointsTinggi as $point) {
            imagefilledellipse($img, $point['x'], $point['y'], 9, 9, $tinggiColor);
            imageellipse($img, $point['x'], $point['y'], 9, 9, $white);
            $tLabel = rtrim(rtrim(number_format($point['value'], 1, '.', ''), '0'), '.');
            imagestring($img, 1, $point['x'] - (int) ((strlen($tLabel) * 5) / 2), max($paddingTop + 1, $point['y'] - 14), $tLabel, $tinggiColor);
        }
        foreach ($pointsBerat as $point) {
            imagefilledellipse($img, $point['x'], $point['y'], 9, 9, $beratColor);
            imageellipse($img, $point['x'], $point['y'], 9, 9, $white);
            $bLabel = rtrim(rtrim(number_format($point['value'], 1, '.', ''), '0'), '.');
            imagestring($img, 1, $point['x'] - (int) ((strlen($bLabel) * 5) / 2), min($paddingTop + $chartH - 12, $point['y'] + 8), $bLabel, $beratColor);
        }

        imageline($img, $paddingLeft, $paddingTop, $paddingLeft, $paddingTop + $chartH, $dark);
        imageline($img, $width - $paddingRight, $paddingTop, $width - $paddingRight, $paddingTop + $chartH, $dark);
        imageline($img, $paddingLeft, $paddingTop + $chartH, $width - $paddingRight, $paddingTop + $chartH, $dark);

        return self::imageToDataUri($img);
    }

    /**
     * @param  array<int, float>  $values
     * @return array{0: float, 1: float}
     */
    private static function axisRangeFromValues(array $values): array
    {
        if (empty($values)) {
            return [0.0, 10.0];
        }

        $min = min($values);
        $max = max($values);

        if ($min === $max) {
            $pad = max(1.0, $max * 0.25);

            return [max(0.0, $min - $pad), $max + $pad];
        }

        $pad = ($max - $min) * 0.2;

        return [max(0.0, $min - $pad), $max + $pad];
    }

    private static function truncateLabel(string $label, int $max): string
    {
        if (strlen($label) <= $max) {
            return $label;
        }

        return substr($label, 0, max(1, $max - 2)).'..';
    }

    /**
     * @param  \GdImage|resource  $img
     */
    private static function imageToDataUri($img): ?string
    {
        ob_start();
        imagepng($img);
        $png = ob_get_clean();
        imagedestroy($img);

        if ($png === false || $png === '') {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode($png);
    }
}
