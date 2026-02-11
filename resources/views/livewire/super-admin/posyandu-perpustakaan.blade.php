<div>
    <div class="space-y-6">
        {{-- Header --}}
        @include('livewire.super-admin.posyandu-detail.header')

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                    <i class="ph ph-books text-3xl mr-3 text-primary"></i>
                    Perpustakaan {{ $posyandu->nama_posyandu ?? '' }}
                </h1>
                <button wire:click="openAddModal"
                    class="flex items-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-primaryDark transition-colors">
                    <i class="ph ph-plus-circle text-lg mr-2"></i>
                    Tambah Buku
                </button>
            </div>

            @if(session('message'))
                <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-800 text-sm">{{ session('message') }}</div>
            @endif

            @if($items->isEmpty())
                <div class="text-center py-16 text-gray-500">
                    <i class="ph ph-books text-6xl mb-4 block"></i>
                    <p class="text-lg">Belum ada buku di perpustakaan.</p>
                    <button wire:click="openAddModal" class="mt-4 text-primary font-medium hover:underline">Tambah buku pertama</button>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
                    @foreach($items as $item)
                        <div class="group relative bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100">
                            {{-- Book Cover --}}
                            <div class="relative aspect-[3/4] cursor-pointer" wire:click="openFlipbook({{ $item->id }})">
                                @if($item->cover_image)
                                    <img src="{{ uploads_asset($item->cover_image) }}" alt="{{ $item->judul }}"
                                        class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-primary/20 to-primary/40 flex items-center justify-center">
                                        <i class="ph ph-book-open text-4xl text-primary/60"></i>
                                    </div>
                                @endif
                                
                                {{-- Overlay on hover --}}
                                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <button class="px-4 py-2 bg-white rounded-full text-primary font-medium text-sm flex items-center gap-2 hover:bg-gray-100 transition">
                                        <i class="ph ph-book-open-text text-lg"></i>
                                        Baca Buku
                                    </button>
                                </div>

                                {{-- Badge kategori --}}
                                @if($item->kategori)
                                    <span class="absolute top-2 left-2 px-2 py-0.5 text-xs font-medium rounded-full bg-primary/90 text-white">
                                        {{ $kategoriOptions[$item->kategori] ?? ucfirst($item->kategori) }}
                                    </span>
                                @endif

                                {{-- Badge halaman/PDF --}}
                                <span class="absolute bottom-2 right-2 px-2 py-0.5 text-xs font-medium rounded-full bg-black/60 text-white flex items-center gap-1">
                                    @if($item->file_path)
                                        <i class="ph ph-file-pdf"></i> PDF
                                    @else
                                        {{ $item->jumlah_halaman }} hal
                                    @endif
                                </span>

                                {{-- Status badge --}}
                                @if(!$item->is_active)
                                    <div class="absolute inset-0 bg-gray-900/50 flex items-center justify-center">
                                        <span class="px-3 py-1 bg-gray-800 text-white text-xs rounded-full">Nonaktif</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Book Info --}}
                            <div class="p-3">
                                <h3 class="font-semibold text-gray-800 text-sm line-clamp-2 mb-1">{{ $item->judul }}</h3>
                                @if($item->penulis)
                                    <p class="text-xs text-gray-500 line-clamp-1">{{ $item->penulis }}</p>
                                @endif
                            </div>

                            {{-- Action buttons --}}
                            <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button wire:click="openEditModal({{ $item->id }})"
                                    class="p-1.5 rounded-full bg-white/90 text-blue-600 hover:bg-blue-100 shadow-sm">
                                    <i class="ph ph-pencil text-sm"></i>
                                </button>
                                <button wire:click="toggleActive({{ $item->id }})"
                                    class="p-1.5 rounded-full bg-white/90 {{ $item->is_active ? 'text-amber-600 hover:bg-amber-100' : 'text-green-600 hover:bg-green-100' }} shadow-sm">
                                    <i class="ph ph-{{ $item->is_active ? 'eye-slash' : 'eye' }} text-sm"></i>
                                </button>
                                <button wire:click="deleteBook({{ $item->id }})" wire:confirm="Hapus buku '{{ $item->judul }}'?"
                                    class="p-1.5 rounded-full bg-white/90 text-red-600 hover:bg-red-100 shadow-sm">
                                    <i class="ph ph-trash text-sm"></i>
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

        {{-- Modal Tambah Buku --}}
        @if($showAddModal)
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
                <div class="flex items-center justify-center min-h-screen px-4 py-8">
                    <div class="fixed inset-0 bg-gray-500/75" wire:click="closeAddModal"></div>
                    <div class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="ph ph-book-open text-2xl mr-2 text-primary"></i>
                            Tambah Buku Baru
                        </h3>
                        <form wire:submit.prevent="saveBook">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Judul Buku <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="judul" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-primary focus:border-primary" placeholder="Masukkan judul buku">
                                    @error('judul') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Penulis</label>
                                    <input type="text" wire:model="penulis" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-primary focus:border-primary" placeholder="Nama penulis">
                                    @error('penulis') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                                    <select wire:model="kategori" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-primary focus:border-primary">
                                        <option value="">-- Pilih Kategori --</option>
                                        @foreach($kategoriOptions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('kategori') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                                    <textarea wire:model="deskripsi" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-primary focus:border-primary" placeholder="Deskripsi singkat tentang buku"></textarea>
                                    @error('deskripsi') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- Upload Type Selection --}}
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Upload <span class="text-red-500">*</span></label>
                                    <div class="flex gap-4">
                                        <label class="flex items-center cursor-pointer">
                                            <input type="radio" wire:model.live="uploadType" value="images" class="w-4 h-4 text-primary border-gray-300 focus:ring-primary">
                                            <span class="ml-2 text-sm text-gray-700">Gambar Halaman</span>
                                        </label>
                                        <label class="flex items-center cursor-pointer">
                                            <input type="radio" wire:model.live="uploadType" value="pdf" class="w-4 h-4 text-primary border-gray-300 focus:ring-primary">
                                            <span class="ml-2 text-sm text-gray-700">File PDF</span>
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Cover Buku (opsional)</label>
                                    <div x-data="{ dragging: false }"
                                         @dragover.prevent="dragging = true"
                                         @dragleave.prevent="dragging = false"
                                         @drop.prevent="dragging = false; $refs.coverInput.files = $event.dataTransfer.files; $refs.coverInput.dispatchEvent(new Event('change', { bubbles: true }))"
                                         class="relative border-2 border-dashed rounded-xl p-4 text-center transition-colors cursor-pointer aspect-[3/4]"
                                         :class="dragging ? 'border-primary bg-primary/10' : 'border-gray-300 hover:border-primary/50 hover:bg-gray-50'">
                                        <input type="file" x-ref="coverInput" wire:model="coverImage" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                        <div class="pointer-events-none h-full flex flex-col items-center justify-center">
                                            <i class="ph ph-image text-3xl text-gray-400 mb-2"></i>
                                            <p class="text-xs text-gray-500">Cover buku</p>
                                        </div>
                                    </div>
                                    @if($coverImage)
                                        <p class="mt-1 text-xs text-primary"><i class="ph ph-check-circle mr-1"></i> Cover dipilih</p>
                                    @endif
                                    @error('coverImage') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    @if($uploadType === 'pdf')
                                        {{-- PDF Upload --}}
                                        <label class="block text-sm font-medium text-gray-700 mb-1">File PDF <span class="text-red-500">*</span></label>
                                        <div x-data="{ dragging: false }"
                                             @dragover.prevent="dragging = true"
                                             @dragleave.prevent="dragging = false"
                                             @drop.prevent="dragging = false; $refs.pdfInput.files = $event.dataTransfer.files; $refs.pdfInput.dispatchEvent(new Event('change', { bubbles: true }))"
                                             class="relative border-2 border-dashed rounded-xl p-4 text-center transition-colors cursor-pointer aspect-[3/4]"
                                             :class="dragging ? 'border-primary bg-primary/10' : 'border-gray-300 hover:border-primary/50 hover:bg-gray-50'">
                                            <input type="file" x-ref="pdfInput" wire:model="pdfFile" accept=".pdf,application/pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                            <div class="pointer-events-none h-full flex flex-col items-center justify-center">
                                                <i class="ph ph-file-pdf text-3xl text-red-500 mb-2"></i>
                                                <p class="text-xs text-gray-500">Upload PDF</p>
                                                <p class="text-xs text-gray-400 mt-1">(maks. 20 MB)</p>
                                            </div>
                                        </div>
                                        @if($pdfFile)
                                            <p class="mt-1 text-xs text-primary"><i class="ph ph-check-circle mr-1"></i> PDF dipilih</p>
                                        @endif
                                        @error('pdfFile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    @else
                                        {{-- Image Pages Upload --}}
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Halaman Buku <span class="text-red-500">*</span></label>
                                        <div x-data="{ dragging: false }"
                                             @dragover.prevent="dragging = true"
                                             @dragleave.prevent="dragging = false"
                                             @drop.prevent="dragging = false; $refs.pagesInput.files = $event.dataTransfer.files; $refs.pagesInput.dispatchEvent(new Event('change', { bubbles: true }))"
                                             class="relative border-2 border-dashed rounded-xl p-4 text-center transition-colors cursor-pointer aspect-[3/4]"
                                             :class="dragging ? 'border-primary bg-primary/10' : 'border-gray-300 hover:border-primary/50 hover:bg-gray-50'">
                                            <input type="file" x-ref="pagesInput" wire:model="halamanFiles" accept="image/*" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                            <div class="pointer-events-none h-full flex flex-col items-center justify-center">
                                                <i class="ph ph-files text-3xl text-gray-400 mb-2"></i>
                                                <p class="text-xs text-gray-500">Upload halaman</p>
                                                <p class="text-xs text-gray-400 mt-1">(bisa banyak sekaligus)</p>
                                            </div>
                                        </div>
                                        @if($halamanFiles && count($halamanFiles) > 0)
                                            <p class="mt-1 text-xs text-primary"><i class="ph ph-check-circle mr-1"></i> {{ count($halamanFiles) }} halaman dipilih</p>
                                        @endif
                                        @error('halamanFiles') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        @error('halamanFiles.*') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    @endif
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end gap-2">
                                <button type="button" wire:click="closeAddModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Batal</button>
                                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primaryDark flex items-center">
                                    <i class="ph ph-floppy-disk text-lg mr-2"></i>
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- Modal Edit Buku --}}
        @if($showEditModal)
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="fixed inset-0 bg-gray-500/75" wire:click="closeEditModal"></div>
                    <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="ph ph-pencil text-2xl mr-2 text-primary"></i>
                            Edit Buku
                        </h3>
                        <form wire:submit.prevent="updateBook">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Judul Buku <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="judul" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-primary focus:border-primary">
                                    @error('judul') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Penulis</label>
                                    <input type="text" wire:model="penulis" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-primary focus:border-primary">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                                    <select wire:model="kategori" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-primary focus:border-primary">
                                        <option value="">-- Pilih Kategori --</option>
                                        @foreach($kategoriOptions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                                    <textarea wire:model="deskripsi" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-primary focus:border-primary"></textarea>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end gap-2">
                                <button type="button" wire:click="closeEditModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Batal</button>
                                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primaryDark">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- Modal Flipbook --}}
        @if($showFlipbookModal && $viewingBook)
            @if($viewingBook->file_path)
                {{-- PDF Viewer (Responsive: Scroll on mobile, Flipbook on desktop) --}}
                <div class="fixed inset-0 z-50 overflow-hidden bg-gray-900/95" aria-modal="true"
                     x-data="pdfViewer({
                         pdfUrl: '{{ uploads_asset($viewingBook->file_path) }}',
                         title: @js($viewingBook->judul)
                     })"
                     x-init="init()"
                     @keydown.escape.window="$wire.closeFlipbook()"
                     @keydown.left.window="!isMobile && prevPage()"
                     @keydown.right.window="!isMobile && nextPage()"
                     @resize.window="checkMobile()">
                    
                    {{-- Header --}}
                    <div class="absolute top-0 left-0 right-0 z-10 bg-gradient-to-b from-black/80 to-transparent p-4">
                        <div class="max-w-6xl mx-auto flex items-center justify-between">
                            <div class="text-white">
                                <h2 class="text-lg md:text-xl font-bold" x-text="title"></h2>
                                <p class="text-xs md:text-sm text-white/70">{{ $viewingBook->penulis ?? '' }} <span class="ml-2 px-2 py-0.5 bg-red-500/80 rounded text-xs">PDF</span></p>
                            </div>
                            <button wire:click="closeFlipbook" class="p-2 rounded-full bg-white/10 text-white hover:bg-white/20 transition">
                                <i class="ph ph-x text-xl md:text-2xl"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Loading State --}}
                    <div x-show="loading" class="h-full flex items-center justify-center">
                        <div class="text-center text-white">
                            <i class="ph ph-spinner text-5xl animate-spin mb-4"></i>
                            <p>Memuat PDF...</p>
                        </div>
                    </div>

                    {{-- Mobile: Scroll View --}}
                    <div x-show="!loading && isMobile" x-cloak class="h-full overflow-y-auto pt-20 pb-4 px-4">
                        <div x-ref="mobileContainer" class="max-w-lg mx-auto space-y-4">
                            {{-- Pages will be rendered here --}}
                        </div>
                    </div>

                    {{-- Desktop: Flipbook View --}}
                    <div x-show="!loading && !isMobile" x-cloak class="h-full flex items-center justify-center px-4 py-20">
                        <div class="relative w-full max-w-5xl h-[70vh]">
                            <div class="relative h-full flex items-center justify-center">
                                {{-- Left Page --}}
                                <div class="relative w-1/2 h-full bg-white shadow-2xl rounded-l-lg overflow-hidden flex items-center justify-center"
                                     :class="currentPage <= 1 ? 'bg-gray-50' : ''">
                                    <canvas x-ref="leftCanvas" class="max-w-full max-h-full" :class="currentPage <= 1 ? 'hidden' : ''"></canvas>
                                    <div class="absolute bottom-4 left-4 px-3 py-1 bg-black/50 text-white text-sm rounded-full" x-show="currentPage > 1">
                                        <span x-text="currentPage - 1"></span>
                                    </div>
                                </div>

                                {{-- Center spine --}}
                                <div class="w-2 h-full bg-gradient-to-r from-gray-400 via-gray-300 to-gray-400 shadow-inner"></div>

                                {{-- Right Page --}}
                                <div class="relative w-1/2 h-full bg-white shadow-2xl rounded-r-lg overflow-hidden flex items-center justify-center">
                                    <canvas x-ref="rightCanvas" class="max-w-full max-h-full"></canvas>
                                    <div class="absolute bottom-4 right-4 px-3 py-1 bg-black/50 text-white text-sm rounded-full" x-show="currentPage <= totalPages">
                                        <span x-text="currentPage"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- Navigation Arrows --}}
                            <button @click="prevPage()" 
                                    class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-16 p-4 rounded-full bg-white/10 text-white hover:bg-white/20 transition disabled:opacity-30"
                                    :disabled="currentPage <= 1">
                                <i class="ph ph-caret-left text-3xl"></i>
                            </button>
                            <button @click="nextPage()" 
                                    class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-16 p-4 rounded-full bg-white/10 text-white hover:bg-white/20 transition disabled:opacity-30"
                                    :disabled="currentPage >= totalPages">
                                <i class="ph ph-caret-right text-3xl"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Footer Controls (Desktop only) --}}
                    <div x-show="!loading && !isMobile" class="absolute bottom-0 left-0 right-0 z-10 bg-gradient-to-t from-black/80 to-transparent p-4">
                        <div class="max-w-6xl mx-auto">
                            <div class="flex items-center justify-center gap-4 text-white">
                                <span class="text-sm">Halaman</span>
                                <div class="flex items-center gap-2">
                                    <button @click="goToPage(1)" class="px-2 py-1 text-sm hover:bg-white/20 rounded" :disabled="currentPage <= 1">
                                        <i class="ph ph-skip-back"></i>
                                    </button>
                                    <button @click="prevPage()" class="px-2 py-1 text-sm hover:bg-white/20 rounded" :disabled="currentPage <= 1">
                                        <i class="ph ph-caret-left"></i>
                                    </button>
                                    <span class="px-4 py-1 bg-white/20 rounded-full text-sm font-medium">
                                        <span x-text="currentPage"></span> / <span x-text="totalPages"></span>
                                    </span>
                                    <button @click="nextPage()" class="px-2 py-1 text-sm hover:bg-white/20 rounded" :disabled="currentPage >= totalPages">
                                        <i class="ph ph-caret-right"></i>
                                    </button>
                                    <button @click="goToPage(totalPages)" class="px-2 py-1 text-sm hover:bg-white/20 rounded" :disabled="currentPage >= totalPages">
                                        <i class="ph ph-skip-forward"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PDF.js Library --}}
                <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
                <script>
                    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                    window._pdfDocuments = window._pdfDocuments || {};
                    
                    function pdfViewer(config) {
                        const instanceId = 'pdf_' + Date.now();
                        
                        return {
                            pdfUrl: config.pdfUrl,
                            title: config.title,
                            instanceId: instanceId,
                            currentPage: 1,
                            totalPages: 0,
                            loading: true,
                            isMobile: window.innerWidth < 768,
                            mobileRendered: false,
                            
                            async init() {
                                this.checkMobile();
                                await this.loadPdf();
                            },
                            
                            checkMobile() {
                                const wasMobile = this.isMobile;
                                this.isMobile = window.innerWidth < 768;
                                if (wasMobile !== this.isMobile && !this.loading) {
                                    this.$nextTick(() => {
                                        if (this.isMobile && !this.mobileRendered) {
                                            this.renderMobilePages();
                                        } else if (!this.isMobile) {
                                            this.renderDesktopPages();
                                        }
                                    });
                                }
                            },
                            
                            async loadPdf() {
                                try {
                                    const loadingTask = pdfjsLib.getDocument(this.pdfUrl);
                                    const doc = await loadingTask.promise;
                                    window._pdfDocuments[this.instanceId] = doc;
                                    this.totalPages = doc.numPages;
                                    this.loading = false;
                                    await this.$nextTick();
                                    setTimeout(() => {
                                        if (this.isMobile) {
                                            this.renderMobilePages();
                                        } else {
                                            this.renderDesktopPages();
                                        }
                                    }, 100);
                                } catch (error) {
                                    console.error('Error loading PDF:', error);
                                    this.loading = false;
                                }
                            },
                            
                            async renderMobilePages() {
                                const doc = window._pdfDocuments[this.instanceId];
                                const container = this.$refs.mobileContainer;
                                if (!doc || !container) return;
                                
                                container.innerHTML = '';
                                this.mobileRendered = true;
                                
                                for (let i = 1; i <= this.totalPages; i++) {
                                    const page = await doc.getPage(i);
                                    const viewport = page.getViewport({ scale: 1 });
                                    const containerWidth = container.clientWidth || 350;
                                    // Increase scale for better quality (2x for retina)
                                    const scale = ((containerWidth * 0.95) / viewport.width) * 2;
                                    const scaledViewport = page.getViewport({ scale });
                                    
                                    const wrapper = document.createElement('div');
                                    wrapper.className = 'relative bg-white rounded-lg shadow-lg overflow-hidden';
                                    
                                    const canvas = document.createElement('canvas');
                                    canvas.width = scaledViewport.width;
                                    canvas.height = scaledViewport.height;
                                    // Scale down display size while keeping high resolution
                                    canvas.style.width = (scaledViewport.width / 2) + 'px';
                                    canvas.style.height = (scaledViewport.height / 2) + 'px';
                                    canvas.className = 'w-full h-auto';
                                    
                                    const pageNum = document.createElement('div');
                                    pageNum.className = 'absolute bottom-2 right-2 px-2 py-1 bg-black/50 text-white text-xs rounded-full';
                                    pageNum.textContent = i + ' / ' + this.totalPages;
                                    
                                    wrapper.appendChild(canvas);
                                    wrapper.appendChild(pageNum);
                                    container.appendChild(wrapper);
                                    
                                    const context = canvas.getContext('2d');
                                    await page.render({ canvasContext: context, viewport: scaledViewport }).promise;
                                }
                            },
                            
                            async renderDesktopPages() {
                                const leftCanvas = this.$refs.leftCanvas;
                                const rightCanvas = this.$refs.rightCanvas;
                                const doc = window._pdfDocuments[this.instanceId];
                                
                                if (!leftCanvas || !rightCanvas || !doc) return;
                                
                                const leftCtx = leftCanvas.getContext('2d');
                                const rightCtx = rightCanvas.getContext('2d');
                                leftCtx.clearRect(0, 0, leftCanvas.width, leftCanvas.height);
                                rightCtx.clearRect(0, 0, rightCanvas.width, rightCanvas.height);
                                
                                if (this.currentPage <= this.totalPages) {
                                    await this.renderPage(this.currentPage, rightCanvas);
                                }
                                if (this.currentPage > 1) {
                                    await this.renderPage(this.currentPage - 1, leftCanvas);
                                }
                            },
                            
                            async renderPage(pageNum, canvas) {
                                try {
                                    const doc = window._pdfDocuments[this.instanceId];
                                    if (!doc) return;
                                    
                                    const page = await doc.getPage(pageNum);
                                    const container = canvas.parentElement;
                                    const containerHeight = container.clientHeight || 500;
                                    const containerWidth = container.clientWidth || 400;
                                    
                                    const viewport = page.getViewport({ scale: 1 });
                                    const baseScale = Math.min(
                                        (containerWidth * 0.9) / viewport.width, 
                                        (containerHeight * 0.9) / viewport.height
                                    );
                                    // Higher resolution for sharper text
                                    const scale = baseScale * 2;
                                    const scaledViewport = page.getViewport({ scale });
                                    
                                    canvas.width = scaledViewport.width;
                                    canvas.height = scaledViewport.height;
                                    // Display at original size for crisp rendering
                                    canvas.style.width = (scaledViewport.width / 2) + 'px';
                                    canvas.style.height = (scaledViewport.height / 2) + 'px';
                                    
                                    const context = canvas.getContext('2d');
                                    await page.render({ canvasContext: context, viewport: scaledViewport }).promise;
                                } catch (error) {
                                    console.error('Error rendering page', pageNum, error);
                                }
                            },
                            
                            async nextPage() {
                                if (this.currentPage < this.totalPages) {
                                    this.currentPage++;
                                    await this.renderDesktopPages();
                                }
                            },
                            
                            async prevPage() {
                                if (this.currentPage > 1) {
                                    this.currentPage--;
                                    await this.renderDesktopPages();
                                }
                            },
                            
                            async goToPage(pageNum) {
                                this.currentPage = Math.max(1, Math.min(pageNum, this.totalPages));
                                await this.renderDesktopPages();
                            },
                            
                            destroy() {
                                delete window._pdfDocuments[this.instanceId];
                            }
                        }
                    }
                </script>
            @else
                {{-- Image-based Viewer (Responsive: Scroll on mobile, Flipbook on desktop) --}}
                <div class="fixed inset-0 z-50 overflow-hidden bg-gray-900/95" aria-modal="true"
                     x-data="imageViewer({
                         pages: @js($viewingBook->halaman_images ? array_map(function($p) { return uploads_asset($p); }, $viewingBook->halaman_images) : []),
                         title: @js($viewingBook->judul)
                     })"
                     x-init="checkMobile()"
                     @keydown.escape.window="$wire.closeFlipbook()"
                     @keydown.left.window="!isMobile && prevPage()"
                     @keydown.right.window="!isMobile && nextPage()"
                     @resize.window="checkMobile()">
                    
                    {{-- Header --}}
                    <div class="absolute top-0 left-0 right-0 z-10 bg-gradient-to-b from-black/80 to-transparent p-4">
                        <div class="max-w-6xl mx-auto flex items-center justify-between">
                            <div class="text-white">
                                <h2 class="text-lg md:text-xl font-bold" x-text="title"></h2>
                                <p class="text-xs md:text-sm text-white/70">{{ $viewingBook->penulis ?? '' }}</p>
                            </div>
                            <button wire:click="closeFlipbook" class="p-2 rounded-full bg-white/10 text-white hover:bg-white/20 transition">
                                <i class="ph ph-x text-xl md:text-2xl"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Mobile: Scroll View --}}
                    <div x-show="isMobile" x-cloak class="h-full overflow-y-auto pt-20 pb-4 px-4">
                        <div class="max-w-lg mx-auto space-y-4">
                            <template x-for="(page, index) in pages" :key="index">
                                <div class="relative bg-white rounded-lg shadow-lg overflow-hidden">
                                    <img :src="page" class="w-full h-auto" alt="">
                                    <div class="absolute bottom-2 right-2 px-2 py-1 bg-black/50 text-white text-xs rounded-full">
                                        <span x-text="(index + 1) + ' / ' + pages.length"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Desktop: Flipbook View --}}
                    <div x-show="!isMobile" x-cloak class="h-full flex items-center justify-center px-4 py-20">
                        <div class="relative w-full max-w-5xl h-[70vh]">
                            <div class="relative h-full flex items-center justify-center">
                                {{-- Left Page --}}
                                <div class="relative w-1/2 h-full bg-white shadow-2xl rounded-l-lg overflow-hidden"
                                     :class="currentPage === 0 ? 'opacity-50' : ''">
                                    <template x-if="currentPage > 0">
                                        <img :src="pages[currentPage - 1]" class="w-full h-full object-contain bg-gray-100" alt="">
                                    </template>
                                    <template x-if="currentPage === 0">
                                        <div class="w-full h-full bg-gradient-to-r from-gray-200 to-gray-100 flex items-center justify-center">
                                            <div class="text-center text-gray-400">
                                                <i class="ph ph-book-open text-6xl mb-4"></i>
                                                <p class="text-sm">Halaman Depan</p>
                                            </div>
                                        </div>
                                    </template>
                                    <div class="absolute bottom-4 left-4 px-3 py-1 bg-black/50 text-white text-sm rounded-full" x-show="currentPage > 0">
                                        <span x-text="currentPage"></span>
                                    </div>
                                </div>

                                {{-- Center spine --}}
                                <div class="w-2 h-full bg-gradient-to-r from-gray-400 via-gray-300 to-gray-400 shadow-inner"></div>

                                {{-- Right Page --}}
                                <div class="relative w-1/2 h-full bg-white shadow-2xl rounded-r-lg overflow-hidden"
                                     :class="currentPage >= pages.length ? 'opacity-50' : ''">
                                    <template x-if="currentPage < pages.length">
                                        <img :src="pages[currentPage]" class="w-full h-full object-contain bg-gray-100" alt="">
                                    </template>
                                    <template x-if="currentPage >= pages.length">
                                        <div class="w-full h-full bg-gradient-to-l from-gray-200 to-gray-100 flex items-center justify-center">
                                            <div class="text-center text-gray-400">
                                                <i class="ph ph-check-circle text-6xl mb-4"></i>
                                                <p class="text-sm">Selesai</p>
                                            </div>
                                        </div>
                                    </template>
                                    <div class="absolute bottom-4 right-4 px-3 py-1 bg-black/50 text-white text-sm rounded-full" x-show="currentPage < pages.length">
                                        <span x-text="currentPage + 1"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- Navigation Arrows --}}
                            <button @click="prevPage()" 
                                    class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-16 p-4 rounded-full bg-white/10 text-white hover:bg-white/20 transition disabled:opacity-30"
                                    :disabled="currentPage === 0">
                                <i class="ph ph-caret-left text-3xl"></i>
                            </button>
                            <button @click="nextPage()" 
                                    class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-16 p-4 rounded-full bg-white/10 text-white hover:bg-white/20 transition disabled:opacity-30"
                                    :disabled="currentPage >= pages.length">
                                <i class="ph ph-caret-right text-3xl"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Footer Controls (Desktop only) --}}
                    <div x-show="!isMobile" class="absolute bottom-0 left-0 right-0 z-10 bg-gradient-to-t from-black/80 to-transparent p-4">
                        <div class="max-w-6xl mx-auto">
                            <div class="flex items-center justify-center gap-4 text-white">
                                <span class="text-sm">Halaman</span>
                                <div class="flex items-center gap-2">
                                    <button @click="currentPage = 0" class="px-2 py-1 text-sm hover:bg-white/20 rounded">
                                        <i class="ph ph-skip-back"></i>
                                    </button>
                                    <span class="px-4 py-1 bg-white/20 rounded-full text-sm font-medium">
                                        <span x-text="currentPage + 1"></span> / <span x-text="pages.length"></span>
                                    </span>
                                    <button @click="currentPage = pages.length - 1" class="px-2 py-1 text-sm hover:bg-white/20 rounded">
                                        <i class="ph ph-skip-forward"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Page thumbnails --}}
                            <div class="mt-4 flex gap-2 justify-center overflow-x-auto pb-2 max-w-full">
                                <template x-for="(page, index) in pages" :key="index">
                                    <button @click="goToPage(index)" 
                                            class="flex-shrink-0 w-12 h-16 rounded overflow-hidden border-2 transition-all hover:scale-110"
                                            :class="index === currentPage || index === currentPage - 1 ? 'border-primary ring-2 ring-primary/50' : 'border-white/30 hover:border-white/60'">
                                        <img :src="page" class="w-full h-full object-cover" alt="">
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    function imageViewer(config) {
                        return {
                            pages: config.pages || [],
                            title: config.title || '',
                            currentPage: 0,
                            isMobile: window.innerWidth < 768,
                            
                            checkMobile() {
                                this.isMobile = window.innerWidth < 768;
                            },
                            
                            nextPage() {
                                if (this.currentPage < this.pages.length) {
                                    this.currentPage += 2;
                                    if (this.currentPage > this.pages.length) {
                                        this.currentPage = this.pages.length;
                                    }
                                }
                            },
                            
                            prevPage() {
                                if (this.currentPage > 0) {
                                    this.currentPage -= 2;
                                    if (this.currentPage < 0) {
                                        this.currentPage = 0;
                                    }
                                }
                            },
                            
                            goToPage(index) {
                                this.currentPage = index % 2 === 0 ? index : index - 1;
                            }
                        }
                    }
                </script>
            @endif
        @endif
    </div>
</div>
