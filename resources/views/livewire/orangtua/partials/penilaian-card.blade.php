{{-- Kartu penilaian — tampilan sederhana & bersih --}}
@php
    $card = $penilaian['card'] ?? null;
    $warnaUtama = $card['warna'] ?? ($penilaian['kategori_color'] ?? 'green');

    $badgeClass = match ($warnaUtama) {
        'green' => 'bg-emerald-100 text-emerald-800',
        'orange' => 'bg-amber-100 text-amber-800',
        'yellow' => 'bg-yellow-100 text-yellow-800',
        'red' => 'bg-rose-100 text-rose-800',
        default => 'bg-gray-100 text-gray-800',
    };

    $statusDot = match ($warnaUtama) {
        'green' => 'bg-emerald-500',
        'orange' => 'bg-amber-500',
        'yellow' => 'bg-yellow-500',
        'red' => 'bg-rose-500',
        default => 'bg-gray-400',
    };

    $indeksBadge = fn (string $color) => match ($color) {
        'green' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'orange' => 'bg-amber-50 text-amber-700 border-amber-200',
        'yellow' => 'bg-yellow-50 text-yellow-800 border-yellow-200',
        'red' => 'bg-rose-50 text-rose-700 border-rose-200',
        default => 'bg-gray-50 text-gray-700 border-gray-200',
    };
@endphp

<article class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    {{-- Header --}}
    <div class="px-5 py-4 border-b border-gray-100 flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <h4 class="font-semibold text-gray-900 text-base leading-snug">{{ $namaSasaran }}</h4>
            @if(!empty($tanggalKunjungan))
                <p class="text-xs text-gray-500 mt-1">{{ $tanggalKunjungan }}</p>
            @endif
        </div>
        @if(!empty($card['kesimpulan']))
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $badgeClass }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $statusDot }}"></span>
                {{ $card['kesimpulan'] }}
            </span>
        @endif
    </div>

    <div class="p-5 space-y-5">
        {{-- Data pengukuran --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <tbody class="divide-y divide-gray-100">
                    <tr>
                        <td class="py-2 pr-4 text-gray-500 w-36">Jenis Kelamin</td>
                        <td class="py-2 font-medium text-gray-900">{{ $card['jenis_kelamin'] ?? '-' }}</td>
                        <td class="py-2 pr-4 text-gray-500 w-36">Umur</td>
                        <td class="py-2 font-medium text-gray-900">{{ isset($card['umur_bulan']) ? $card['umur_bulan'] . ' bulan' : ($card['umur_label'] ?? '-') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 text-gray-500">Berat Badan</td>
                        <td class="py-2 font-medium text-gray-900">{{ number_format($card['berat_badan'], 1, ',', '.') }} kg</td>
                        <td class="py-2 pr-4 text-gray-500">Tinggi Badan</td>
                        <td class="py-2 font-medium text-gray-900">{{ number_format($card['tinggi_badan'], 1, ',', '.') }} cm</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 text-gray-500">Tekanan Darah</td>
                        <td class="py-2 font-medium text-gray-900">{{ !empty($card['tekanan_darah']) ? $card['tekanan_darah'] . ' mmHg' : '-' }}</td>
                        <td class="py-2 pr-4 text-gray-500">Gula Darah</td>
                        <td class="py-2 font-medium text-gray-900">{{ isset($card['gula_darah']) && $card['gula_darah'] !== null ? number_format($card['gula_darah'], 0, ',', '.') . ' mg/dL' : '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Indeks penilaian --}}
        @if(!empty($card['indeks']) || !empty($penilaian['indeks']))
            <div>
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-3">Detail Penilaian</p>
                <div class="space-y-2">
                    @foreach($card['indeks'] ?? ($penilaian['indeks'] ?? []) as $item)
                        @php
                            $saran = $item['saran'] ?? ($item['rekomendasi'] ?? '');
                            $badge = $indeksBadge($item['color'] ?? 'gray');
                        @endphp
                        <div class="rounded-lg border px-4 py-3 {{ $badge }}">
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1 mb-1">
                                <span class="text-xs font-bold">{{ $item['singkat'] }}</span>
                                <span class="text-gray-400">·</span>
                                <span class="text-xs font-semibold">{{ $item['status'] }}</span>
                                @if(!empty($item['nilai']))
                                    <span class="text-gray-400">·</span>
                                    <span class="text-xs">{{ $item['nilai'] }}</span>
                                @endif
                            </div>
                            @if(!empty($item['nama']))
                                <p class="text-[11px] text-gray-500 mb-1">{{ $item['nama'] }}</p>
                            @endif
                            <p class="text-xs leading-relaxed opacity-90">{{ $saran }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if(!empty($penilaian['penjelasan']))
            <p class="text-xs text-gray-500 leading-relaxed border-t border-gray-100 pt-4">
                {{ $penilaian['penjelasan'] }}
            </p>
        @endif
    </div>
</article>
