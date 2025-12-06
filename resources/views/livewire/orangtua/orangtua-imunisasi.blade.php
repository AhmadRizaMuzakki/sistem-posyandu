<div>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="ph ph-syringe text-2xl mr-3 text-primary"></i>
                    Status Imunisasi
                </h2>
            </div>
        </div>

        {{-- Daftar Imunisasi --}}
        @if($imunisasiList->count() > 0)
            @foreach($imunisasiList as $item)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">
                            {{ $item['sasaran']['nama'] }}
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm text-gray-600">
                            <div>
                                <span class="font-medium">NIK:</span> {{ $item['sasaran']['nik'] ?? '-' }}
                            </div>
                            <div>
                                <span class="font-medium">Kategori:</span> 
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-semibold capitalize">
                                    {{ $item['sasaran']['kategori'] }}
                                </span>
                            </div>
                            <div>
                                <span class="font-medium">Tanggal Lahir:</span> 
                                {{ $item['sasaran']['tanggal_lahir'] ? \Carbon\Carbon::parse($item['sasaran']['tanggal_lahir'])->format('d/m/Y') : '-' }}
                            </div>
                            <div>
                                <span class="font-medium">Total Imunisasi:</span> 
                                <span class="font-semibold text-primary">{{ $item['imunisasi']->count() }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Tabel Imunisasi --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        No
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Jenis Imunisasi
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tanggal Imunisasi
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Keterangan
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($item['imunisasi'] as $index => $imunisasi)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ $imunisasi->jenis_imunisasi ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ $imunisasi->tanggal_imunisasi ? \Carbon\Carbon::parse($imunisasi->tanggal_imunisasi)->format('d/m/Y') : '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            {{ $imunisasi->keterangan ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        @else
            {{-- Jika tidak ada data imunisasi --}}
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="text-center py-12">
                    <i class="ph ph-syringe text-6xl mb-4 text-gray-300"></i>
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Belum Ada Data Imunisasi</h3>
                    <p class="text-gray-500">
                        @if($allSasaran->count() > 0)
                            Data imunisasi untuk sasaran Anda belum tersedia.
                        @else
                            Anda belum memiliki sasaran terdaftar. Silakan hubungi admin posyandu untuk mendaftarkan sasaran.
                        @endif
                    </p>
                </div>
            </div>
        @endif

        {{-- Info Sasaran Tanpa Imunisasi --}}
        @if($allSasaran->count() > $imunisasiList->count())
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="ph ph-info text-yellow-600 text-xl mr-3 mt-0.5"></i>
                    <div>
                        <h4 class="text-sm font-semibold text-yellow-800 mb-1">Informasi</h4>
                        <p class="text-sm text-yellow-700">
                            Beberapa sasaran Anda belum memiliki catatan imunisasi. Silakan hubungi kader posyandu untuk mengupdate data imunisasi.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
