{{-- Daftar Riwayat Imunisasi — satu tabel gabungan --}}
@if($imunisasiList->count() > 0)
    @php
        $totalBaris = $imunisasiList->sum(fn ($item) => $item['imunisasi']->count());
        $kategoriLabel = fn ($slug) => match ($slug) {
            'bayibalita' => 'Bayi/Balita',
            'remaja' => 'Remaja',
            'dewasa' => 'Dewasa',
            'pralansia' => 'Pralansia',
            'lansia' => 'Lansia',
            default => ucfirst($slug ?? '-'),
        };
    @endphp

    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4 pb-4 border-b border-gray-200">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">
                    @if(!empty($filterAktif) && !empty($filterNama))
                        Riwayat Imunisasi — {{ $filterNama }}
                    @else
                        Riwayat Imunisasi Keluarga
                    @endif
                </h3>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ $imunisasiList->count() }} sasaran · {{ $totalBaris }} catatan imunisasi
                    @if(!empty($filterAktif))
                        <span class="text-primary">(filter aktif)</span>
                    @endif
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Sasaran</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Imunisasi</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Imunisasi</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tinggi (cm)</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Berat (kg)</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tekanan Darah</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gula Darah</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php $no = 1; @endphp
                    @foreach($imunisasiList as $item)
                        @foreach($item['imunisasi'] as $imunisasi)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">{{ $no++ }}</td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $item['sasaran']['nama'] }}</div>
                                    <div class="text-xs text-gray-500">NIK: {{ $item['sasaran']['nik'] ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-semibold">
                                        {{ $kategoriLabel($item['sasaran']['kategori'] ?? '') }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $imunisasi->jenis_imunisasi ?? '-' }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $imunisasi->tanggal_imunisasi ? \Carbon\Carbon::parse($imunisasi->tanggal_imunisasi)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600">
                                    @if(!is_null($imunisasi->tinggi_badan))
                                        {{ number_format($imunisasi->tinggi_badan, 1, ',', '.') }} cm
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600">
                                    @if(!is_null($imunisasi->berat_badan))
                                        {{ number_format($imunisasi->berat_badan, 1, ',', '.') }} kg
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600">
                                    @if($imunisasi->tekanan_darah)
                                        {{ $imunisasi->tekanan_darah }} mmHg
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600">
                                    @if(!is_null($imunisasi->gula_darah))
                                        {{ number_format($imunisasi->gula_darah, 0, ',', '.') }} mg/dL
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600">{{ $imunisasi->keterangan ?? '-' }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
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
