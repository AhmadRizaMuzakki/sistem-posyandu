{{-- Informasi Utama --}}
<div class="space-y-6">
    {{-- Card Logo Posyandu --}}
    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 overflow-hidden">
        <h2 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4 flex items-center min-w-0">
            <i class="ph ph-image text-2xl mr-3 text-primary shrink-0"></i>
            <span class="break-words">Logo Posyandu</span>
        </h2>
        <div class="flex flex-col items-center justify-center py-4 sm:py-6">
            @if($posyandu->logo_posyandu)
                <div class="relative group max-w-full">
                    <img
                        src="{{ uploads_asset($posyandu->logo_posyandu) }}"
                        alt="Logo {{ $posyandu->nama_posyandu }}"
                        class="w-40 h-40 sm:w-48 sm:h-48 max-w-full object-contain rounded-lg border-2 border-gray-200 shadow-md hover:shadow-lg transition-shadow">
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 rounded-lg transition-all flex items-center justify-center">
                        <a
                            href="{{ uploads_asset($posyandu->logo_posyandu) }}"
                            target="_blank"
                            class="opacity-0 group-hover:opacity-100 transition-opacity bg-white px-4 py-2 rounded-lg shadow-md hover:shadow-lg flex items-center space-x-2">
                            <i class="ph ph-arrows-out text-lg text-primary"></i>
                            <span class="text-sm font-medium text-gray-700">Lihat Full Size</span>
                        </a>
                    </div>
                </div>
                <p class="mt-4 text-sm text-gray-500 text-center break-words px-2">Logo {{ $posyandu->nama_posyandu }}</p>
            @else
                <div class="w-40 h-40 sm:w-48 sm:h-48 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex flex-col items-center justify-center">
                    <i class="ph ph-image text-6xl text-gray-400 mb-3"></i>
                    <p class="text-sm text-gray-500 text-center px-4">Belum ada logo posyandu</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Card SK Posyandu --}}
    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 overflow-hidden">
        <div class="flex flex-col gap-3 mb-4">
            <h2 class="text-lg sm:text-xl font-semibold text-gray-800 flex items-center min-w-0">
                <i class="ph ph-file-text text-2xl mr-3 text-primary shrink-0"></i>
                <span class="break-words">SK Posyandu</span>
            </h2>
            <div class="grid grid-cols-1 min-[400px]:grid-cols-2 sm:flex sm:flex-wrap gap-2">
                <a 
                    href="{{ route('superadmin.posyandu.sk.pdf', encrypt($posyandu->id_posyandu)) }}"
                    target="_blank"
                    class="px-3 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center gap-1.5 whitespace-nowrap">
                    <i class="ph ph-download text-sm shrink-0"></i>
                    <span>Download SK</span>
                </a>
                <button 
                    type="button"
                    wire:click="$set('showUploadModal', true)"
                    class="px-3 py-2 text-sm bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors flex items-center justify-center gap-1.5 whitespace-nowrap">
                    <i class="ph ph-upload text-sm shrink-0"></i>
                    <span>Upload SK</span>
                </button>
                @if($posyandu->sk_posyandu)
                <button 
                    type="button"
                    wire:click="openConfirmModal('deleteSk', 'Apakah Anda yakin ingin menghapus file SK ini?')"
                    class="px-3 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center justify-center gap-1.5 whitespace-nowrap min-[400px]:col-span-2 sm:col-span-1">
                    <i class="ph ph-trash text-sm shrink-0"></i>
                    <span>Hapus SK</span>
                </button>
                @endif
            </div>
        </div>
        <div class="flex flex-col items-center justify-center py-4 sm:py-6">
            @if($posyandu->sk_posyandu)
                <div class="relative group w-full max-w-md">
                    <div class="p-4 sm:p-6 bg-gray-50 rounded-lg border-2 border-gray-200 hover:border-primary transition-colors">
                        <div class="flex flex-col items-center space-y-3 min-w-0">
                            <i class="ph ph-file-pdf text-5xl sm:text-6xl text-red-500"></i>
                            <div class="text-center w-full min-w-0 px-1">
                                <a href="{{ uploads_asset($posyandu->sk_posyandu) }}" target="_blank" 
                                   class="text-primary hover:underline font-medium text-sm inline-flex items-center justify-center gap-2">
                                    <i class="ph ph-eye text-lg shrink-0"></i>
                                    <span>Lihat File SK</span>
                                </a>
                                <p class="text-xs text-gray-500 mt-2 break-all">{{ basename($posyandu->sk_posyandu) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="w-full max-w-md p-4 sm:p-6 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex flex-col items-center justify-center">
                    <i class="ph ph-file-x text-5xl sm:text-6xl text-gray-400 mb-3"></i>
                    <p class="text-sm text-gray-500 text-center px-4">Belum ada file SK posyandu</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Card Gambar Posyandu (tampil di halaman detail publik di atas peta) --}}
    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 overflow-hidden">
        <div class="flex flex-col gap-3 mb-4">
            <h2 class="text-lg sm:text-xl font-semibold text-gray-800 flex items-center min-w-0">
                <i class="ph ph-images text-2xl mr-3 text-primary shrink-0"></i>
                <span class="break-words">Gambar Posyandu</span>
            </h2>
            <button 
                type="button"
                wire:click="$set('showGambarModal', true)"
                class="w-full sm:w-auto px-3 py-2 text-sm bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors flex items-center justify-center gap-1.5 whitespace-nowrap self-stretch sm:self-start">
                <i class="ph ph-plus text-sm shrink-0"></i>
                <span>Tambah Gambar</span>
            </button>
        </div>
        <p class="text-sm text-gray-500 mb-4">Gambar-gambar ini ditampilkan di halaman detail posyandu (publik) sebagai galeri foto.</p>
        
        @php
            $gambarList = $posyandu->gambarPosyandu ?? collect();
        @endphp

        @if($gambarList->count() > 0)
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
                @foreach($gambarList as $gambar)
                <div class="relative group aspect-square rounded-lg overflow-hidden border border-gray-200 shadow-sm">
                    <img src="{{ uploads_asset($gambar->path) }}" 
                         alt="{{ $gambar->caption ?? 'Gambar Posyandu' }}" 
                         class="w-full h-full object-cover">
                    {{-- Overlay actions: selalu tampil di mobile (tanpa hover) --}}
                    <div class="absolute inset-0 bg-black/50 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                        <a href="{{ uploads_asset($gambar->path) }}" target="_blank" 
                           class="w-9 h-9 rounded-full bg-white text-gray-700 flex items-center justify-center hover:bg-primary hover:text-white transition-colors">
                            <i class="ph ph-eye text-lg"></i>
                        </a>
                        <button type="button"
                                wire:click="deleteGambarPosyandu({{ $gambar->id }})"
                                wire:confirm="Hapus gambar ini?"
                                class="w-9 h-9 rounded-full bg-white text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition-colors">
                            <i class="ph ph-trash text-lg"></i>
                        </button>
                    </div>
                    @if($gambar->caption)
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-2">
                        <p class="text-white text-xs truncate">{{ $gambar->caption }}</p>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        @else
            <div class="w-full p-6 sm:p-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300 flex flex-col items-center justify-center">
                <i class="ph ph-images text-5xl sm:text-6xl text-gray-400 mb-3"></i>
                <p class="text-sm text-gray-500 text-center">Belum ada gambar posyandu.</p>
                <p class="text-xs text-gray-400 mt-1 text-center px-2">Klik "Tambah Gambar" untuk mengunggah foto.</p>
            </div>
        @endif
    </div>

    {{-- Grid Informasi dan Statistik --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
    {{-- Card Informasi Posyandu --}}
    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 overflow-hidden">
        <h2 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4 flex items-center min-w-0">
            <i class="ph ph-info text-2xl mr-3 text-primary shrink-0"></i>
            <span class="break-words">Informasi Posyandu</span>
        </h2>
        <div class="space-y-4 min-w-0">
            <div class="min-w-0">
                <label class="text-sm font-medium text-gray-500">Nama Posyandu</label>
                <p class="text-gray-800 mt-1 break-words">{{ $posyandu->nama_posyandu }}</p>
            </div>
            @if($posyandu->alamat_posyandu)
            <div class="min-w-0">
                <label class="text-sm font-medium text-gray-500">Alamat</label>
                <p class="text-gray-800 mt-1 break-words">{{ $posyandu->alamat_posyandu }}</p>
            </div>
            @endif
            @if($posyandu->domisili_posyandu)
            <div class="min-w-0">
                <label class="text-sm font-medium text-gray-500">Domisili</label>
                <p class="text-gray-800 mt-1 break-words">{{ $posyandu->domisili_posyandu }}</p>
            </div>
            @endif
            <div>
                <label class="text-sm font-medium text-gray-500">Jumlah Sasaran</label>
                @php
                    $totalSasaran = ($posyandu->sasaran_bayibalita ? $posyandu->sasaran_bayibalita->count() : 0) +
                                    ($posyandu->sasaran_remaja ? $posyandu->sasaran_remaja->count() : 0) +
                                    ($posyandu->sasaran_dewasa ? $posyandu->sasaran_dewasa->count() : 0) +
                                    ($posyandu->sasaran_ibuhamil ? $posyandu->sasaran_ibuhamil->count() : 0) +
                                    ($posyandu->sasaran_pralansia ? $posyandu->sasaran_pralansia->count() : 0) +
                                    ($posyandu->sasaran_lansia ? $posyandu->sasaran_lansia->count() : 0);
                @endphp
                <p class="text-gray-800 mt-1">{{ number_format($totalSasaran, 0, ',', '.') }} orang</p>
            </div>
        </div>
    </div>

    {{-- Card Statistik --}}
    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 overflow-hidden">
        <h2 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4 flex items-center min-w-0">
            <i class="ph ph-chart-bar text-2xl mr-3 text-primary shrink-0"></i>
            <span class="break-words">Statistik</span>
        </h2>
        <div class="space-y-4">
            <div class="flex items-center justify-between gap-3 p-3 sm:p-4 bg-blue-50 rounded-lg min-w-0">
                <div class="min-w-0">
                    <p class="text-sm text-gray-600">Total Kader</p>
                    <p class="text-2xl font-bold text-primary mt-1">{{ $posyandu->kader ? $posyandu->kader->count() : 0 }}</p>
                </div>
                <i class="ph ph-users text-3xl sm:text-4xl text-blue-300 shrink-0"></i>
            </div>
            <div class="flex items-center justify-between gap-3 p-3 sm:p-4 bg-green-50 rounded-lg min-w-0">
                <div class="min-w-0">
                    <p class="text-sm text-gray-600">Total Sasaran Bayi/Balita</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">{{ $posyandu->sasaran_bayibalita ? $posyandu->sasaran_bayibalita->count() : 0 }}</p>
                </div>
                <i class="ph ph-baby text-3xl sm:text-4xl text-green-300 shrink-0"></i>
            </div>
            <div class="flex items-center justify-between gap-3 p-3 sm:p-4 bg-purple-50 rounded-lg min-w-0">
                <div class="min-w-0">
                    <p class="text-sm text-gray-600">Total Sasaran Remaja</p>
                    <p class="text-2xl font-bold text-purple-600 mt-1">{{ $posyandu->sasaran_remaja ? $posyandu->sasaran_remaja->count() : 0 }}</p>
                </div>
                <i class="ph ph-user text-3xl sm:text-4xl text-purple-300 shrink-0"></i>
            </div>
            <div class="flex items-center justify-between gap-3 p-3 sm:p-4 bg-orange-50 rounded-lg min-w-0">
                <div class="min-w-0">
                    <p class="text-sm text-gray-600">Total Sasaran Dewasa</p>
                    <p class="text-2xl font-bold text-orange-600 mt-1">{{ $posyandu->sasaran_dewasa ? $posyandu->sasaran_dewasa->count() : 0 }}</p>
                </div>
                <i class="ph ph-users text-3xl sm:text-4xl text-orange-300 shrink-0"></i>
            </div>
            <div class="flex items-center justify-between gap-3 p-3 sm:p-4 bg-pink-50 rounded-lg min-w-0">
                <div class="min-w-0">
                    <p class="text-sm text-gray-600">Total Ibu Hamil</p>
                    <p class="text-2xl font-bold text-pink-600 mt-1">{{ $posyandu->sasaran_ibuhamil ? $posyandu->sasaran_ibuhamil->count() : 0 }}</p>
                </div>
                <i class="ph ph-heart text-3xl sm:text-4xl text-pink-300 shrink-0"></i>
            </div>
            <div class="flex items-center justify-between gap-3 p-3 sm:p-4 bg-yellow-50 rounded-lg min-w-0">
                <div class="min-w-0">
                    <p class="text-sm text-gray-600">Total Pralansia</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $posyandu->sasaran_pralansia ? $posyandu->sasaran_pralansia->count() : 0 }}</p>
                </div>
                <i class="ph ph-user-circle text-3xl sm:text-4xl text-yellow-300 shrink-0"></i>
            </div>
            <div class="flex items-center justify-between gap-3 p-3 sm:p-4 bg-indigo-50 rounded-lg min-w-0">
                <div class="min-w-0">
                    <p class="text-sm text-gray-600">Total Lansia</p>
                    <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $posyandu->sasaran_lansia ? $posyandu->sasaran_lansia->count() : 0 }}</p>
                </div>
                <i class="ph ph-user-gear text-3xl sm:text-4xl text-indigo-300 shrink-0"></i>
            </div>
        </div>
    </div>
    </div>

    {{-- Chart Pendidikan --}}
    @if(isset($pendidikanChartData) && count($pendidikanChartData['labels']) > 0)
    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 overflow-hidden">
        <h2 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4 flex items-center min-w-0">
            <i class="ph ph-chart-pie text-2xl mr-3 text-primary shrink-0"></i>
            <span class="break-words">Chart Pendidikan</span>
        </h2>
        <div class="h-64 sm:h-80">
            <canvas id="pendidikanChart"></canvas>
        </div>
    </div>
    @else
    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 overflow-hidden">
        <h2 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4 flex items-center min-w-0">
            <i class="ph ph-chart-pie text-2xl mr-3 text-primary shrink-0"></i>
            <span class="break-words">Chart Pendidikan</span>
        </h2>
        <div class="text-center py-12 text-gray-500">
            <i class="ph ph-graduation-cap text-4xl mb-2"></i>
            <p>Belum ada data pendidikan untuk ditampilkan</p>
        </div>
    </div>
    @endif
</div>

@push('scripts')
@if(isset($pendidikanChartData) && count($pendidikanChartData['labels']) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const pendidikanData = @json($pendidikanChartData);
        
        const ctx = document.getElementById('pendidikanChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: pendidikanData.labels,
                datasets: [{
                    label: 'Jumlah',
                    data: pendidikanData.data,
                    backgroundColor: [
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(251, 191, 36, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(14, 165, 233, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(20, 184, 166, 0.8)',
                        'rgba(249, 115, 22, 0.8)',
                        'rgba(99, 102, 241, 0.8)',
                    ],
                    borderColor: [
                        'rgba(239, 68, 68, 1)',
                        'rgba(251, 191, 36, 1)',
                        'rgba(34, 197, 94, 1)',
                        'rgba(59, 130, 246, 1)',
                        'rgba(168, 85, 247, 1)',
                        'rgba(236, 72, 153, 1)',
                        'rgba(14, 165, 233, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(139, 92, 246, 1)',
                        'rgba(20, 184, 166, 1)',
                        'rgba(249, 115, 22, 1)',
                        'rgba(99, 102, 241, 1)',
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
                        position: 'right',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            font: {
                                size: 12
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
@endif
@endpush

{{-- Modal Upload SK --}}
@include('livewire.super-admin.posyandu-detail.partials.upload-sk')

{{-- Modal Upload Gambar Posyandu --}}
@include('livewire.super-admin.posyandu-detail.partials.upload-gambar')

@include('components.confirm-modal')
