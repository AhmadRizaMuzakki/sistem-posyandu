{{-- Daftar Imunisasi --}}
<div class="bg-white rounded-lg shadow-sm p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-gray-800 flex items-center">
            <i class="ph ph-syringe text-2xl mr-3 text-primary"></i>
            Daftar Imunisasi
        </h2>
        <button wire:click="openImunisasiModal" 
                class="flex items-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700 transition-colors">
            <i class="ph ph-plus text-lg mr-2"></i>
            Tambah Imunisasi
        </button>
    </div>

    {{-- Search Bar --}}
    <div class="mb-4">
        <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                <i class="ph ph-magnifying-glass text-lg"></i>
            </span>
            <input type="text" 
                   wire:model.live.debounce.300ms="search"
                   class="w-full py-2 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                   placeholder="Cari berdasarkan nama sasaran, jenis imunisasi, kategori, atau keterangan...">
        </div>
    </div>

    @if($imunisasiList->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sasaran</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Imunisasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tinggi (cm)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Berat (kg)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Petugas Kesehatan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($imunisasiList as $index => $imunisasi)
                        @php
                            $sasaran = $imunisasi->sasaran;
                            $sasaranNama = $sasaran ? $sasaran->nama_sasaran : 'Tidak ditemukan';
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $sasaranNama }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 capitalize">
                                    {{ $imunisasi->kategori_sasaran }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $imunisasi->jenis_imunisasi }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $imunisasi->tanggal_imunisasi ? \Carbon\Carbon::parse($imunisasi->tanggal_imunisasi)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                @if(!is_null($imunisasi->tinggi_badan))
                                    {{ number_format($imunisasi->tinggi_badan, 1, ',', '.') }} cm
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                @if(!is_null($imunisasi->berat_badan))
                                    {{ number_format($imunisasi->berat_badan, 1, ',', '.') }} kg
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $imunisasi->petugasKesehatan->nama_petugas_kesehatan ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button wire:click="openImunisasiModal({{ $imunisasi->id_imunisasi }})" 
                                        class="text-indigo-600 hover:text-indigo-900 mr-3">
                                    <i class="ph ph-pencil"></i>
                                </button>
                                <button wire:click="deleteImunisasi({{ $imunisasi->id_imunisasi }})" 
                                        wire:confirm="Apakah Anda yakin ingin menghapus data imunisasi ini?"
                                        class="text-red-600 hover:text-red-900">
                                    <i class="ph ph-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-12 text-gray-500">
            <i class="ph ph-syringe text-4xl mb-2"></i>
            <p>Belum ada data imunisasi</p>
            <button wire:click="openImunisasiModal" 
                    class="mt-4 px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700 transition-colors">
                Tambah Imunisasi Pertama
            </button>
        </div>
    @endif

    {{-- Card Keterangan: per kategori X dari Y sasaran (di bawah tabel) --}}
    @if(isset($imunisasiKeteranganPerKategori))
    <div class="mt-5 pt-5 border-t border-gray-100">
        {{-- Header --}}
        <div class="flex items-center gap-2 mb-3">
            <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                <i class="ph ph-chart-pie-slice text-primary text-sm"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-800">Keterangan Imunisasi</h3>
                <p class="text-xs text-gray-500">Per kategori · Tahun {{ date('Y') }}</p>
            </div>
        </div>

        {{-- Kartu per kategori --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 xl:grid-cols-6 gap-2">
            @php
                $kategoriStyles = [
                    'bayibalita' => ['bar' => 'bg-amber-500', 'icon' => 'bg-amber-500/15', 'text' => 'text-amber-700', 'ph' => 'ph-baby'],
                    'remaja'     => ['bar' => 'bg-violet-500', 'icon' => 'bg-violet-500/15', 'text' => 'text-violet-700', 'ph' => 'ph-person'],
                    'dewasa'     => ['bar' => 'bg-emerald-500', 'icon' => 'bg-emerald-500/15', 'text' => 'text-emerald-700', 'ph' => 'ph-users-three'],
                    'pralansia'  => ['bar' => 'bg-orange-500', 'icon' => 'bg-orange-500/15', 'text' => 'text-orange-700', 'ph' => 'ph-user-circle'],
                    'lansia'     => ['bar' => 'bg-indigo-500', 'icon' => 'bg-indigo-500/15', 'text' => 'text-indigo-700', 'ph' => 'ph-user-gear'],
                ];
            @endphp
            @foreach($imunisasiKeteranganPerKategori as $row)
                @if($row['total_sasaran'] > 0)
                @php
                    $c = $kategoriStyles[$row['kategori']] ?? ['bar' => 'bg-primary', 'icon' => 'bg-primary/15', 'text' => 'text-gray-700', 'ph' => 'ph-syringe'];
                    $persen = $row['total_sasaran'] > 0 ? round(($row['sudah_imunisasi'] / $row['total_sasaran']) * 100) : 0;
                @endphp
                <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-100 hover:shadow transition-shadow">
                    <div class="flex items-center justify-between gap-2 mb-1.5">
                        <div class="w-7 h-7 rounded-md {{ $c['icon'] }} flex items-center justify-center flex-shrink-0">
                            <i class="ph {{ $c['ph'] }} text-sm {{ $c['text'] }}"></i>
                        </div>
                        <span class="text-[10px] font-bold {{ $c['text'] }}">{{ $persen }}%</span>
                    </div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">{{ $row['label'] }}</p>
                    <p class="text-base font-bold text-gray-800 tabular-nums leading-tight">
                        <span class="{{ $c['text'] }}">{{ $row['sudah_imunisasi'] }}</span><span class="text-gray-300">/</span><span class="text-gray-600">{{ $row['total_sasaran'] }}</span>
                    </p>
                    <div class="h-1 bg-gray-100 rounded-full overflow-hidden mt-1.5">
                        <div class="h-full {{ $c['bar'] }} rounded-full transition-all duration-500" style="width: {{ min($persen, 100) }}%"></div>
                    </div>
                </div>
                @endif
            @endforeach
        </div>

        {{-- Total ringkasan --}}
        @php
            $totalPersen = $totalSemuaSasaran > 0 ? round(($totalSudahImunisasi / $totalSemuaSasaran) * 100) : 0;
        @endphp
        <div class="mt-3 px-3 py-2.5 rounded-lg bg-primary/5 border border-primary/10 flex flex-wrap items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center flex-shrink-0">
                <i class="ph ph-clipboard-text text-white text-sm"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500">Total</p>
                <p class="text-sm font-bold text-gray-800 tabular-nums">{{ $totalSudahImunisasi }} dari {{ $totalSemuaSasaran }} sasaran <span class="text-primary font-semibold">({{ $totalPersen }}%)</span></p>
            </div>
            <div class="flex-1 min-w-[80px] max-w-[120px]">
                <div class="h-1.5 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-primary rounded-full transition-all duration-500" style="width: {{ $totalPersen }}%"></div>
                </div>
            </div>
            <span class="text-xs text-gray-400">Tahun {{ date('Y') }}</span>
        </div>
    </div>
    @endif
</div>

