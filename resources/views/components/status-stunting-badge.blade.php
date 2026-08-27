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
        'Stunting' => 'bg-rose-100 text-rose-800',
        'Tidak stunting' => 'bg-emerald-100 text-emerald-800',
        default => 'bg-gray-100 text-gray-800',
    };
@endphp

@if($label !== '-')
    <span {{ $attributes->merge(['class' => "inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full capitalize {$badgeClasses}"]) }}>
        {{ $label }}
    </span>
@else
    <span {{ $attributes->merge(['class' => 'text-xs text-gray-500']) }}>-</span>
@endif
