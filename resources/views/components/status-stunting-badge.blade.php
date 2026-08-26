@props([
    'beratBadan' => null,
    'tinggiBadan' => null,
    'tanggalLahir' => null,
    'tanggalUkur' => null,
    'jenisKelamin' => null,
    'tekananDarah' => null,
    'gulaDarah' => null,
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
    $statusItem = null;

    // Samakan dengan halaman orangtua: pakai evaluasi() lalu ambil indeks TB/U (stunting)
    if ($berat !== null && $tinggi !== null) {
        $penilaian = $antropometri->evaluasi(
            $berat,
            $tinggi,
            $lahir,
            $ukur,
            $jenisKelamin,
            $tekananDarah ?: null,
            $gula
        );

        if ($penilaian) {
            $tbU = collect($penilaian['indeks'] ?? [])->firstWhere('singkat', 'TB/U');
            if ($tbU) {
                $statusItem = [
                    'status' => $tbU['status'] ?? ($penilaian['kategori'] ?? null),
                    'color' => $tbU['color'] ?? ($penilaian['kategori_color'] ?? 'gray'),
                ];
            }
        }
    }

    // Fallback jika hanya tinggi yang ada (tetap TB/U)
    if (! $statusItem) {
        $statusItem = $antropometri->statusStunting($tinggi, $lahir, $ukur, $jenisKelamin);
    }

    // Tema badge sama seperti halaman orangtua (penilaian-card)
    $badgeClasses = match ($statusItem['color'] ?? null) {
        'green' => 'bg-emerald-500 text-white',
        'orange' => 'bg-amber-500 text-white',
        'yellow' => 'bg-yellow-500 text-white',
        'red' => 'bg-rose-500 text-white',
        default => 'bg-gray-500 text-white',
    };
@endphp

@if($statusItem && ! empty($statusItem['status']))
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $badgeClasses }}">
        {{ $statusItem['status'] }}
    </span>
@else
    <span class="text-sm text-gray-500">-</span>
@endif
