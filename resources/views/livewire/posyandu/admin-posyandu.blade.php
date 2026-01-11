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
            </div>
        </div>

        <!-- Card SK Posyandu -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center justify-between">
                <div class="flex items-center">
                    <i class="ph ph-file-text text-2xl mr-3 text-primary"></i>
                    SK Posyandu
                </div>
                <div class="flex items-center space-x-2">
                    <a 
                        href="{{ route('adminPosyandu.sk.pdf') }}"
                        target="_blank"
                        class="px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center space-x-2">
                        <i class="ph ph-download text-sm"></i>
                        <span>Download SK</span>
                    </a>
                    <button 
                        wire:click="$set('showUploadModal', true)"
                        class="px-4 py-2 text-sm bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors flex items-center space-x-2">
                        <i class="ph ph-upload text-sm"></i>
                        <span>Upload SK</span>
                    </button>
                    @if($posyandu->sk_posyandu)
                    <button 
                        wire:click="deleteSk"
                        wire:confirm="Apakah Anda yakin ingin menghapus file SK ini?"
                        class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center space-x-2">
                        <i class="ph ph-trash text-sm"></i>
                        <span>Hapus SK</span>
                    </button>
                    @endif
                </div>
            </h2>
            <div class="flex flex-col items-center justify-center py-6">
                @if($posyandu->sk_posyandu)
                    <div class="relative group w-full max-w-md">
                        <div class="p-6 bg-gray-50 rounded-lg border-2 border-gray-200 hover:border-primary transition-colors">
                            <div class="flex flex-col items-center space-y-3">
                                <i class="ph ph-file-pdf text-6xl text-red-500"></i>
                                <div class="text-center">
                                    <a href="{{ asset($posyandu->sk_posyandu) }}" target="_blank" 
                                       class="text-primary hover:underline font-medium text-sm flex items-center justify-center space-x-2">
                                        <i class="ph ph-eye text-lg"></i>
                                        <span>Lihat File SK</span>
                                    </a>
                                    <p class="text-xs text-gray-500 mt-2">{{ basename($posyandu->sk_posyandu) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="w-full max-w-md p-6 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex flex-col items-center justify-center">
                        <i class="ph ph-file-x text-6xl text-gray-400 mb-3"></i>
                        <p class="text-sm text-gray-500 text-center px-4">Belum ada file SK posyandu</p>
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

    {{-- Modal Upload SK --}}
    <div x-data="{ 
        show: @entangle('showUploadModal'),
        isDragging: false,
        fileName: '',
        fileSize: '',
        error: '',
        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }
    }" 
    x-show="show"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
    x-on:close-modal.window="show = false">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Overlay --}}
            <div x-show="show" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
                 x-on:click="show = false"></div>

            {{-- Modal Panel --}}
            <div x-show="show"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                 x-on:click.away="show = false">
                
                {{-- Header --}}
                <div class="bg-primary px-6 py-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-white">Upload File SK Posyandu</h3>
                    <button x-on:click="show = false" class="text-white hover:text-gray-200">
                        <i class="ph ph-x text-xl"></i>
                    </button>
                </div>

                {{-- Body --}}
                <div class="bg-white px-6 py-4">
                    <form wire:submit.prevent="uploadSk">
                        {{-- Drag and Drop Area --}}
                        <div 
                            x-on:dragover.prevent="isDragging = true"
                            x-on:dragleave.prevent="isDragging = false"
                            x-on:drop.prevent="
                                isDragging = false;
                                if ($event.dataTransfer.files.length > 0) {
                                    const file = $event.dataTransfer.files[0];
                                    fileName = file.name;
                                    fileSize = formatFileSize(file.size);
                                    error = '';
                                    
                                    // Validasi ukuran
                                    if (file.size > 5242880) {
                                        error = 'Ukuran file melebihi 5MB';
                                        fileName = '';
                                        fileSize = '';
                                        return;
                                    }
                                    
                                    // Validasi ekstensi
                                    const ext = file.name.split('.').pop().toLowerCase();
                                    if (!['pdf', 'doc', 'docx'].includes(ext)) {
                                        error = 'Format file tidak diizinkan. Hanya PDF, DOC, atau DOCX.';
                                        fileName = '';
                                        fileSize = '';
                                        return;
                                    }
                                    
                                    // Set file ke Livewire
                                    const dataTransfer = new DataTransfer();
                                    dataTransfer.items.add(file);
                                    $refs.fileInput.files = dataTransfer.files;
                                    $refs.fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                                }
                            "
                            :class="isDragging ? 'border-primary bg-blue-50' : 'border-gray-300'"
                            class="border-2 border-dashed rounded-lg p-8 text-center transition-colors cursor-pointer hover:border-primary hover:bg-gray-50"
                            x-on:click="$refs.fileInput.click()">
                            
                            <input 
                                type="file" 
                                wire:model="skFile"
                                x-ref="fileInput"
                                accept=".pdf,.doc,.docx"
                                class="hidden"
                                x-on:change="
                                    if ($event.target.files.length > 0) {
                                        const file = $event.target.files[0];
                                        fileName = file.name;
                                        fileSize = formatFileSize(file.size);
                                        error = '';
                                        
                                        // Validasi ukuran
                                        if (file.size > 5242880) {
                                            error = 'Ukuran file melebihi 5MB';
                                            fileName = '';
                                            fileSize = '';
                                            $refs.fileInput.value = '';
                                            return;
                                        }
                                        
                                        // Validasi ekstensi
                                        const ext = file.name.split('.').pop().toLowerCase();
                                        if (!['pdf', 'doc', 'docx'].includes(ext)) {
                                            error = 'Format file tidak diizinkan. Hanya PDF, DOC, atau DOCX.';
                                            fileName = '';
                                            fileSize = '';
                                            $refs.fileInput.value = '';
                                            return;
                                        }
                                    }
                                ">
                            
                            <div x-show="!fileName" class="space-y-4">
                                <div class="flex justify-center">
                                    <i class="ph ph-cloud-arrow-up text-6xl text-gray-400"></i>
                                </div>
                                <div>
                                    <p class="text-gray-600 font-medium">Drag & drop file di sini</p>
                                    <p class="text-gray-400 text-sm mt-2">atau</p>
                                    <button type="button" class="mt-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                                        Pilih File
                                    </button>
                                </div>
                                <p class="text-xs text-gray-400 mt-4">
                                    Format yang didukung: PDF, DOC, DOCX (Maks. 5MB)
                                </p>
                            </div>

                            {{-- File Preview --}}
                            <div x-show="fileName" class="space-y-4">
                                <div class="flex items-center justify-center">
                                    <i class="ph ph-file text-6xl text-primary"></i>
                                </div>
                                <div>
                                    <p class="text-gray-800 font-medium" x-text="fileName"></p>
                                    <p class="text-gray-500 text-sm mt-1" x-text="fileSize"></p>
                                </div>
                                <button 
                                    type="button"
                                    x-on:click="fileName = ''; fileSize = ''; error = ''; $refs.fileInput.value = ''; @this.set('skFile', null);"
                                    class="text-red-600 hover:text-red-700 text-sm">
                                    <i class="ph ph-trash mr-1"></i> Hapus
                                </button>
                            </div>
                        </div>

                        {{-- Error Message --}}
                        <div x-show="error" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-red-600 text-sm" x-text="error"></p>
                        </div>

                        @error('skFile')
                            <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                                <p class="text-red-600 text-sm">{{ $message }}</p>
                            </div>
                        @enderror

                        {{-- Actions --}}
                        <div class="mt-6 flex justify-end space-x-3">
                            <button 
                                type="button"
                                x-on:click="show = false"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                                Batal
                            </button>
                            <button 
                                type="submit"
                                :disabled="!fileName || error"
                                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="uploadSk">
                                    <i class="ph ph-upload mr-2"></i> Upload
                                </span>
                                <span wire:loading wire:target="uploadSk">
                                    <i class="ph ph-spinner ph-spin mr-2"></i> Mengupload...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Message Alert --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => show = false, 5000)"
             class="fixed top-4 right-4 z-50 max-w-sm w-full">
            <div class="rounded-lg shadow-lg p-4 {{ session('messageType') === 'success' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        @if(session('messageType') === 'success')
                            <i class="ph ph-check-circle text-2xl text-green-600"></i>
                        @else
                            <i class="ph ph-x-circle text-2xl text-red-600"></i>
                        @endif
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium {{ session('messageType') === 'success' ? 'text-green-800' : 'text-red-800' }}">
                            {{ session('message') }}
                        </p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button x-on:click="show = false" class="inline-flex {{ session('messageType') === 'success' ? 'text-green-500 hover:text-green-600' : 'text-red-500 hover:text-red-600' }}">
                            <i class="ph ph-x"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
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


