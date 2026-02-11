<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $posyandu->nama_posyandu }} - Info Posyandu | Posyandu Karanggan</title>
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
    </style>
</head>
<body class="font-sans text-slate-700 antialiased bg-slate-50">

    {{-- Navbar --}}
    <nav class="fixed w-full z-50 bg-white/90 backdrop-blur-md shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="{{ route('index') }}#posyandu" class="flex items-center gap-2 text-slate-600 hover:text-primary transition">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span class="font-medium">Kembali ke Daftar Posyandu</span>
                </a>
                <a href="{{ route('index') }}" class="flex items-center gap-2">
                    <div class="w-9 h-9 bg-primary rounded-full flex items-center justify-center text-white">
                        <img src="{{ asset('images/home.jpeg') }}" alt="Logo Posyandu" class="w-full h-full object-cover">
                    </div>
                    <span class="font-bold text-slate-800">Posyandu Karanggan</span>
                </a>
            </div>
        </div>
    </nav>

    <main class="pt-24 pb-16 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto space-y-8">

            {{-- Header --}}
            <div class="text-center">
                <h1 class="text-3xl font-bold text-slate-900">{{ $posyandu->nama_posyandu }}</h1>
                <p class="text-slate-600 mt-1">Informasi dan profil posyandu</p>
            </div>

            {{-- Logo Posyandu --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center">
                    <i class="fa-solid fa-image text-primary mr-2"></i>
                    Logo Posyandu
                </h2>
                <div class="flex justify-center py-4">
                    @if($posyandu->logo_posyandu)
                        <img src="{{ uploads_asset($posyandu->logo_posyandu) }}" alt="Logo {{ $posyandu->nama_posyandu }}" class="w-40 h-40 object-contain rounded-xl border-2 border-slate-100 shadow-sm" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                        <div class="hidden w-40 h-40 bg-slate-100 rounded-xl border-2 border-dashed border-slate-300 flex flex-col items-center justify-center text-slate-500">
                            <i class="fa-solid fa-image text-4xl mb-2"></i>
                            <span class="text-sm">Logo</span>
                        </div>
                    @else
                        <div class="w-40 h-40 bg-slate-100 rounded-xl border-2 border-dashed border-slate-300 flex flex-col items-center justify-center text-slate-500">
                            <i class="fa-solid fa-image text-4xl mb-2"></i>
                            <span class="text-sm">Belum ada logo</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Informasi Posyandu --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center">
                    <i class="fa-solid fa-circle-info text-primary mr-2"></i>
                    Informasi Posyandu
                </h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-slate-500">Nama Posyandu</dt>
                        <dd class="text-slate-800 mt-0.5">{{ $posyandu->nama_posyandu }}</dd>
                    </div>
                    @if($posyandu->domisili_posyandu)
                    <div>
                        <dt class="text-sm font-medium text-slate-500">Domisili</dt>
                        <dd class="text-slate-800 mt-0.5">{{ $posyandu->domisili_posyandu }}</dd>
                    </div>
                    @endif
                    @if($posyandu->alamat_posyandu)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-slate-500">Alamat</dt>
                        <dd class="text-slate-800 mt-0.5">{{ $posyandu->alamat_posyandu }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-slate-500">Jumlah Sasaran</dt>
                        <dd class="text-slate-800 mt-0.5">
                            @php
                                $totalSasaran = ($posyandu->sasaran_bayibalita_count ?? 0) +
                                    ($posyandu->sasaran_remaja_count ?? 0) +
                                    ($posyandu->sasaran_dewasa_count ?? 0) +
                                    ($posyandu->sasaran_ibuhamil_count ?? 0) +
                                    ($posyandu->sasaran_pralansia_count ?? 0) +
                                    ($posyandu->sasaran_lansia_count ?? 0);
                            @endphp
                            {{ number_format($totalSasaran, 0, ',', '.') }} orang
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Lokasi / Peta (Google Maps embed only - whitelist aman dari XSS) --}}
            @php
                $linkMaps = $posyandu->link_maps ?? '';
                $embedUrl = null;
                if (!empty($linkMaps) && str_starts_with($linkMaps, 'https://www.google.com/maps/embed')) {
                    $embedUrl = $linkMaps;
                }
            @endphp
            @if($embedUrl)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center">
                    <i class="fa-solid fa-map-location-dot text-primary mr-2"></i>
                    Lokasi Posyandu
                </h2>
                <div class="rounded-xl overflow-hidden border border-slate-200">
                    <iframe
                        src="{{ $embedUrl }}"
                        width="600"
                        height="450"
                        style="border:0; width:100%; height:450px; max-width:100%;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
            @endif

            {{-- SK Posyandu (hanya tampil jika ada, tanpa tombol upload/hapus) --}}
            @if($posyandu->sk_posyandu)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center">
                    <i class="fa-solid fa-file-pdf text-primary mr-2"></i>
                    SK Posyandu
                </h2>
                <a href="{{ uploads_asset($posyandu->sk_posyandu) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-4 py-2 bg-primary/10 text-primary rounded-lg hover:bg-primary/20 transition font-medium">
                    <i class="fa-solid fa-external-link-alt"></i>
                    Lihat / Unduh SK Posyandu
                </a>
            </div>
            @endif

            {{-- Statistik semua sasaran (gunakan withCount, hindari N+1) --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 text-center">
                    <p class="text-2xl font-bold text-primary">{{ $posyandu->kader_count ?? 0 }}</p>
                    <p class="text-sm text-slate-500 mt-1">Kader</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 text-center">
                    <p class="text-2xl font-bold text-green-600">{{ $posyandu->sasaran_bayibalita_count ?? 0 }}</p>
                    <p class="text-sm text-slate-500 mt-1">Bayi/Balita</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 text-center">
                    <p class="text-2xl font-bold text-cyan-600">{{ $posyandu->sasaran_remaja_count ?? 0 }}</p>
                    <p class="text-sm text-slate-500 mt-1">Remaja</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 text-center">
                    <p class="text-2xl font-bold text-orange-600">{{ $posyandu->sasaran_dewasa_count ?? 0 }}</p>
                    <p class="text-sm text-slate-500 mt-1">Dewasa</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 text-center">
                    <p class="text-2xl font-bold text-pink-600">{{ $posyandu->sasaran_ibuhamil_count ?? 0 }}</p>
                    <p class="text-sm text-slate-500 mt-1">Ibu Hamil</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 text-center">
                    <p class="text-2xl font-bold text-amber-600">{{ $posyandu->sasaran_pralansia_count ?? 0 }}</p>
                    <p class="text-sm text-slate-500 mt-1">Pralansia</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 text-center">
                    <p class="text-2xl font-bold text-indigo-600">{{ $posyandu->sasaran_lansia_count ?? 0 }}</p>
                    <p class="text-sm text-slate-500 mt-1">Lansia</p>
                </div>
            </div>

            {{-- Daftar Kader (tanpa NIK, tanpa aksi) --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center">
                    <i class="fa-solid fa-users text-primary mr-2"></i>
                    Daftar Kader
                    <span class="ml-2 text-sm font-normal text-slate-500">({{ $posyandu->kader_count ?? 0 }} kader)</span>
                </h2>
                @if($posyandu->kader && $posyandu->kader->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-slate-600">
                            <thead class="text-xs font-medium text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3 rounded-tl-lg">Foto</th>
                                    <th class="px-4 py-3">Nama</th>
                                    <th class="px-4 py-3">Tanggal Lahir</th>
                                    <th class="px-4 py-3">Alamat</th>
                                    <th class="px-4 py-3 rounded-tr-lg">Jabatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($posyandu->kader as $kader)
                                <tr class="border-b border-slate-100 hover:bg-slate-50/50 transition">
                                    <td class="px-4 py-3">
                                        @if($kader->foto_kader)
                                            <img src="{{ uploads_asset($kader->foto_kader) }}" alt="" class="w-12 h-12 rounded-full object-cover border-2 border-slate-100" onerror="this.parentElement.innerHTML='<span class=\'flex w-12 h-12 rounded-full bg-slate-200 items-center justify-center text-slate-400 text-xs\'>-</span>'">
                                        @else
                                            <span class="flex w-12 h-12 rounded-full bg-slate-200 items-center justify-center text-slate-400 text-xs">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $kader->nama_kader ?? optional($kader->user)->name ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $kader->tanggal_lahir ? \Carbon\Carbon::parse($kader->tanggal_lahir)->format('d/m/Y') : '-' }}</td>
                                    <td class="px-4 py-3">{{ $kader->alamat_kader ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        @if($kader->jabatan_kader)
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-primary/10 text-primary">{{ $kader->jabatan_kader }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-10 text-slate-500">
                        <i class="fa-solid fa-users text-4xl text-slate-300 mb-2"></i>
                        <p>Belum ada kader terdaftar</p>
                    </div>
                @endif
            </div>

            {{-- Perpustakaan Digital --}}
            @if($posyandu->perpustakaan && $posyandu->perpustakaan->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6" x-data="publicFlipbook()">
                <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center">
                    <i class="fa-solid fa-book-open text-primary mr-2"></i>
                    Perpustakaan Digital
                    <span class="ml-2 text-sm font-normal text-slate-500">({{ $posyandu->perpustakaan->count() }} buku)</span>
                </h2>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    @foreach($posyandu->perpustakaan as $book)
                        <div class="group cursor-pointer" 
                             @click="openBook({
                                 judul: '{{ addslashes($book->judul) }}',
                                 penulis: '{{ addslashes($book->penulis ?? '') }}',
                                 isPdf: {{ $book->file_path ? 'true' : 'false' }},
                                 pdfUrl: '{{ $book->file_path ? uploads_asset($book->file_path) : '' }}',
                                 pages: @js($book->halaman_images ? array_map(function($p) { return uploads_asset($p); }, $book->halaman_images) : [])
                             })">
                            <div class="relative aspect-[3/4] rounded-xl overflow-hidden bg-slate-100 border border-slate-200 shadow-md group-hover:shadow-xl transition-all duration-300">
                                @if($book->cover_image)
                                    <img src="{{ uploads_asset($book->cover_image) }}" alt="{{ $book->judul }}" 
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-primary/20 to-primary/40 flex items-center justify-center">
                                        <i class="fa-solid fa-book text-4xl text-primary/60"></i>
                                    </div>
                                @endif
                                
                                {{-- Overlay --}}
                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <span class="px-4 py-2 bg-white rounded-full text-primary text-sm font-medium">
                                        <i class="fa-solid fa-book-open mr-1"></i> Baca
                                    </span>
                                </div>
                                
                                {{-- Badge kategori --}}
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
                                
                                {{-- Jumlah halaman / PDF --}}
                                <span class="absolute bottom-2 right-2 px-2 py-0.5 text-xs font-medium rounded-full bg-black/60 text-white flex items-center gap-1">
                                    @if($book->file_path)
                                        <i class="fa-solid fa-file-pdf"></i> PDF
                                    @else
                                        {{ $book->jumlah_halaman }} hal
                                    @endif
                                </span>
                            </div>
                            <div class="mt-2 px-1">
                                <h3 class="font-medium text-slate-800 text-sm line-clamp-2">{{ $book->judul }}</h3>
                                @if($book->penulis)
                                    <p class="text-xs text-slate-500 mt-0.5 line-clamp-1">{{ $book->penulis }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Flipbook Modal (Responsive: Scroll on mobile, Flipbook on desktop) --}}
                <div x-show="showFlipbook" x-cloak
                     class="fixed inset-0 z-50 bg-slate-900/95"
                     @keydown.escape.window="closeBook()"
                     @keydown.left.window="!isMobile && prevPage()"
                     @keydown.right.window="!isMobile && nextPage()"
                     @resize.window="checkMobile()">
                    
                    {{-- Header --}}
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

                    {{-- Loading --}}
                    <div x-show="loading" class="h-full flex items-center justify-center">
                        <div class="text-center text-white">
                            <i class="fa-solid fa-spinner text-5xl animate-spin mb-4"></i>
                            <p>Memuat...</p>
                        </div>
                    </div>

                    {{-- Mobile: Scroll View --}}
                    <div x-show="!loading && isMobile" x-cloak class="h-full overflow-y-auto pt-20 pb-4 px-4">
                        <div x-ref="mobileContainer" class="max-w-lg mx-auto space-y-4">
                            {{-- For images --}}
                            <template x-if="!isPdf">
                                <template x-for="(page, index) in pages" :key="index">
                                    <div class="relative bg-white rounded-lg shadow-lg overflow-hidden">
                                        <img :src="page" class="w-full h-auto" alt="">
                                        <div class="absolute bottom-2 right-2 px-2 py-1 bg-black/50 text-white text-xs rounded-full" x-text="(index + 1) + ' / ' + pages.length"></div>
                                    </div>
                                </template>
                            </template>
                            {{-- PDF pages will be rendered dynamically --}}
                        </div>
                    </div>

                    {{-- Desktop: Flipbook View --}}
                    <div x-show="!loading && !isMobile" x-cloak class="h-full flex items-center justify-center px-4 py-20">
                        <div class="relative w-full max-w-5xl h-[70vh]">
                            <div class="relative h-full flex items-center justify-center">
                                {{-- Left Page --}}
                                <div class="relative w-1/2 h-full bg-white shadow-2xl rounded-l-lg overflow-hidden flex items-center justify-center"
                                     :class="(currentPage === 0 && !isPdf) || (isPdf && currentPage <= 1) ? 'bg-slate-100' : ''">
                                    <template x-if="isPdf">
                                        <canvas x-ref="leftCanvas" class="max-w-full max-h-full"></canvas>
                                    </template>
                                    <template x-if="!isPdf && currentPage > 0">
                                        <img :src="pages[currentPage - 1]" class="max-w-full max-h-full object-contain" alt="">
                                    </template>
                                    <div x-show="(!isPdf && currentPage === 0) || (isPdf && currentPage <= 1)" class="absolute inset-0 flex items-center justify-center text-slate-400">
                                        <div class="text-center"><i class="fa-solid fa-book-open text-5xl mb-4"></i><p class="text-sm">Cover</p></div>
                                    </div>
                                    <div class="absolute bottom-4 left-4 px-3 py-1 bg-black/50 text-white text-sm rounded-full" x-show="isPdf ? currentPage > 1 : currentPage > 0">
                                        <span x-text="isPdf ? currentPage - 1 : currentPage"></span>
                                    </div>
                                </div>

                                <div class="w-2 h-full bg-gradient-to-r from-slate-400 via-slate-300 to-slate-400 shadow-inner"></div>

                                {{-- Right Page --}}
                                <div class="relative w-1/2 h-full bg-white shadow-2xl rounded-r-lg overflow-hidden flex items-center justify-center">
                                    <template x-if="isPdf">
                                        <canvas x-ref="rightCanvas" class="max-w-full max-h-full"></canvas>
                                    </template>
                                    <template x-if="!isPdf && currentPage < pages.length">
                                        <img :src="pages[currentPage]" class="max-w-full max-h-full object-contain" alt="">
                                    </template>
                                    <div x-show="!isPdf && currentPage >= pages.length" class="absolute inset-0 flex items-center justify-center text-slate-400 bg-slate-100">
                                        <div class="text-center"><i class="fa-solid fa-circle-check text-5xl mb-4"></i><p class="text-sm">Selesai</p></div>
                                    </div>
                                    <div class="absolute bottom-4 right-4 px-3 py-1 bg-black/50 text-white text-sm rounded-full" x-show="isPdf ? currentPage <= totalPages : currentPage < pages.length">
                                        <span x-text="isPdf ? currentPage : currentPage + 1"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- Navigation Arrows --}}
                            <button @click="prevPage()" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-16 p-4 rounded-full bg-white/10 text-white hover:bg-white/20 transition disabled:opacity-30"
                                    :disabled="isPdf ? currentPage <= 1 : currentPage === 0">
                                <i class="fa-solid fa-chevron-left text-2xl"></i>
                            </button>
                            <button @click="nextPage()" class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-16 p-4 rounded-full bg-white/10 text-white hover:bg-white/20 transition disabled:opacity-30"
                                    :disabled="isPdf ? currentPage >= totalPages : currentPage >= pages.length">
                                <i class="fa-solid fa-chevron-right text-2xl"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Footer (Desktop only) --}}
                    <div x-show="!loading && !isMobile" class="absolute bottom-0 left-0 right-0 z-10 bg-gradient-to-t from-black/80 to-transparent p-4">
                        <div class="max-w-6xl mx-auto">
                            <div class="flex items-center justify-center gap-4 text-white">
                                <span class="text-sm">Halaman</span>
                                <div class="flex items-center gap-2">
                                    <button @click="goToFirst()" class="px-2 py-1 text-sm hover:bg-white/20 rounded"><i class="fa-solid fa-backward-step"></i></button>
                                    <button @click="prevPage()" class="px-2 py-1 text-sm hover:bg-white/20 rounded"><i class="fa-solid fa-chevron-left"></i></button>
                                    <span class="px-4 py-1 bg-white/20 rounded-full text-sm font-medium">
                                        <span x-text="isPdf ? currentPage : currentPage + 1"></span> / <span x-text="isPdf ? totalPages : pages.length"></span>
                                    </span>
                                    <button @click="nextPage()" class="px-2 py-1 text-sm hover:bg-white/20 rounded"><i class="fa-solid fa-chevron-right"></i></button>
                                    <button @click="goToLast()" class="px-2 py-1 text-sm hover:bg-white/20 rounded"><i class="fa-solid fa-forward-step"></i></button>
                                </div>
                            </div>
                            <div x-show="!isPdf" class="mt-4 flex gap-2 justify-center overflow-x-auto pb-2 max-w-full">
                                <template x-for="(page, index) in pages" :key="index">
                                    <button @click="currentPage = index % 2 === 0 ? index : index - 1" 
                                            class="flex-shrink-0 w-12 h-16 rounded overflow-hidden border-2 transition-all hover:scale-110"
                                            :class="index === currentPage || index === currentPage - 1 ? 'border-primary ring-2 ring-primary/50' : 'border-white/30 hover:border-white/60'">
                                        <img :src="page" class="w-full h-full object-cover" alt="">
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PDF.js for public page --}}
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
                                const scale = ((container.clientWidth || 350) * 0.95) / viewport.width;
                                const scaledViewport = page.getViewport({ scale });
                                
                                const wrapper = document.createElement('div');
                                wrapper.className = 'relative bg-white rounded-lg shadow-lg overflow-hidden';
                                const canvas = document.createElement('canvas');
                                canvas.width = scaledViewport.width;
                                canvas.height = scaledViewport.height;
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
                                const scale = Math.min(((container.clientWidth || 400) * 0.9) / viewport.width, ((container.clientHeight || 500) * 0.9) / viewport.height);
                                const scaledViewport = page.getViewport({ scale });
                                canvas.width = scaledViewport.width;
                                canvas.height = scaledViewport.height;
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
                    }
                }
            </script>
            @endif

            <div class="text-center pt-4">
                <a href="{{ route('index') }}#posyandu" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-primary text-white font-medium hover:bg-primaryDark transition shadow-lg shadow-primary/20">
                    <i class="fa-solid fa-arrow-left"></i>
                    Kembali ke Daftar Posyandu
                </a>
            </div>
        </div>
    </main>

    <footer class="border-t border-slate-200 bg-white py-6 text-center text-sm text-slate-500">
        © {{ date('Y') }} Posyandu Karanggan. All rights reserved.
    </footer>
</body>
</html>
