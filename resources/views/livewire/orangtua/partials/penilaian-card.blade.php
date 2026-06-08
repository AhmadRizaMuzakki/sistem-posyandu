{{-- Kartu penilaian antropometri — tampilan modern & mudah dibaca --}}
@php
    $card = $penilaian['card'] ?? null;
    $warnaUtama = $card['warna'] ?? ($penilaian['kategori_color'] ?? 'green');

    $headerTheme = match ($warnaUtama) {
        'green' => ['gradient' => 'from-emerald-500 via-emerald-600 to-teal-600', 'ring' => 'ring-emerald-100', 'accent' => 'text-emerald-600'],
        'orange' => ['gradient' => 'from-amber-500 via-orange-500 to-orange-600', 'ring' => 'ring-amber-100', 'accent' => 'text-orange-600'],
        'yellow' => ['gradient' => 'from-yellow-400 via-amber-400 to-amber-500', 'ring' => 'ring-yellow-100', 'accent' => 'text-amber-600'],
        'red' => ['gradient' => 'from-rose-500 via-red-500 to-red-600', 'ring' => 'ring-rose-100', 'accent' => 'text-rose-600'],
        default => ['gradient' => 'from-slate-500 via-slate-600 to-slate-700', 'ring' => 'ring-slate-100', 'accent' => 'text-slate-600'],
    };

    $indeksIcon = [
        'BB/U' => 'ph-scales',
        'TB/U' => 'ph-ruler',
        'BB/TB' => 'ph-chart-bar-horizontal',
        'IMT/U' => 'ph-chart-pie-slice',
        'IMT' => 'ph-heartbeat',
        'TD' => 'ph-heartbeat',
        'GD' => 'ph-drop',
    ];

    $statusTheme = function (string $color) {
        return match ($color) {
            'green' => [
                'wrap' => 'bg-emerald-50/80 border-emerald-100',
                'badge' => 'bg-emerald-500 text-white',
                'title' => 'text-emerald-900',
                'body' => 'text-emerald-800/90',
                'icon' => 'text-emerald-500',
            ],
            'orange' => [
                'wrap' => 'bg-amber-50/80 border-amber-100',
                'badge' => 'bg-amber-500 text-white',
                'title' => 'text-amber-900',
                'body' => 'text-amber-900/80',
                'icon' => 'text-amber-500',
            ],
            'yellow' => [
                'wrap' => 'bg-yellow-50/80 border-yellow-100',
                'badge' => 'bg-yellow-500 text-white',
                'title' => 'text-yellow-900',
                'body' => 'text-yellow-900/80',
                'icon' => 'text-yellow-600',
            ],
            'red' => [
                'wrap' => 'bg-rose-50/80 border-rose-100',
                'badge' => 'bg-rose-500 text-white',
                'title' => 'text-rose-900',
                'body' => 'text-rose-900/80',
                'icon' => 'text-rose-500',
            ],
            default => [
                'wrap' => 'bg-slate-50/80 border-slate-100',
                'badge' => 'bg-slate-500 text-white',
                'title' => 'text-slate-900',
                'body' => 'text-slate-700',
                'icon' => 'text-slate-500',
            ],
        };
    };
@endphp

<article class="group rounded-2xl bg-white border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden ring-1 {{ $headerTheme['ring'] }}">
    {{-- Header bergradasi --}}
    <div class="bg-gradient-to-r {{ $headerTheme['gradient'] }} px-5 py-4 text-white">
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-11 h-11 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shrink-0">
                    <i class="ph ph-user text-xl"></i>
                </div>
                <div class="min-w-0">
                    <h4 class="font-bold text-lg leading-tight tracking-tight truncate">{{ $namaSasaran }}</h4>
                    @if(!empty($tanggalKunjungan))
                        <p class="text-xs text-white/85 mt-1 flex items-center gap-1">
                            <i class="ph ph-calendar-blank"></i>
                            <span class="truncate">{{ $tanggalKunjungan }}</span>
                        </p>
                    @endif
                </div>
            </div>
            <span class="shrink-0 text-[10px] font-semibold uppercase tracking-wider bg-white/20 backdrop-blur-sm rounded-full px-2.5 py-1">
                PMK 2/2020
            </span>
        </div>
    </div>

    <div class="p-5 space-y-5">
        {{-- Ringkasan pengukuran --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
            @php
                $stats = [
                    ['icon' => 'ph-gender-intersex', 'label' => 'Jenis Kelamin', 'value' => $card['jenis_kelamin'] ?? '-'],
                    ['icon' => 'ph-cake', 'label' => 'Umur', 'value' => isset($card['umur_bulan']) ? $card['umur_bulan'] . ' Bulan' : ($card['umur_label'] ?? '-')],
                    ['icon' => 'ph-scales', 'label' => 'Berat Badan', 'value' => number_format($card['berat_badan'], 1, ',', '.') . ' kg'],
                    ['icon' => 'ph-ruler', 'label' => 'Tinggi Badan', 'value' => number_format($card['tinggi_badan'], 1, ',', '.') . ' cm'],
                    ['icon' => 'ph-heartbeat', 'label' => 'Tekanan Darah', 'value' => !empty($card['tekanan_darah']) ? $card['tekanan_darah'] . ' mmHg' : '-'],
                    ['icon' => 'ph-drop', 'label' => 'Gula Darah', 'value' => isset($card['gula_darah']) && $card['gula_darah'] !== null ? number_format($card['gula_darah'], 0, ',', '.') . ' mg/dL' : '-'],
                ];
            @endphp
            @foreach($stats as $stat)
                <div class="rounded-xl bg-gray-50 border border-gray-100 px-3 py-2.5 text-center sm:text-left">
                    <i class="ph {{ $stat['icon'] }} {{ $headerTheme['accent'] }} text-base mb-1"></i>
                    <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">{{ $stat['label'] }}</p>
                    <p class="text-sm font-bold text-gray-800 mt-0.5 leading-snug">{{ $stat['value'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Indeks penilaian --}}
        <div class="space-y-3">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Hasil Penilaian</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($card['indeks'] ?? ($penilaian['indeks'] ?? []) as $item)
                    @php
                        $theme = $statusTheme($item['color'] ?? 'gray');
                        $icon = $indeksIcon[$item['singkat'] ?? ''] ?? 'ph-chart-line';
                        $saran = $item['saran'] ?? ($item['rekomendasi'] ?? '');
                    @endphp
                    <div class="rounded-xl border p-4 {{ $theme['wrap'] }}">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-lg bg-white shadow-sm flex items-center justify-center shrink-0">
                                <i class="ph {{ $icon }} {{ $theme['icon'] }} text-lg"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <span class="text-[11px] font-bold uppercase tracking-wide text-gray-500">{{ $item['singkat'] }}</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $theme['badge'] }} shadow-sm">
                                        {{ $item['status'] }}
                                    </span>
                                </div>
                                <p class="text-xs font-semibold {{ $theme['title'] }}">{{ $item['nama'] ?? '' }}</p>
                                @if(!empty($item['nilai']))
                                    <p class="text-[11px] font-medium text-gray-500 mt-0.5 mb-1.5">
                                        Nilai: <span class="text-gray-700">{{ $item['nilai'] }}</span>
                                    </p>
                                @endif
                                <p class="text-xs leading-relaxed {{ $theme['body'] }}">{{ $saran }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Footer kesimpulan --}}
        @if(!empty($card['kesimpulan']))
            <div class="rounded-xl bg-gray-50 border border-gray-100 px-4 py-3 space-y-1">
                <div class="flex items-center gap-2">
                    <i class="ph ph-info {{ $headerTheme['accent'] }} text-sm"></i>
                    <p class="text-xs text-gray-700">
                        <span class="font-semibold text-gray-900">Kesimpulan:</span> {{ $card['kesimpulan'] }}
                    </p>
                </div>
                @if(!empty($penilaian['penjelasan']))
                    <p class="text-xs text-gray-500 leading-relaxed pl-5">{{ $penilaian['penjelasan'] }}</p>
                @endif
            </div>
        @endif
    </div>
</article>
