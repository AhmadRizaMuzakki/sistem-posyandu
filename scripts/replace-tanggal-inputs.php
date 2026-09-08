<?php

$root = dirname(__DIR__);

function replaceHariBulanTahunBlock(
    string $path,
    string $hariModel,
    string $tanggalModel,
    string $label,
    bool $required = true
): int {
    $content = file_get_contents($path);
    $hariEsc = preg_quote($hariModel, '#');

    $pattern = '#<div>\s*<label class="block text-gray-700 text-sm font-bold mb-2">.*?</label>\s*<div class="grid grid-cols-3 gap-2">\s*<div>\s*(?:<label[^>]*>.*?</label>\s*)?<select[^>]*wire:model(?:\.live)?="' . $hariEsc . '".*?</div>\s*(?:@error\(\'[^\']+\'\).*?@enderror\s*)?</div>#s';

    // Also handle indented variant with extra wrapper leftovers from failed replace
    $reqAttr = $required ? 'true' : 'false';
    $replace = <<<HTML
<x-tanggal-dmy-input
                            label="{$label}"
                            :required="{$reqAttr}"
                            wire:model="{$tanggalModel}"
                        />
HTML;

    $count = 0;
    $new = preg_replace($pattern, $replace, $content, 1, $count);
    if ($count > 0) {
        file_put_contents($path, $new);
    }

    return $count;
}

// Fix broken ibuhamil suami blocks from previous partial replace
foreach ([
    'resources/views/livewire/super-admin/posyandu-detail/modals/ibuhamil-modal.blade.php',
    'resources/views/livewire/posyandu/modals/ibuhamil-modal.blade.php',
] as $rel) {
    $path = $root . '/' . $rel;
    $c = file_get_contents($path);
    $fixed = preg_replace(
        '#<x-tanggal-dmy-input\s+label="Tanggal Lahir Suami"\s+:required="false"\s+wire:model="tanggal_lahir_suami_ibuhamil"\s*/>\s*@error\(\'tanggal_lahir_suami_ibuhamil\'\).*?@enderror\s*</div>#s',
        '<x-tanggal-dmy-input
                                label="Tanggal Lahir Suami"
                                :required="false"
                                wire:model="tanggal_lahir_suami_ibuhamil"
                            />',
        $c,
        1,
        $n
    );
    echo "fix $rel => $n\n";
    if ($n) {
        file_put_contents($path, $fixed);
    }
}

$jobs = [
    ['resources/views/livewire/super-admin/posyandu-detail/modals/balita-modal.blade.php', 'hari_lahir_sasaran', 'tanggal_lahir_sasaran', 'Tanggal Lahir', true],
    ['resources/views/livewire/super-admin/posyandu-detail/modals/balita-modal.blade.php', 'hari_lahir_orangtua', 'tanggal_lahir_orangtua', 'Tanggal Lahir Orangtua', true],
    ['resources/views/livewire/posyandu/modals/balita-modal.blade.php', 'hari_lahir_sasaran', 'tanggal_lahir_sasaran', 'Tanggal Lahir', true],
    ['resources/views/livewire/posyandu/modals/balita-modal.blade.php', 'hari_lahir_orangtua', 'tanggal_lahir_orangtua', 'Tanggal Lahir Orangtua', true],

    ['resources/views/livewire/super-admin/posyandu-detail/modals/remaja-modal.blade.php', 'hari_lahir_remaja', 'tanggal_lahir_remaja', 'Tanggal Lahir', true],
    ['resources/views/livewire/super-admin/posyandu-detail/modals/remaja-modal.blade.php', 'hari_lahir_orangtua_remaja', 'tanggal_lahir_orangtua_remaja', 'Tanggal Lahir Orangtua', true],
    ['resources/views/livewire/posyandu/modals/remaja-modal.blade.php', 'hari_lahir_remaja', 'tanggal_lahir_remaja', 'Tanggal Lahir', true],
    ['resources/views/livewire/posyandu/modals/remaja-modal.blade.php', 'hari_lahir_orangtua_remaja', 'tanggal_lahir_orangtua_remaja', 'Tanggal Lahir Orangtua', true],

    ['resources/views/livewire/super-admin/posyandu-detail/modals/dewasa-modal.blade.php', 'hari_lahir_dewasa', 'tanggal_lahir_dewasa', 'Tanggal Lahir', true],
    ['resources/views/livewire/posyandu/modals/dewasa-modal.blade.php', 'hari_lahir_dewasa', 'tanggal_lahir_dewasa', 'Tanggal Lahir', true],

    ['resources/views/livewire/super-admin/posyandu-detail/modals/pralansia-modal.blade.php', 'hari_lahir_pralansia', 'tanggal_lahir_pralansia', 'Tanggal Lahir', true],
    ['resources/views/livewire/posyandu/modals/pralansia-modal.blade.php', 'hari_lahir_pralansia', 'tanggal_lahir_pralansia', 'Tanggal Lahir', true],

    ['resources/views/livewire/super-admin/posyandu-detail/modals/lansia-modal.blade.php', 'hari_lahir_lansia', 'tanggal_lahir_lansia', 'Tanggal Lahir', true],
    ['resources/views/livewire/posyandu/modals/lansia-modal.blade.php', 'hari_lahir_lansia', 'tanggal_lahir_lansia', 'Tanggal Lahir', true],

    ['resources/views/livewire/super-admin/posyandu-detail/modals/orangtua-modal.blade.php', 'hari_lahir_orangtua', 'tanggal_lahir_orangtua', 'Tanggal Lahir', true],
    ['resources/views/livewire/posyandu/modals/orangtua-modal.blade.php', 'hari_lahir_orangtua', 'tanggal_lahir_orangtua', 'Tanggal Lahir', true],

    ['resources/views/livewire/super-admin/posyandu-detail/modals/ibuhamil-modal.blade.php', 'hari_lahir_ibuhamil', 'tanggal_lahir_ibuhamil', 'Tanggal Lahir', true],
    ['resources/views/livewire/posyandu/modals/ibuhamil-modal.blade.php', 'hari_lahir_ibuhamil', 'tanggal_lahir_ibuhamil', 'Tanggal Lahir', true],

    ['resources/views/livewire/super-admin/posyandu-detail/modals/pendidikan-modal.blade.php', 'hari_lahir_pendidikan', 'tanggal_lahir_pendidikan', 'Tanggal Lahir', true],
    ['resources/views/livewire/posyandu/modals/pendidikan-modal.blade.php', 'hari_lahir_pendidikan', 'tanggal_lahir_pendidikan', 'Tanggal Lahir', true],
];

foreach ($jobs as [$rel, $hari, $tanggal, $label, $req]) {
    $path = $root . '/' . $rel;
    if (! is_file($path)) {
        echo "MISSING $rel\n";
        continue;
    }
    $n = replaceHariBulanTahunBlock($path, $hari, $tanggal, $label, $req);
    echo "$rel :: $hari => $n\n";
}

echo "DONE\n";
