<div>
    <div class="p-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Dashboard Super Admin</h1>
        <p class="text-gray-500 mb-6">Selamat datang di halaman utama Dashboard Super Admin</p>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
            <!-- Card Total Posyandu -->
            <a href="{{ route('posyandu.list') }}" class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center hover:shadow-lg transition-shadow cursor-pointer">
                <div class="bg-blue-100 p-4 rounded-full mb-4">
                    <i class="ph ph-buildings text-4xl text-blue-600"></i>
                </div>
                <span class="text-2xl font-bold text-gray-700">{{ $totalPosyandu ?? '0' }}</span>
                <span class="text-sm text-gray-500 mt-1">Total Posyandu</span>
                <span class="text-xs text-primary mt-2 hover:underline">Kelola Posyandu →</span>
            </a>
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

        <!-- Grafik Jumlah Sasaran per Posyandu -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <i class="ph ph-chart-bar text-2xl mr-3 text-primary"></i>
                Grafik Jumlah Sasaran per Posyandu
            </h2>
            <div class="h-96">
                <canvas id="posyanduChart"></canvas>
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
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
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
        const posyanduData = @json($posyanduData);

        const labels = posyanduData.map(item => item.nama);
        const data = posyanduData.map(item => item.jumlah);

        const ctx = document.getElementById('posyanduChart').getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Sasaran',
                    data: data,
                    backgroundColor: [
                        'rgba(79, 70, 229, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(251, 191, 36, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(14, 165, 233, 0.8)',
                    ],
                    borderColor: [
                        'rgba(79, 70, 229, 1)',
                        'rgba(34, 197, 94, 1)',
                        'rgba(251, 191, 36, 1)',
                        'rgba(239, 68, 68, 1)',
                        'rgba(168, 85, 247, 1)',
                        'rgba(236, 72, 153, 1)',
                        'rgba(14, 165, 233, 1)',
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
                                return 'Jumlah Sasaran: ' + context.parsed.y + ' orang';
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
                            text: 'Nama Posyandu'
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });

        // Grafik Bar Chart untuk Sasaran per Kategori
        const sasaranByCategory = @json($sasaranByCategory);
        const categoryLabels = ['Bayi/Balita', 'Remaja', 'Ibu Hamil', 'Dewasa', 'Pralansia', 'Lansia'];
        const categoryData = [
            sasaranByCategory.bayibalita,
            sasaranByCategory.remaja,
            sasaranByCategory.ibuhamil,
            sasaranByCategory.dewasa,
            sasaranByCategory.pralansia,
            sasaranByCategory.lansia
        ];

        const categoryCtx = document.getElementById('sasaranCategoryChart').getContext('2d');
        new Chart(categoryCtx, {
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

        // Grafik Pendidikan Sasaran (gabungan)
        const pendidikanData = @json($pendidikanData);
        const pendidikanLabels = pendidikanData.labels;
        const pendidikanCounts = pendidikanData.data;

        const pendidikanCtx = document.getElementById('pendidikanChart').getContext('2d');
        new Chart(pendidikanCtx, {
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
    });
</script>
@endpush
