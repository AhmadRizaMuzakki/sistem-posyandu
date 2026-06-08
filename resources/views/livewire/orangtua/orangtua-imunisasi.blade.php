<div>
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

        {{-- 2. Grafik batang & penilaian (hanya saat nama sasaran dipilih) --}}
        @if($filterNama !== '')
            <div wire:key="grafik-{{ md5($filterNama) }}">
                @include('livewire.orangtua.partials.imunisasi-grafik-penilaian')
            </div>
        @endif
    </div>
</div>

@assets
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endassets

@script
<script>
    let orangtuaImunisasiCharts = [];

    function destroyOrangtuaImunisasiCharts() {
        orangtuaImunisasiCharts.forEach((c) => c.destroy());
        orangtuaImunisasiCharts = [];
    }

    function initOrangtuaImunisasiCharts() {
        destroyOrangtuaImunisasiCharts();

        if (typeof Chart === 'undefined') {
            return;
        }

        const pertumbuhanData = @js($grafikPertumbuhan ?? []);
        if (!pertumbuhanData.length) {
            return;
        }

        const isMobile = window.innerWidth < 768;
        const chartFontSize = isMobile ? 11 : 12;
        const chartLegendSize = isMobile ? 11 : 12;
        const barColors = {
            bb: { bg: 'rgba(59, 130, 246, 0.8)', border: 'rgba(59, 130, 246, 1)' },
            tb: { bg: 'rgba(16, 185, 129, 0.8)', border: 'rgba(16, 185, 129, 1)' },
        };

        pertumbuhanData.forEach((item, index) => {
            const canvas = document.getElementById('grafikPertumbuhan' + index);
            if (!canvas) {
                return;
            }

            orangtuaImunisasiCharts.push(new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: item.labels,
                    datasets: [
                        {
                            label: 'Berat Badan (kg)',
                            data: item.berat,
                            backgroundColor: barColors.bb.bg,
                            borderColor: barColors.bb.border,
                            borderWidth: 2,
                            borderRadius: 8,
                            yAxisID: 'y',
                        },
                        {
                            label: 'Tinggi Badan (cm)',
                            data: item.tinggi,
                            backgroundColor: barColors.tb.bg,
                            borderColor: barColors.tb.border,
                            borderWidth: 2,
                            borderRadius: 8,
                            yAxisID: 'y1',
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
                            labels: { font: { size: chartLegendSize }, padding: isMobile ? 12 : 8 },
                        },
                        tooltip: {
                            titleFont: { size: chartFontSize },
                            bodyFont: { size: chartFontSize },
                        },
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            position: 'left',
                            beginAtZero: true,
                            title: { display: true, text: 'Berat Badan (kg)', font: { size: chartFontSize } },
                            ticks: { font: { size: chartFontSize } },
                        },
                        y1: {
                            type: 'linear',
                            position: 'right',
                            beginAtZero: true,
                            grid: { drawOnChartArea: false },
                            title: { display: true, text: 'Tinggi Badan (cm)', font: { size: chartFontSize } },
                            ticks: { font: { size: chartFontSize } },
                        },
                        x: {
                            ticks: { font: { size: chartFontSize }, maxRotation: 45 },
                            title: {
                                display: true,
                                text: 'Tanggal Kunjungan',
                                font: { size: chartFontSize },
                            },
                        },
                    },
                },
            }));
        });
    }

    requestAnimationFrame(() => {
        requestAnimationFrame(() => initOrangtuaImunisasiCharts());
    });
</script>
@endscript
