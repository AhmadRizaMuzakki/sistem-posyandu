{{-- Kartu penilaian — grid data + kartu indeks + kesimpulan --}}
@php
    $card = $penilaian['card'] ?? null;
    $warnaUtama = $card['warna'] ?? ($penilaian['kategori_color'] ?? 'green');

    $accentClass = match ($warnaUtama) {
        'green' => 'text-emerald-600',
        'orange' => 'text-amber-600',
        'yellow' => 'text-yellow-600',
        'red' => 'text-rose-600',
        default => 'text-gray-500',
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
                'wrap' => 'bg-white border-gray-200',
                'badge' => 'bg-amber-500 text-white',
                'title' => 'text-gray-800',
                'body' => 'text-gray-600',
                'icon' => 'text-amber-500',
            ],
            'yellow' => [
                'wrap' => 'bg-white border-gray-200',
                'badge' => 'bg-yellow-500 text-white',
                'title' => 'text-gray-800',
                'body' => 'text-gray-600',
                'icon' => 'text-yellow-600',
            ],
            'red' => [
                'wrap' => 'bg-white border-gray-200',
                'badge' => 'bg-rose-500 text-white',
                'title' => 'text-gray-800',
                'body' => 'text-gray-600',
                'icon' => 'text-rose-500',
            ],
            default => [
                'wrap' => 'bg-white border-gray-200',
                'badge' => 'bg-gray-500 text-white',
                'title' => 'text-gray-800',
                'body' => 'text-gray-600',
                'icon' => 'text-gray-500',
            ],
        };
    };

    $statusStunting = $card['status_stunting'] ?? ($penilaian['status_stunting'] ?? null);
    if ($statusStunting === null) {
        $tbU = collect($card['indeks'] ?? ($penilaian['indeks'] ?? []))->firstWhere('singkat', 'TB/U');
        if ($tbU) {
            $statusTb = strtolower((string) ($tbU['status'] ?? ''));
            if ($statusTb !== '' && $statusTb !== 'tidak tersedia') {
                $statusStunting = str_contains($statusTb, 'pendek') ? 'Stunting' : 'Tidak stunting';
            }
        }
    }
    $statusStuntingColor = $card['status_stunting_color']
        ?? ($penilaian['status_stunting_color'] ?? null)
        ?? ($statusStunting === 'Stunting' ? 'red' : ($statusStunting === 'Tidak stunting' ? 'green' : 'gray'));
    $statusStuntingBadge = match ($statusStuntingColor) {
        'red' => 'bg-rose-500 text-white',
        'green' => 'bg-emerald-500 text-white',
        default => 'bg-gray-500 text-white',
    };
@endphp

<article class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    {{-- Header sasaran --}}
    <div class="px-5 py-4 border-b border-gray-100">
        <h4 class="font-semibold text-gray-900">{{ $namaSasaran }}</h4>
        @if(!empty($tanggalKunjungan))
            <p class="text-xs text-gray-500 mt-1">{{ $tanggalKunjungan }}</p>
        @endif
    </div>

    <div class="p-5 space-y-5">
        {{-- Grid data pengukuran --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
            @php
                $stats = [
                    ['icon' => 'ph-gender-intersex', 'label' => 'Jenis Kelamin', 'value' => $card['jenis_kelamin'] ?? ($penilaian['jenis_kelamin'] ?? '-')],
                    ['icon' => 'ph-calendar-blank', 'label' => 'Tanggal Lahir', 'value' => $tanggalLahir ?? '-'],
                    ['icon' => 'ph-cake', 'label' => 'Umur', 'value' => isset($card['umur_bulan']) ? $card['umur_bulan'] . ' Bulan' : ($card['umur_label'] ?? ($penilaian['umur_label'] ?? '-'))],
                    ['icon' => 'ph-scales', 'label' => 'Berat Badan', 'value' => isset($card['berat_badan']) ? number_format($card['berat_badan'], 1, ',', '.') . ' kg' : (isset($penilaian['berat_badan']) ? number_format($penilaian['berat_badan'], 1, ',', '.') . ' kg' : '-')],
                    ['icon' => 'ph-ruler', 'label' => 'Tinggi Badan', 'value' => isset($card['tinggi_badan']) ? number_format($card['tinggi_badan'], 1, ',', '.') . ' cm' : (isset($penilaian['tinggi_badan']) ? number_format($penilaian['tinggi_badan'], 1, ',', '.') . ' cm' : '-')],
                    ['icon' => 'ph-heartbeat', 'label' => 'Tekanan Darah', 'value' => !empty($card['tekanan_darah']) ? $card['tekanan_darah'] . ' mmHg' : '-'],
                    ['icon' => 'ph-drop', 'label' => 'Gula Darah', 'value' => isset($card['gula_darah']) && $card['gula_darah'] !== null ? number_format($card['gula_darah'], 0, ',', '.') . ' mg/dL' : '-'],
                ];
            @endphp
            @foreach($stats as $stat)
                <div class="rounded-xl bg-gray-50 border border-gray-100 px-3 py-2.5">
                    <i class="ph {{ $stat['icon'] }} {{ $accentClass }} text-base mb-1"></i>
                    <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">{{ $stat['label'] }}</p>
                    <p class="text-sm font-bold text-gray-800 mt-0.5 leading-snug">{{ $stat['value'] }}</p>
                </div>
            @endforeach
            <div class="rounded-xl bg-gray-50 border border-gray-100 px-3 py-2.5">
                <i class="ph ph-chart-line-up {{ $accentClass }} text-sm mb-1"></i>
                <p class="text-[8px] font-medium text-gray-400 uppercase tracking-wide">Status Stunting</p>
                <div class="mt-0.5">
                    @if($statusStunting)
                        <span class="inline-flex items-center px-1 py-0 rounded-full text-[7px] leading-3 font-medium {{ $statusStuntingBadge }}">
                            {{ $statusStunting }}
                        </span>
                    @else
                        <p class="text-[10px] font-bold text-gray-800 leading-snug">-</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Hasil penilaian per indeks --}}
        @if(!empty($card['indeks']) || !empty($penilaian['indeks']))
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
                                <div class="w-9 h-9 rounded-lg bg-white border border-gray-100 flex items-center justify-center shrink-0">
                                    <i class="ph {{ $icon }} {{ $theme['icon'] }} text-lg"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                        <span class="text-[11px] font-bold uppercase tracking-wide text-gray-500">{{ $item['singkat'] }}</span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $theme['badge'] }}">
                                            {{ $item['status'] }}
                                        </span>
                                    </div>
                                    <p class="text-xs font-medium {{ $theme['title'] }} mb-1">{{ $item['nama'] ?? '' }}</p>
                                    @if(!empty($item['nilai']))
                                        <p class="text-[11px] font-medium text-gray-500 mb-1.5">
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
        @endif

        {{-- Kesimpulan --}}
        @if(!empty($card['kesimpulan']) || $statusStunting)
            <div class="rounded-xl bg-gray-50 border border-gray-100 px-4 py-3 space-y-1.5">
                @if(!empty($card['kesimpulan']))
                    <div class="flex items-start gap-2">
                        <i class="ph ph-info {{ $accentClass }} text-sm mt-0.5 shrink-0"></i>
                        <p class="text-xs text-gray-700">
                            <span class="font-semibold text-gray-900">Kesimpulan:</span> {{ $card['kesimpulan'] }}
                        </p>
                    </div>
                @endif
                @if($statusStunting)
                    <div class="flex items-center gap-1.5 {{ !empty($card['kesimpulan']) ? 'pl-5' : '' }}">
                        <span class="text-[8px] text-gray-700 font-semibold">Status Stunting:</span>
                        <span class="inline-flex items-center px-1 py-0 rounded-full text-[7px] leading-3 font-medium {{ $statusStuntingBadge }}">
                            {{ $statusStunting }}
                        </span>
                    </div>
                @endif
                @if(!empty($penilaian['penjelasan']))
                    <p class="text-xs text-gray-500 leading-relaxed {{ !empty($card['kesimpulan']) || $statusStunting ? 'pl-5' : '' }}">{{ $penilaian['penjelasan'] }}</p>
                @endif
            </div>
        @endif
    </div>
</article>
