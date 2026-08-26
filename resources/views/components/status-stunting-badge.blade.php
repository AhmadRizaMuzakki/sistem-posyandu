@props([
    'beratBadan' => null,
    'tinggiBadan' => null,
    'tanggalLahir' => null,
    'tanggalUkur' => null,
    'jenisKelamin' => null,
    'tekananDarah' => null,
    'gulaDarah' => null,
    'kategoriSasaran' => null,
])

@php
    use App\Services\AntropometriService;
    use Carbon\Carbon;

    $lahir = $tanggalLahir
        ? ($tanggalLahir instanceof Carbon ? $tanggalLahir : Carbon::parse($tanggalLahir))
        : null;
    $ukur = $tanggalUkur
        ? ($tanggalUkur instanceof Carbon ? $tanggalUkur : Carbon::parse($tanggalUkur))
        : null;

    $berat = $beratBadan !== null && $beratBadan !== '' ? (float) $beratBadan : null;
    $tinggi = $tinggiBadan !== null && $tinggiBadan !== '' ? (float) $tinggiBadan : null;
    $gula = $gulaDarah !== null && $gulaDarah !== '' ? (float) $gulaDarah : null;

    $antropometri = app(AntropometriService::class);
    $label = $antropometri->labelStatusStunting(
        $berat,
        $tinggi,
        $lahir,
        $ukur,
        $jenisKelamin,
        $tekananDarah ?: null,
        $gula,
        $kategoriSasaran
    );

    $badgeClasses = match ($label) {
        'Stunting' => 'bg-rose-500 text-white',
        'Tidak stunting' => 'bg-emerald-500 text-white',
        default => 'bg-gray-500 text-white',
    };
@endphp

@if($label !== '-')
    <span {{ $attributes->merge(['class' => "inline-flex items-center px-1 py-0 rounded-full text-[7px] leading-3 font-medium {$badgeClasses}"]) }}>
        {{ $label }}
    </span>
@else
    <span {{ $attributes->merge(['class' => 'text-[9px] text-gray-500']) }}>-</span>
@endif
