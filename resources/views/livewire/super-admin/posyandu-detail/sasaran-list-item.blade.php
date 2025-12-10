{{-- Logika Pengecekan: Apakah kategori ini Balita atau Remaja? --}}
@php
    // Cek apakah judul mengandung kata 'balita' atau 'remaja' (case insensitive)
    $isDetailed = \Illuminate\Support\Str::contains(strtolower($title), ['balita', 'remaja']);
@endphp

<div class="bg-white rounded-lg shadow-sm p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-gray-800 flex items-center">
            <i class="ph {{ $icon }} text-2xl mr-3 text-primary"></i>
            {{ $title }}
        </h2>
        <div class="flex items-center gap-4">
            <span class="text-sm text-gray-500">{{ $count }} sasaran</span>
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
                        <th class="px-6 py-3">Nama</th>
                        <th class="px-6 py-3">No KK</th>
                        <th class="px-6 py-3">Tanggal Lahir</th>
                        <th class="px-6 py-3">Jenis Kelamin</th>
                        <th class="px-6 py-3">Umur</th>

                        {{-- Kolom Khusus Balita & Remaja --}}
                        @if($isDetailed)
                            <th class="px-6 py-3">Alamat</th>
                            <th class="px-6 py-3">Kepersertaan BPJS</th>
                            <th class="px-6 py-3">Nomor BPJS</th>
                            <th class="px-6 py-3">Nomor Telepon</th>
                        @endif

                        <th class="px-6 py-3">Orang Tua</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sasaran as $item)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $item->nik_sasaran ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $item->nama_sasaran ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $item->no_kk_sasaran ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $item->tanggal_lahir ? \Carbon\Carbon::parse($item->tanggal_lahir)->format('d/m/Y') : '-' }}</td>
                        <td class="px-6 py-4">{{ $item->jenis_kelamin ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $item->umur_sasaran ?? '-' }} tahun</td>

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
                            <td class="px-6 py-4">{{ $item->nomor_telepon ?? '-' }}</td>
                        @endif

                        <td class="px-6 py-4">
                            @if($item->user && $item->user->hasRole('orangtua'))
                                <span class="text-sm">{{ $item->user->name ?? '-' }}</span>
                                @if($item->user->email)
                                    <br><span class="text-xs text-gray-400">{{ $item->user->email }}</span>
                                @endif
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                @if(isset($item->id_sasaran_bayi_balita) && $item->id_sasaran_bayi_balita ||
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
                        <td colspan="{{ $isDetailed ? 12 : 8 }}" class="px-6 py-8 text-center text-gray-500">
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