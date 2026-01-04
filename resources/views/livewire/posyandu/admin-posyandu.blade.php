<div>
    <div class="p-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Dashboard Posyandu - {{ $posyandu->nama_posyandu }}</h1>
        <p class="text-gray-500 mb-6">Selamat datang di halaman utama Dashboard Posyandu</p>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
            <!-- Card Nama Posyandu -->
            <div class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center">
                <div class="bg-blue-100 p-4 rounded-full mb-4">
                    <i class="ph ph-buildings text-4xl text-blue-600"></i>
                </div>
                <span class="text-lg font-bold text-gray-700 text-center">{{ $posyandu->nama_posyandu ?? '-' }}</span>
                <span class="text-sm text-gray-500 mt-1">Nama Posyandu</span>
            </div>
            <!-- Card Total Kader -->
            <div class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center">
                <div class="bg-green-100 p-4 rounded-full mb-4">
                    <i class="ph ph-user-switch text-4xl text-green-600"></i>
                </div>
                <span class="text-2xl font-bold text-gray-700">{{ $totalKader ?? '0' }}</span>
                <span class="text-sm text-gray-500 mt-1">Total Kader</span>
            </div>
            <!-- Card Total Sasaran -->
            <div class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center">
                <div class="bg-yellow-100 p-4 rounded-full mb-4">
                    <i class="ph ph-users-three text-4xl text-yellow-600"></i>
                </div>
                <span class="text-2xl font-bold text-gray-700">{{ $totalSasaran ?? '0' }}</span>
                <span class="text-sm text-gray-500 mt-1">Total Sasaran</span>
            </div>
        </div>

        <!-- Informasi Posyandu -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <i class="ph ph-info text-2xl mr-3 text-primary"></i>
                Informasi Posyandu
            </h2>
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-500">Nama Posyandu</label>
                    <p class="text-gray-800 mt-1">{{ $posyandu->nama_posyandu }}</p>
                </div>
                @if($posyandu->alamat_posyandu)
                <div>
                    <label class="text-sm font-medium text-gray-500">Alamat</label>
                    <p class="text-gray-800 mt-1">{{ $posyandu->alamat_posyandu }}</p>
                </div>
                @endif
                @if($posyandu->domisili_posyandu)
                <div>
                    <label class="text-sm font-medium text-gray-500">Domisili</label>
                    <p class="text-gray-800 mt-1">{{ $posyandu->domisili_posyandu }}</p>
                </div>
                @endif
                <div>
                    <label class="text-sm font-medium text-gray-500">Jumlah Sasaran</label>
                    <p class="text-gray-800 mt-1">{{ number_format($totalSasaran ?? 0, 0, ',', '.') }} orang</p>
                </div>
                @if($posyandu->sk_posyandu)
                <div>
                    <label class="text-sm font-medium text-gray-500">SK Posyandu</label>
                    <p class="text-gray-800 mt-1">{{ $posyandu->sk_posyandu }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Grafik Jumlah Sasaran per Kategori -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <i class="ph ph-chart-bar text-2xl mr-3 text-primary"></i>
                Grafik Jumlah Sasaran per Kategori
            </h2>
            <div class="h-96">
                <canvas id="sasaranCategoryChart"></canvas>
            </div>
        </div>

        <!-- Grafik Pendidikan Sasaran -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <i class="ph ph-graduation-cap text-2xl mr-3 text-primary"></i>
                Grafik Pendidikan Sasaran (Remaja, Dewasa, Ibu Hamil, Pralansia, Lansia)
            </h2>
            <div class="h-96">
                <canvas id="pendidikanChart"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Pastikan data tersedia
        const sasaranByCategory = @json($sasaranByCategory ?? []);
        const pendidikanData = @json($pendidikanData ?? ['labels' => [], 'data' => []]);

        // Grafik Bar Chart untuk Sasaran per Kategori
        const categoryLabels = ['Bayi/Balita', 'Remaja', 'Ibu Hamil', 'Dewasa', 'Pralansia', 'Lansia'];
        const categoryData = [
            sasaranByCategory.bayibalita || 0,
            sasaranByCategory.remaja || 0,
            sasaranByCategory.ibuhamil || 0,
            sasaranByCategory.dewasa || 0,
            sasaranByCategory.pralansia || 0,
            sasaranByCategory.lansia || 0
        ];

        const categoryCtx = document.getElementById('sasaranCategoryChart');
        if (categoryCtx) {
            new Chart(categoryCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: categoryLabels,
                    datasets: [{
                        label: 'Jumlah Sasaran',
                        data: categoryData,
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(251, 191, 36, 0.8)',
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(168, 85, 247, 0.8)',
                            'rgba(236, 72, 153, 0.8)',
                        ],
                        borderColor: [
                            'rgba(59, 130, 246, 1)',
                            'rgba(16, 185, 129, 1)',
                            'rgba(251, 191, 36, 1)',
                            'rgba(239, 68, 68, 1)',
                            'rgba(168, 85, 247, 1)',
                            'rgba(236, 72, 153, 1)',
                        ],
                        borderWidth: 2,
                        borderRadius: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Jumlah: ' + context.parsed.y + ' orang';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                callback: function(value) {
                                    return value + ' orang';
                                }
                            },
                            title: {
                                display: true,
                                text: 'Jumlah Sasaran'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Kategori Sasaran'
                            }
                        }
                    }
                }
            });
        }

        // Grafik Pendidikan Sasaran (gabungan) - di bagian bawah
        const pendidikanLabels = pendidikanData.labels || [];
        const pendidikanCounts = pendidikanData.data || [];
        const pendidikanCtx = document.getElementById('pendidikanChart');
        if (pendidikanCtx) {
            new Chart(pendidikanCtx.getContext('2d'), {
                type: 'pie',
                data: {
                    labels: pendidikanLabels,
                    datasets: [{
                        label: 'Jumlah Sasaran',
                        data: pendidikanCounts,
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(251, 191, 36, 0.8)',
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(168, 85, 247, 0.8)',
                            'rgba(236, 72, 153, 0.8)',
                            'rgba(34, 197, 94, 0.8)',
                            'rgba(249, 115, 22, 0.8)',
                            'rgba(139, 92, 246, 0.8)',
                            'rgba(20, 184, 166, 0.8)',
                        ],
                        borderColor: [
                            'rgba(59, 130, 246, 1)',
                            'rgba(16, 185, 129, 1)',
                            'rgba(251, 191, 36, 1)',
                            'rgba(239, 68, 68, 1)',
                            'rgba(168, 85, 247, 1)',
                            'rgba(236, 72, 153, 1)',
                            'rgba(34, 197, 94, 1)',
                            'rgba(249, 115, 22, 1)',
                            'rgba(139, 92, 246, 1)',
                            'rgba(20, 184, 166, 1)',
                        ],
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 10,
                                font: {
                                    size: 11
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return label + ': ' + value + ' orang (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush


