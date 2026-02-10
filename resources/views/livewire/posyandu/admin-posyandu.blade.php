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

        {{-- Laporan Absensi (Petugas & Bayi) --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <i class="ph ph-clipboard-text text-2xl mr-3 text-primary"></i>
                Laporan Absensi Bulan Ini
            </h2>
            <p class="text-sm text-gray-500 mb-4">Ringkasan absen petugas kesehatan untuk {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('F Y') }}</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Card Absen Petugas --}}
                <div class="border border-gray-200 rounded-xl p-5 hover:border-teal-200 transition-colors">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="ph ph-user-list text-2xl text-teal-600"></i>
                        <h3 class="font-semibold text-gray-800">Absensi Petugas Kesehatan</h3>
                    </div>
                    <div class="space-y-2 text-sm mb-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total jadwal</span>
                            <span class="font-semibold">{{ $absenPetugas['total'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Hadir</span>
                            <span class="font-semibold text-green-600">{{ $absenPetugas['hadir'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tidak hadir</span>
                            <span class="font-semibold text-red-600">{{ $absenPetugas['tidak_hadir'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Belum hadir</span>
                            <span class="font-semibold text-amber-600">{{ $absenPetugas['belum_hadir'] ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('adminPosyandu.laporan') }}" class="inline-flex items-center px-3 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200 transition-colors">
                            <i class="ph ph-list mr-1"></i> Halaman Laporan
                        </a>
                        <a href="{{ route('adminPosyandu.laporan.absensi.pdf') }}?bulan={{ date('n') }}&tahun={{ date('Y') }}" target="_blank" class="inline-flex items-center px-3 py-2 rounded-lg bg-teal-600 text-white text-sm font-medium hover:bg-teal-700 transition-colors">
                            <i class="ph ph-file-pdf mr-1"></i> Export PDF
                        </a>
                    </div>
                </div>
            </div>

            {{-- Card Status Keluarga Orangtua --}}
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Status Keluarga Orangtua (Dewasa/Pralansia/Lansia)</h3>
                <div class="flex flex-wrap gap-4">
                    <div class="flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-800 rounded-lg">
                        <i class="ph ph-user text-xl"></i>
                        <span><strong>Kepala Keluarga:</strong> {{ $statusKeluargaCount['kepala_keluarga'] ?? 0 }} orang</span>
                    </div>
                    <div class="flex items-center gap-2 px-4 py-2 bg-purple-50 text-purple-800 rounded-lg">
                        <i class="ph ph-heart text-xl"></i>
                        <span><strong>Istri:</strong> {{ $statusKeluargaCount['istri'] ?? 0 }} orang</span>
                    </div>
                </div>
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

        <!-- Import Sasaran (per kategori & master) -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <i class="ph ph-upload-simple text-2xl mr-3 text-primary"></i>
                Import Sasaran
            </h2>
            <p class="text-sm text-gray-500 mb-4">Impor data sasaran dari file Excel/CSV. Satu file master (banyak sheet) atau satu file per kategori.</p>
            <div class="flex flex-wrap items-center gap-3">
                <button wire:click="openImportModal('master')"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-primary/90 transition-colors">
                    <i class="ph ph-file-xls text-lg mr-2"></i>
                    Import Master Excel
                </button>
                <span class="text-gray-400">|</span>
                <span class="text-sm text-gray-600">Import per kategori:</span>
                @foreach(['bayibalita' => 'Bayi Balita', 'remaja' => 'Remaja', 'dewasa' => 'Dewasa', 'ibuhamil' => 'Ibu Hamil', 'pralansia' => 'Pralansia', 'lansia' => 'Lansia'] as $kode => $label)
                    <button wire:click="openImportModal('{{ $kode }}')"
                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-green-700 border border-green-600 rounded-lg hover:bg-green-50 transition-colors">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            <p class="text-xs text-gray-400 mt-3">
                <a href="{{ route('adminPosyandu.sasaran') }}" class="text-primary hover:underline">Kelola daftar sasaran</a> untuk tambah manual, edit, atau export.
            </p>
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
                        wire:click="openConfirmModal('deleteSk', 'Apakah Anda yakin ingin menghapus file SK ini?')"
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
                                    <a href="{{ uploads_asset($posyandu->sk_posyandu) }}" target="_blank" 
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

        <!-- Card Gambar Posyandu (tampil di halaman detail publik di atas peta) -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center justify-between">
                <div class="flex items-center">
                    <i class="ph ph-image text-2xl mr-3 text-primary"></i>
                    Gambar Posyandu
                </div>
                <button 
                    wire:click="$set('showGambarModal', true)"
                    class="px-4 py-2 text-sm bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors flex items-center space-x-2">
                    <i class="ph ph-upload text-sm"></i>
                    <span>Upload</span>
                </button>
            </h2>
            <p class="text-sm text-gray-500 mb-4">Gambar ini ditampilkan di halaman detail posyandu (publik) di atas peta lokasi.</p>
            <div class="flex flex-col items-center justify-center py-6">
                @if($posyandu->gambar_posyandu)
                    <div class="relative group w-full max-w-2xl">
                        <img src="{{ uploads_asset($posyandu->gambar_posyandu) }}" alt="Gambar {{ $posyandu->nama_posyandu }}" class="w-full h-auto max-h-80 object-cover rounded-lg border-2 border-gray-200 shadow-md">
                        <div class="mt-3 flex items-center justify-center gap-2">
                            <a href="{{ uploads_asset($posyandu->gambar_posyandu) }}" target="_blank" class="text-primary hover:underline text-sm font-medium flex items-center gap-1">
                                <i class="ph ph-eye"></i> Lihat full size
                            </a>
                            <button 
                                wire:click="openConfirmModal('deleteGambar', 'Hapus gambar posyandu? Gambar tidak akan tampil di halaman detail.')"
                                class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center space-x-2">
                                <i class="ph ph-trash text-sm"></i>
                                <span>Hapus</span>
                            </button>
                        </div>
                    </div>
                @else
                    <div class="w-full max-w-md p-8 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex flex-col items-center justify-center">
                        <i class="ph ph-image text-6xl text-gray-400 mb-3"></i>
                        <p class="text-sm text-gray-500 text-center px-4">Belum ada gambar posyandu. Upload untuk ditampilkan di halaman detail di atas peta.</p>
                        <button 
                            wire:click="$set('showGambarModal', true)"
                            class="mt-4 px-4 py-2 text-sm bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors flex items-center space-x-2">
                            <i class="ph ph-upload text-sm"></i>
                            <span>Upload</span>
                        </button>
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
            <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center justify-between">
                <div class="flex items-center">
                    <i class="ph ph-graduation-cap text-2xl mr-3 text-primary"></i>
                    Grafik Pendidikan Sasaran (Remaja, Dewasa, Ibu Hamil, Pralansia, Lansia)
                </div>
                <button 
                    wire:click="openPendidikanModal"
                    class="px-4 py-2 text-sm bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors flex items-center space-x-2">
                    <i class="ph ph-plus text-sm"></i>
                    <span>Update Pendidikan Semua Sasaran</span>
                </button>
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

    {{-- Modal Upload Gambar Posyandu --}}
    <div x-data="{ 
        show: @entangle('showGambarModal'),
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
    style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="show" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" x-on:click="show = false"></div>
            <div x-show="show" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" x-on:click.away="show = false">
                <div class="bg-primary px-6 py-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-white">Upload Gambar Posyandu</h3>
                    <button type="button" x-on:click="show = false" class="text-white hover:text-gray-200"><i class="ph ph-x text-xl"></i></button>
                </div>
                <div class="bg-white px-6 py-4">
                    <p class="text-sm text-gray-600 mb-4">Gambar ditampilkan di halaman detail posyandu (publik) di atas peta. Format: JPEG, PNG, JPG (Maks. 2MB).</p>
                    <form wire:submit.prevent="uploadGambar">
                        <div 
                            x-on:dragover.prevent="isDragging = true"
                            x-on:dragleave.prevent="isDragging = false"
                            x-on:drop.prevent="
                                isDragging = false;
                                if ($event.dataTransfer.files.length > 0) {
                                    const file = $event.dataTransfer.files[0];
                                    if (!file.type.match(/^image\/(jpeg|png|jpg)$/)) { error = 'Hanya JPEG, PNG, JPG'; return; }
                                    if (file.size > 2097152) { error = 'Maksimal 2MB'; return; }
                                    fileName = file.name; fileSize = formatFileSize(file.size); error = '';
                                    const dt = new DataTransfer(); dt.items.add(file);
                                    $refs.gambarInput.files = dt.files;
                                    $refs.gambarInput.dispatchEvent(new Event('change', { bubbles: true }));
                                }
                            "
                            :class="isDragging ? 'border-primary bg-primary/5' : 'border-gray-300'"
                            class="border-2 border-dashed rounded-lg p-8 text-center cursor-pointer hover:border-primary hover:bg-gray-50"
                            x-on:click="$refs.gambarInput.click()">
                            <input type="file" wire:model="gambarFile" x-ref="gambarInput" accept="image/jpeg,image/png,image/jpg" class="hidden"
                                x-on:change="
                                    if ($event.target.files.length > 0) {
                                        const file = $event.target.files[0];
                                        if (!file.type.match(/^image\/(jpeg|png|jpg)$/)) { error = 'Hanya JPEG, PNG, JPG'; fileName = ''; return; }
                                        if (file.size > 2097152) { error = 'Maksimal 2MB'; fileName = ''; return; }
                                        fileName = file.name; fileSize = formatFileSize(file.size); error = '';
                                    }
                                ">
                            <div x-show="!fileName" class="space-y-2">
                                <i class="ph ph-image text-6xl text-gray-400"></i>
                                <p class="text-gray-600 font-medium">Seret gambar ke sini atau klik untuk memilih</p>
                                <p class="text-xs text-gray-400">JPEG, PNG, JPG (Maks. 2MB)</p>
                            </div>
                            <div x-show="fileName" class="space-y-2">
                                <i class="ph ph-image text-6xl text-primary"></i>
                                <p class="text-gray-800 font-medium" x-text="fileName"></p>
                                <p class="text-gray-500 text-sm" x-text="fileSize"></p>
                            </div>
                        </div>
                        <div x-show="error" class="mt-3 p-2 bg-red-50 border border-red-200 rounded text-red-600 text-sm" x-text="error"></div>
                        @error('gambarFile') <div class="mt-3 p-2 bg-red-50 border border-red-200 rounded text-red-600 text-sm">{{ $message }}</div> @enderror
                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" x-on:click="show = false" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Batal</button>
                            <button type="submit" :disabled="!fileName" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark disabled:opacity-50" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="uploadGambar"><i class="ph ph-upload mr-2"></i> Upload</span>
                                <span wire:loading wire:target="uploadGambar"><i class="ph ph-spinner ph-spin mr-2"></i> Mengupload...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Input Pendidikan Semua Sasaran --}}
    <div x-data="{ 
        show: @entangle('showPendidikanModal')
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
                    <h3 class="text-lg font-semibold text-white">Update Pendidikan Semua Sasaran</h3>
                    <button wire:click="closePendidikanModal" class="text-white hover:text-gray-200">
                        <i class="ph ph-x text-xl"></i>
                    </button>
                </div>

                {{-- Body --}}
                <div class="bg-white px-6 py-4">
                    <form wire:submit.prevent="updatePendidikanSemuaSasaran">
                        <div class="mb-4">
                            <p class="text-sm text-gray-600 mb-4">
                                Pilih pendidikan yang akan diterapkan ke <strong>semua sasaran</strong> (Remaja, Dewasa, Pralansia, Lansia, Ibu Hamil) di posyandu ini. 
                                Data akan otomatis tersimpan di menu Pendidikan.
                            </p>
                            
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Pendidikan Terakhir <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="pendidikan_terakhir"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                <option value="">Pilih Pendidikan Terakhir...</option>
                                <option value="Tidak/Belum Sekolah">Tidak/Belum Sekolah</option>
                                <option value="PAUD">PAUD</option>
                                <option value="TK">TK</option>
                                <option value="Tidak Tamat SD/Sederajat">Tidak Tamat SD/Sederajat</option>
                                <option value="Tamat SD/Sederajat">Tamat SD/Sederajat</option>
                                <option value="SLTP/Sederajat">SLTP/Sederajat</option>
                                <option value="SLTA/Sederajat">SLTA/Sederajat</option>
                                <option value="Diploma I/II">Diploma I/II</option>
                                <option value="Akademi/Diploma III/Sarjana Muda">Akademi/Diploma III/Sarjana Muda</option>
                                <option value="Diploma IV/Strata I">Diploma IV/Strata I</option>
                                <option value="Strata II">Strata II</option>
                                <option value="Strata III">Strata III</option>
                            </select>
                            @error('pendidikan_terakhir') 
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Actions --}}
                        <div class="mt-6 flex justify-end space-x-3">
                            <button 
                                type="button"
                                wire:click="closePendidikanModal"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                                Batal
                            </button>
                            <button 
                                type="submit"
                                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="updatePendidikanSemuaSasaran">
                                    <i class="ph ph-check mr-2"></i> Update Semua
                                </span>
                                <span wire:loading wire:target="updatePendidikanSemuaSasaran">
                                    <i class="ph ph-spinner ph-spin mr-2"></i> Memproses...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Notification Modal --}}
    @include('components.notification-modal')
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

@include('livewire.posyandu.modals.import-modal')
@include('components.confirm-modal')


