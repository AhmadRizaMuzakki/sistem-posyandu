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

            {{-- Gambar Posyandu (di atas peta) --}}
            @if($posyandu->gambar_posyandu)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center">
                    <i class="fa-solid fa-image text-primary mr-2"></i>
                    Gambar Posyandu
                </h2>
                <div class="rounded-xl overflow-hidden border border-slate-200">
                    <img src="{{ uploads_asset($posyandu->gambar_posyandu) }}" alt="Gambar {{ $posyandu->nama_posyandu }}" class="w-full h-auto max-h-[400px] object-cover" loading="lazy" onerror="this.style.display='none';">
                </div>
            </div>
            @endif

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

            {{-- Galeri Posyandu (foto dari menu Galeri posyandu) --}}
            @php $galeriKegiatan = $posyandu->galeri ?? collect(); @endphp
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center">
                    <i class="fa-solid fa-images text-primary mr-2"></i>
                    Galeri Kegiatan {{ $posyandu->nama_posyandu }}
                </h2>
                @if($galeriKegiatan->isNotEmpty())
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 sm:gap-3">
                        @foreach($galeriKegiatan as $item)
                            @php $imgUrl = uploads_asset($item->path); @endphp
                            <a href="{{ $imgUrl }}" target="_blank" rel="noopener" class="block group relative aspect-square rounded-lg overflow-hidden bg-slate-100 shadow hover:shadow-md transition-all">
                                <img src="{{ $imgUrl }}" alt="{{ $item->caption ?? 'Galeri kegiatan' }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling && (this.nextElementSibling.style.display='flex');">
                                <span class="absolute inset-0 flex items-center justify-center text-slate-400 bg-slate-100" style="display:none"><i class="fa-solid fa-image text-2xl"></i></span>
                                @if($item->caption)
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-2">
                                        <p class="text-white text-xs font-medium line-clamp-2">{{ $item->caption }}</p>
                                    </div>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-10 text-slate-500">
                        <i class="fa-solid fa-images text-4xl text-slate-300 mb-2"></i>
                        <p class="text-sm">Belum ada foto galeri. Foto yang diunggah dari menu Galeri posyandu akan tampil di sini.</p>
                    </div>
                @endif
            </div>

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
