{{-- Informasi Utama --}}
<div class="space-y-6">
    {{-- Card Logo Posyandu --}}
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
            <i class="ph ph-image text-2xl mr-3 text-primary"></i>
            Logo Posyandu
        </h2>
        <div class="flex flex-col items-center justify-center py-6">
            @if($posyandu->logo_posyandu)
                <div class="relative group">
                    <img
                        src="{{ asset($posyandu->logo_posyandu) }}"
                        alt="Logo {{ $posyandu->nama_posyandu }}"
                        class="w-48 h-48 object-contain rounded-lg border-2 border-gray-200 shadow-md hover:shadow-lg transition-shadow">
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 rounded-lg transition-all flex items-center justify-center">
                        <a
                            href="{{ asset($posyandu->logo_posyandu) }}"
                            target="_blank"
                            class="opacity-0 group-hover:opacity-100 transition-opacity bg-white px-4 py-2 rounded-lg shadow-md hover:shadow-lg flex items-center space-x-2">
                            <i class="ph ph-arrows-out text-lg text-primary"></i>
                            <span class="text-sm font-medium text-gray-700">Lihat Full Size</span>
                        </a>
                    </div>
                </div>
                <p class="mt-4 text-sm text-gray-500 text-center">Logo {{ $posyandu->nama_posyandu }}</p>
            @else
                <div class="w-48 h-48 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex flex-col items-center justify-center">
                    <i class="ph ph-image text-6xl text-gray-400 mb-3"></i>
                    <p class="text-sm text-gray-500 text-center px-4">Belum ada logo posyandu</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Grid Informasi dan Statistik --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    {{-- Card Informasi Posyandu --}}
    <div class="bg-white rounded-lg shadow-sm p-6">
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
                @php
                    $totalSasaran = $posyandu->sasaran_bayibalita->count() +
                                    $posyandu->sasaran_remaja->count() +
                                    $this->getOrangtuaCountByUmur(20, 40) + // Dewasa
                                    $posyandu->sasaran_ibuhamil->count() +
                                    $this->getOrangtuaCountByUmur(40, 60) + // Pralansia
                                    $this->getOrangtuaCountByUmur(60, null); // Lansia
                @endphp
                <p class="text-gray-800 mt-1">{{ number_format($totalSasaran, 0, ',', '.') }} orang</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">SK Posyandu</label>
                <div class="mt-2">
                    @if($posyandu->sk_posyandu)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200 mb-2">
                            <div class="flex items-center space-x-3">
                                <i class="ph ph-file text-2xl text-primary"></i>
                                <div>
                                    <a href="{{ asset($posyandu->sk_posyandu) }}" target="_blank" class="text-primary hover:underline font-medium">
                                        Lihat File SK
                                    </a>
                                    <p class="text-xs text-gray-500 mt-1">Klik untuk melihat atau download</p>
                                </div>
                            </div>
                            <button
                                wire:click="deleteSk"
                                wire:confirm="Apakah Anda yakin ingin menghapus file SK ini?"
                                class="text-red-600 hover:text-red-700 p-2 hover:bg-red-50 rounded-lg transition-colors"
                                title="Hapus File">
                                <i class="ph ph-trash text-lg"></i>
                            </button>
                        </div>
                        <button
                            wire:click="$set('showUploadModal', true)"
                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors text-sm flex items-center space-x-2">
                            <i class="ph ph-upload"></i>
                            <span>Ganti File SK</span>
                        </button>
                    @else
                        <button
                            wire:click="$set('showUploadModal', true)"
                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors text-sm flex items-center space-x-2">
                            <i class="ph ph-upload"></i>
                            <span>Upload File SK</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Card Statistik --}}
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
            <i class="ph ph-chart-bar text-2xl mr-3 text-primary"></i>
            Statistik
        </h2>
        <div class="space-y-4">
            <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                <div>
                    <p class="text-sm text-gray-600">Total Kader</p>
                    <p class="text-2xl font-bold text-primary mt-1">{{ $posyandu->kader->count() }}</p>
                </div>
                <i class="ph ph-users text-4xl text-blue-300"></i>
            </div>
            <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg">
                <div>
                    <p class="text-sm text-gray-600">Total Sasaran Bayi/Balita</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">{{ $posyandu->sasaran_bayibalita->count() }}</p>
                </div>
                <i class="ph ph-baby text-4xl text-green-300"></i>
            </div>
            <div class="flex items-center justify-between p-4 bg-purple-50 rounded-lg">
                <div>
                    <p class="text-sm text-gray-600">Total Sasaran Remaja</p>
                    <p class="text-2xl font-bold text-purple-600 mt-1">{{ $posyandu->sasaran_remaja->count() }}</p>
                </div>
                <i class="ph ph-user text-4xl text-purple-300"></i>
            </div>
            <div class="flex items-center justify-between p-4 bg-orange-50 rounded-lg">
                <div>
                    <p class="text-sm text-gray-600">Total Sasaran Dewasa</p>
                    <p class="text-2xl font-bold text-orange-600 mt-1">{{ $this->getOrangtuaCountByUmur(20, 40) }}</p>
                </div>
                <i class="ph ph-users text-4xl text-orange-300"></i>
            </div>
            <div class="flex items-center justify-between p-4 bg-pink-50 rounded-lg">
                <div>
                    <p class="text-sm text-gray-600">Total Ibu Hamil</p>
                    <p class="text-2xl font-bold text-pink-600 mt-1">{{ $posyandu->sasaran_ibuhamil->count() }}</p>
                </div>
                <i class="ph ph-heart text-4xl text-pink-300"></i>
            </div>
            <div class="flex items-center justify-between p-4 bg-yellow-50 rounded-lg">
                <div>
                    <p class="text-sm text-gray-600">Total Pralansia</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $this->getOrangtuaCountByUmur(40, 60) }}</p>
                </div>
                <i class="ph ph-user-circle text-4xl text-yellow-300"></i>
            </div>
            <div class="flex items-center justify-between p-4 bg-indigo-50 rounded-lg">
                <div>
                    <p class="text-sm text-gray-600">Total Lansia</p>
                    <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $this->getOrangtuaCountByUmur(60, null) }}</p>
                </div>
                <i class="ph ph-user-gear text-4xl text-indigo-300"></i>
            </div>
        </div>
    </div>
    </div>
</div>

