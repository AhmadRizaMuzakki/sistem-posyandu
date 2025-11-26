{{-- Informasi Utama --}}
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
            @if($posyandu->jumlah_sasaran)
            <div>
                <label class="text-sm font-medium text-gray-500">Jumlah Sasaran</label>
                <p class="text-gray-800 mt-1">{{ number_format($posyandu->jumlah_sasaran, 0, ',', '.') }} orang</p>
            </div>
            @endif
            @if($posyandu->sk_posyandu)
            <div>
                <label class="text-sm font-medium text-gray-500">SK Posyandu</label>
                <p class="text-gray-800 mt-1">{{ $posyandu->sk_posyandu }}</p>
            </div>
            @endif
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
                    <p class="text-2xl font-bold text-orange-600 mt-1">{{ $posyandu->sasaran_dewasa->count() }}</p>
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
                    <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $posyandu->sasaran_pralansia->count() }}</p>
                </div>
                <i class="ph ph-user-circle text-4xl text-yellow-300"></i>
            </div>
            <div class="flex items-center justify-between p-4 bg-indigo-50 rounded-lg">
                <div>
                    <p class="text-sm text-gray-600">Total Lansia</p>
                    <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $posyandu->sasaran_lansia->count() }}</p>
                </div>
                <i class="ph ph-user-gear text-4xl text-indigo-300"></i>
            </div>
        </div>
    </div>
</div>

