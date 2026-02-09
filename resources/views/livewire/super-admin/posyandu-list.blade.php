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

        {{-- Notification Modal --}}
        @include('components.notification-modal')

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
                                            @if($posyandu->logo_posyandu)
                                                <button
                                                    wire:click="deleteLogo({{ $posyandu->id_posyandu }})"
                                                    wire:confirm="Apakah Anda yakin ingin menghapus logo untuk posyandu '{{ $posyandu->nama_posyandu }}'?"
                                                    class="text-orange-600 hover:text-orange-700 p-2 hover:bg-orange-50 rounded transition-colors"
                                                    title="Hapus Logo">
                                                    <i class="ph ph-image-broken text-lg"></i>
                                                </button>
                                            @endif
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

                    {{-- Link Maps (Embed Google Maps) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Link Embed Google Maps
                        </label>
                        <textarea
                            wire:model="link_maps"
                            rows="2"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors @error('link_maps') border-red-500 @enderror"
                            placeholder="Paste URL embed (https://www.google.com/maps/embed?pb=...) atau kode iframe lengkap dari Google Maps (Share → Embed a map)"></textarea>
                        <p class="mt-1 text-xs text-gray-500">Paste URL embed atau full iframe dari Google Maps (Share → Embed a map). Hanya URL embed google.com/maps/embed yang diterima.</p>
                        @error('link_maps')
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
                                            <a href="{{ uploads_asset($currentSkPath) }}" target="_blank" class="text-primary hover:underline font-medium text-sm">
                                                Lihat File SK Saat Ini
                                            </a>
                                            <p class="text-xs text-gray-500">Kosongkan jika tidak ingin mengubah</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div
                            x-data="{
                                isDragging: false,
                                handleDrop(e) {
                                    e.preventDefault();
                                    this.isDragging = false;
                                    const files = e.dataTransfer.files;
                                    if (files.length) {
                                        const input = this.$refs.skInput;
                                        const dt = new DataTransfer();
                                        dt.items.add(files[0]);
                                        input.files = dt.files;
                                        input.dispatchEvent(new Event('change', { bubbles: true }));
                                    }
                                }
                            }"
                            @dragover.prevent="isDragging = true"
                            @dragleave="isDragging = false"
                            @drop.prevent="handleDrop($event)"
                            @click="$refs.skInput.click()"
                            :class="isDragging ? 'border-primary bg-primary/5 ring-2 ring-primary/30' : 'border-gray-300 hover:border-primary/50'"
                            class="relative border-2 border-dashed rounded-xl p-8 text-center cursor-pointer transition-all duration-200 bg-gray-50 hover:bg-gray-100 @error('skFile') border-red-500 @enderror">
                            <input type="file" wire:model="skFile" x-ref="skInput" accept=".pdf,.doc,.docx" class="hidden">
                            <i class="ph ph-file-cloud text-4xl text-gray-400 mb-3 block"></i>
                            <p class="text-gray-600 font-medium mb-1">
                                <span x-text="isDragging ? 'Lepaskan file di sini' : 'Seret file ke sini atau klik untuk memilih'"></span>
                            </p>
                            <p class="text-xs text-gray-500">Format: PDF, DOC, DOCX (Maks. 5MB)</p>
                            @if($skFile)
                                <p class="mt-3 text-sm text-primary font-medium">
                                    <i class="ph ph-check-circle mr-1"></i>{{ $skFile->getClientOriginalName() }}
                                </p>
                            @endif
                        </div>
                        @error('skFile')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
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
                                        <img src="{{ uploads_asset($currentLogoPath) }}" alt="Logo" class="w-16 h-16 object-cover rounded">
                                        <div>
                                            <a href="{{ uploads_asset($currentLogoPath) }}" target="_blank" class="text-primary hover:underline font-medium text-sm">
                                                Lihat Logo Saat Ini
                                            </a>
                                            <p class="text-xs text-gray-500">Kosongkan jika tidak ingin mengubah</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div
                            x-data="{
                                isDragging: false,
                                handleDrop(e) {
                                    e.preventDefault();
                                    this.isDragging = false;
                                    const files = e.dataTransfer.files;
                                    if (files.length) {
                                        const input = this.$refs.logoInput;
                                        const dt = new DataTransfer();
                                        dt.items.add(files[0]);
                                        input.files = dt.files;
                                        input.dispatchEvent(new Event('change', { bubbles: true }));
                                    }
                                }
                            }"
                            @dragover.prevent="isDragging = true"
                            @dragleave="isDragging = false"
                            @drop.prevent="handleDrop($event)"
                            @click="$refs.logoInput.click()"
                            :class="isDragging ? 'border-primary bg-primary/5 ring-2 ring-primary/30' : 'border-gray-300 hover:border-primary/50'"
                            class="relative border-2 border-dashed rounded-xl p-8 text-center cursor-pointer transition-all duration-200 bg-gray-50 hover:bg-gray-100 @error('logoFile') border-red-500 @enderror">
                            <input type="file" wire:model="logoFile" x-ref="logoInput" accept="image/jpeg,image/png,image/jpg" class="hidden">
                            <i class="ph ph-image-square text-4xl text-gray-400 mb-3 block"></i>
                            <p class="text-gray-600 font-medium mb-1">
                                <span x-text="isDragging ? 'Lepaskan gambar di sini' : 'Seret gambar ke sini atau klik untuk memilih'"></span>
                            </p>
                            <p class="text-xs text-gray-500">Format: JPEG, PNG, JPG (Maks. 2MB)</p>
                            @if($logoFile)
                                <p class="mt-3 text-sm text-primary font-medium">
                                    <i class="ph ph-check-circle mr-1"></i>{{ $logoFile->getClientOriginalName() }}
                                </p>
                            @endif
                        </div>
                        @error('logoFile')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Gambar Posyandu (tampil di halaman detail di atas peta) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Gambar Posyandu
                        </label>
                        <p class="text-xs text-gray-500 mb-2">Gambar ini ditampilkan di halaman detail posyandu (publik) di atas peta lokasi.</p>
                        @if($isEditMode && $currentGambarPath)
                            <div class="mb-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <img src="{{ uploads_asset($currentGambarPath) }}" alt="Gambar" class="w-24 h-16 object-cover rounded">
                                        <div>
                                            <a href="{{ uploads_asset($currentGambarPath) }}" target="_blank" class="text-primary hover:underline font-medium text-sm">
                                                Lihat Gambar Saat Ini
                                            </a>
                                            <p class="text-xs text-gray-500">Kosongkan jika tidak ingin mengubah</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div
                            x-data="{
                                isDragging: false,
                                handleDrop(e) {
                                    e.preventDefault();
                                    this.isDragging = false;
                                    const files = e.dataTransfer.files;
                                    if (files.length) {
                                        const input = this.$refs.gambarInput;
                                        const dt = new DataTransfer();
                                        dt.items.add(files[0]);
                                        input.files = dt.files;
                                        input.dispatchEvent(new Event('change', { bubbles: true }));
                                    }
                                }
                            }"
                            @dragover.prevent="isDragging = true"
                            @dragleave="isDragging = false"
                            @drop.prevent="handleDrop($event)"
                            @click="$refs.gambarInput.click()"
                            :class="isDragging ? 'border-primary bg-primary/5 ring-2 ring-primary/30' : 'border-gray-300 hover:border-primary/50'"
                            class="relative border-2 border-dashed rounded-xl p-8 text-center cursor-pointer transition-all duration-200 bg-gray-50 hover:bg-gray-100 @error('gambarFile') border-red-500 @enderror">
                            <input type="file" wire:model="gambarFile" x-ref="gambarInput" accept="image/jpeg,image/png,image/jpg" class="hidden">
                            <i class="ph ph-image text-4xl text-gray-400 mb-3 block"></i>
                            <p class="text-gray-600 font-medium mb-1">
                                <span x-text="isDragging ? 'Lepaskan gambar di sini' : 'Seret gambar ke sini atau klik untuk memilih'"></span>
                            </p>
                            <p class="text-xs text-gray-500">Format: JPEG, PNG, JPG (Maks. 2MB). Ditampilkan di atas peta di halaman detail.</p>
                            @if($gambarFile)
                                <p class="mt-3 text-sm text-primary font-medium">
                                    <i class="ph ph-check-circle mr-1"></i>{{ $gambarFile->getClientOriginalName() }}
                                </p>
                            @endif
                        </div>
                        @error('gambarFile')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
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

