{{-- Logika Pengecekan: Apakah kategori ini Balita atau Remaja? --}}
@php
    // Cek apakah judul mengandung kata 'balita' atau 'remaja' (case insensitive)
    $lowerTitle = strtolower($title);
    $isDetailed = \Illuminate\Support\Str::contains($lowerTitle, ['balita', 'remaja']);
    // Cek apakah ini daftar Bayi/Balita (untuk format umur khusus bulan dan kolom pendidikan)
    $isBalitaList = \Illuminate\Support\Str::contains($lowerTitle, ['bayi/balita', 'bayi dan balita', 'bayi & balita']);
    // Cek apakah kategori ini Ibu Hamil
    $isIbuHamil = \Illuminate\Support\Str::contains($lowerTitle, ['ibu hamil']);
    // Tampilkan kolom Pendidikan hanya jika bukan Ibu Hamil dan bukan Balita
    $showPendidikanColumn = !$isIbuHamil && !$isBalitaList;
@endphp

<div class="bg-white rounded-lg shadow-sm p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-gray-800 flex items-center">
            <i class="ph {{ $icon }} text-2xl mr-3 text-primary"></i>
            {{ $title }}
        </h2>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500">{{ $count }} sasaran</span>

            @isset($exportUrl)
                <a href="{{ $exportUrl }}"
                   target="_blank"
                   class="flex items-center px-3 py-2 text-xs font-medium text-primary border border-primary rounded-lg hover:bg-primary hover:text-white transition-colors">
                    <i class="ph ph-file-pdf text-sm mr-2"></i>
                    Export PDF
                </a>
            @endisset

            <button wire:click="{{ $openModal }}"
                    class="flex items-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-indigo-700 transition-colors">
                <i class="ph ph-plus-circle text-lg mr-2"></i>
                Tambah Sasaran
            </button>
        </div>
    </div>

    {{-- Search Bar --}}
    @if(isset($searchProperty))
    <div class="mb-4">
        <div class="relative">
            <input type="text"
                   wire:model.live.debounce.300ms="{{ $searchProperty }}"
                   placeholder="Cari berdasarkan nama..."
                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            <i class="ph ph-magnifying-glass absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-lg"></i>
            @if(isset($search) && $search)
            <button wire:click="$set('{{ $searchProperty }}', '')"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <i class="ph ph-x text-lg"></i>
            </button>
            @endif
        </div>
    </div>
    @endif

    @if($count > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">NIK</th>
                        <th class="px-6 py-3">No KK</th>
                        <th class="px-6 py-3">Nama</th>
                        <th class="px-6 py-3">Tanggal Lahir</th>
                        <th class="px-6 py-3">Jenis Kelamin</th>
                        <th class="px-6 py-3">Status Keluarga</th>
                        <th class="px-6 py-3">Umur</th>

                        {{-- Kolom Pendidikan untuk Remaja, Dewasa, Lansia, Pralansia --}}
                        @if($showPendidikanColumn)
                            <th class="px-6 py-3">Pendidikan</th>
                        @endif

                        {{-- Kolom Khusus Balita & Remaja --}}
                        @if($isDetailed)
                            <th class="px-6 py-3">Alamat</th>
                            <th class="px-6 py-3">Kepersertaan BPJS</th>
                            <th class="px-6 py-3">Nomor BPJS</th>
                            @if(!$isBalitaList)
                                <th class="px-6 py-3">Nomor Telepon</th>
                            @endif
                        @endif

                        {{-- Kolom Khusus Ibu Hamil - Data Suami dan BPJS --}}
                        @if($isIbuHamil)
                            <th class="px-6 py-3">Minggu Kandungan</th>
                            <th class="px-6 py-3">Pekerjaan</th>
                            <th class="px-6 py-3">Alamat</th>
                            <th class="px-6 py-3">RT</th>
                            <th class="px-6 py-3">RW</th>
                            <th class="px-6 py-3">Nama Suami</th>
                            <th class="px-6 py-3">NIK Suami</th>
                            <th class="px-6 py-3">Pekerjaan Suami</th>
                            <th class="px-6 py-3">Status Keluarga Suami</th>
                            <th class="px-6 py-3">Kepersertaan BPJS</th>
                            <th class="px-6 py-3">Nomor Telepon</th>
                        @endif

                        {{-- Kolom untuk Lansia, Dewasa, Pralansia --}}
                        @if(!$isDetailed && !$isIbuHamil)
                            <th class="px-6 py-3">Alamat</th>
                            <th class="px-6 py-3">Kepersertaan BPJS</th>
                            <th class="px-6 py-3">Nomor BPJS</th>
                            <th class="px-6 py-3">Nomor Telepon</th>
                        @endif

                        {{-- Kolom Data Orang Tua (hanya untuk Balita dan Remaja) --}}
                        @if($isDetailed)
                            <th class="px-6 py-3">Nama Orang Tua</th>
                            <th class="px-6 py-3">Tempat Lahir Orang Tua</th>
                            <th class="px-6 py-3">Pekerjaan Orang Tua</th>
                            <th class="px-6 py-3">Pendidikan Orang Tua</th>
                        @endif
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sasaran as $item)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $item->nik_sasaran ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $item->no_kk_sasaran ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $item->nama_sasaran ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $item->tanggal_lahir ? \Carbon\Carbon::parse($item->tanggal_lahir)->format('d/m/Y') : '-' }}</td>
                        <td class="px-6 py-4">{{ $item->jenis_kelamin ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $item->status_keluarga ? ucfirst($item->status_keluarga) : '-' }}</td>
                        <td class="px-6 py-4">
                            @php
                                $umurLabel = '-';
                                if ($item->tanggal_lahir) {
                                    $dob = \Carbon\Carbon::parse($item->tanggal_lahir);
                                    $now = \Carbon\Carbon::now();
                                    // Pastikan tahun & bulan selalu integer
                                    $tahun = (int) $dob->diffInYears($now);
                                    if ($isBalitaList) {
                                        // Untuk balita, hitung umur dalam bulan berdasarkan tanggal lahir
                                        $bulan = (int) $dob->diffInMonths($now);
                                        $umurLabel = $bulan . ' bln';
                                    } else {
                                        $umurLabel = $tahun . ' th';
                                    }
                                } elseif (!is_null($item->umur_sasaran)) {
                                    $umurLabel = (int) $item->umur_sasaran . ' th';
                                }
                            @endphp
                            {{ $umurLabel }}
                        </td>

                        {{-- Isi Kolom Pendidikan untuk Remaja, Dewasa, Lansia, Pralansia --}}
                        @if($showPendidikanColumn)
                            <td class="px-6 py-4">{{ $item->pendidikan ?? '-' }}</td>
                        @endif

                        {{-- Isi Kolom Khusus Balita & Remaja --}}
                        @if($isDetailed)
                            <td class="px-6 py-4">{{ $item->alamat_sasaran ?? '-' }}</td>
                            <td class="px-6 py-4">
                                @if($item->kepersertaan_bpjs)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $item->kepersertaan_bpjs == 'PBI' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $item->kepersertaan_bpjs }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">{{ $item->nomor_bpjs ?? '-' }}</td>
                            @if(!$isBalitaList)
                                <td class="px-6 py-4">{{ $item->nomor_telepon ?? '-' }}</td>
                            @endif
                        @endif

                        {{-- Isi Kolom Khusus Ibu Hamil - Data Suami dan BPJS --}}
                        @if($isIbuHamil)
                            <td class="px-6 py-4">{{ $item->minggu_kandungan ? $item->minggu_kandungan . ' minggu' : '-' }}</td>
                            <td class="px-6 py-4">{{ $item->pekerjaan ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $item->alamat_sasaran ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $item->rt ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $item->rw ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $item->nama_suami ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $item->nik_suami ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $item->pekerjaan_suami ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $item->status_keluarga_suami ? ucfirst($item->status_keluarga_suami) : '-' }}</td>
                            <td class="px-6 py-4">
                                @if($item->kepersertaan_bpjs)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $item->kepersertaan_bpjs == 'PBI' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $item->kepersertaan_bpjs }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">{{ $item->nomor_telepon ?? '-' }}</td>
                        @endif

                        {{-- Isi Kolom untuk Lansia, Dewasa, Pralansia --}}
                        @if(!$isDetailed && !$isIbuHamil)
                            <td class="px-6 py-4">{{ $item->alamat_sasaran ?? '-' }}</td>
                            <td class="px-6 py-4">
                                @if($item->kepersertaan_bpjs)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $item->kepersertaan_bpjs == 'PBI' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $item->kepersertaan_bpjs }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">{{ $item->nomor_bpjs ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $item->nomor_telepon ?? '-' }}</td>
                        @endif

                        {{-- Kolom Data Orang Tua (hanya untuk Balita dan Remaja) --}}
                        @if($isDetailed)
                            @php
                                // Ambil data orangtua
                                $orangtua = null;
                                if (isset($item->orangtua) && $item->orangtua) {
                                    $orangtua = $item->orangtua;
                                }

                                // Tentukan apakah harus menampilkan strip
                                $showStrip = false;
                                if (isset($item->nik_orangtua) && $item->nik_orangtua == '-') {
                                    $showStrip = true;
                                } elseif (!$orangtua) {
                                    $showStrip = true;
                                }
                            @endphp
                            <td class="px-6 py-4">
                                @if($showStrip)
                                    <span class="text-gray-400">-</span>
                                @elseif($orangtua)
                                    {{ $orangtua->nama ?? '-' }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($showStrip)
                                    <span class="text-gray-400">-</span>
                                @elseif($orangtua)
                                    {{ $orangtua->tempat_lahir ?? '-' }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($showStrip)
                                    <span class="text-gray-400">-</span>
                                @elseif($orangtua)
                                    {{ $orangtua->pekerjaan ?? '-' }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($showStrip)
                                    <span class="text-gray-400">-</span>
                                @elseif($orangtua)
                                    {{ $orangtua->pendidikan ?? '-' }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        @endif
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                @if(isset($item->id_sasaran_bayibalita) && $item->id_sasaran_bayibalita ||
                                    isset($item->id_sasaran_remaja) && $item->id_sasaran_remaja ||
                                    isset($item->id_sasaran_dewasa) && $item->id_sasaran_dewasa ||
                                    isset($item->id_sasaran_pralansia) && $item->id_sasaran_pralansia ||
                                    isset($item->id_sasaran_lansia) && $item->id_sasaran_lansia ||
                                    isset($item->id_sasaran_ibuhamil) && $item->id_sasaran_ibuhamil)
                                <button wire:click="{{ $editMethod }}({{ $item->getKey() }})"
                                        class="text-blue-600 hover:text-blue-800 transition-colors"
                                        title="Edit">
                                    <i class="ph ph-pencil-simple text-xl"></i>
                                </button>
                                <button wire:click="{{ $deleteMethod }}({{ $item->getKey() }})"
                                        wire:confirm="Apakah Anda yakin ingin menghapus sasaran ini?"
                                        class="text-red-600 hover:text-red-800 transition-colors"
                                        title="Hapus">
                                    <i class="ph ph-trash text-xl"></i>
                                </button>
                                @else
                                <button wire:click="editOrangtua({{ $item->getKey() }})"
                                        class="text-blue-600 hover:text-blue-800 transition-colors"
                                        title="Edit">
                                    <i class="ph ph-pencil-simple text-xl"></i>
                                </button>
                                <button wire:click="deleteOrangtua({{ $item->getKey() }})"
                                        wire:confirm="Apakah Anda yakin ingin menghapus data orangtua ini?"
                                        class="text-red-600 hover:text-red-800 transition-colors"
                                        title="Hapus">
                                    <i class="ph ph-trash text-xl"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        {{-- Sesuaikan colspan berdasarkan jumlah kolom yang aktif --}}
                        @php
                            $colspan = 7; // Base columns: NIK, Nama, No KK, Tanggal Lahir, Jenis Kelamin, Umur, Aksi
                            if ($isDetailed) {
                                if ($showPendidikanColumn) {
                                    $colspan += 1; // Pendidikan
                                }
                                $colspan += 3; // Alamat, Kepersertaan BPJS, Nomor BPJS
                                if (!$isBalitaList) {
                                    $colspan += 1; // Nomor Telepon (hanya untuk remaja)
                                }
                                $colspan += 4; // Nama Orang Tua, Tempat Lahir Orang Tua, Pekerjaan Orang Tua, Pendidikan Orang Tua
                            }
                            if ($isIbuHamil) {
                                $colspan += 11; // Minggu Kandungan, Pekerjaan, Alamat, RT, RW, Nama Suami, NIK Suami, Pekerjaan Suami, Status Keluarga Suami, Kepersertaan BPJS, Nomor Telepon
                            } elseif (!$isDetailed) {
                                if ($showPendidikanColumn) {
                                    $colspan += 1; // Pendidikan
                                }
                                $colspan += 4; // Alamat, Kepersertaan BPJS, Nomor BPJS, Nomor Telepon
                            }
                        @endphp
                        <td colspan="{{ $colspan }}" class="px-6 py-8 text-center text-gray-500">
                            <i class="ph ph-magnifying-glass text-3xl mb-2 block"></i>
                            <p>Tidak ada data ditemukan</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            {{-- Pagination --}}
            @if(isset($pagination) && $pagination['total'] > 3)
            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-500">
                    Menampilkan {{ (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 }}
                    sampai {{ min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) }}
                    dari {{ $pagination['total'] }} data
                </div>
                <div class="flex items-center gap-2">
                    {{-- Previous Button --}}
                    <button wire:click="$set('{{ $pageProperty }}', {{ max(1, $pagination['current_page'] - 1) }})"
                            @if($pagination['current_page'] == 1) disabled @endif
                            class="px-3 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <i class="ph ph-caret-left"></i>
                    </button>

                    {{-- Page Numbers --}}
                    @for($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++)
                        <button wire:click="$set('{{ $pageProperty }}', {{ $i }})"
                                class="px-3 py-1 text-sm font-medium rounded-lg transition-colors {{ $i == $pagination['current_page'] ? 'bg-primary text-white' : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50' }}">
                            {{ $i }}
                        </button>
                    @endfor

                    {{-- Next Button --}}
                    <button wire:click="$set('{{ $pageProperty }}', {{ min($pagination['total_pages'], $pagination['current_page'] + 1) }})"
                            @if($pagination['current_page'] == $pagination['total_pages']) disabled @endif
                            class="px-3 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <i class="ph ph-caret-right"></i>
                    </button>
                </div>
            </div>
            @elseif(isset($pagination) && $pagination['total'] > 0)
            <div class="mt-4 flex justify-end">
                <span class="text-xs text-gray-500">
                    Menampilkan semua {{ $pagination['total'] }} data
                </span>
            </div>
            @endif
        </div>
    @else
        <div class="text-center py-8 text-gray-500">
            <i class="ph {{ $icon }} text-4xl mb-2"></i>
            <p>{{ $emptyMessage }}</p>
        </div>
    @endif
</div>
