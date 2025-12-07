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
                   placeholder="Cari berdasarkan jenis imunisasi, kategori, atau keterangan...">
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Input Oleh</th>
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
                                {{ $imunisasi->user->name ?? '-' }}
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
</div>

