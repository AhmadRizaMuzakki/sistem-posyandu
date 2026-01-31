<div>
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                    <i class="ph ph-images text-3xl mr-3 text-primary"></i>
                    Galeri
                </h1>
                <p class="text-sm text-gray-500 mt-1">Foto dari seluruh galeri (global + semua posyandu)</p>
            </div>
            <button wire:click="openUploadModal"
                class="flex items-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-indigo-700 transition-colors">
                <i class="ph ph-plus-circle text-lg mr-2"></i>
                Tambah Foto
            </button>
        </div>

        @if(session('message'))
            <div class="mb-4 p-3 rounded-lg text-sm {{ session('messageType') === 'error' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">{{ session('message') }}</div>
        @endif

        @if($items->isEmpty())
            <div class="text-center py-16 text-gray-500">
                <i class="ph ph-images text-6xl mb-4 block"></i>
                <p class="text-lg">Belum ada foto di galeri.</p>
                <button wire:click="openUploadModal" class="mt-4 text-primary font-medium hover:underline">Tambah foto pertama</button>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($items as $item)
                    <div class="group relative aspect-[4/3] rounded-lg overflow-hidden bg-gray-100 border border-gray-200">
                        <img src="{{ uploads_asset($item->path) }}" alt="{{ $item->caption ?? 'Galeri' }}"
                            class="w-full h-full object-cover">
                        @if($item->posyandu)
                            <span class="absolute top-2 left-2 px-2 py-0.5 rounded bg-primary/90 text-white text-xs font-medium">{{ $item->posyandu->nama_posyandu }}</span>
                        @else
                            <span class="absolute top-2 left-2 px-2 py-0.5 rounded bg-gray-600/90 text-white text-xs font-medium">Global</span>
                        @endif
                        @if($item->caption)
                            <div class="absolute inset-x-0 bottom-0 p-2 bg-black/60 text-white text-xs line-clamp-2">{{ $item->caption }}</div>
                        @endif
                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                            <button wire:click="deleteFoto({{ $item->id }})" wire:confirm="Hapus foto ini?"
                                class="p-2 rounded-full bg-red-600 text-white hover:bg-red-700">
                                <i class="ph ph-trash text-lg"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-6">
                {{ $items->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Upload --}}
    @if($showUploadModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500/75" wire:click="closeUploadModal"></div>
                <div class="relative bg-white rounded-lg shadow-xl w-full max-w-xl min-h-[520px] flex flex-col p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Tambah Foto Galeri</h3>
                    <form wire:submit.prevent="saveFoto">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Foto <span class="text-red-500">*</span> (bisa pilih banyak)</label>
                                <div x-data="{ dragging: false }"
                                     @dragover.prevent="dragging = true"
                                     @dragleave.prevent="dragging = false"
                                     @drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change', { bubbles: true }))"
                                     class="relative border-2 border-dashed rounded-xl p-8 text-center transition-colors cursor-pointer aspect-square max-w-md mx-auto flex items-center justify-center"
                                     :class="dragging ? 'border-primary bg-primary/10' : 'border-gray-300 hover:border-primary/50 hover:bg-gray-50'">
                                    <input type="file" x-ref="fileInput" wire:model="fotoFiles" accept="image/*" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                    <div class="pointer-events-none">
                                        <i class="ph ph-cloud-arrow-up text-5xl text-gray-400 mb-3 block"></i>
                                        <p class="text-sm text-gray-600">Drag & drop foto di sini atau <span class="text-primary font-medium">klik untuk memilih</span></p>
                                        <p class="text-xs text-gray-400 mt-1">JPG, PNG, GIF, WebP • Maks. 2 MB per foto • Bisa banyak sekaligus</p>
                                    </div>
                                </div>
                                @if($fotoFiles && count($fotoFiles) > 0)
                                    <p class="mt-2 text-sm text-primary font-medium"><i class="ph ph-check-circle mr-1"></i> {{ count($fotoFiles) }} foto dipilih</p>
                                @endif
                                @error('fotoFiles') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                @error('fotoFiles.*') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Caption (opsional)</label>
                                <input type="text" wire:model="caption" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-primary focus:border-primary" placeholder="Deskripsi foto">
                                @error('caption') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-2">
                            <button type="button" wire:click="closeUploadModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Batal</button>
                            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
