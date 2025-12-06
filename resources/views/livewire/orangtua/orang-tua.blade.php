<div>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Dashboard Orangtua</h1>
            <p class="text-gray-600">Selamat datang, {{ Auth::user()->name }}!</p>
        </div>

        {{-- Quick Actions --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Card Imunisasi --}}
            <a href="{{ route('orangtua.imunisasi') }}" class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow border-l-4 border-primary">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Status Imunisasi</h3>
                        <p class="text-sm text-gray-600 mb-4">Lihat riwayat imunisasi sasaran Anda</p>
                        <div class="flex items-center text-primary font-medium">
                            <span>Lihat Detail</span>
                            <i class="ph ph-arrow-right ml-2"></i>
                        </div>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-full p-4">
                        <i class="ph ph-syringe text-3xl text-primary"></i>
                    </div>
                </div>
            </a>

            {{-- Card Informasi --}}
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Informasi Posyandu</h3>
                        <p class="text-sm text-gray-600 mb-4">Hubungi kader posyandu untuk informasi lebih lanjut</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-4">
                        <i class="ph ph-info text-3xl text-blue-600"></i>
                    </div>
                </div>
            </div>

            {{-- Card Bantuan --}}
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Bantuan</h3>
                        <p class="text-sm text-gray-600 mb-4">Butuh bantuan? Hubungi admin posyandu</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-4">
                        <i class="ph ph-question text-3xl text-green-600"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Informasi Penting --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <div class="flex items-start">
                <i class="ph ph-info text-blue-600 text-xl mr-3 mt-0.5"></i>
                <div>
                    <h4 class="text-sm font-semibold text-blue-800 mb-2">Informasi Penting</h4>
                    <ul class="text-sm text-blue-700 space-y-1 list-disc list-inside">
                        <li>Pastikan data sasaran Anda sudah terdaftar di posyandu</li>
                        <li>Status imunisasi akan diperbarui oleh kader posyandu setelah melakukan imunisasi</li>
                        <li>Jika ada ketidaksesuaian data, silakan hubungi kader posyandu terdekat</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
