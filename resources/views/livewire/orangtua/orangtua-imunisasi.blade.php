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

        {{-- 2. Grafik & penilaian (hanya saat nama sasaran dipilih) --}}
        @if($filterNama !== '')
            <div wire:key="grafik-penilaian-{{ md5($filterNama) }}">
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
    function registerOrangtuaGrafikPertumbuhan() {
        if (window.__orangtuaGrafikRegistered) {
            return;
        }
        window.__orangtuaGrafikRegistered = true;

        Alpine.data('orangtuaGrafikPertumbuhan', (config) => ({
                chart: null,
                config,

                init() {
                    this.$nextTick(() => this.render());
                },

                render(attempt = 0) {
                    if (!this.config.labels?.length) {
                        return;
                    }

                    if (typeof Chart === 'undefined') {
                        if (attempt < 30) {
                            setTimeout(() => this.render(attempt + 1), 100);
                        }
                        return;
                    }

                    if (this.chart) {
                        this.chart.destroy();
                        this.chart = null;
                    }

                    const canvas = this.$refs.canvas;
                    if (!canvas) {
                        return;
                    }

                    const isMobile = window.innerWidth < 768;
                    const fontSize = isMobile ? 11 : 12;

                    this.chart = new Chart(canvas.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: this.config.labels,
                            datasets: [
                                {
                                    label: 'Berat (kg)',
                                    data: this.config.berat,
                                    backgroundColor: 'rgba(59, 130, 246, 0.75)',
                                    borderColor: 'rgb(59, 130, 246)',
                                    borderWidth: 1,
                                    borderRadius: 4,
                                    yAxisID: 'y',
                                    minBarLength: 8,
                                },
                                {
                                    label: 'Tinggi (cm)',
                                    data: this.config.tinggi,
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
                },

                destroy() {
                    if (this.chart) {
                        this.chart.destroy();
                        this.chart = null;
                    }
                },
            }));
    }

    if (window.Alpine) {
        registerOrangtuaGrafikPertumbuhan();
    } else {
        document.addEventListener('alpine:init', registerOrangtuaGrafikPertumbuhan);
    }
</script>
@endscript
