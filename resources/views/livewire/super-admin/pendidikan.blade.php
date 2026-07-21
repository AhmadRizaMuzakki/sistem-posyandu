<div>
    <div class="space-y-6">
        {{-- Header --}}
        @include('livewire.super-admin.posyandu-detail.header')

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

            @if($pendidikanList->total() > 0)
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">RT</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">RW</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pendidikan Terakhir</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($pendidikanList as $index => $pendidikan)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $pendidikanList->firstItem() + $loop->index }}</td>
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
                                        {{ $pendidikan->rt ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $pendidikan->rw ?? '-' }}
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

                @if($pendidikanList->hasPages())
                    <div class="mt-5 pt-4 border-t border-gray-100 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs sm:text-sm text-gray-500">
                            Menampilkan
                            <span class="font-medium text-gray-700">{{ $pendidikanList->firstItem() }}</span>
                            –
                            <span class="font-medium text-gray-700">{{ $pendidikanList->lastItem() }}</span>
                            dari
                            <span class="font-medium text-gray-700">{{ $pendidikanList->total() }}</span>
                            data
                        </p>
                        <div class="flex items-center gap-1.5 flex-wrap justify-center sm:justify-end">
                            <button wire:click="previousPage"
                                    @if($pendidikanList->onFirstPage()) disabled @endif
                                    class="inline-flex items-center justify-center w-9 h-9 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-white transition-colors"
                                    aria-label="Sebelumnya">
                                <i class="ph ph-caret-left text-base"></i>
                            </button>

                            @foreach($pendidikanList->getUrlRange(max(1, $pendidikanList->currentPage() - 2), min($pendidikanList->lastPage(), $pendidikanList->currentPage() + 2)) as $page => $url)
                                <button wire:click="gotoPage({{ $page }})"
                                        class="inline-flex items-center justify-center min-w-[2.25rem] h-9 px-2.5 text-sm font-medium rounded-lg transition-colors
                                        {{ $page == $pendidikanList->currentPage()
                                            ? 'bg-primary text-white border border-primary shadow-sm'
                                            : 'text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 hover:border-gray-300' }}">
                                    {{ $page }}
                                </button>
                            @endforeach

                            <button wire:click="nextPage"
                                    @if(!$pendidikanList->hasMorePages()) disabled @endif
                                    class="inline-flex items-center justify-center w-9 h-9 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-white transition-colors"
                                    aria-label="Berikutnya">
                                <i class="ph ph-caret-right text-base"></i>
                            </button>
                        </div>
                    </div>
                @endif
            @else
                <div class="text-center py-12 text-gray-500">
                    <i class="ph ph-graduation-cap text-4xl mb-2"></i>
                    @if(!empty($search))
                        <p>Tidak ada data pendidikan yang cocok dengan pencarian</p>
                    @else
                        <p>Belum ada data pendidikan</p>
                        <button wire:click="openPendidikanModal" 
                                class="mt-4 px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            Tambah Pendidikan Pertama
                        </button>
                    @endif
                </div>
            @endif
        </div>

        {{-- Pesan Sukses --}}
        @include('livewire.super-admin.posyandu-detail.message-alert')

        {{-- Modal Form Pendidikan --}}
        @include('livewire.super-admin.posyandu-detail.modals.pendidikan-modal')
    </div>
</div>

{{-- Scripts --}}
@include('livewire.super-admin.posyandu-detail.scripts')
