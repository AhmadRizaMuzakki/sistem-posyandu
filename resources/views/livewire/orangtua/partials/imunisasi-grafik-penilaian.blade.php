@if($totalImunisasi > 0)
    @if(count($grafikPertumbuhan ?? []) > 0)
    {{-- Grafik Batang Pertumbuhan --}}
    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-2 flex items-center">
            <i class="ph ph-chart-bar text-2xl mr-3 text-primary shrink-0"></i>
            <span class="break-words">Grafik Pertumbuhan</span>
        </h2>
        <p class="text-sm text-gray-600 mb-6">
            Berdasarkan {{ $totalImunisasi }} catatan imunisasi {{ $filterNama }}.
        </p>

            <div class="space-y-8">
                <h3 class="text-sm font-semibold text-gray-700">Grafik Berat & Tinggi Badan per Kunjungan</h3>
                @foreach($grafikPertumbuhan as $index => $grafik)
                    <div class="border border-gray-100 rounded-lg p-4 bg-gray-50/40">
                        <div class="flex flex-wrap items-center gap-2 mb-4">
                            <span class="font-semibold text-gray-800">{{ $grafik['nama'] }}</span>
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-primary/10 text-primary">{{ $grafik['kategori'] }}</span>
                        </div>
                        <div class="h-80 w-full">
                            <canvas id="grafikPertumbuhan{{ $index }}"></canvas>
                        </div>
                    </div>
                @endforeach
            </div>
    </div>
    @endif

    {{-- Penilaian Antropometri per Kategori --}}
    <div class="rounded-2xl bg-gradient-to-br from-white to-gray-50/80 border border-gray-100 shadow-md overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-white/80">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                    <i class="ph ph-scales text-xl text-primary"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 tracking-tight">Hasil Penilaian</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Berdasarkan pengukuran terakhir setiap kunjungan imunisasi/posyandu</p>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-8">
            @foreach($penilaianPerKategori as $kategori)
                @if(count($kategori['sasaran']) > 0)
                    @php
                        $kategoriStyles = [
                            'bayibalita' => ['chip' => 'bg-amber-100 text-amber-800', 'icon' => 'ph-baby', 'dot' => 'bg-amber-400'],
                            'remaja' => ['chip' => 'bg-violet-100 text-violet-800', 'icon' => 'ph-person', 'dot' => 'bg-violet-400'],
                            'dewasa' => ['chip' => 'bg-emerald-100 text-emerald-800', 'icon' => 'ph-users-three', 'dot' => 'bg-emerald-400'],
                            'pralansia' => ['chip' => 'bg-orange-100 text-orange-800', 'icon' => 'ph-user-circle', 'dot' => 'bg-orange-400'],
                            'lansia' => ['chip' => 'bg-indigo-100 text-indigo-800', 'icon' => 'ph-user-gear', 'dot' => 'bg-indigo-400'],
                        ];
                        $style = $kategoriStyles[$kategori['slug']] ?? ['chip' => 'bg-gray-100 text-gray-800', 'icon' => 'ph-user', 'dot' => 'bg-gray-400'];
                    @endphp
                    <section>
                        <div class="flex items-center gap-2.5 mb-4">
                            <span class="w-2 h-2 rounded-full {{ $style['dot'] }}"></span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide {{ $style['chip'] }}">
                                <i class="ph {{ $style['icon'] }} text-sm"></i>
                                {{ $kategori['label'] }}
                            </span>
                        </div>
                        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
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
                                    <div class="rounded-2xl border border-dashed border-gray-200 bg-white p-5 text-center">
                                        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                                            <i class="ph ph-warning text-xl text-gray-400"></i>
                                        </div>
                                        <p class="font-semibold text-gray-800">{{ $sasaran['nama'] }}</p>
                                        <p class="text-xs text-gray-400 mt-1 mb-2">NIK: {{ $sasaran['nik'] ?? '-' }}</p>
                                        @if($sasaran['berat_badan'] === null && $sasaran['tinggi_badan'] === null)
                                            <p class="text-sm text-gray-500">Belum ada data BB/TB pada imunisasi terakhir.</p>
                                        @else
                                            <p class="text-sm text-gray-500">Data pengukuran tidak lengkap untuk penilaian.</p>
                                        @endif
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
