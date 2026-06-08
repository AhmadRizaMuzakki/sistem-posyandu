<div id="orangtua-imunisasi-root">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="ph ph-syringe text-2xl mr-3 text-primary"></i>
                    Status Imunisasi
                </h2>
                @if($imunisasiList->count() > 0)
                    <button wire:click="exportImunisasiPdf"
                            class="inline-flex items-center px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors">
                        <i class="ph ph-file-pdf text-lg mr-2"></i>
                        Export PDF
                    </button>
                @endif
            </div>

            {{-- Filter --}}
            @if(count($namaSasaranList ?? []) > 0)
                <form method="GET"
                      action="{{ route('orangtua.imunisasi') }}"
                      class="pt-4 border-t border-gray-200 max-w-md">
                    <label for="filter-sasaran" class="block text-sm font-medium text-gray-700 mb-1">Filter Nama Sasaran</label>
                    <select id="filter-sasaran"
                            name="sasaran"
                            onchange="this.form.submit()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary text-sm bg-white">
                        <option value="">— Pilih sasaran —</option>
                        @foreach($namaSasaranList as $nama)
                            <option value="{{ $nama }}" @selected(trim($filterNama ?? '') === trim($nama))>{{ $nama }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>

        {{-- 1. Tabel riwayat imunisasi --}}
        @include('livewire.orangtua.partials.imunisasi-daftar')

        {{-- 2. Grafik & penilaian (hanya saat nama sasaran dipilih) --}}
        @if($filterAktif ?? false)
            <div wire:key="grafik-penilaian-{{ md5($filterNama) }}">
                @include('livewire.orangtua.partials.imunisasi-grafik-penilaian', [
                    'filterNama' => $filterNama,
                    'grafikPertumbuhan' => $grafikPertumbuhan,
                    'penilaianPerKategori' => $penilaianPerKategori,
                    'totalImunisasi' => $totalImunisasi,
                ])
            </div>
        @else
            <div class="bg-white rounded-xl border border-dashed border-gray-200 p-8 text-center">
                <i class="ph ph-funnel text-3xl text-gray-300 mb-2"></i>
                <p class="text-sm text-gray-600">Pilih nama sasaran pada filter di atas untuk melihat grafik pertumbuhan dan hasil penilaian.</p>
            </div>
        @endif
    </div>
</div>

@once
@push('scripts')
<script>
    (function () {
        if (window.__orangtuaGrafikInit) {
            return;
        }
        window.__orangtuaGrafikInit = true;

        const chartInstances = new WeakMap();
        let initTimer = null;

        function waitForChartJs(callback, attempts = 40) {
            if (typeof Chart !== 'undefined') {
                callback();
                return;
            }
            if (attempts <= 0) {
                return;
            }
            setTimeout(() => waitForChartJs(callback, attempts - 1), 100);
        }

        function destroyOrangtuaCharts(root) {
            root.querySelectorAll('canvas.orangtua-grafik-canvas').forEach((canvas) => {
                const existing = chartInstances.get(canvas);
                if (existing) {
                    existing.destroy();
                    chartInstances.delete(canvas);
                }
            });
        }

        function initOrangtuaCharts(root) {
            destroyOrangtuaCharts(root);

            const canvases = root.querySelectorAll('canvas.orangtua-grafik-canvas');
            if (!canvases.length) {
                return;
            }

            waitForChartJs(() => {
                const isMobile = window.innerWidth < 768;
                const fontSize = isMobile ? 11 : 12;

                canvases.forEach((canvas) => {
                    let labels, berat, tinggi;
                    try {
                        labels = JSON.parse(canvas.dataset.labels || '[]');
                        berat = JSON.parse(canvas.dataset.berat || '[]');
                        tinggi = JSON.parse(canvas.dataset.tinggi || '[]');
                    } catch (e) {
                        return;
                    }

                    if (!labels.length) {
                        return;
                    }

                    const chart = new Chart(canvas.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [
                                {
                                    label: 'Berat (kg)',
                                    data: berat,
                                    backgroundColor: 'rgba(59, 130, 246, 0.75)',
                                    borderColor: 'rgb(59, 130, 246)',
                                    borderWidth: 1,
                                    borderRadius: 4,
                                    yAxisID: 'y',
                                    minBarLength: 8,
                                },
                                {
                                    label: 'Tinggi (cm)',
                                    data: tinggi,
                                    backgroundColor: 'rgba(16, 185, 129, 0.75)',
                                    borderColor: 'rgb(16, 185, 129)',
                                    borderWidth: 1,
                                    borderRadius: 4,
                                    yAxisID: 'y1',
                                    minBarLength: 8,
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { mode: 'index', intersect: false },
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                    labels: { font: { size: fontSize }, boxWidth: 12 },
                                },
                            },
                            scales: {
                                y: {
                                    type: 'linear',
                                    position: 'left',
                                    beginAtZero: true,
                                    title: { display: true, text: 'Berat (kg)', font: { size: fontSize } },
                                    ticks: { font: { size: fontSize } },
                                },
                                y1: {
                                    type: 'linear',
                                    position: 'right',
                                    beginAtZero: true,
                                    grid: { drawOnChartArea: false },
                                    title: { display: true, text: 'Tinggi (cm)', font: { size: fontSize } },
                                    ticks: { font: { size: fontSize } },
                                },
                                x: {
                                    ticks: { font: { size: fontSize }, maxRotation: 45 },
                                },
                            },
                        },
                    });

                    chartInstances.set(canvas, chart);
                });
            });
        }

        function scheduleChartInit() {
            clearTimeout(initTimer);
            initTimer = setTimeout(() => {
                const root = document.getElementById('orangtua-imunisasi-root');
                if (!root) {
                    return;
                }
                requestAnimationFrame(() => requestAnimationFrame(() => initOrangtuaCharts(root)));
            }, 80);
        }

        function registerHooks() {
            Livewire.hook('commit', ({ succeed }) => {
                succeed(() => scheduleChartInit());
            });
        }

        document.addEventListener('DOMContentLoaded', scheduleChartInit);

        if (window.Livewire) {
            registerHooks();
        } else {
            document.addEventListener('livewire:init', registerHooks);
        }
    })();
</script>
@endpush
@endonce
