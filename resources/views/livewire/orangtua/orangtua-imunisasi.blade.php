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
                <div class="pt-4 border-t border-gray-200 max-w-md">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Filter Nama Sasaran</label>
                    <select wire:model.live="filterNama"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary text-sm">
                        <option value="">— Pilih sasaran —</option>
                        @foreach($namaSasaranList as $nama)
                            <option value="{{ $nama }}">{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        {{-- 1. Tabel riwayat imunisasi --}}
        @include('livewire.orangtua.partials.imunisasi-daftar')

        {{-- 2. Grafik & penilaian (hanya saat nama sasaran dipilih) --}}
        @if($filterNama !== '')
            <div wire:key="grafik-penilaian-{{ md5($filterNama) }}">
                @include('livewire.orangtua.partials.imunisasi-grafik-penilaian')
            </div>
        @endif
    </div>
</div>

@script
<script>
    (function () {
        const componentRoot = $wire.$el;
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

        function destroyOrangtuaCharts() {
            componentRoot.querySelectorAll('canvas.orangtua-grafik-canvas').forEach((canvas) => {
                const existing = chartInstances.get(canvas);
                if (existing) {
                    existing.destroy();
                    chartInstances.delete(canvas);
                }
            });
        }

        function initOrangtuaCharts() {
            destroyOrangtuaCharts();

            const canvases = componentRoot.querySelectorAll('canvas.orangtua-grafik-canvas');
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
                requestAnimationFrame(() => requestAnimationFrame(initOrangtuaCharts));
            }, 50);
        }

        function registerCommitHook() {
            Livewire.hook('commit', ({ component, succeed }) => {
                if (component.el !== componentRoot) {
                    return;
                }
                succeed(() => scheduleChartInit());
            });
        }

        scheduleChartInit();

        $wire.$watch('filterNama', () => {
            scheduleChartInit();
        });

        if (window.Livewire) {
            registerCommitHook();
        } else {
            document.addEventListener('livewire:init', registerCommitHook);
        }
    })();
</script>
@endscript
