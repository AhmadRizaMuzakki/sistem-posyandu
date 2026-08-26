<div id="orangtua-imunisasi-root">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="ph ph-syringe text-2xl mr-3 text-primary"></i>
                    Status Imunisasi
                </h2>
                @if(($totalBaris ?? 0) > 0)
                    <a href="{{ route('orangtua.imunisasi.pdf', array_filter([
                        'sasaran' => $filterNama ?? '',
                        'bulan' => $filterBulan ?? '',
                        'tahun' => $filterTahun ?? '',
                    ])) }}"
                       target="_blank"
                       class="inline-flex items-center px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors">
                        <i class="ph ph-file-pdf text-lg mr-2"></i>
                        Export PDF
                    </a>
                @endif
            </div>
        </div>

        {{-- 1. Tabel riwayat imunisasi + filter --}}
        @include('livewire.orangtua.partials.imunisasi-daftar')

        {{-- 2. Grafik & penilaian (hanya saat nama sasaran dipilih) --}}
        @if($filterNamaAktif ?? false)
            <div wire:key="grafik-penilaian-{{ md5($filterNama . '|' . ($filterBulan ?? '') . '|' . ($filterTahun ?? '')) }}">
                @include('livewire.orangtua.partials.imunisasi-grafik-penilaian', [
                    'filterNama' => $filterNama,
                    'filterBulanTahunAktif' => $filterBulanTahunAktif ?? false,
                    'periodeLabel' => $periodeLabel ?? null,
                    'grafikPertumbuhan' => $grafikPertumbuhan,
                    'penilaianPerKategori' => $penilaianPerKategori,
                    'totalImunisasi' => $totalImunisasi,
                ])
            </div>
        @else
            <div class="bg-white rounded-xl border border-dashed border-gray-200 p-8 text-center">
                <i class="ph ph-funnel text-3xl text-gray-300 mb-2"></i>
                <p class="text-sm text-gray-600">Pilih nama sasaran pada filter riwayat untuk melihat grafik pertumbuhan dan hasil penilaian.</p>
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

                    const toNums = (arr) => arr
                        .map((v) => (v === null || v === undefined || v === '' ? null : Number(v)))
                        .filter((v) => v !== null && !Number.isNaN(v));

                    const axisRange = (values) => {
                        const nums = toNums(values);
                        if (!nums.length) {
                            return { min: 0, max: 10 };
                        }
                        let min = Math.min(...nums);
                        let max = Math.max(...nums);
                        if (min === max) {
                            const pad = Math.max(1, max * 0.25);
                            min = Math.max(0, min - pad);
                            max = max + pad;
                        } else {
                            const pad = (max - min) * 0.2;
                            min = Math.max(0, min - pad);
                            max = max + pad;
                        }
                        return { min, max };
                    };

                    const tinggiRange = axisRange(tinggi);
                    const beratRange = axisRange(berat);

                    const chart = new Chart(canvas.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels,
                            datasets: [
                                {
                                    label: 'Tinggi (cm)',
                                    data: tinggi,
                                    borderColor: 'rgb(16, 185, 129)',
                                    backgroundColor: 'rgba(16, 185, 129, 0.08)',
                                    borderWidth: 2.5,
                                    pointBackgroundColor: 'rgb(16, 185, 129)',
                                    pointBorderColor: '#ffffff',
                                    pointBorderWidth: 2,
                                    pointRadius: 5,
                                    pointHoverRadius: 7,
                                    pointStyle: 'circle',
                                    tension: 0,
                                    fill: false,
                                    yAxisID: 'y',
                                    spanGaps: true,
                                },
                                {
                                    label: 'Berat (kg)',
                                    data: berat,
                                    borderColor: 'rgb(59, 130, 246)',
                                    backgroundColor: 'rgba(59, 130, 246, 0.08)',
                                    borderWidth: 2.5,
                                    pointBackgroundColor: 'rgb(59, 130, 246)',
                                    pointBorderColor: '#ffffff',
                                    pointBorderWidth: 2,
                                    pointRadius: 5,
                                    pointHoverRadius: 7,
                                    pointStyle: 'circle',
                                    tension: 0,
                                    fill: false,
                                    yAxisID: 'y1',
                                    spanGaps: true,
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
                                    align: 'center',
                                    labels: {
                                        font: { size: fontSize },
                                        boxWidth: 10,
                                        boxHeight: 10,
                                        usePointStyle: true,
                                        pointStyle: 'circle',
                                        padding: 16,
                                    },
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(17, 24, 39, 0.92)',
                                    titleFont: { size: fontSize },
                                    bodyFont: { size: fontSize },
                                    padding: 10,
                                    callbacks: {
                                        label(ctx) {
                                            const raw = ctx.parsed.y;
                                            if (raw === null || raw === undefined) {
                                                return `${ctx.dataset.label}: -`;
                                            }
                                            const unit = ctx.dataset.yAxisID === 'y' ? 'cm' : 'kg';
                                            return `${ctx.dataset.label}: ${Number(raw).toLocaleString('id-ID', { maximumFractionDigits: 1 })} ${unit}`;
                                        },
                                    },
                                },
                            },
                            scales: {
                                y: {
                                    type: 'linear',
                                    position: 'left',
                                    min: tinggiRange.min,
                                    max: tinggiRange.max,
                                    title: {
                                        display: true,
                                        text: 'Tinggi (cm)',
                                        font: { size: fontSize, weight: '600' },
                                        color: 'rgb(16, 185, 129)',
                                    },
                                    ticks: {
                                        font: { size: fontSize },
                                        color: 'rgb(16, 185, 129)',
                                        callback(value) {
                                            return Number(value).toLocaleString('id-ID', { maximumFractionDigits: 0 });
                                        },
                                    },
                                    grid: {
                                        color: 'rgba(156, 163, 175, 0.35)',
                                        drawBorder: false,
                                    },
                                },
                                y1: {
                                    type: 'linear',
                                    position: 'right',
                                    min: beratRange.min,
                                    max: beratRange.max,
                                    grid: { drawOnChartArea: false },
                                    title: {
                                        display: true,
                                        text: 'Berat (kg)',
                                        font: { size: fontSize, weight: '600' },
                                        color: 'rgb(59, 130, 246)',
                                    },
                                    ticks: {
                                        font: { size: fontSize },
                                        color: 'rgb(59, 130, 246)',
                                        callback(value) {
                                            return Number(value).toLocaleString('id-ID', { maximumFractionDigits: 1 });
                                        },
                                    },
                                },
                                x: {
                                    offset: true,
                                    ticks: {
                                        font: { size: fontSize },
                                        maxRotation: 0,
                                        autoSkip: true,
                                        color: '#6b7280',
                                    },
                                    grid: {
                                        color: 'rgba(156, 163, 175, 0.2)',
                                        drawBorder: false,
                                    },
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
