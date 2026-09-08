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

        function xySeries(series) {
            return (series || []).map((pt) => ({ x: Number(pt.x), y: Number(pt.y) }));
        }

        function kmsCalloutPlugin(points, unit = 'kg') {
            return {
                id: 'kmsCallouts',
                afterDatasetsDraw(chart) {
                    if (!points || !points.length) {
                        return;
                    }
                    const meta = chart.getDatasetMeta(chart.data.datasets.length - 1);
                    if (!meta || !meta.data) {
                        return;
                    }
                    const ctx = chart.ctx;
                    const sample = points.slice(-Math.min(3, points.length));
                    const offsets = [-58, -78, -58];

                    sample.forEach((pt, idx) => {
                        const el = meta.data[points.length - sample.length + idx];
                        if (!el) {
                            return;
                        }
                        const x = el.x;
                        const y = el.y;
                        const nilai = pt.nilai ?? pt.berat ?? pt.tinggi;
                        const nilaiText = `${Number(nilai).toLocaleString('id-ID', { maximumFractionDigits: 1 })} ${unit}`;
                        const metaParts = [`${Math.round(Number(pt.umur_bulan))} bln`];
                        if (pt.tanggal) {
                            metaParts.push(pt.tanggal);
                        }
                        const metaText = metaParts.join(' · ');

                        ctx.save();
                        ctx.font = 'bold 14px sans-serif';
                        const nilaiW = ctx.measureText(nilaiText).width;
                        ctx.font = '10px sans-serif';
                        const metaW = ctx.measureText(metaText).width;
                        const padX = 10;
                        const boxW = Math.max(nilaiW, metaW) + padX * 2;
                        const boxH = 40;
                        let bx = x - boxW / 2;
                        let by = y + offsets[idx % offsets.length];
                        const area = chart.chartArea;
                        bx = Math.max(area.left + 2, Math.min(bx, area.right - boxW - 2));
                        by = Math.max(area.top + 2, Math.min(by, area.bottom - boxH - 2));
                        if (by + boxH + 10 > y) {
                            by = Math.max(area.top + 2, y - boxH - 16);
                        }

                        ctx.fillStyle = '#eff6ff';
                        ctx.strokeStyle = '#2563eb';
                        ctx.lineWidth = 1.5;
                        ctx.beginPath();
                        if (typeof ctx.roundRect === 'function') {
                            ctx.roundRect(bx, by, boxW, boxH, 6);
                        } else {
                            ctx.rect(bx, by, boxW, boxH);
                        }
                        ctx.fill();
                        ctx.stroke();
                        ctx.beginPath();
                        ctx.moveTo(bx + boxW / 2, by + boxH);
                        ctx.lineTo(x, y - 6);
                        ctx.stroke();

                        ctx.fillStyle = '#1d4ed8';
                        ctx.font = 'bold 14px sans-serif';
                        ctx.fillText(nilaiText, bx + padX, by + 17);

                        ctx.fillStyle = '#6b7280';
                        ctx.font = '10px sans-serif';
                        ctx.fillText(metaText, bx + padX, by + 32);
                        ctx.restore();
                    });
                },
            };
        }

        function buildKmsChart(canvas, kms, fontSize) {
            const curves = kms.curves || {};
            const yMin = Number(kms.y_min ?? 2);
            const yMax = Number(kms.y_max ?? 26);
            const xMin = Number(kms.x_min ?? 0);
            const xMax = Number(kms.x_max ?? 60);
            const unit = kms.unit || 'kg';
            const yAxisLabel = kms.y_axis_label || 'Berat Badan (kg)';
            const yStep = (kms.indeks === 'tb_u') ? 5 : 2;
            const childPoints = (kms.points || []).map((p) => {
                const nilai = p.nilai ?? p.berat ?? p.tinggi;
                return {
                    x: Number(p.umur_bulan),
                    y: Number(nilai),
                    umur_bulan: p.umur_bulan,
                    tanggal: p.tanggal,
                    nilai,
                };
            });

            const zoneLine = (data, bg, border = 'rgba(146,64,14,0.45)') => ({
                data,
                borderColor: border,
                backgroundColor: bg,
                borderWidth: 1,
                borderDash: [4, 3],
                pointRadius: 0,
                pointHoverRadius: 0,
                tension: 0.15,
                fill: false,
                order: 10,
            });

            const datasets = [
                {
                    ...zoneLine(xySeries(curves.sd3_min), 'rgba(254,202,202,0.72)', 'rgba(146,64,14,0.35)'),
                    label: '< −3 SD',
                    fill: { value: yMin },
                },
                {
                    ...zoneLine(xySeries(curves.min), 'rgba(253,224,71,0.55)'),
                    label: '−3 s/d −2 SD',
                    fill: '-1',
                },
                {
                    ...zoneLine(xySeries(curves.sd1_min), 'rgba(187,247,208,0.55)'),
                    label: '−2 s/d −1 SD',
                    fill: '-1',
                },
                {
                    ...zoneLine(xySeries(curves.sd1_max), 'rgba(74,222,128,0.55)'),
                    label: '−1 s/d +1 SD',
                    fill: '-1',
                },
                {
                    ...zoneLine(xySeries(curves.max), 'rgba(187,247,208,0.55)'),
                    label: '+1 s/d +2 SD',
                    fill: '-1',
                },
                {
                    label: '> +2 SD',
                    data: xySeries(curves.max).map((p) => ({ x: p.x, y: yMax })),
                    borderWidth: 0,
                    pointRadius: 0,
                    backgroundColor: 'rgba(253,186,116,0.50)',
                    fill: '-1',
                    order: 10,
                },
                {
                    label: 'Median (0 SD)',
                    data: xySeries(curves.median),
                    borderColor: 'rgb(21,128,61)',
                    backgroundColor: 'rgb(21,128,61)',
                    borderWidth: 2.5,
                    pointRadius: 0,
                    tension: 0.15,
                    fill: false,
                    order: 5,
                },
                {
                    label: '+3 SD',
                    data: xySeries(curves.sd3_max),
                    borderColor: 'rgba(220,38,38,0.55)',
                    borderWidth: 1,
                    borderDash: [4, 3],
                    pointRadius: 0,
                    tension: 0.15,
                    fill: false,
                    order: 6,
                },
                {
                    label: 'Data Anak',
                    data: childPoints,
                    borderColor: 'rgb(37,99,235)',
                    backgroundColor: 'rgb(37,99,235)',
                    borderWidth: 2.5,
                    pointBackgroundColor: 'rgb(37,99,235)',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    tension: 0,
                    fill: false,
                    order: 1,
                },
            ];

            return new Chart(canvas.getContext('2d'), {
                type: 'line',
                data: { datasets },
                plugins: [kmsCalloutPlugin(kms.points || [], unit)],
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    parsing: false,
                    interaction: { mode: 'nearest', intersect: false, axis: 'x' },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                font: { size: fontSize - 1 },
                                boxWidth: 10,
                                filter(item) {
                                    return ['Data Anak', 'Median (0 SD)'].includes(item.text);
                                },
                            },
                        },
                        tooltip: {
                            filter(ctx) {
                                return ctx.dataset.label === 'Data Anak';
                            },
                            callbacks: {
                                title(items) {
                                    const raw = items[0]?.raw;
                                    if (!raw) return '';
                                    return `${Math.round(Number(raw.x))} bulan` + (raw.tanggal ? ` · ${raw.tanggal}` : '');
                                },
                                label(ctx) {
                                    return `${yAxisLabel.replace(/ \(.*\)$/, '')}: ${Number(ctx.parsed.y).toLocaleString('id-ID', { maximumFractionDigits: 1 })} ${unit}`;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            type: 'linear',
                            min: xMin,
                            max: xMax,
                            title: {
                                display: true,
                                text: 'Umur (Bulan)',
                                color: '#374151',
                                font: { size: fontSize, weight: '600' },
                            },
                            ticks: {
                                stepSize: 6,
                                font: { size: fontSize },
                                color: '#6b7280',
                            },
                            grid: { color: 'rgba(156,163,175,0.28)' },
                        },
                        y: {
                            type: 'linear',
                            min: yMin,
                            max: yMax,
                            title: {
                                display: true,
                                text: yAxisLabel,
                                color: '#374151',
                                font: { size: fontSize, weight: '600' },
                            },
                            ticks: {
                                stepSize: yStep,
                                font: { size: fontSize },
                                color: '#6b7280',
                            },
                            grid: { color: 'rgba(156,163,175,0.28)' },
                        },
                    },
                },
            });
        }

        function buildLineChart(canvas, labels, berat, tinggi, fontSize) {
            const toNums = (arr) => arr
                .map((v) => (v === null || v === undefined || v === '' ? null : Number(v)))
                .filter((v) => v !== null && !Number.isNaN(v));

            const beratNums = toNums(berat);
            let yMax = beratNums.length ? Math.max(...beratNums) : 10;
            let yMin = beratNums.length ? Math.min(...beratNums) : 0;
            yMax = Math.ceil(yMax * 1.15);
            yMin = Math.max(0, Math.floor(yMin * 0.85));
            if (yMax < 10) {
                yMax = 10;
            }

            return new Chart(canvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Berat Badan (kg)',
                            data: berat,
                            borderColor: 'rgb(37, 99, 235)',
                            backgroundColor: 'rgb(37, 99, 235)',
                            borderWidth: 2.5,
                            pointBackgroundColor: 'rgb(37, 99, 235)',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointStyle: 'circle',
                            tension: 0.15,
                            fill: false,
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
                                boxWidth: 12,
                                boxHeight: 12,
                                usePointStyle: true,
                                pointStyle: 'line',
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
                                        return 'Berat Badan: -';
                                    }
                                    return `Berat Badan: ${Number(raw).toLocaleString('id-ID', { maximumFractionDigits: 1 })} kg`;
                                },
                            },
                        },
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            position: 'left',
                            beginAtZero: yMin === 0,
                            min: yMin,
                            max: yMax,
                            title: {
                                display: true,
                                text: 'Berat Badan (kg)',
                                color: '#374151',
                                font: { size: fontSize, weight: '600' },
                            },
                            ticks: {
                                font: { size: fontSize },
                                color: '#6b7280',
                                callback(value) {
                                    return Number(value).toLocaleString('id-ID', { maximumFractionDigits: 0 });
                                },
                            },
                            grid: {
                                color: 'rgba(156, 163, 175, 0.35)',
                                drawBorder: false,
                            },
                        },
                        x: {
                            offset: true,
                            title: {
                                display: true,
                                text: 'Tanggal Kunjungan',
                                color: '#374151',
                                font: { size: fontSize, weight: '600' },
                            },
                            ticks: {
                                font: { size: fontSize },
                                maxRotation: 0,
                                autoSkip: true,
                                color: '#6b7280',
                            },
                            grid: {
                                display: false,
                                drawBorder: false,
                            },
                        },
                    },
                },
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
                    let labels, berat, tinggi, kms, mode;
                    try {
                        labels = JSON.parse(canvas.dataset.labels || '[]');
                        berat = JSON.parse(canvas.dataset.berat || '[]');
                        tinggi = JSON.parse(canvas.dataset.tinggi || '[]');
                        kms = JSON.parse(canvas.dataset.kms || 'null');
                        mode = canvas.dataset.mode || 'line';
                    } catch (e) {
                        return;
                    }

                    let chart = null;
                    if ((mode === 'kms_bb_u' || mode === 'kms_tb_u') && kms && kms.curves) {
                        chart = buildKmsChart(canvas, kms, fontSize);
                    } else if (labels.length) {
                        chart = buildLineChart(canvas, labels, berat, tinggi, fontSize);
                    }

                    if (chart) {
                        chartInstances.set(canvas, chart);
                    }
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
