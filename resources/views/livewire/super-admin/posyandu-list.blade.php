<div>
    <div class="p-6">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Daftar Posyandu</h1>
                <p class="text-gray-500">Kelola data posyandu di sistem</p>
            </div>
            <button
                wire:click="openModal"
                class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors flex items-center space-x-2 shadow-md">
                <i class="ph ph-plus text-xl"></i>
                <span>Tambah Posyandu</span>
            </button>
        </div>

        {{-- Search --}}
        <div class="mb-6">
            <div class="relative">
                <i class="ph ph-magnifying-glass absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl"></i>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari posyandu..."
                    class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
            </div>
        </div>

        {{-- Pesan Sukses/Error --}}
        @if(session()->has('message'))
            <div class="mb-6 p-4 rounded-lg {{ session('messageType') === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200' }}">
                <div class="flex items-center space-x-2">
                    <i class="ph {{ session('messageType') === 'success' ? 'ph-check-circle' : 'ph-x-circle' }} text-xl"></i>
                    <span>{{ session('message') }}</span>
                </div>
            </div>
        @endif

        {{-- Tabel List Posyandu --}}
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            @if(count($posyanduList) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Posyandu</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alamat</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Domisili</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Sasaran</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($posyanduList as $index => $posyandu)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $posyandu->nama_posyandu }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-700 max-w-xs truncate">{{ $posyandu->alamat_posyandu ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-700">{{ $posyandu->domisili_posyandu ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $totalSasaran = $posyandu->sasaran_bayibalita->count() +
                                                            $posyandu->sasaran_remaja->count() +
                                                            $posyandu->sasaran_dewasa->count() +
                                                            $posyandu->sasaran_ibuhamil->count() +
                                                            $posyandu->sasaran_pralansia->count() +
                                                            $posyandu->sasaran_lansia->count();
                                        @endphp
                                        <div class="text-sm text-gray-700">{{ number_format($totalSasaran, 0, ',', '.') }} orang</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <a
                                                href="{{ route('posyandu.info', encrypt($posyandu->id_posyandu)) }}"
                                                class="text-primary hover:text-primary-dark p-2 hover:bg-primary/10 rounded transition-colors"
                                                title="Lihat Detail">
                                                <i class="ph ph-eye text-lg"></i>
                                            </a>
                                            <button
                                                wire:click="edit({{ $posyandu->id_posyandu }})"
                                                class="text-yellow-600 hover:text-yellow-700 p-2 hover:bg-yellow-50 rounded transition-colors"
                                                title="Edit">
                                                <i class="ph ph-pencil text-lg"></i>
                                            </button>
                                            <button
                                                wire:click="delete({{ $posyandu->id_posyandu }})"
                                                wire:confirm="Apakah Anda yakin ingin menghapus posyandu '{{ $posyandu->nama_posyandu }}'? Tindakan ini tidak dapat dibatalkan."
                                                class="text-red-600 hover:text-red-700 p-2 hover:bg-red-50 rounded transition-colors"
                                                title="Hapus">
                                                <i class="ph ph-trash text-lg"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-12 text-center">
                    <i class="ph ph-buildings text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-lg">Tidak ada posyandu ditemukan</p>
                    @if($search)
                        <p class="text-gray-400 text-sm mt-2">Coba gunakan kata kunci lain</p>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Tambah Posyandu --}}
    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" wire:click="closeModal">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" wire:click.stop>
                <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                        <i class="ph {{ $isEditMode ? 'ph-pencil-circle' : 'ph-plus-circle' }} text-2xl mr-3 text-primary"></i>
                        {{ $isEditMode ? 'Edit Posyandu' : 'Tambah Posyandu Baru' }}
                    </h2>
                    <button
                        wire:click="closeModal"
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="ph ph-x text-2xl"></i>
                    </button>
                </div>

                <form wire:submit.prevent="store" class="p-6 space-y-6">
                    {{-- Nama Posyandu --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Posyandu <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            wire:model="nama_posyandu"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors @error('nama_posyandu') border-red-500 @enderror"
                            placeholder="Masukkan nama posyandu">
                        @error('nama_posyandu')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Alamat --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Alamat
                        </label>
                        <textarea
                            wire:model="alamat_posyandu"
                            rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors @error('alamat_posyandu') border-red-500 @enderror"
                            placeholder="Masukkan alamat posyandu"></textarea>
                        @error('alamat_posyandu')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Domisili --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Domisili
                        </label>
                        <input
                            type="text"
                            wire:model="domisili_posyandu"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors @error('domisili_posyandu') border-red-500 @enderror"
                            placeholder="Masukkan domisili posyandu">
                        @error('domisili_posyandu')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- File SK --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            File SK Posyandu
                        </label>
                        @if($isEditMode && $currentSkPath)
                            <div class="mb-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <i class="ph ph-file text-xl text-primary"></i>
                                        <div>
                                            <a href="{{ asset($currentSkPath) }}" target="_blank" class="text-primary hover:underline font-medium text-sm">
                                                Lihat File SK Saat Ini
                                            </a>
                                            <p class="text-xs text-gray-500">Kosongkan jika tidak ingin mengubah</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <input
                            type="file"
                            wire:model="skFile"
                            accept=".pdf,.doc,.docx"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors @error('skFile') border-red-500 @enderror">
                        @error('skFile')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @if($skFile)
                            <p class="mt-2 text-sm text-gray-600">
                                <i class="ph ph-file text-lg mr-1"></i>
                                {{ $skFile->getClientOriginalName() }}
                            </p>
                        @endif
                        <p class="mt-1 text-xs text-gray-500">Format: PDF, DOC, DOCX (Maks. 5MB)</p>
                    </div>

                    {{-- Logo --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Logo Posyandu
                        </label>
                        @if($isEditMode && $currentLogoPath)
                            <div class="mb-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <img src="{{ asset($currentLogoPath) }}" alt="Logo" class="w-16 h-16 object-cover rounded">
                                        <div>
                                            <a href="{{ asset($currentLogoPath) }}" target="_blank" class="text-primary hover:underline font-medium text-sm">
                                                Lihat Logo Saat Ini
                                            </a>
                                            <p class="text-xs text-gray-500">Kosongkan jika tidak ingin mengubah</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <input
                            type="file"
                            wire:model="logoFile"
                            accept="image/*"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors @error('logoFile') border-red-500 @enderror">
                        @error('logoFile')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @if($logoFile)
                            <p class="mt-2 text-sm text-gray-600">
                                <i class="ph ph-image text-lg mr-1"></i>
                                {{ $logoFile->getClientOriginalName() }}
                            </p>
                        @endif
                        <p class="mt-1 text-xs text-gray-500">Format: JPEG, PNG, JPG (Maks. 2MB)</p>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                        <button
                            type="button"
                            wire:click="closeModal"
                            class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors flex items-center space-x-2">
                            <i class="ph ph-check"></i>
                            <span>Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

