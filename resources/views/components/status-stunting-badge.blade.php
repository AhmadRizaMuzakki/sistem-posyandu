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
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $badgeClasses }}">
        {{ $label }}
    </span>
@else
    <span class="text-sm text-gray-500">-</span>
@endif
