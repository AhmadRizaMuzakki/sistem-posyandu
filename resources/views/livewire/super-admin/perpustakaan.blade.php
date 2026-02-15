<div>
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                    <i class="ph ph-books text-3xl mr-3 text-primary"></i>
                    Perpustakaan
                </h1>
                <p class="text-sm text-gray-500 mt-1">Buku dari seluruh posyandu. Kelola buku di halaman masing-masing posyandu.</p>
            </div>
            <button wire:click="openAddModal" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primaryDark transition">
                <i class="ph ph-plus text-lg"></i>
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
                <p class="text-sm mt-2">Klik tombol Tambah Buku untuk menambahkan buku baru.</p>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
                @foreach($items as $item)
                    <div class="group relative bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100">
                        <div class="relative aspect-[3/4] cursor-pointer" wire:click="openFlipbook({{ $item->id }})">
                            @if($item->cover_image)
                                <img src="{{ uploads_asset($item->cover_image) }}" alt="{{ $item->judul }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-primary/20 to-primary/40 flex items-center justify-center">
                                    <i class="ph ph-book-open text-4xl text-primary/60"></i>
                                </div>
                            @endif
                            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <span class="px-4 py-2 bg-white rounded-full text-primary font-medium text-sm flex items-center gap-2">
                                    <i class="ph ph-book-open-text text-lg"></i>
                                    Baca Buku
                                </span>
                            </div>
                            @if($item->kategori)
                                <span class="absolute top-2 left-2 px-2 py-0.5 text-xs font-medium rounded-full bg-primary/90 text-white">
                                    {{ $kategoriOptions[$item->kategori] ?? ucfirst($item->kategori) }}
                                </span>
                            @endif
                            @if($item->posyandu)
                                <span class="absolute bottom-2 left-2 right-2 px-2 py-1 bg-black/60 text-white text-xs rounded truncate">{{ $item->posyandu->nama_posyandu }}</span>
                            @endif
                            @if(!$item->is_active)
                                <div class="absolute inset-0 bg-gray-900/50 flex items-center justify-center">
                                    <span class="px-3 py-1 bg-gray-800 text-white text-xs rounded-full">Nonaktif</span>
                                </div>
                            @endif
                        </div>
                        <div class="p-3">
                            <h3 class="font-semibold text-gray-800 text-sm line-clamp-2 mb-1">{{ $item->judul }}</h3>
                            @if($item->penulis)
                                <p class="text-xs text-gray-500 line-clamp-1">{{ $item->penulis }}</p>
                            @endif
                            @if($item->posyandu)
                                <a href="{{ route('posyandu.perpustakaan', encrypt($item->posyandu->id_posyandu)) }}" 
                                   class="mt-2 inline-flex items-center gap-1 text-xs text-primary hover:underline">
                                    <i class="ph ph-pencil"></i>
                                    Kelola di {{ $item->posyandu->nama_posyandu }}
                                </a>
                            @endif
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

    {{-- Modal Flipbook --}}
    @if($showFlipbookModal && $viewingBook)
        @if($viewingBook->file_path)
            <div class="fixed inset-0 z-50 overflow-hidden bg-gray-900/95" aria-modal="true"
                 x-data="pdfViewer({ pdfUrl: '{{ uploads_asset($viewingBook->file_path) }}', title: @js($viewingBook->judul) })"
                 x-init="init()"
                 @keydown.escape.window="$wire.closeFlipbook()"
                 @keydown.left.window="!isMobile && prevPage()"
                 @keydown.right.window="!isMobile && nextPage()"
                 @resize.window="checkMobile()">
                <div class="absolute top-0 left-0 right-0 z-10 bg-gradient-to-b from-black/80 to-transparent p-4">
                    <div class="max-w-6xl mx-auto flex items-center justify-between">
                        <div class="text-white">
                            <h2 class="text-lg md:text-xl font-bold" x-text="title"></h2>
                            <p class="text-xs md:text-sm text-white/70">{{ $viewingBook->penulis ?? '' }} <span class="ml-2 px-2 py-0.5 bg-red-500/80 rounded text-xs">PDF</span></p>
                        </div>
                        <button wire:click="closeFlipbook" class="p-2 rounded-full bg-white/10 text-white hover:bg-white/20 transition"><i class="ph ph-x text-xl md:text-2xl"></i></button>
                    </div>
                </div>
                <div x-show="loading" class="h-full flex items-center justify-center">
                    <div class="text-center text-white"><i class="ph ph-spinner text-5xl animate-spin mb-4"></i><p>Memuat PDF...</p></div>
                </div>
                <div x-show="!loading && isMobile" x-cloak class="h-full overflow-y-auto pt-20 pb-4 px-4"><div x-ref="mobileContainer" class="max-w-lg mx-auto space-y-4"></div></div>
                <div x-show="!loading && !isMobile" x-cloak class="h-full flex items-center justify-center px-4 py-20">
                    <div class="relative w-full max-w-5xl h-[70vh]">
                        <div class="relative h-full flex items-center justify-center">
                            <div class="relative w-1/2 h-full bg-white shadow-2xl rounded-l-lg overflow-hidden flex items-center justify-center" :class="currentPage <= 1 ? 'bg-gray-50' : ''">
                                <canvas x-ref="leftCanvas" class="max-w-full max-h-full" :class="currentPage <= 1 ? 'hidden' : ''"></canvas>
                                <div class="absolute bottom-4 left-4 px-3 py-1 bg-black/50 text-white text-sm rounded-full" x-show="currentPage > 1"><span x-text="currentPage - 1"></span></div>
                            </div>
                            <div class="w-2 h-full bg-gradient-to-r from-gray-400 via-gray-300 to-gray-400 shadow-inner"></div>
                            <div class="relative w-1/2 h-full bg-white shadow-2xl rounded-r-lg overflow-hidden flex items-center justify-center">
                                <canvas x-ref="rightCanvas" class="max-w-full max-h-full"></canvas>
                                <div class="absolute bottom-4 right-4 px-3 py-1 bg-black/50 text-white text-sm rounded-full" x-show="currentPage <= totalPages"><span x-text="currentPage"></span></div>
                            </div>
                        </div>
                        <button @click="prevPage()" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-16 p-4 rounded-full bg-white/10 text-white hover:bg-white/20 transition disabled:opacity-30" :disabled="currentPage <= 1"><i class="ph ph-caret-left text-3xl"></i></button>
                        <button @click="nextPage()" class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-16 p-4 rounded-full bg-white/10 text-white hover:bg-white/20 transition disabled:opacity-30" :disabled="currentPage >= totalPages"><i class="ph ph-caret-right text-3xl"></i></button>
                    </div>
                </div>
                <div x-show="!loading && !isMobile" class="absolute bottom-0 left-0 right-0 z-10 bg-gradient-to-t from-black/80 to-transparent p-4">
                    <div class="max-w-6xl mx-auto flex items-center justify-center gap-4 text-white">
                        <span class="text-sm">Halaman</span>
                        <div class="flex items-center gap-2">
                            <button @click="goToPage(1)" class="px-2 py-1 text-sm hover:bg-white/20 rounded" :disabled="currentPage <= 1"><i class="ph ph-skip-back"></i></button>
                            <button @click="prevPage()" class="px-2 py-1 text-sm hover:bg-white/20 rounded" :disabled="currentPage <= 1"><i class="ph ph-caret-left"></i></button>
                            <span class="px-4 py-1 bg-white/20 rounded-full text-sm font-medium"><span x-text="currentPage"></span> / <span x-text="totalPages"></span></span>
                            <button @click="nextPage()" class="px-2 py-1 text-sm hover:bg-white/20 rounded" :disabled="currentPage >= totalPages"><i class="ph ph-caret-right"></i></button>
                            <button @click="goToPage(totalPages)" class="px-2 py-1 text-sm hover:bg-white/20 rounded" :disabled="currentPage >= totalPages"><i class="ph ph-skip-forward"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
            <script>pdfjsLib.GlobalWorkerOptions.workerSrc='https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';window._pdfDocuments=window._pdfDocuments||{};function pdfViewer(c){const i='pdf_'+Date.now();return{pdfUrl:c.pdfUrl,title:c.title,instanceId:i,currentPage:1,totalPages:0,loading:true,isMobile:window.innerWidth<768,mobileRendered:false,async init(){this.checkMobile();await this.loadPdf()},checkMobile(){const w=this.isMobile;this.isMobile=window.innerWidth<768;if(w!==this.isMobile&&!this.loading){this.$nextTick(()=>{if(this.isMobile&&!this.mobileRendered)this.renderMobilePages();else if(!this.isMobile)this.renderDesktopPages()})}},async loadPdf(){try{const d=await pdfjsLib.getDocument(this.pdfUrl).promise;window._pdfDocuments[this.instanceId]=d;this.totalPages=d.numPages;this.loading=false;await this.$nextTick();setTimeout(()=>{this.isMobile?this.renderMobilePages():this.renderDesktopPages()},100)}catch(e){console.error(e);this.loading=false}},async renderMobilePages(){const d=window._pdfDocuments[this.instanceId],c=this.$refs.mobileContainer;if(!d||!c)return;c.innerHTML='';this.mobileRendered=true;for(let p=1;p<=this.totalPages;p++){const x=await d.getPage(p),v=x.getViewport({scale:1}),s=(((c.clientWidth||350)*0.95)/v.width)*2,sv=x.getViewport({scale:s}),w=document.createElement('div');w.className='relative bg-white rounded-lg shadow-lg overflow-hidden';const cn=document.createElement('canvas');cn.width=sv.width;cn.height=sv.height;cn.style.width=(sv.width/2)+'px';cn.style.height=(sv.height/2)+'px';cn.className='w-full h-auto';const pn=document.createElement('div');pn.className='absolute bottom-2 right-2 px-2 py-1 bg-black/50 text-white text-xs rounded-full';pn.textContent=p+' / '+this.totalPages;w.appendChild(cn);w.appendChild(pn);c.appendChild(w);await x.render({canvasContext:cn.getContext('2d'),viewport:sv}).promise}},async renderDesktopPages(){const l=this.$refs.leftCanvas,r=this.$refs.rightCanvas,d=window._pdfDocuments[this.instanceId];if(!d||!l||!r)return;l.getContext('2d').clearRect(0,0,l.width,l.height);r.getContext('2d').clearRect(0,0,r.width,r.height);if(this.currentPage<=this.totalPages)await this.renderPage(this.currentPage,r);if(this.currentPage>1)await this.renderPage(this.currentPage-1,l)},async renderPage(n,cn){try{const d=window._pdfDocuments[this.instanceId];if(!d)return;const p=await d.getPage(n),ct=cn.parentElement,v=p.getViewport({scale:1}),bs=Math.min((ct.clientWidth||400)*0.9/v.width,(ct.clientHeight||500)*0.9/v.height)*2,sv=p.getViewport({scale:bs});cn.width=sv.width;cn.height=sv.height;cn.style.width=(sv.width/2)+'px';cn.style.height=(sv.height/2)+'px';await p.render({canvasContext:cn.getContext('2d'),viewport:sv}).promise}catch(e){}},async nextPage(){if(this.currentPage<this.totalPages){this.currentPage++;await this.renderDesktopPages()}},async prevPage(){if(this.currentPage>1){this.currentPage--;await this.renderDesktopPages()}},async goToPage(n){this.currentPage=Math.max(1,Math.min(n,this.totalPages));await this.renderDesktopPages()},destroy(){delete window._pdfDocuments[this.instanceId]}}}</script>
        @else
            <div class="fixed inset-0 z-50 overflow-hidden bg-gray-900/95" aria-modal="true"
                 x-data="imageViewer({pages:@js($viewingBook->halaman_images?array_map(fn($p)=>uploads_asset($p),$viewingBook->halaman_images):[]),title:@js($viewingBook->judul)})"
                 x-init="checkMobile()"
                 @keydown.escape.window="$wire.closeFlipbook()"
                 @keydown.left.window="!isMobile && prevPage()"
                 @keydown.right.window="!isMobile && nextPage()"
                 @resize.window="checkMobile()">
                <div class="absolute top-0 left-0 right-0 z-10 bg-gradient-to-b from-black/80 to-transparent p-4">
                    <div class="max-w-6xl mx-auto flex items-center justify-between">
                        <div class="text-white"><h2 class="text-lg md:text-xl font-bold" x-text="title"></h2><p class="text-xs md:text-sm text-white/70">{{ $viewingBook->penulis ?? '' }}</p></div>
                        <button wire:click="closeFlipbook" class="p-2 rounded-full bg-white/10 text-white hover:bg-white/20 transition"><i class="ph ph-x text-xl md:text-2xl"></i></button>
                    </div>
                </div>
                <div x-show="isMobile" x-cloak class="h-full overflow-y-auto pt-20 pb-4 px-4">
                    <div class="max-w-lg mx-auto space-y-4">
                        <template x-for="(page, index) in pages" :key="index">
                            <div class="relative bg-white rounded-lg shadow-lg overflow-hidden">
                                <img :src="page" class="w-full h-auto" alt="">
                                <div class="absolute bottom-2 right-2 px-2 py-1 bg-black/50 text-white text-xs rounded-full" x-text="(index + 1) + ' / ' + pages.length"></div>
                            </div>
                        </template>
                    </div>
                </div>
                <div x-show="!isMobile" x-cloak class="h-full flex items-center justify-center px-4 py-20">
                    <div class="relative w-full max-w-5xl h-[70vh]">
                        <div class="relative h-full flex items-center justify-center">
                            <div class="relative w-1/2 h-full bg-white shadow-2xl rounded-l-lg overflow-hidden" :class="currentPage === 0 ? 'opacity-50' : ''">
                                <template x-if="currentPage > 0"><img :src="pages[currentPage - 1]" class="w-full h-full object-contain bg-gray-100" alt=""></template>
                                <template x-if="currentPage === 0"><div class="w-full h-full bg-gradient-to-r from-gray-200 to-gray-100 flex items-center justify-center"><div class="text-center text-gray-400"><i class="ph ph-book-open text-6xl mb-4"></i><p class="text-sm">Halaman Depan</p></div></div></template>
                                <div class="absolute bottom-4 left-4 px-3 py-1 bg-black/50 text-white text-sm rounded-full" x-show="currentPage > 0"><span x-text="currentPage"></span></div>
                            </div>
                            <div class="w-2 h-full bg-gradient-to-r from-gray-400 via-gray-300 to-gray-400 shadow-inner"></div>
                            <div class="relative w-1/2 h-full bg-white shadow-2xl rounded-r-lg overflow-hidden" :class="currentPage >= pages.length ? 'opacity-50' : ''">
                                <template x-if="currentPage < pages.length"><img :src="pages[currentPage]" class="w-full h-full object-contain bg-gray-100" alt=""></template>
                                <template x-if="currentPage >= pages.length"><div class="w-full h-full bg-gradient-to-l from-gray-200 to-gray-100 flex items-center justify-center"><div class="text-center text-gray-400"><i class="ph ph-check-circle text-6xl mb-4"></i><p class="text-sm">Selesai</p></div></div></template>
                                <div class="absolute bottom-4 right-4 px-3 py-1 bg-black/50 text-white text-sm rounded-full" x-show="currentPage < pages.length"><span x-text="currentPage + 1"></span></div>
                            </div>
                        </div>
                        <button @click="prevPage()" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-16 p-4 rounded-full bg-white/10 text-white hover:bg-white/20 transition disabled:opacity-30" :disabled="currentPage === 0"><i class="ph ph-caret-left text-3xl"></i></button>
                        <button @click="nextPage()" class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-16 p-4 rounded-full bg-white/10 text-white hover:bg-white/20 transition disabled:opacity-30" :disabled="currentPage >= pages.length"><i class="ph ph-caret-right text-3xl"></i></button>
                    </div>
                </div>
                <div x-show="!isMobile" class="absolute bottom-0 left-0 right-0 z-10 bg-gradient-to-t from-black/80 to-transparent p-4">
                    <div class="max-w-6xl mx-auto flex items-center justify-center gap-4 text-white">
                        <span class="text-sm">Halaman</span>
                        <div class="flex items-center gap-2">
                            <button @click="currentPage = 0" class="px-2 py-1 text-sm hover:bg-white/20 rounded"><i class="ph ph-skip-back"></i></button>
                            <span class="px-4 py-1 bg-white/20 rounded-full text-sm font-medium"><span x-text="currentPage + 1"></span> / <span x-text="pages.length"></span></span>
                            <button @click="currentPage = pages.length - 1" class="px-2 py-1 text-sm hover:bg-white/20 rounded"><i class="ph ph-skip-forward"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <script>function imageViewer(c){return{pages:c.pages||[],title:c.title||'',currentPage:0,isMobile:window.innerWidth<768,checkMobile(){this.isMobile=window.innerWidth<768},nextPage(){if(this.currentPage<this.pages.length){this.currentPage+=2;if(this.currentPage>this.pages.length)this.currentPage=this.pages.length}},prevPage(){if(this.currentPage>0){this.currentPage-=2;if(this.currentPage<0)this.currentPage=0}},goToPage(i){this.currentPage=i%2===0?i:i-1}}}</script>
        @endif
    @endif
</div>
