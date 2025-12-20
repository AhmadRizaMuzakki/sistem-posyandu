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

        {{-- Daftar Keluarga --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Daftar Keluarga</h2>
                    <p class="text-sm text-gray-600 mt-1">Data anggota keluarga yang terdaftar di posyandu berdasarkan No. KK</p>
                </div>
                <div class="flex items-center gap-3">
                    @if($allKeluarga->count() > 0)
                        <button wire:click="exportKeluarga" 
                           class="inline-flex items-center px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg shadow-md transition-all duration-200 transform hover:scale-105">
                            <i class="ph ph-file-pdf text-lg mr-2"></i>
                            Export PDF
                        </button>
                    @endif
                    <div class="bg-primary bg-opacity-10 rounded-full p-3">
                        <i class="ph ph-users text-2xl text-primary"></i>
                    </div>
                </div>
            </div>

            @if($allKeluarga->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIK</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Lahir</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Umur</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Kelamin</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alamat</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($allKeluarga as $index => $anggota)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $anggota['nama'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $anggota['nik'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($anggota['kategori'] == 'Bayi/Balita') bg-blue-100 text-blue-800
                                            @elseif($anggota['kategori'] == 'Remaja') bg-green-100 text-green-800
                                            @elseif($anggota['kategori'] == 'Dewasa') bg-yellow-100 text-yellow-800
                                            @elseif($anggota['kategori'] == 'Pralansia') bg-orange-100 text-orange-800
                                            @else bg-purple-100 text-purple-800
                                            @endif">
                                            {{ $anggota['kategori'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $anggota['tanggal_lahir'] ? \Carbon\Carbon::parse($anggota['tanggal_lahir'])->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $anggota['umur'] ? $anggota['umur'] . ' tahun' : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        @if($anggota['jenis_kelamin'] == 'Laki-laki' || $anggota['jenis_kelamin'] == 'L')
                                            Laki-laki
                                        @elseif($anggota['jenis_kelamin'] == 'Perempuan' || $anggota['jenis_kelamin'] == 'P')
                                            Perempuan
                                        @else
                                            {{ $anggota['jenis_kelamin'] ?? '-' }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $anggota['alamat'] ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <div class="bg-gray-100 rounded-full p-4 w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <i class="ph ph-users text-3xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-600 font-medium">Belum ada data anggota keluarga terdaftar</p>
                    <p class="text-sm text-gray-500 mt-2">Silakan hubungi kader posyandu untuk mendaftarkan anggota keluarga Anda</p>
                </div>
            @endif
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
