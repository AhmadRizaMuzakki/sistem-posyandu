{{-- Daftar Riwayat Imunisasi — satu tabel gabungan --}}
@php
    $kategoriLabel = fn ($slug) => match ($slug) {
        'bayibalita' => 'Bayi/Balita',
        'remaja' => 'Remaja',
        'dewasa' => 'Dewasa',
        'pralansia' => 'Pralansia',
        'lansia' => 'Lansia',
        default => ucfirst($slug ?? '-'),
    };
    $hasData = ($totalBaris ?? 0) > 0;
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    {{-- Header tabel --}}
    <div class="px-6 sm:px-8 pt-6 sm:pt-7 pb-5 sm:pb-6 border-b border-gray-100">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 leading-snug">
                    @if(!empty($filterNamaAktif) && !empty($filterNama))
                        Riwayat Imunisasi — {{ $filterNama }}
                    @else
                        Riwayat Imunisasi Keluarga
                    @endif
                </h3>
                <p class="text-sm text-gray-500 mt-2.5 leading-relaxed">
                    @if($hasData)
                        Menampilkan {{ $riwayatFirstItem ?? 0 }}–{{ $riwayatLastItem ?? 0 }} dari {{ $totalBaris }} catatan
                        · {{ $imunisasiList->count() }} sasaran
                    @else
                        {{ $imunisasiList->count() }} sasaran · 0 catatan imunisasi
                    @endif
                    @if(!empty($filterAktif))
                        <span class="inline-flex items-center mt-2 sm:mt-0 sm:ml-2 px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">
                            Filter aktif
                            @if(!empty($periodeLabel))
                                · {{ $periodeLabel }}
                            @endif
                        </span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- Panel filter --}}
    @if(count($namaSasaranList ?? []) > 0)
        <div class="px-6 sm:px-8 py-5 sm:py-6 bg-gray-50/80 border-b border-gray-100">
            <div class="flex items-center gap-2 mb-4">
                <i class="ph ph-funnel text-primary text-base"></i>
                <span class="text-sm font-medium text-gray-700">Filter Riwayat Rekap</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-5">
                <div>
                    <label for="filter-sasaran" class="block text-xs font-medium text-gray-500 mb-2">Nama Sasaran</label>
                    <select id="filter-sasaran"
                            wire:model.live="filterNama"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm bg-white shadow-sm">
                        <option value="">Semua sasaran</option>
                        @foreach($namaSasaranList as $nama)
                            <option value="{{ $nama }}">{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filter-bulan" class="block text-xs font-medium text-gray-500 mb-2">Bulan</label>
                    <select id="filter-bulan"
                            wire:model.live="filterBulan"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm bg-white shadow-sm">
                        <option value="">Semua Bulan</option>
                        @foreach(['1'=>'Januari','2'=>'Februari','3'=>'Maret','4'=>'April','5'=>'Mei','6'=>'Juni','7'=>'Juli','8'=>'Agustus','9'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $v => $l)
                            <option value="{{ $v }}">{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filter-tahun" class="block text-xs font-medium text-gray-500 mb-2">Tahun</label>
                    <select id="filter-tahun"
                            wire:model.live="filterTahun"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm bg-white shadow-sm">
                        <option value="">Semua Tahun</option>
                        @for($y = now()->year; $y >= now()->year - 5; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label for="filter-limit" class="block text-xs font-medium text-gray-500 mb-2">Per halaman</label>
                    <select id="filter-limit"
                            wire:model.live="filterLimit"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm bg-white shadow-sm">
                        @foreach([5, 10, 25, 50] as $n)
                            <option value="{{ $n }}">{{ $n }} baris</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    @endif

    @if($hasData)
        <div class="overflow-x-auto px-2 sm:px-4 pt-3 pb-4 sm:pb-5">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Sasaran</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kategori</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jenis Imunisasi</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tinggi</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Berat</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tekanan Darah</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Gula Darah</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($riwayatRows as $index => $row)
                        @php $imunisasi = $row['imunisasi']; @endphp
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <td class="px-5 py-5 whitespace-nowrap text-sm text-gray-500 align-middle">{{ ($riwayatFirstItem ?? 1) + $loop->index }}</td>
                            <td class="px-5 py-5 whitespace-nowrap align-middle">
                                <div class="text-sm font-medium text-gray-900 leading-relaxed">{{ $row['sasaran']['nama'] }}</div>
                                <div class="text-xs text-gray-400 mt-1">NIK: {{ $row['sasaran']['nik'] ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-5 whitespace-nowrap align-middle">
                                <span class="inline-flex px-2.5 py-1 bg-blue-50 text-blue-700 rounded-md text-xs font-medium">
                                    {{ $kategoriLabel($row['sasaran']['kategori'] ?? '') }}
                                </span>
                            </td>
                            <td class="px-5 py-5 whitespace-nowrap text-sm text-gray-800 align-middle">
                                {{ $imunisasi->jenis_imunisasi ?? '-' }}
                            </td>
                            <td class="px-5 py-5 whitespace-nowrap text-sm text-gray-600 align-middle">
                                {{ $imunisasi->tanggal_imunisasi ? \Carbon\Carbon::parse($imunisasi->tanggal_imunisasi)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-5 py-5 whitespace-nowrap text-sm text-gray-600 align-middle">
                                @if(!is_null($imunisasi->tinggi_badan))
                                    {{ number_format($imunisasi->tinggi_badan, 1, ',', '.') }} cm
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-5 py-5 whitespace-nowrap text-sm text-gray-600 align-middle">
                                @if(!is_null($imunisasi->berat_badan))
                                    {{ number_format($imunisasi->berat_badan, 1, ',', '.') }} kg
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-5 py-5 whitespace-nowrap text-sm text-gray-600 align-middle">
                                @if($imunisasi->tekanan_darah)
                                    {{ $imunisasi->tekanan_darah }} mmHg
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-5 py-5 whitespace-nowrap text-sm text-gray-600 align-middle">
                                @if(!is_null($imunisasi->gula_darah))
                                    {{ number_format($imunisasi->gula_darah, 0, ',', '.') }} mg/dL
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-5 py-5 text-sm text-gray-600 max-w-[160px] truncate align-middle" title="{{ $imunisasi->keterangan ?? '-' }}">
                                {{ $imunisasi->keterangan ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(!empty($riwayatHasPages))
            <div class="px-6 sm:px-8 py-4 sm:py-5 border-t border-gray-100 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs sm:text-sm text-gray-500">
                    Menampilkan
                    <span class="font-medium text-gray-700">{{ $riwayatFirstItem }}</span>
                    –
                    <span class="font-medium text-gray-700">{{ $riwayatLastItem }}</span>
                    dari
                    <span class="font-medium text-gray-700">{{ $totalBaris }}</span>
                    data
                </p>
                <div class="flex items-center gap-1.5 flex-wrap justify-center sm:justify-end">
                    <button wire:click="previousRiwayatPage"
                            @if(($riwayatCurrentPage ?? 1) <= 1) disabled @endif
                            class="inline-flex items-center justify-center w-9 h-9 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-white transition-colors"
                            aria-label="Sebelumnya">
                        <i class="ph ph-caret-left text-base"></i>
                    </button>

                    @for($page = max(1, ($riwayatCurrentPage ?? 1) - 2); $page <= min(($riwayatLastPage ?? 1), ($riwayatCurrentPage ?? 1) + 2); $page++)
                        <button wire:click="gotoRiwayatPage({{ $page }})"
                                class="inline-flex items-center justify-center min-w-[2.25rem] h-9 px-2.5 text-sm font-medium rounded-lg transition-colors
                                {{ $page == ($riwayatCurrentPage ?? 1)
                                    ? 'bg-primary text-white border border-primary shadow-sm'
                                    : 'text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 hover:border-gray-300' }}">
                            {{ $page }}
                        </button>
                    @endfor

                    <button wire:click="nextRiwayatPage"
                            @if(($riwayatCurrentPage ?? 1) >= ($riwayatLastPage ?? 1)) disabled @endif
                            class="inline-flex items-center justify-center w-9 h-9 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-white transition-colors"
                            aria-label="Berikutnya">
                        <i class="ph ph-caret-right text-base"></i>
                    </button>
                </div>
            </div>
        @endif
    @else
        <div class="text-center py-16 sm:py-20 px-6 sm:px-8">
            <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                <i class="ph ph-syringe text-2xl text-gray-300"></i>
            </div>
            <h4 class="text-base font-semibold text-gray-700 mb-2">Belum Ada Data Imunisasi</h4>
            <p class="text-sm text-gray-500 max-w-md mx-auto leading-relaxed">
                @if($allSasaran->count() > 0)
                    @if(!empty($filterBulanTahunAktif) && !empty($periodeLabel))
                        Tidak ada data imunisasi pada periode {{ $periodeLabel }}.
                    @elseif(!empty($filterNamaAktif))
                        Data imunisasi untuk sasaran terpilih belum tersedia.
                    @else
                        Data imunisasi untuk sasaran Anda belum tersedia.
                    @endif
                @else
                    Anda belum memiliki sasaran terdaftar. Silakan hubungi admin posyandu untuk mendaftarkan sasaran.
                @endif
            </p>
        </div>
    @endif
</div>

@if($allSasaran->count() > $imunisasiList->count())
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 sm:p-6 mt-5">
        <div class="flex items-start gap-3">
            <i class="ph ph-info text-amber-600 text-lg mt-0.5"></i>
            <div>
                <h4 class="text-sm font-semibold text-amber-800 mb-1">Informasi</h4>
                <p class="text-sm text-amber-700 leading-relaxed">
                    Beberapa sasaran Anda belum memiliki catatan imunisasi. Silakan hubungi kader posyandu untuk mengupdate data imunisasi.
                </p>
            </div>
        </div>
    </div>
@endif
