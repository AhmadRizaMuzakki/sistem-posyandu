<div>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">{{ $posyandu->nama_posyandu }}</h1>
                    <p class="text-gray-500 mt-1">Manajemen Data Pendidikan</p>
                </div>
            </div>
        </div>

        {{-- Daftar Pendidikan --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="ph ph-graduation-cap text-2xl mr-3 text-primary"></i>
                    Daftar Pendidikan
                </h2>
                <button wire:click="openPendidikanModal"
                        class="flex items-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="ph ph-plus text-lg mr-2"></i>
                    Tambah Pendidikan
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
                           placeholder="Cari berdasarkan nama, NIK, atau pendidikan terakhir...">
                </div>
            </div>

            @if($pendidikanList->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIK</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Lahir</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Kelamin</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Umur</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pendidikan Terakhir</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($pendidikanList as $index => $pendidikan)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $pendidikan->nik ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $pendidikan->nama }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $pendidikan->tanggal_lahir ? \Carbon\Carbon::parse($pendidikan->tanggal_lahir)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $pendidikan->jenis_kelamin ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $pendidikan->umur ? $pendidikan->umur . ' tahun' : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $pendidikan->pendidikan_terakhir ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button wire:click="openPendidikanModal({{ $pendidikan->id_pendidikan }})"
                                                class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            <i class="ph ph-pencil"></i>
                                        </button>
                                        <button wire:click="deletePendidikan({{ $pendidikan->id_pendidikan }})"
                                                wire:confirm="Apakah Anda yakin ingin menghapus data pendidikan ini?"
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
                    <i class="ph ph-graduation-cap text-4xl mb-2"></i>
                    <p>Belum ada data pendidikan</p>
                    <button wire:click="openPendidikanModal"
                            class="mt-4 px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        Tambah Pendidikan Pertama
                    </button>
                </div>
            @endif
        </div>

        {{-- Modal Form Pendidikan --}}
        @include('livewire.posyandu.modals.pendidikan-modal')
    </div>
</div>
