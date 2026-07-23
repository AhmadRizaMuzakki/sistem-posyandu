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
        $paddingTop = max(16, (int) ($height * 0.08));
        $paddingBottom = max(40, (int) ($height * 0.22));
        $chartW = $width - $paddingLeft - $paddingRight;
        $chartH = $height - $paddingTop - $paddingBottom;

        $max = max(1, (int) max(array_column($rows, 'jumlah')));
        $count = count($rows);
        $gap = max(8, (int) ($width * 0.02));
        $barW = max(14, (int) (($chartW - ($gap * ($count + 1))) / $count));

        // Grid lines
        for ($i = 0; $i <= 4; $i++) {
            $y = $paddingTop + (int) (($chartH / 4) * $i);
            imageline($img, $paddingLeft, $y, $width - $paddingRight, $y, $gray);
            $val = (int) round($max * (1 - ($i / 4)));
            imagestring($img, 2, 8, $y - 6, (string) $val, $dark);
        }

        foreach ($rows as $index => $row) {
            $value = (float) ($row['jumlah'] ?? 0);
            $barH = (int) round(($value / $max) * $chartH);
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
