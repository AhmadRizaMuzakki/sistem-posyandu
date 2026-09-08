<?php

/**
 * Ganti blok UI Hari/Bulan/Tahun menjadi x-tanggal-dmy-input (Alpine → hari/bulan/tahun).
 * Tidak mengubah field Livewire / database.
 */

$root = dirname(__DIR__);

$monthNames = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
];

/**
 * @param array{hari:string,bulan:string,tahun:string,tanggal:?string,label:string,required:bool,indent:string} $cfg
 */
function buildReplacement(array $cfg): string
{
    $indent = $cfg['indent'];
    $req = $cfg['required'] ? 'true' : 'false';
    $tanggalAttr = $cfg['tanggal']
        ? "\n{$indent}    error-tanggal=\"{$cfg['tanggal']}\""
        : '';

    return <<<HTML
{$indent}<x-tanggal-dmy-input
{$indent}    label="{$cfg['label']}"
{$indent}    :required="{$req}"
{$indent}    hari="{$cfg['hari']}"
{$indent}    bulan="{$cfg['bulan']}"
{$indent}    tahun="{$cfg['tahun']}"
{$indent}    error-hari="{$cfg['hari']}"
{$indent}    error-bulan="{$cfg['bulan']}"
{$indent}    error-tahun="{$cfg['tahun']}"{$tanggalAttr}
{$indent}/>
HTML;
}

/**
 * Pola generik: label + grid 3 kolom hari/bulan/tahun (+ optional @error tanggal)
 */
function replaceBlock(string $content, array $cfg): array
{
    $hari = preg_quote($cfg['hari'], '#');
    $bulan = preg_quote($cfg['bulan'], '#');
    $tahun = preg_quote($cfg['tahun'], '#');
    $tanggal = $cfg['tanggal'] ? preg_quote($cfg['tanggal'], '#') : null;

    // Label text flexible; match from opening <div> that contains the hari select
    $pattern = '#<div>\s*<label class="block text-gray-700 text-sm font-bold mb-2">[^<]*(?:<span[^>]*>\*</span>)?</label>\s*'
        . '<div class="grid grid-cols-3 gap-2">\s*'
        . '<div>\s*(?:<label[^>]*>.*?</label>\s*)?'
        . '(?:<select[^>]*wire:model(?:\.live)?="' . $hari . '"[\s\S]*?</select>|<input[^>]*wire:model(?:\.live)?="' . $hari . '"[^>]*>)\s*'
        . '(?:@error\(\'' . $hari . '\'\)[\s\S]*?@enderror\s*)?</div>\s*'
        . '<div>\s*(?:<label[^>]*>.*?</label>\s*)?'
        . '<select[^>]*wire:model(?:\.live)?="' . $bulan . '"[\s\S]*?</select>\s*'
        . '(?:@error\(\'' . $bulan . '\'\)[\s\S]*?@enderror\s*)?</div>\s*'
        . '<div>\s*(?:<label[^>]*>.*?</label>\s*)?'
        . '<input[^>]*wire:model(?:\.live)?="' . $tahun . '"[^>]*>\s*'
        . '(?:@error\(\'' . $tahun . '\'\)[\s\S]*?@enderror\s*)?</div>\s*'
        . '</div>\s*'
        . ($tanggal ? '(?:@error\(\'' . $tanggal . '\'\)[\s\S]*?@enderror\s*)?' : '')
        . '</div>#';

    $count = 0;
    $replacement = buildReplacement($cfg);
    $new = preg_replace($pattern, $replacement, $content, 1, $count);

    return [$new ?? $content, $count];
}

$jobs = [
    // [relative path, jobs...]
    ['resources/views/livewire/super-admin/posyandu-detail/modals/balita-modal.blade.php', [
        ['hari' => 'hari_lahir_sasaran', 'bulan' => 'bulan_lahir_sasaran', 'tahun' => 'tahun_lahir_sasaran', 'tanggal' => 'tanggal_lahir_sasaran', 'label' => 'Tanggal Lahir', 'required' => true, 'indent' => '                        '],
        ['hari' => 'hari_lahir_orangtua', 'bulan' => 'bulan_lahir_orangtua', 'tahun' => 'tahun_lahir_orangtua', 'tanggal' => 'tanggal_lahir_orangtua', 'label' => 'Tanggal Lahir Orangtua', 'required' => true, 'indent' => '                        '],
    ]],
    ['resources/views/livewire/posyandu/modals/balita-modal.blade.php', [
        ['hari' => 'hari_lahir_sasaran', 'bulan' => 'bulan_lahir_sasaran', 'tahun' => 'tahun_lahir_sasaran', 'tanggal' => 'tanggal_lahir_sasaran', 'label' => 'Tanggal Lahir', 'required' => true, 'indent' => '                        '],
        ['hari' => 'hari_lahir_orangtua', 'bulan' => 'bulan_lahir_orangtua', 'tahun' => 'tahun_lahir_orangtua', 'tanggal' => 'tanggal_lahir_orangtua', 'label' => 'Tanggal Lahir Orangtua', 'required' => true, 'indent' => '                        '],
    ]],
    ['resources/views/livewire/super-admin/posyandu-detail/modals/remaja-modal.blade.php', [
        ['hari' => 'hari_lahir_remaja', 'bulan' => 'bulan_lahir_remaja', 'tahun' => 'tahun_lahir_remaja', 'tanggal' => 'tanggal_lahir_remaja', 'label' => 'Tanggal Lahir', 'required' => true, 'indent' => '                        '],
        ['hari' => 'hari_lahir_orangtua_remaja', 'bulan' => 'bulan_lahir_orangtua_remaja', 'tahun' => 'tahun_lahir_orangtua_remaja', 'tanggal' => 'tanggal_lahir_orangtua_remaja', 'label' => 'Tanggal Lahir Orangtua', 'required' => true, 'indent' => '                        '],
    ]],
    ['resources/views/livewire/posyandu/modals/remaja-modal.blade.php', [
        ['hari' => 'hari_lahir_remaja', 'bulan' => 'bulan_lahir_remaja', 'tahun' => 'tahun_lahir_remaja', 'tanggal' => 'tanggal_lahir_remaja', 'label' => 'Tanggal Lahir', 'required' => true, 'indent' => '                        '],
        ['hari' => 'hari_lahir_orangtua_remaja', 'bulan' => 'bulan_lahir_orangtua_remaja', 'tahun' => 'tahun_lahir_orangtua_remaja', 'tanggal' => 'tanggal_lahir_orangtua_remaja', 'label' => 'Tanggal Lahir Orangtua', 'required' => true, 'indent' => '                        '],
    ]],
    ['resources/views/livewire/super-admin/posyandu-detail/modals/dewasa-modal.blade.php', [
        ['hari' => 'hari_lahir_dewasa', 'bulan' => 'bulan_lahir_dewasa', 'tahun' => 'tahun_lahir_dewasa', 'tanggal' => 'tanggal_lahir_dewasa', 'label' => 'Tanggal Lahir', 'required' => true, 'indent' => '                        '],
    ]],
    ['resources/views/livewire/posyandu/modals/dewasa-modal.blade.php', [
        ['hari' => 'hari_lahir_dewasa', 'bulan' => 'bulan_lahir_dewasa', 'tahun' => 'tahun_lahir_dewasa', 'tanggal' => 'tanggal_lahir_dewasa', 'label' => 'Tanggal Lahir', 'required' => true, 'indent' => '                        '],
    ]],
    ['resources/views/livewire/super-admin/posyandu-detail/modals/pralansia-modal.blade.php', [
        ['hari' => 'hari_lahir_pralansia', 'bulan' => 'bulan_lahir_pralansia', 'tahun' => 'tahun_lahir_pralansia', 'tanggal' => 'tanggal_lahir_pralansia', 'label' => 'Tanggal Lahir', 'required' => true, 'indent' => '                        '],
    ]],
    ['resources/views/livewire/posyandu/modals/pralansia-modal.blade.php', [
        ['hari' => 'hari_lahir_pralansia', 'bulan' => 'bulan_lahir_pralansia', 'tahun' => 'tahun_lahir_pralansia', 'tanggal' => 'tanggal_lahir_pralansia', 'label' => 'Tanggal Lahir', 'required' => true, 'indent' => '                        '],
    ]],
    ['resources/views/livewire/super-admin/posyandu-detail/modals/lansia-modal.blade.php', [
        ['hari' => 'hari_lahir_lansia', 'bulan' => 'bulan_lahir_lansia', 'tahun' => 'tahun_lahir_lansia', 'tanggal' => 'tanggal_lahir_lansia', 'label' => 'Tanggal Lahir', 'required' => true, 'indent' => '                        '],
    ]],
    ['resources/views/livewire/posyandu/modals/lansia-modal.blade.php', [
        ['hari' => 'hari_lahir_lansia', 'bulan' => 'bulan_lahir_lansia', 'tahun' => 'tahun_lahir_lansia', 'tanggal' => 'tanggal_lahir_lansia', 'label' => 'Tanggal Lahir', 'required' => true, 'indent' => '                        '],
    ]],
    ['resources/views/livewire/super-admin/posyandu-detail/modals/orangtua-modal.blade.php', [
        ['hari' => 'hari_lahir_orangtua', 'bulan' => 'bulan_lahir_orangtua', 'tahun' => 'tahun_lahir_orangtua', 'tanggal' => 'tanggal_lahir_orangtua', 'label' => 'Tanggal Lahir', 'required' => true, 'indent' => '                        '],
    ]],
    ['resources/views/livewire/posyandu/modals/orangtua-modal.blade.php', [
        ['hari' => 'hari_lahir_orangtua', 'bulan' => 'bulan_lahir_orangtua', 'tahun' => 'tahun_lahir_orangtua', 'tanggal' => 'tanggal_lahir_orangtua', 'label' => 'Tanggal Lahir', 'required' => true, 'indent' => '                        '],
    ]],
    ['resources/views/livewire/super-admin/posyandu-detail/modals/ibuhamil-modal.blade.php', [
        ['hari' => 'hari_lahir_ibuhamil', 'bulan' => 'bulan_lahir_ibuhamil', 'tahun' => 'tahun_lahir_ibuhamil', 'tanggal' => 'tanggal_lahir_ibuhamil', 'label' => 'Tanggal Lahir', 'required' => true, 'indent' => '                        '],
        ['hari' => 'hari_lahir_suami_ibuhamil', 'bulan' => 'bulan_lahir_suami_ibuhamil', 'tahun' => 'tahun_lahir_suami_ibuhamil', 'tanggal' => 'tanggal_lahir_suami_ibuhamil', 'label' => 'Tanggal Lahir Suami', 'required' => false, 'indent' => '                            '],
    ]],
    ['resources/views/livewire/posyandu/modals/ibuhamil-modal.blade.php', [
        ['hari' => 'hari_lahir_ibuhamil', 'bulan' => 'bulan_lahir_ibuhamil', 'tahun' => 'tahun_lahir_ibuhamil', 'tanggal' => 'tanggal_lahir_ibuhamil', 'label' => 'Tanggal Lahir', 'required' => true, 'indent' => '                        '],
        ['hari' => 'hari_lahir_suami_ibuhamil', 'bulan' => 'bulan_lahir_suami_ibuhamil', 'tahun' => 'tahun_lahir_suami_ibuhamil', 'tanggal' => 'tanggal_lahir_suami_ibuhamil', 'label' => 'Tanggal Lahir Suami', 'required' => false, 'indent' => '                            '],
    ]],
    ['resources/views/livewire/super-admin/posyandu-detail/modals/pendidikan-modal.blade.php', [
        ['hari' => 'hari_lahir_pendidikan', 'bulan' => 'bulan_lahir_pendidikan', 'tahun' => 'tahun_lahir_pendidikan', 'tanggal' => 'tanggal_lahir_pendidikan', 'label' => 'Tanggal Lahir', 'required' => true, 'indent' => '                        '],
    ]],
    ['resources/views/livewire/posyandu/modals/pendidikan-modal.blade.php', [
        ['hari' => 'hari_lahir_pendidikan', 'bulan' => 'bulan_lahir_pendidikan', 'tahun' => 'tahun_lahir_pendidikan', 'tanggal' => 'tanggal_lahir_pendidikan', 'label' => 'Tanggal Lahir', 'required' => true, 'indent' => '                        '],
    ]],
];

foreach ($jobs as [$rel, $cfgs]) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
    if (! is_file($path)) {
        echo "MISSING $rel\n";
        continue;
    }
    $content = file_get_contents($path);
    $total = 0;
    foreach ($cfgs as $cfg) {
        [$content, $n] = replaceBlock($content, $cfg);
        $total += $n;
        if ($n === 0) {
            echo "FAIL  {$cfg['hari']} in $rel\n";
        } else {
            echo "OK    {$cfg['hari']} in $rel\n";
        }
    }
    file_put_contents($path, $content);
    echo "→ $rel ($total replacements)\n";
}
