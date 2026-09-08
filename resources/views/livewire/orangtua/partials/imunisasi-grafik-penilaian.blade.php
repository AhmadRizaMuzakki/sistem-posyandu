<div class="space-y-6">
    {{-- Grafik Pertumbuhan --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 sm:p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-1">Grafik Pertumbuhan</h2>
        <p class="text-sm text-gray-500 mb-5">
            @if($totalImunisasi > 0)
                {{ $totalImunisasi }} kunjungan — {{ $filterNama }}
                @if(!empty($filterBulanTahunAktif) && !empty($periodeLabel))
                    · {{ $periodeLabel }}
                @endif
            @else
                Belum ada data kunjungan untuk {{ $filterNama }}
                @if(!empty($filterBulanTahunAktif) && !empty($periodeLabel))
                    pada periode {{ $periodeLabel }}
                @endif
            @endif
        </p>

        @if(count($grafikPertumbuhan ?? []) > 0)
            @foreach($grafikPertumbuhan as $index => $grafik)
                @php
                    $hasKmsBb = !empty($grafik['kms']);
                    $hasKmsTb = !empty($grafik['kms_tb']);
                    $isKms = ($grafik['chart_mode'] ?? '') === 'kms' && ($hasKmsBb || $hasKmsTb);
                    $kmsMeta = $grafik['kms'] ?? $grafik['kms_tb'] ?? null;
                    $sharedLegend = [];
                    if ($hasKmsBb && $hasKmsTb) {
                        foreach ($grafik['kms']['legend'] as $i => $bbLeg) {
                            $tbLeg = $grafik['kms_tb']['legend'][$i] ?? null;
                            $sharedLegend[] = [
                                'color' => $bbLeg['color'],
                                'range' => $bbLeg['range'] ?? '',
                                'status' => trim(($bbLeg['status'] ?? '').($tbLeg ? ' / '.($tbLeg['status'] ?? '') : '')),
                            ];
                        }
                    } elseif ($hasKmsBb) {
                        $sharedLegend = $grafik['kms']['legend'];
                    } elseif ($hasKmsTb) {
                        $sharedLegend = $grafik['kms_tb']['legend'];
                    }
                @endphp

                <div @class(['mt-8' => $index > 0])>
                    @if($isKms)
                        <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
                            <div class="px-4 sm:px-5 pt-4 sm:pt-5 pb-3 border-b border-gray-100">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <span class="text-sm font-semibold text-gray-900">{{ $grafik['nama'] }}</span>
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600">{{ $grafik['kategori'] }}</span>
                                    @if($hasKmsBb)
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">BB/U</span>
                                    @endif
                                    @if($hasKmsTb)
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-sky-50 text-sky-700 border border-sky-100">TB/U</span>
                                    @endif
                                </div>
                                @if(!empty($kmsMeta['subtitle']))
                                    <p class="text-xs text-gray-500">{{ $kmsMeta['subtitle'] }}</p>
                                @endif

                                @if(!empty($sharedLegend))
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2 mt-3">
                                        @foreach($sharedLegend as $leg)
                                            <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-2">
                                                <span class="shrink-0 w-3.5 h-3.5 rounded-sm border border-gray-300" style="background: {{ $leg['color'] }}"></span>
                                                <div class="min-w-0 leading-tight">
                                                    <div class="text-[11px] font-semibold text-gray-800 tabular-nums">{{ $leg['range'] ?? '' }}</div>
                                                    <div class="text-[10px] text-gray-500">{{ $leg['status'] ?? '' }}</div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if($hasKmsBb && $hasKmsTb)
                                        <p class="text-[10px] text-gray-400 mt-2">Status legend: BB/U / TB/U</p>
                                    @endif
                                @endif
                            </div>

                            <div class="p-4 sm:p-5 space-y-6">
                                @if($hasKmsBb)
                                    @include('livewire.orangtua.partials.kms-chart-block', [
                                        'kms' => $grafik['kms'],
                                        'badge' => 'BB/U',
                                        'badgeClass' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                        'shortTitle' => 'Berat Badan menurut Umur',
                                        'mode' => 'kms_bb_u',
                                        'labels' => $grafik['labels'],
                                        'berat' => $grafik['berat'],
                                        'tinggi' => $grafik['tinggi'],
                                        'compact' => true,
                                    ])
                                @endif

                                @if($hasKmsBb && $hasKmsTb)
                                    <div class="border-t border-dashed border-gray-200"></div>
                                @endif

                                @if($hasKmsTb)
                                    @include('livewire.orangtua.partials.kms-chart-block', [
                                        'kms' => $grafik['kms_tb'],
                                        'badge' => 'TB/U',
                                        'badgeClass' => 'bg-sky-50 text-sky-700 border-sky-100',
                                        'shortTitle' => 'Tinggi Badan menurut Umur',
                                        'mode' => 'kms_tb_u',
                                        'labels' => $grafik['labels'],
                                        'berat' => $grafik['berat'],
                                        'tinggi' => $grafik['tinggi'],
                                        'compact' => true,
                                    ])
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="flex flex-wrap items-center gap-2 mb-3">
                            <span class="text-sm font-medium text-gray-800">{{ $grafik['nama'] }}</span>
                            <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600">{{ $grafik['kategori'] }}</span>
                        </div>
                        <div wire:ignore class="relative h-72 sm:h-80 w-full">
                            <canvas
                                class="orangtua-grafik-canvas w-full h-full"
                                data-mode="line"
                                data-labels='@json($grafik['labels'])'
                                data-berat='@json($grafik['berat'])'
                                data-tinggi='@json($grafik['tinggi'])'
                                data-kms='null'
                            ></canvas>
                        </div>
                    @endif
                </div>
            @endforeach
        @else
            <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50 p-8 text-center text-sm text-gray-500">
                <i class="ph ph-chart-bar text-3xl text-gray-300 mb-2"></i>
                <p>Grafik akan tampil setelah ada data tinggi dan berat badan pada kunjungan imunisasi.</p>
            </div>
        @endif
    </div>

    {{-- Hasil Penilaian --}}
    @php
        $adaPenilaian = collect($penilaianPerKategori)->contains(fn ($k) => count($k['sasaran']) > 0);
    @endphp

    <div class="bg-white rounded-xl border border-gray-200 p-5 sm:p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-1">Hasil Penilaian</h2>
        <p class="text-sm text-gray-500 mb-5">
            @if(!empty($filterBulanTahunAktif) && !empty($periodeLabel))
                Pengukuran terakhir pada periode {{ $periodeLabel }}
            @else
                Pengukuran terakhir setiap kunjungan imunisasi/posyandu
            @endif
        </p>

        @if($adaPenilaian)
            <div class="space-y-6">
                @foreach($penilaianPerKategori as $kategori)
                    @if(count($kategori['sasaran']) > 0)
                        <section>
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-3">{{ $kategori['label'] }}</p>
                            <div class="space-y-4">
                                @foreach($kategori['sasaran'] as $sasaran)
                                    @if($sasaran['penilaian'] && !empty($sasaran['penilaian']['card']))
                                        @include('livewire.orangtua.partials.penilaian-card', [
                                            'penilaian' => $sasaran['penilaian'],
                                            'namaSasaran' => $sasaran['nama'],
                                            'tanggalLahir' => $sasaran['tanggal_lahir'] ?? null,
                                            'tanggalKunjungan' => trim(
                                                ($sasaran['tanggal_imunisasi_terakhir'] ?? '') .
                                                (!empty($sasaran['jenis_imunisasi_terakhir']) ? ' — ' . $sasaran['jenis_imunisasi_terakhir'] : '')
                                            ),
                                        ])
                                    @else
                                        <div class="rounded-xl border border-dashed border-gray-200 p-5 text-center text-sm text-gray-500">
                                            <p class="font-medium text-gray-800">{{ $sasaran['nama'] }}</p>
                                            <p class="mt-1">Data pengukuran belum lengkap untuk penilaian.</p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </section>
                    @endif
                @endforeach
            </div>
        @else
            <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50 p-8 text-center text-sm text-gray-500">
                <i class="ph ph-clipboard-text text-3xl text-gray-300 mb-2"></i>
                <p>Hasil penilaian akan tampil setelah ada data pengukuran pada kunjungan imunisasi.</p>
            </div>
        @endif
    </div>
</div>
