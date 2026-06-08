@if($totalImunisasi > 0)
    <div class="space-y-6">
        {{-- Grafik Pertumbuhan --}}
        @if(count($grafikPertumbuhan ?? []) > 0)
            <div class="bg-white rounded-xl border border-gray-200 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-1">Grafik Pertumbuhan</h2>
                <p class="text-sm text-gray-500 mb-5">
                    {{ $totalImunisasi }} kunjungan — {{ $filterNama }}
                </p>

                @foreach($grafikPertumbuhan as $index => $grafik)
                    <div @class(['mt-6' => $index > 0])>
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-sm font-medium text-gray-800">{{ $grafik['nama'] }}</span>
                            <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600">{{ $grafik['kategori'] }}</span>
                        </div>
                        <div class="relative h-72 sm:h-80 w-full">
                            <canvas
                                class="orangtua-grafik-canvas"
                                data-index="{{ $index }}"
                                data-labels='@json($grafik['labels'])'
                                data-berat='@json($grafik['berat'])'
                                data-tinggi='@json($grafik['tinggi'])'
                            ></canvas>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Hasil Penilaian --}}
        @php
            $adaPenilaian = collect($penilaianPerKategori)->contains(fn ($k) => count($k['sasaran']) > 0);
        @endphp

        @if($adaPenilaian)
            <div class="bg-white rounded-xl border border-gray-200 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-1">Hasil Penilaian</h2>
                <p class="text-sm text-gray-500 mb-5">Pengukuran terakhir setiap kunjungan imunisasi/posyandu</p>

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
            </div>
        @endif
    </div>
@endif
