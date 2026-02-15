<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri Kegiatan - Posyandu Karanggan</title>
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
                    <div class="w-9 h-9 bg-primary rounded-full flex items-center justify-center text-white overflow-hidden">
                        <img src="{{ asset('images/home.jpeg') }}" alt="Logo" class="w-full h-full object-cover">
                    </div>
                    <span class="font-bold text-slate-800">Posyandu Karanggan</span>
                </a>
            </div>
        </div>
    </nav>

    <main class="pt-24 pb-16 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

            <div class="text-center mb-12">
                <span class="inline-block py-1 px-3 rounded-full bg-teal-100 text-primary text-sm font-semibold mb-4">Momen Berharga</span>
                <h1 class="text-4xl font-bold text-slate-900 mb-4">Galeri Kegiatan</h1>
                <p class="text-slate-600 max-w-2xl mx-auto text-lg">Dokumentasi kegiatan dan momen berharga dari berbagai Posyandu Karanggan.</p>
            </div>

            @if($galeriKoleksi->isNotEmpty())
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    @foreach($galeriKoleksi as $item)
                        @php $imgUrl = uploads_asset($item->path); @endphp
                        <a href="{{ $imgUrl }}" target="_blank" rel="noopener" class="block group relative aspect-square rounded-xl overflow-hidden bg-slate-100 shadow-md hover:shadow-xl transition-all duration-300">
                            <img src="{{ $imgUrl }}" alt="{{ $item->caption ?? 'Galeri kegiatan' }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling && (this.nextElementSibling.style.display='flex');">
                            <span class="absolute inset-0 flex items-center justify-center text-slate-400 bg-slate-100" style="display:none"><i class="fa-solid fa-image text-3xl"></i></span>
                            @if($item->caption)
                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-3">
                                    <p class="text-white text-sm font-medium line-clamp-2">{{ $item->caption }}</p>
                                </div>
                            @endif
                            @if($item->posyandu)
                                <span class="absolute top-2 left-2 px-2 py-0.5 bg-black/50 text-white text-xs rounded">{{ $item->posyandu->nama_posyandu }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>

                <div class="mt-12 flex justify-center">
                    {{ $galeriKoleksi->links() }}
                </div>
            @else
                <div class="text-center py-20 bg-white rounded-2xl border-2 border-dashed border-slate-300">
                    <i class="fa-solid fa-images text-5xl text-slate-400 mb-4"></i>
                    <p class="text-slate-600 font-medium mb-2">Belum ada foto galeri</p>
                    <p class="text-slate-400 text-sm mb-6">Foto akan tampil di sini setelah diunggah oleh admin Posyandu.</p>
                    <a href="{{ route('index') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-primary text-white font-medium hover:bg-primaryDark transition">
                        <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
                    </a>
                </div>
            @endif
        </div>
    </main>

    <footer class="border-t border-slate-200 bg-white py-6 text-center text-sm text-slate-500">
        © {{ date('Y') }} Posyandu Karanggan. All rights reserved.
    </footer>
</body>
</html>
