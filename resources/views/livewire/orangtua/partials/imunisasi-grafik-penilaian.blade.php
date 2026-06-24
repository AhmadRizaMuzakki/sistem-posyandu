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
                <div @class(['mt-6' => $index > 0])>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-sm font-medium text-gray-800">{{ $grafik['nama'] }}</span>
                        <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600">{{ $grafik['kategori'] }}</span>
                    </div>
                    <div wire:ignore class="relative h-72 sm:h-80 w-full">
                        <canvas
                            class="orangtua-grafik-canvas w-full h-full"
                            data-labels='@json($grafik['labels'])'
                            data-berat='@json($grafik['berat'])'
                            data-tinggi='@json($grafik['tinggi'])'
                        ></canvas>
                    </div>
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
