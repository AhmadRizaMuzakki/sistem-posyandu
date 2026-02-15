<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perpustakaan Digital - Posyandu Karanggan</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/home.jpeg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: '#0D9488',
                        primaryDark: '#0F766E',
                        lightBg: '#F0FDFA',
                    }
                }
            }
        };
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .pagination { display: flex; justify-content: center; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
        .pagination a, .pagination span { padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 500; transition: all 0.2s; }
        .pagination a { background: white; border: 1px solid #e2e8f0; color: #475569; text-decoration: none; }
        .pagination a:hover { background: #0D9488; color: white; border-color: #0D9488; }
        .pagination .active span { background: #0D9488; color: white; border-color: #0D9488; }
        .pagination .disabled span { background: #f1f5f9; color: #94a3b8; cursor: not-allowed; }
    </style>
</head>
<body class="font-sans text-slate-700 antialiased bg-slate-50">

    <nav class="fixed w-full z-50 bg-white/90 backdrop-blur-md shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="{{ route('index') }}" class="flex items-center gap-2 text-slate-600 hover:text-primary transition">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span class="font-medium">Kembali ke Beranda</span>
                </a>
                <a href="{{ route('index') }}" class="flex items-center gap-2">
                    <div class="w-9 h-9 bg-primary rounded-full flex items-center justify-center text-white">
                        <img src="{{ asset('images/home.jpeg') }}" alt="Logo" class="w-full h-full object-cover rounded-full">
                    </div>
                    <span class="font-bold text-slate-800">Posyandu Karanggan</span>
                </a>
            </div>
        </div>
    </nav>

    <main class="pt-24 pb-16 px-4 sm:px-6 lg:px-8" x-data="publicFlipbook()" @keydown.escape.window="closeBook()">
        <div class="max-w-7xl mx-auto">

            <div class="text-center mb-12">
                <span class="inline-block py-1 px-3 rounded-full bg-teal-100 text-primary text-sm font-semibold mb-4">Perpustakaan Digital</span>
                <h1 class="text-4xl font-bold text-slate-900 mb-4">Koleksi Buku dari Berbagai Posyandu</h1>
                <p class="text-slate-600 max-w-2xl mx-auto text-lg">Buku digital seputar kesehatan, gizi, parenting, dan topik lain. Klik buku untuk langsung membaca.</p>
            </div>

            @if($perpustakaanKoleksi->isNotEmpty())
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6">
                    @foreach($perpustakaanKoleksi as $book)
                        <div class="group cursor-pointer" @click="openBook({
                            judul: @js($book->judul),
                            penulis: @js($book->penulis ?? ''),
                            isPdf: {{ $book->file_path ? 'true' : 'false' }},
                            pdfUrl: @js($book->file_path ? uploads_asset($book->file_path) : ''),
                            pages: @js($book->halaman_images ? array_map(fn($p) => uploads_asset($p), $book->halaman_images) : [])
                        })">
                            <div class="relative aspect-[3/4] rounded-xl overflow-hidden bg-slate-100 border border-slate-200 shadow-md group-hover:shadow-xl transition-all duration-300">
                                @if($book->cover_image)
                                    <img src="{{ uploads_asset($book->cover_image) }}" alt="{{ $book->judul }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-primary/20 to-primary/40 flex items-center justify-center">
                                        <i class="fa-solid fa-book text-4xl text-primary/60"></i>
                                    </div>
                                @endif
                                @if($book->kategori)
                                    @php
                                        $kategoriLabels = [
                                            'kesehatan' => 'Kesehatan',
                                            'gizi' => 'Gizi',
                                            'parenting' => 'Parenting',
                                            'ibu_hamil' => 'Ibu Hamil',
                                            'bayi_balita' => 'Bayi/Balita',
                                            'lansia' => 'Lansia',
                                            'umum' => 'Umum',
                                        ];
                                    @endphp
                                    <span class="absolute top-2 left-2 px-2 py-0.5 text-xs font-medium rounded-full bg-primary text-white">
                                        {{ $kategoriLabels[$book->kategori] ?? ucfirst($book->kategori) }}
                                    </span>
                                @endif
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <span class="px-4 py-2 bg-white rounded-full text-primary text-sm font-medium">
                                        <i class="fa-solid fa-book-open mr-1"></i> Baca
                                    </span>
                                </div>
                                @if($book->posyandu)
                                <span class="absolute bottom-2 left-2 right-2 px-2 py-1 bg-black/60 text-white text-xs rounded truncate">{{ $book->posyandu->nama_posyandu }}</span>
                                @endif
                            </div>
                            <div class="mt-2 px-1">
                                <h3 class="font-medium text-slate-800 text-sm line-clamp-2 group-hover:text-primary transition">{{ $book->judul }}</h3>
                                @if($book->penulis)
                                    <p class="text-xs text-slate-500 mt-0.5 line-clamp-1">{{ $book->penulis }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-12 flex justify-center">
                    {{ $perpustakaanKoleksi->links() }}
                </div>

                {{-- Flipbook Modal --}}
                <div x-show="showFlipbook" x-cloak x-transition
                     class="fixed inset-0 z-[100] bg-slate-900/95"
                     @keydown.left.window="!isMobile && prevPage()"
                     @keydown.right.window="!isMobile && nextPage()"
                     @resize.window="checkMobile()">
                    <div class="absolute top-0 left-0 right-0 z-10 bg-gradient-to-b from-black/80 to-transparent p-4">
                        <div class="max-w-6xl mx-auto flex items-center justify-between">
                            <div class="text-white">
                                <h2 class="text-lg md:text-xl font-bold" x-text="currentBook?.judul"></h2>
                                <p class="text-xs md:text-sm text-white/70">
                                    <span x-text="currentBook?.penulis"></span>
                                    <span x-show="currentBook?.isPdf" class="ml-2 px-2 py-0.5 bg-red-500/80 rounded text-xs">PDF</span>
                                </p>
                            </div>
                            <button @click="closeBook()" class="p-2 rounded-full bg-white/10 text-white hover:bg-white/20 transition">
                                <i class="fa-solid fa-xmark text-xl md:text-2xl"></i>
                            </button>
                        </div>
                    </div>
                    <div x-show="loading" class="h-full flex items-center justify-center pt-20">
                        <div class="text-center text-white">
                            <i class="fa-solid fa-spinner text-5xl animate-spin mb-4"></i>
                            <p>Memuat...</p>
                        </div>
                    </div>
                    <div x-show="!loading && isMobile" x-cloak class="h-full overflow-y-auto pt-20 pb-4 px-4">
                        <div x-show="isPdf" x-ref="mobileContainer" class="max-w-lg mx-auto space-y-4"></div>
                        <div x-show="!isPdf" class="max-w-lg mx-auto space-y-4">
                            <template x-for="(page, index) in pages" :key="index">
                                <div class="relative bg-white rounded-lg shadow-lg overflow-hidden">
                                    <img :src="page" class="w-full h-auto" alt="">
                                    <div class="absolute bottom-2 right-2 px-2 py-1 bg-black/50 text-white text-xs rounded-full" x-text="(index + 1) + ' / ' + pages.length"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div x-show="!loading && !isMobile" x-cloak class="h-full flex items-center justify-center px-4 py-20">
                        <div class="relative w-full max-w-5xl h-[70vh]">
                            <div class="relative h-full flex items-center justify-center">
                                <div class="relative w-1/2 h-full bg-white shadow-2xl rounded-l-lg overflow-hidden flex items-center justify-center" :class="(currentPage === 0 && !isPdf) || (isPdf && currentPage <= 1) ? 'bg-slate-50' : ''">
                                    <template x-if="isPdf"><canvas x-ref="leftCanvas" class="max-w-full max-h-full" :class="currentPage <= 1 ? 'hidden' : ''"></canvas></template>
                                    <template x-if="!isPdf && currentPage > 0"><img :src="pages[currentPage - 1]" class="max-w-full max-h-full object-contain" alt=""></template>
                                    <div x-show="!isPdf && currentPage === 0" class="absolute inset-0 flex items-center justify-center text-slate-400">
                                        <div class="text-center"><i class="fa-solid fa-book-open text-5xl mb-4"></i><p class="text-sm">Cover</p></div>
                                    </div>
                                    <div class="absolute bottom-4 left-4 px-3 py-1 bg-black/50 text-white text-sm rounded-full" x-show="isPdf ? currentPage > 1 : currentPage > 0"><span x-text="isPdf ? currentPage - 1 : currentPage"></span></div>
                                </div>
                                <div class="w-2 h-full bg-gradient-to-r from-slate-400 via-slate-300 to-slate-400 shadow-inner"></div>
                                <div class="relative w-1/2 h-full bg-white shadow-2xl rounded-r-lg overflow-hidden flex items-center justify-center">
                                    <template x-if="isPdf"><canvas x-ref="rightCanvas" class="max-w-full max-h-full"></canvas></template>
                                    <template x-if="!isPdf && currentPage < pages.length"><img :src="pages[currentPage]" class="max-w-full max-h-full object-contain" alt=""></template>
                                    <div x-show="!isPdf && currentPage >= pages.length" class="absolute inset-0 flex items-center justify-center text-slate-400 bg-slate-100">
                                        <div class="text-center"><i class="fa-solid fa-circle-check text-5xl mb-4"></i><p class="text-sm">Selesai</p></div>
                                    </div>
                                    <div class="absolute bottom-4 right-4 px-3 py-1 bg-black/50 text-white text-sm rounded-full" x-show="isPdf ? currentPage <= totalPages : currentPage < pages.length"><span x-text="isPdf ? currentPage : currentPage + 1"></span></div>
                                </div>
                            </div>
                            <button @click="prevPage()" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-16 p-4 rounded-full bg-white/10 text-white hover:bg-white/20 transition disabled:opacity-30" :disabled="isPdf ? currentPage <= 1 : currentPage === 0"><i class="fa-solid fa-chevron-left text-2xl"></i></button>
                            <button @click="nextPage()" class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-16 p-4 rounded-full bg-white/10 text-white hover:bg-white/20 transition disabled:opacity-30" :disabled="isPdf ? currentPage >= totalPages : currentPage >= pages.length"><i class="fa-solid fa-chevron-right text-2xl"></i></button>
                        </div>
                    </div>
                    <div x-show="!loading && !isMobile" class="absolute bottom-0 left-0 right-0 z-10 bg-gradient-to-t from-black/80 to-transparent p-4">
                        <div class="max-w-6xl mx-auto">
                            <div class="flex items-center justify-center gap-4 text-white">
                                <span class="text-sm">Halaman</span>
                                <div class="flex items-center gap-2">
                                    <button @click="goToFirst()" class="px-2 py-1 text-sm hover:bg-white/20 rounded"><i class="fa-solid fa-backward-step"></i></button>
                                    <button @click="prevPage()" class="px-2 py-1 text-sm hover:bg-white/20 rounded"><i class="fa-solid fa-chevron-left"></i></button>
                                    <span class="px-4 py-1 bg-white/20 rounded-full text-sm font-medium"><span x-text="isPdf ? currentPage : currentPage + 1"></span> / <span x-text="isPdf ? totalPages : pages.length"></span></span>
                                    <button @click="nextPage()" class="px-2 py-1 text-sm hover:bg-white/20 rounded"><i class="fa-solid fa-chevron-right"></i></button>
                                    <button @click="goToLast()" class="px-2 py-1 text-sm hover:bg-white/20 rounded"><i class="fa-solid fa-forward-step"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-20 bg-white rounded-2xl border-2 border-dashed border-slate-300">
                    <i class="fa-solid fa-book-open text-5xl text-slate-400 mb-4"></i>
                    <p class="text-slate-600 font-medium mb-2">Belum ada buku di perpustakaan</p>
                    <p class="text-slate-400 text-sm">Buku digital akan tampil di sini setelah ditambahkan oleh admin Posyandu.</p>
                    <a href="{{ route('index') }}" class="inline-flex items-center gap-2 mt-6 px-6 py-3 rounded-full bg-primary text-white font-medium hover:bg-primaryDark transition">
                        <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
                    </a>
                </div>
            @endif
        </div>
    </main>

    <footer class="border-t border-slate-200 bg-white py-6 text-center text-sm text-slate-500">
        © {{ date('Y') }} Posyandu Karanggan. All rights reserved.
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        window._publicPdfDoc = null;
        function publicFlipbook() {
            return {
                showFlipbook: false, currentBook: null, currentPage: 0, pages: [],
                isPdf: false, totalPages: 0, loading: false,
                isMobile: window.innerWidth < 768, mobileRendered: false,
                checkMobile() {
                    const wasMobile = this.isMobile;
                    this.isMobile = window.innerWidth < 768;
                    if (this.showFlipbook && wasMobile !== this.isMobile && !this.loading) {
                        this.$nextTick(() => {
                            if (this.isMobile && this.isPdf && !this.mobileRendered) this.renderMobilePdfPages();
                            else if (!this.isMobile && this.isPdf) this.renderPdfPages();
                        });
                    }
                },
                async openBook(book) {
                    this.currentBook = book;
                    this.isPdf = book.isPdf;
                    this.showFlipbook = true;
                    this.mobileRendered = false;
                    this.checkMobile();
                    if (book.isPdf && book.pdfUrl) {
                        this.loading = true;
                        this.currentPage = 1;
                        try {
                            window._publicPdfDoc = await pdfjsLib.getDocument(book.pdfUrl).promise;
                            this.totalPages = window._publicPdfDoc.numPages;
                            this.loading = false;
                            await this.$nextTick();
                            setTimeout(() => this.isMobile ? this.renderMobilePdfPages() : this.renderPdfPages(), 100);
                        } catch (e) { console.error('Error loading PDF:', e); this.loading = false; }
                    } else {
                        this.pages = book.pages || [];
                        this.currentPage = 0;
                    }
                },
                closeBook() {
                    this.showFlipbook = false;
                    this.currentBook = null;
                    this.pages = [];
                    window._publicPdfDoc = null;
                    this.isPdf = false;
                    this.mobileRendered = false;
                },
                async renderMobilePdfPages() {
                    const doc = window._publicPdfDoc;
                    const container = this.$refs.mobileContainer;
                    if (!doc || !container) return;
                    container.innerHTML = '';
                    this.mobileRendered = true;
                    for (let i = 1; i <= this.totalPages; i++) {
                        const page = await doc.getPage(i);
                        const viewport = page.getViewport({ scale: 1 });
                        const scale = (((container.clientWidth || 350) * 0.95) / viewport.width) * 2;
                        const scaledViewport = page.getViewport({ scale });
                        const wrapper = document.createElement('div');
                        wrapper.className = 'relative bg-white rounded-lg shadow-lg overflow-hidden';
                        const canvas = document.createElement('canvas');
                        canvas.width = scaledViewport.width;
                        canvas.height = scaledViewport.height;
                        canvas.style.width = (scaledViewport.width / 2) + 'px';
                        canvas.style.height = (scaledViewport.height / 2) + 'px';
                        canvas.className = 'w-full h-auto';
                        const pageNum = document.createElement('div');
                        pageNum.className = 'absolute bottom-2 right-2 px-2 py-1 bg-black/50 text-white text-xs rounded-full';
                        pageNum.textContent = i + ' / ' + this.totalPages;
                        wrapper.appendChild(canvas);
                        wrapper.appendChild(pageNum);
                        container.appendChild(wrapper);
                        await page.render({ canvasContext: canvas.getContext('2d'), viewport: scaledViewport }).promise;
                    }
                },
                async renderPdfPages() {
                    const doc = window._publicPdfDoc;
                    const left = this.$refs.leftCanvas, right = this.$refs.rightCanvas;
                    if (!doc || !left || !right) return;
                    left.getContext('2d').clearRect(0, 0, left.width, left.height);
                    right.getContext('2d').clearRect(0, 0, right.width, right.height);
                    if (this.currentPage <= this.totalPages) await this.renderPdfPage(this.currentPage, right);
                    if (this.currentPage > 1) await this.renderPdfPage(this.currentPage - 1, left);
                },
                async renderPdfPage(pageNum, canvas) {
                    try {
                        const doc = window._publicPdfDoc;
                        if (!doc) return;
                        const page = await doc.getPage(pageNum);
                        const container = canvas.parentElement;
                        const viewport = page.getViewport({ scale: 1 });
                        const baseScale = Math.min(((container.clientWidth || 400) * 0.9) / viewport.width, ((container.clientHeight || 500) * 0.9) / viewport.height);
                        const scale = baseScale * 2;
                        const scaledViewport = page.getViewport({ scale });
                        canvas.width = scaledViewport.width;
                        canvas.height = scaledViewport.height;
                        canvas.style.width = (scaledViewport.width / 2) + 'px';
                        canvas.style.height = (scaledViewport.height / 2) + 'px';
                        await page.render({ canvasContext: canvas.getContext('2d'), viewport: scaledViewport }).promise;
                    } catch (e) { console.error('Error rendering page', pageNum, e); }
                },
                async nextPage() {
                    if (this.isPdf) { if (this.currentPage < this.totalPages) { this.currentPage++; await this.renderPdfPages(); } }
                    else { if (this.currentPage < this.pages.length) { this.currentPage += 2; if (this.currentPage > this.pages.length) this.currentPage = this.pages.length; } }
                },
                async prevPage() {
                    if (this.isPdf) { if (this.currentPage > 1) { this.currentPage--; await this.renderPdfPages(); } }
                    else { if (this.currentPage > 0) { this.currentPage -= 2; if (this.currentPage < 0) this.currentPage = 0; } }
                },
                async goToFirst() { if (this.isPdf) { this.currentPage = 1; await this.renderPdfPages(); } else { this.currentPage = 0; } },
                async goToLast() { if (this.isPdf) { this.currentPage = this.totalPages; await this.renderPdfPages(); } else { this.currentPage = Math.max(0, this.pages.length - 1); } }
            };
        }
    </script>
</body>
</html>
