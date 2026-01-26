<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $posyandu ? $posyandu->nama_posyandu : 'Posyandu' }} - Sehat Bersama, Tumbuh Bahagia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: '#0D9488', // Emerald Teal
                        primaryDark: '#0F766E',
                        secondary: '#FDBA74', // Soft Orange
                        lightBg: '#F0FDFA', // Very Light Teal
                    }
                }
            }
        }
    </script>
    <style>
        /* Custom Animations for UX Delight */
        .fade-in-up { animation: fadeInUp 0.8s ease-out; }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Custom Pagination Styling */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .pagination > * {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .pagination a, .pagination span {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        .pagination a {
            background: white;
            border: 1px solid #e2e8f0;
            color: #475569;
            text-decoration: none;
        }
        .pagination a:hover {
            background: #0D9488;
            color: white;
            border-color: #0D9488;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(13, 148, 136, 0.2);
        }
        .pagination .active span {
            background: #0D9488;
            color: white;
            border-color: #0D9488;
            box-shadow: 0 2px 4px rgba(13, 148, 136, 0.2);
        }
        .pagination .disabled span {
            background: #f1f5f9;
            color: #94a3b8;
            border-color: #e2e8f0;
            cursor: not-allowed;
        }
    </style>
</head>
<body class="font-sans text-slate-700 antialiased bg-white">

    <nav class="fixed w-full z-50 bg-white/90 backdrop-blur-md shadow-sm transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center text-white font-bold text-xl">
                        <i class="fa-solid fa-heart-pulse"></i>
                    </div>
                    <a href="#beranda" class="font-bold text-xl tracking-tight text-slate-800 hover:text-primary transition">{{ $posyandu ? $posyandu->nama_posyandu : 'Posyandu' }}</a>
                </div>
                <div class="hidden md:flex space-x-8">
                    <a href="#beranda" class="text-slate-600 hover:text-primary font-medium transition">Beranda</a>
                    <a href="#layanan" class="text-slate-600 hover:text-primary font-medium transition">Layanan</a>
                    <a href="#jadwal" class="text-slate-600 hover:text-primary font-medium transition">Jadwal</a>
                    <a href="#galeri" class="text-slate-600 hover:text-primary font-medium transition">Galeri</a>
                </div>
                <div class="hidden md:flex items-center gap-4">
                    @if (Route::has('login'))
                        @auth
                            @php
                                $dashboardRoute = '/';
                                if (Auth::user()->hasRole('superadmin')) {
                                    $dashboardRoute = route('admin.dashboard');
                                } elseif (Auth::user()->hasRole('adminPosyandu')) {
                                    $dashboardRoute = route('adminPosyandu.dashboard');
                                } elseif (Auth::user()->hasRole('orangtua')) {
                                    $dashboardRoute = route('orangtua.dashboard');
                                }
                            @endphp
                            <a href="{{ $dashboardRoute }}" class="text-slate-600 hover:text-primary font-medium transition">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-slate-600 hover:text-primary font-medium transition">Masuk</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-6 py-2 border border-transparent text-sm font-medium rounded-full text-white bg-primary hover:bg-primaryDark shadow-lg shadow-primary/30 transition hover:-translate-y-0.5">
                                    Daftar
                                </a>
                            @endif
                        @endauth
                    @else
                        <a href="#kontak" class="inline-flex items-center justify-center px-6 py-2 border border-transparent text-sm font-medium rounded-full text-white bg-primary hover:bg-primaryDark shadow-lg shadow-primary/30 transition hover:-translate-y-0.5">
                            Hubungi Kader
                        </a>
                    @endif
                </div>
                <button class="md:hidden text-slate-600 hover:text-primary transition" id="mobile-menu-btn">
                    <i class="fa-solid fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div class="hidden md:hidden bg-white border-t border-slate-100 shadow-lg" id="mobile-menu">
            <div class="px-4 py-6 space-y-4">
                <a href="#beranda" class="block text-slate-600 hover:text-primary font-medium py-2 transition">Beranda</a>
                <a href="#layanan" class="block text-slate-600 hover:text-primary font-medium py-2 transition">Layanan</a>
                <a href="#jadwal" class="block text-slate-600 hover:text-primary font-medium py-2 transition">Jadwal</a>
                <a href="#galeri" class="block text-slate-600 hover:text-primary font-medium py-2 transition">Galeri</a>
                <div class="pt-4 border-t border-slate-100 space-y-3">
                    @if (Route::has('login'))
                        @auth
                            @php
                                $dashboardRoute = '/';
                                if (Auth::user()->hasRole('superadmin')) {
                                    $dashboardRoute = route('admin.dashboard');
                                } elseif (Auth::user()->hasRole('adminPosyandu')) {
                                    $dashboardRoute = route('adminPosyandu.dashboard');
                                } elseif (Auth::user()->hasRole('orangtua')) {
                                    $dashboardRoute = route('orangtua.dashboard');
                                }
                            @endphp
                            <a href="{{ $dashboardRoute }}" class="block text-slate-600 hover:text-primary font-medium py-2 transition">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="block text-slate-600 hover:text-primary font-medium py-2 transition">Masuk</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="block w-full text-center px-6 py-3 rounded-full text-white bg-primary hover:bg-primaryDark font-medium transition">
                                    Daftar
                                </a>
                            @endif
                        @endauth
                    @else
                        <a href="#kontak" class="block w-full text-center px-6 py-3 rounded-full text-white bg-primary hover:bg-primaryDark font-medium transition">
                            Hubungi Kader
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <section id="beranda" class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1576765608535-5f04d1e3f289?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80" alt="Background Posyandu" class="w-full h-full object-cover opacity-10">
            <div class="absolute inset-0 bg-gradient-to-t from-white via-transparent to-white"></div>
        </div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <span class="inline-block py-1 px-3 rounded-full bg-teal-100 text-primary text-sm font-semibold mb-6 fade-in-up">
                ? Melayani Sepenuh Hati untuk Keluarga Sehat
            </span>
            <h1 class="text-4xl md:text-6xl font-extrabold text-slate-900 tracking-tight mb-6 leading-tight fade-in-up" style="animation-delay: 0.1s;">
                Mewujudkan Generasi Sehat <br>
                <span class="text-primary">{{ $posyandu ? 'Bersama ' . $posyandu->nama_posyandu : 'Bersama Posyandu' }}</span>
            </h1>
            <p class="mt-4 max-w-3xl mx-auto text-xl text-slate-600 mb-10 fade-in-up" style="animation-delay: 0.2s;">
                {{ $posyandu ? $posyandu->nama_posyandu : 'Posyandu' }} hadir sebagai mitra terpercaya keluarga dalam menjaga kesehatan ibu, anak, dan lansia. Dengan pelayanan terpadu, profesional, dan ramah, kami berkomitmen mewujudkan masyarakat yang sehat dan produktif.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4 fade-in-up" style="animation-delay: 0.3s;">
                <a href="#jadwal" class="px-8 py-4 rounded-full bg-primary text-white font-bold text-lg shadow-xl shadow-teal-500/30 hover:bg-primaryDark transition hover:-translate-y-1">
                    <i class="fa-solid fa-calendar-check mr-2"></i> Cek Jadwal Imunisasi
                </a>
                <a href="#layanan" class="px-8 py-4 rounded-full bg-white text-slate-700 font-bold text-lg border-2 border-slate-200 hover:border-primary hover:text-primary transition">
                    <i class="fa-solid fa-heart-pulse mr-2"></i> Lihat Program Layanan
                </a>
            </div>
        </div>
    </section>

    <section class="py-10 bg-primary mx-4 md:mx-10 rounded-3xl shadow-2xl relative -mt-10 z-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center text-white">
                <div>
                    <div class="text-4xl font-bold mb-1">{{ number_format($totalBayiBalita ?? 0) }}{{ ($totalBayiBalita ?? 0) > 0 ? '+' : '' }}</div>
                    <div class="text-teal-100 text-sm font-medium">Balita Sehat</div>
                </div>
                <div>
                    <div class="text-4xl font-bold mb-1">{{ number_format($totalIbuHamil ?? 0) }}{{ ($totalIbuHamil ?? 0) > 0 ? '+' : '' }}</div>
                    <div class="text-teal-100 text-sm font-medium">Ibu Hamil</div>
                </div>
                <div>
                    <div class="text-4xl font-bold mb-1">{{ number_format($totalKader ?? 0) }}</div>
                    <div class="text-teal-100 text-sm font-medium">Kader Terlatih</div>
                </div>
                <div>
                    <div class="text-4xl font-bold mb-1">{{ $cakupanImunisasi ?? 0 }}%</div>
                    <div class="text-teal-100 text-sm font-medium">Cakupan Imunisasi</div>
                </div>
            </div>
        </div>
    </section>

    <section id="layanan" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="inline-block py-1 px-3 rounded-full bg-teal-100 text-primary text-sm font-semibold mb-4">Layanan Terpadu</span>
                <h2 class="text-4xl md:text-5xl font-bold text-slate-900 mb-4">Layanan Unggulan Kami</h2>
                <p class="text-slate-600 max-w-3xl mx-auto text-lg">Kami menyediakan pelayanan kesehatan terpadu dengan standar tinggi, didukung oleh kader terlatih dan fasilitas yang memadai untuk kenyamanan Anda.</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                <div class="group bg-gradient-to-br from-white to-teal-50/30 p-8 rounded-2xl border border-slate-100 shadow-lg hover:shadow-2xl hover:shadow-primary/20 transition-all duration-300 hover:-translate-y-2">
                    <div class="w-16 h-16 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600 text-2xl mb-6 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <i class="fa-solid fa-baby-carriage"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Kesehatan Ibu & Anak</h3>
                    <p class="text-slate-600 leading-relaxed mb-4">Pemantauan tumbuh kembang balita secara berkala dengan sistem pencatatan digital yang akurat.</p>
                    <ul class="space-y-2 text-sm text-slate-600">
                        <li class="flex items-start"><i class="fa-solid fa-check text-primary mr-2 mt-1"></i> Penimbangan & Pengukuran Tinggi Badan</li>
                        <li class="flex items-start"><i class="fa-solid fa-check text-primary mr-2 mt-1"></i> Pemberian Makanan Tambahan (PMT) Bergizi</li>
                        <li class="flex items-start"><i class="fa-solid fa-check text-primary mr-2 mt-1"></i> Konsultasi Gizi & ASI Eksklusif</li>
                        <li class="flex items-start"><i class="fa-solid fa-check text-primary mr-2 mt-1"></i> Pencatatan dalam Buku KIA</li>
                    </ul>
                </div>

                <div class="group bg-gradient-to-br from-white to-teal-50/30 p-8 rounded-2xl border border-slate-100 shadow-lg hover:shadow-2xl hover:shadow-primary/20 transition-all duration-300 hover:-translate-y-2">
                    <div class="w-16 h-16 bg-teal-100 rounded-xl flex items-center justify-center text-primary text-2xl mb-6 group-hover:bg-primary group-hover:text-white transition-colors">
                        <i class="fa-solid fa-syringe"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Imunisasi Lengkap</h3>
                    <p class="text-slate-600 leading-relaxed mb-4">Program imunisasi dasar lengkap sesuai jadwal yang direkomendasikan Kementerian Kesehatan.</p>
                    <ul class="space-y-2 text-sm text-slate-600">
                        <li class="flex items-start"><i class="fa-solid fa-check text-primary mr-2 mt-1"></i> Imunisasi Dasar Lengkap (BCG, DPT, Polio, dll)</li>
                        <li class="flex items-start"><i class="fa-solid fa-check text-primary mr-2 mt-1"></i> Pemberian Vitamin A (2x/tahun)</li>
                        <li class="flex items-start"><i class="fa-solid fa-check text-primary mr-2 mt-1"></i> Imunisasi Lanjutan (Booster)</li>
                        <li class="flex items-start"><i class="fa-solid fa-check text-primary mr-2 mt-1"></i> Pencatatan Vaksin Digital</li>
                    </ul>
                </div>

                <div class="group bg-gradient-to-br from-white to-teal-50/30 p-8 rounded-2xl border border-slate-100 shadow-lg hover:shadow-2xl hover:shadow-primary/20 transition-all duration-300 hover:-translate-y-2">
                    <div class="w-16 h-16 bg-orange-100 rounded-xl flex items-center justify-center text-orange-500 text-2xl mb-6 group-hover:bg-orange-500 group-hover:text-white transition-colors">
                        <i class="fa-solid fa-person-cane"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Posyandu Lansia</h3>
                    <p class="text-slate-600 leading-relaxed mb-4">Pelayanan kesehatan khusus untuk lansia dengan pendekatan holistik dan ramah.</p>
                    <ul class="space-y-2 text-sm text-slate-600">
                        <li class="flex items-start"><i class="fa-solid fa-check text-primary mr-2 mt-1"></i> Pemeriksaan Tekanan Darah & Gula Darah</li>
                        <li class="flex items-start"><i class="fa-solid fa-check text-primary mr-2 mt-1"></i> Senam Lansia & Aktivitas Fisik</li>
                        <li class="flex items-start"><i class="fa-solid fa-check text-primary mr-2 mt-1"></i> Konsultasi Kesehatan & Gizi</li>
                        <li class="flex items-start"><i class="fa-solid fa-check text-primary mr-2 mt-1"></i> Pemeriksaan Kesehatan Berkala</li>
                    </ul>
                </div>

                <div class="group bg-gradient-to-br from-white to-teal-50/30 p-8 rounded-2xl border border-slate-100 shadow-lg hover:shadow-2xl hover:shadow-primary/20 transition-all duration-300 hover:-translate-y-2">
                    <div class="w-16 h-16 bg-purple-100 rounded-xl flex items-center justify-center text-purple-600 text-2xl mb-6 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                        <i class="fa-solid fa-user-doctor"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Konsultasi Kesehatan</h3>
                    <p class="text-slate-600 leading-relaxed mb-4">Layanan konsultasi kesehatan gratis dengan kader terlatih dan petugas kesehatan.</p>
                    <ul class="space-y-2 text-sm text-slate-600">
                        <li class="flex items-start"><i class="fa-solid fa-check text-primary mr-2 mt-1"></i> Konsultasi Gizi & Pola Makan</li>
                        <li class="flex items-start"><i class="fa-solid fa-check text-primary mr-2 mt-1"></i> Edukasi Kesehatan Keluarga</li>
                        <li class="flex items-start"><i class="fa-solid fa-check text-primary mr-2 mt-1"></i> Pencegahan Penyakit Menular</li>
                    </ul>
                </div>

                <div class="group bg-gradient-to-br from-white to-teal-50/30 p-8 rounded-2xl border border-slate-100 shadow-lg hover:shadow-2xl hover:shadow-primary/20 transition-all duration-300 hover:-translate-y-2">
                    <div class="w-16 h-16 bg-green-100 rounded-xl flex items-center justify-center text-green-600 text-2xl mb-6 group-hover:bg-green-600 group-hover:text-white transition-colors">
                        <i class="fa-solid fa-house-medical"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Kunjungan Rumah</h3>
                    <p class="text-slate-600 leading-relaxed mb-4">Layanan kunjungan rumah oleh kader untuk keluarga yang membutuhkan perhatian khusus.</p>
                    <ul class="space-y-2 text-sm text-slate-600">
                        <li class="flex items-start"><i class="fa-solid fa-check text-primary mr-2 mt-1"></i> Kunjungan Ibu Hamil & Nifas</li>
                        <li class="flex items-start"><i class="fa-solid fa-check text-primary mr-2 mt-1"></i> Pemantauan Balita Berisiko</li>
                        <li class="flex items-start"><i class="fa-solid fa-check text-primary mr-2 mt-1"></i> Pendampingan Keluarga</li>
                    </ul>
                </div>

                <div class="group bg-gradient-to-br from-white to-teal-50/30 p-8 rounded-2xl border border-slate-100 shadow-lg hover:shadow-2xl hover:shadow-primary/20 transition-all duration-300 hover:-translate-y-2">
                    <div class="w-16 h-16 bg-pink-100 rounded-xl flex items-center justify-center text-pink-600 text-2xl mb-6 group-hover:bg-pink-600 group-hover:text-white transition-colors">
                        <i class="fa-solid fa-book-medical"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Edukasi & Penyuluhan</h3>
                    <p class="text-slate-600 leading-relaxed mb-4">Program edukasi kesehatan untuk meningkatkan pengetahuan masyarakat tentang kesehatan.</p>
                    <ul class="space-y-2 text-sm text-slate-600">
                        <li class="flex items-start"><i class="fa-solid fa-check text-primary mr-2 mt-1"></i> Penyuluhan Gizi Seimbang</li>
                        <li class="flex items-start"><i class="fa-solid fa-check text-primary mr-2 mt-1"></i> Pencegahan Stunting</li>
                        <li class="flex items-start"><i class="fa-solid fa-check text-primary mr-2 mt-1"></i> Kesehatan Reproduksi</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 bg-lightBg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center gap-12">
                <div class="md:w-1/2">
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl">
                        <img src="https://images.unsplash.com/photo-1579684385180-1ea55c938de4?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Kegiatan Posyandu" class="w-full object-cover hover:scale-105 transition duration-700">
                    </div>
                </div>
                <div class="md:w-1/2">
                    <span class="inline-block py-1 px-3 rounded-full bg-primary/10 text-primary text-sm font-semibold mb-4">Program Prioritas</span>
                    <h2 class="text-4xl font-bold text-slate-900 mb-6">Pencegahan Stunting Sejak Dini</h2>
                    <p class="text-slate-600 mb-6 text-lg leading-relaxed">
                        Posyandu Karanggan berkomitmen penuh dalam program nasional pencegahan stunting. Melalui pendekatan terpadu yang meliputi edukasi gizi, pemantauan intensif, dan intervensi tepat waktu, kami memastikan setiap anak memiliki kesempatan tumbuh optimal sesuai potensi genetiknya.
                    </p>
                    <div class="space-y-4 mb-6">
                        <div class="flex items-start">
                            <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-white mr-4 flex-shrink-0">
                                <i class="fa-solid fa-check text-sm"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-900 mb-1">Konsultasi Gizi Gratis</h4>
                                <p class="text-slate-600 text-sm">Konsultasi dengan ahli gizi untuk merencanakan menu sehat dan seimbang sesuai kebutuhan anak.</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-white mr-4 flex-shrink-0">
                                <i class="fa-solid fa-check text-sm"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-900 mb-1">Menu PMT Bervariasi & Bergizi</h4>
                                <p class="text-slate-600 text-sm">Pemberian Makanan Tambahan dengan menu yang bervariasi, bergizi, dan disesuaikan dengan kebutuhan gizi anak.</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-white mr-4 flex-shrink-0">
                                <i class="fa-solid fa-check text-sm"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-900 mb-1">Kunjungan Rumah Kader</h4>
                                <p class="text-slate-600 text-sm">Pemantauan langsung di rumah untuk keluarga dengan balita berisiko stunting atau gizi kurang.</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-white mr-4 flex-shrink-0">
                                <i class="fa-solid fa-check text-sm"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-900 mb-1">Pemantauan Tumbuh Kembang</h4>
                                <p class="text-slate-600 text-sm">Pencatatan dan pemantauan pertumbuhan anak secara berkala untuk deteksi dini masalah pertumbuhan.</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-4 border-l-4 border-primary">
                        <p class="text-sm text-slate-600"><strong class="text-primary">Fakta:</strong> Stunting dapat dicegah dengan intervensi gizi yang tepat pada 1000 hari pertama kehidupan (dari kehamilan hingga anak berusia 2 tahun).</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="jadwal" class="py-20 bg-gradient-to-b from-white via-lightBg to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <span class="inline-block py-2 px-4 rounded-full bg-gradient-to-r from-primary/10 to-teal-100 text-primary text-sm font-bold mb-4 shadow-sm">
                    <i class="fa-solid fa-calendar-check mr-2"></i>Jadwal Kegiatan
                </span>
                <h2 class="text-4xl md:text-5xl font-extrabold text-slate-900 mb-4 bg-gradient-to-r from-slate-900 to-slate-700 bg-clip-text text-transparent">
                    Jadwal Kegiatan Posyandu
                </h2>
                <p class="text-slate-600 max-w-2xl mx-auto text-lg leading-relaxed">
                    Jangan lewatkan jadwal penimbangan dan imunisasi. Pastikan membawa buku KIA/KMS dan kartu imunisasi anak Anda.
                </p>
            </div>

            {{-- Filter dan Search Bar --}}
            <div class="mb-8 bg-white rounded-2xl p-6 shadow-lg border border-slate-200">
                <form method="GET" action="#jadwal" class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <input type="text" name="search" value="{{ $search ?? '' }}" 
                                   placeholder="Cari acara, tempat, atau deskripsi..." 
                                   class="w-full pl-12 pr-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition">
                            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        </div>
                    </div>
                    <div class="md:w-64">
                        <select name="filter_posyandu" 
                                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition appearance-none bg-white">
                            <option value="">Semua Posyandu</option>
                            @foreach($daftarPosyandu ?? [] as $p)
                                <option value="{{ $p->id_posyandu }}" {{ ($filterPosyandu ?? '') == $p->id_posyandu ? 'selected' : '' }}>
                                    {{ $p->nama_posyandu }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" 
                            class="px-6 py-3 bg-primary text-white rounded-xl font-semibold hover:bg-primaryDark transition shadow-md hover:shadow-lg flex items-center justify-center">
                        <i class="fa-solid fa-filter mr-2"></i>Filter
                    </button>
                    @if($search || $filterPosyandu)
                        <a href="#jadwal" 
                           class="px-6 py-3 bg-slate-200 text-slate-700 rounded-xl font-semibold hover:bg-slate-300 transition flex items-center justify-center">
                            <i class="fa-solid fa-xmark mr-2"></i>Reset
                        </a>
                    @endif
                </form>
                @if($acaraList && $acaraList->total() > 0)
                    <div class="mt-4 text-sm text-slate-600">
                        <i class="fa-solid fa-info-circle mr-1"></i>
                        Menampilkan <strong>{{ $acaraList->firstItem() }}</strong> - <strong>{{ $acaraList->lastItem() }}</strong> dari <strong>{{ $acaraList->total() }}</strong> acara
                    </div>
                @endif
            </div>
            
            @if($acaraList && $acaraList->isNotEmpty())
                @php
                    // Group acara berdasarkan status (hanya untuk halaman saat ini karena pagination)
                    $acaraMendatang = [];
                    $acaraHariIni = [];
                    $acaraLewat = [];
                    
                    foreach($acaraList as $acara) {
                        $tanggal = \Carbon\Carbon::parse($acara->tanggal);
                        if ($tanggal->isToday()) {
                            $acaraHariIni[] = $acara;
                        } elseif ($tanggal->isPast() && !$tanggal->isToday()) {
                            $acaraLewat[] = $acara;
                        } else {
                            $acaraMendatang[] = $acara;
                        }
                    }
                @endphp

                @if(!empty($acaraHariIni))
                    <div class="mb-8">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="h-1 w-12 bg-gradient-to-r from-primary to-primaryDark rounded-full"></div>
                            <h3 class="text-xl font-bold text-slate-900 flex items-center">
                                <i class="fa-solid fa-star text-yellow-500 mr-2 animate-pulse"></i>
                                Acara Hari Ini
                                @if(count($acaraHariIni) > 9)
                                    <span class="ml-2 text-sm font-normal text-slate-500">(Menampilkan 9 dari {{ count($acaraHariIni) }})</span>
                                @endif
                            </h3>
                        </div>
                        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach(array_slice($acaraHariIni, 0, 9) as $acara)
                                @php
                                    $tanggal = \Carbon\Carbon::parse($acara->tanggal);
                                @endphp
                                <div class="bg-gradient-to-br from-primary/5 via-white to-teal-50 rounded-2xl p-6 shadow-xl border-2 border-primary/20 hover:border-primary/40 transition-all duration-300 hover:-translate-y-1 group relative overflow-hidden">
                                    <div class="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
                                    <div class="relative z-10">
                                        <div class="flex items-start justify-between mb-4">
                                            <div class="w-14 h-14 bg-gradient-to-br from-primary to-primaryDark rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                                                <i class="fa-solid fa-calendar-days text-white text-xl"></i>
                                            </div>
                                            <span class="text-xs font-bold text-white bg-gradient-to-r from-yellow-400 to-orange-500 px-3 py-1.5 rounded-full shadow-md">
                                                <i class="fa-solid fa-fire mr-1"></i>Hari Ini
                                            </span>
                                        </div>
                                        <h3 class="text-xl font-bold text-slate-900 mb-3 group-hover:text-primary transition-colors">{{ $acara->nama_kegiatan }}</h3>
                                        @if($acara->posyandu)
                                            <div class="inline-flex items-center px-3 py-1 bg-primary/10 rounded-lg mb-3">
                                                <i class="fa-solid fa-hospital text-primary text-xs mr-2"></i>
                                                <span class="text-sm text-primary font-semibold">{{ $acara->posyandu->nama_posyandu }}</span>
                                            </div>
                                        @endif
                                        <div class="space-y-2.5 text-slate-700 mb-4">
                                            <div class="flex items-center text-sm font-medium">
                                                <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center mr-3">
                                                    <i class="fa-solid fa-calendar text-primary text-xs"></i>
                                                </div>
                                                <span>{{ $tanggal->locale('id')->translatedFormat('l, d F Y') }}</span>
                                            </div>
                                            @if($acara->tempat)
                                                <div class="flex items-center text-sm font-medium">
                                                    <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center mr-3">
                                                        <i class="fa-solid fa-location-dot text-primary text-xs"></i>
                                                    </div>
                                                    <span>{{ $acara->tempat }}</span>
                                                </div>
                                            @endif
                                            @if($acara->jam_mulai || $acara->jam_selesai)
                                                <div class="flex items-center text-sm font-medium">
                                                    <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center mr-3">
                                                        <i class="fa-solid fa-clock text-primary text-xs"></i>
                                                    </div>
                                                    <span>
                                                        @if($acara->jam_mulai && $acara->jam_selesai)
                                                            {{ \Carbon\Carbon::parse($acara->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($acara->jam_selesai)->format('H:i') }} WIB
                                                        @elseif($acara->jam_mulai)
                                                            {{ \Carbon\Carbon::parse($acara->jam_mulai)->format('H:i') }} WIB
                                                        @elseif($acara->jam_selesai)
                                                            Sampai {{ \Carbon\Carbon::parse($acara->jam_selesai)->format('H:i') }} WIB
                                                        @endif
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                        @if($acara->deskripsi)
                                            <div class="pt-4 border-t border-slate-200">
                                                <p class="text-sm text-slate-600 leading-relaxed">{{ Str::limit($acara->deskripsi, 120) }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(!empty($acaraMendatang))
                    <div class="mb-8">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="h-1 w-12 bg-gradient-to-r from-green-500 to-emerald-500 rounded-full"></div>
                            <h3 class="text-xl font-bold text-slate-900 flex items-center">
                                <i class="fa-solid fa-arrow-up text-green-500 mr-2"></i>
                                Acara Mendatang
                            </h3>
                        </div>
                        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($acaraMendatang as $acara)
                                @php
                                    $tanggal = \Carbon\Carbon::parse($acara->tanggal);
                                    $now = \Carbon\Carbon::now()->startOfDay();
                                    $tanggalStart = $tanggal->copy()->startOfDay();
                                    $daysUntil = abs($now->diffInDays($tanggalStart, false));
                                    
                                    // Format countdown yang lebih user-friendly
                                    $countdownText = '';
                                    if ($daysUntil == 0) {
                                        $countdownText = 'Besok';
                                    } elseif ($daysUntil == 1) {
                                        $countdownText = '1 hari lagi';
                                    } elseif ($daysUntil < 7) {
                                        $countdownText = $daysUntil . ' hari lagi';
                                    } elseif ($daysUntil < 14) {
                                        $countdownText = '1 minggu lagi';
                                    } elseif ($daysUntil < 30) {
                                        $weeks = floor($daysUntil / 7);
                                        $countdownText = $weeks . ' minggu lagi';
                                    } elseif ($daysUntil < 60) {
                                        $countdownText = '1 bulan lagi';
                                    } else {
                                        $months = floor($daysUntil / 30);
                                        $countdownText = $months . ' bulan lagi';
                                    }
                                @endphp
                                <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200 hover:shadow-2xl hover:border-primary/30 transition-all duration-300 hover:-translate-y-1 group">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="w-12 h-12 bg-gradient-to-br from-green-100 to-emerald-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                            <i class="fa-solid fa-calendar-days text-green-600 text-lg"></i>
                                        </div>
                                        <span class="text-xs font-bold text-green-700 bg-green-50 px-3 py-1 rounded-full border border-green-200">
                                            {{ $countdownText }}
                                        </span>
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-900 mb-2 group-hover:text-primary transition-colors">{{ $acara->nama_kegiatan }}</h3>
                                    @if($acara->posyandu)
                                        <div class="inline-flex items-center px-2.5 py-1 bg-primary/5 rounded-lg mb-3">
                                            <i class="fa-solid fa-hospital text-primary text-xs mr-1.5"></i>
                                            <span class="text-xs text-primary font-medium">{{ $acara->posyandu->nama_posyandu }}</span>
                                        </div>
                                    @endif
                                    <div class="space-y-2 text-slate-600 mb-4">
                                        <p class="flex items-center text-sm">
                                            <i class="fa-solid fa-calendar text-primary mr-2 text-xs"></i> 
                                            {{ $tanggal->locale('id')->translatedFormat('l, d F Y') }}
                                        </p>
                                        @if($acara->tempat)
                                            <p class="flex items-center text-sm">
                                                <i class="fa-solid fa-location-dot text-primary mr-2 text-xs"></i> 
                                                {{ $acara->tempat }}
                                            </p>
                                        @endif
                                        @if($acara->jam_mulai || $acara->jam_selesai)
                                            <p class="flex items-center text-sm">
                                                <i class="fa-solid fa-clock text-primary mr-2 text-xs"></i> 
                                                @if($acara->jam_mulai && $acara->jam_selesai)
                                                    {{ \Carbon\Carbon::parse($acara->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($acara->jam_selesai)->format('H:i') }} WIB
                                                @elseif($acara->jam_mulai)
                                                    {{ \Carbon\Carbon::parse($acara->jam_mulai)->format('H:i') }} WIB
                                                @elseif($acara->jam_selesai)
                                                    Sampai {{ \Carbon\Carbon::parse($acara->jam_selesai)->format('H:i') }} WIB
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                    @if($acara->deskripsi)
                                        <div class="pt-3 border-t border-slate-100">
                                            <p class="text-xs text-slate-500 leading-relaxed">{{ Str::limit($acara->deskripsi, 100) }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(!empty($acaraLewat))
                    <div class="mb-8">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="h-1 w-12 bg-gradient-to-r from-slate-300 to-slate-400 rounded-full"></div>
                            <h3 class="text-xl font-bold text-slate-700 flex items-center">
                                <i class="fa-solid fa-history text-slate-400 mr-2"></i>
                                Acara Terlampaui
                            </h3>
                        </div>
                        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($acaraLewat as $acara)
                                @php
                                    $tanggal = \Carbon\Carbon::parse($acara->tanggal);
                                @endphp
                                <div class="bg-white rounded-2xl p-6 shadow-md border border-slate-200 opacity-75 hover:opacity-100 transition-all duration-300">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center">
                                            <i class="fa-solid fa-calendar-days text-slate-400 text-lg"></i>
                                        </div>
                                        <span class="text-xs font-medium text-slate-500 bg-slate-50 px-3 py-1 rounded-full border border-slate-200">
                                            Selesai
                                        </span>
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-600 mb-2">{{ $acara->nama_kegiatan }}</h3>
                                    @if($acara->posyandu)
                                        <div class="inline-flex items-center px-2.5 py-1 bg-slate-50 rounded-lg mb-3">
                                            <i class="fa-solid fa-hospital text-slate-400 text-xs mr-1.5"></i>
                                            <span class="text-xs text-slate-500 font-medium">{{ $acara->posyandu->nama_posyandu }}</span>
                                        </div>
                                    @endif
                                    <div class="space-y-2 text-slate-500 mb-4">
                                        <p class="flex items-center text-sm">
                                            <i class="fa-solid fa-calendar text-slate-400 mr-2 text-xs"></i> 
                                            {{ $tanggal->locale('id')->translatedFormat('l, d F Y') }}
                                        </p>
                                        @if($acara->tempat)
                                            <p class="flex items-center text-sm">
                                                <i class="fa-solid fa-location-dot text-slate-400 mr-2 text-xs"></i> 
                                                {{ $acara->tempat }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Pagination --}}
                @if($acaraList->hasPages())
                    <div class="mt-8 flex justify-center">
                        <div class="bg-white rounded-xl p-4 shadow-lg border border-slate-200">
                            {{ $acaraList->links() }}
                        </div>
                    </div>
                @endif
            @else
                <div class="text-center py-16 bg-white rounded-3xl border-2 border-dashed border-slate-200 shadow-sm">
                    <div class="w-20 h-20 bg-gradient-to-br from-slate-100 to-slate-200 rounded-full flex items-center justify-center mx-auto mb-6">
                        @if($search || $filterPosyandu)
                            <i class="fa-solid fa-search text-slate-400 text-3xl"></i>
                        @else
                            <i class="fa-solid fa-calendar-xmark text-slate-400 text-3xl"></i>
                        @endif
                    </div>
                    <h3 class="text-2xl font-bold text-slate-700 mb-3">
                        @if($search || $filterPosyandu)
                            Tidak Ada Acara Ditemukan
                        @else
                            Belum Ada Jadwal Acara
                        @endif
                    </h3>
                    <p class="text-slate-500 max-w-md mx-auto mb-6">
                        @if($search || $filterPosyandu)
                            Coba ubah filter atau kata kunci pencarian Anda.
                        @else
                            Jadwal acara akan ditampilkan di sini setelah ditambahkan oleh admin posyandu.
                        @endif
                    </p>
                    @if($search || $filterPosyandu)
                        <a href="#jadwal" class="inline-flex items-center px-6 py-3 bg-slate-200 text-slate-700 rounded-full font-semibold hover:bg-slate-300 transition mr-3">
                            <i class="fa-solid fa-arrow-left mr-2"></i>Kembali
                        </a>
                    @endif
                    <a href="{{ Route::has('register') ? route('register') : '#' }}" class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-full font-semibold hover:bg-primaryDark transition shadow-lg hover:shadow-xl">
                        <i class="fa-solid fa-bell mr-2"></i>Daftar untuk Notifikasi
                    </a>
                </div>
            @endif

            <div class="text-center mt-12">
                <div class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-primary/10 to-teal-50 rounded-full border border-primary/20">
                    <i class="fa-solid fa-bell text-primary"></i>
                    <p class="text-slate-700 font-medium">
                        Ingin mendapatkan notifikasi jadwal? 
                        <a href="{{ Route::has('register') ? route('register') : '#' }}" class="text-primary font-bold hover:underline ml-1">
                            Daftar sekarang
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section id="galeri" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <span class="inline-block py-1 px-3 rounded-full bg-teal-100 text-primary text-sm font-semibold mb-4">Momen Berharga</span>
                <h2 class="text-4xl font-bold text-slate-900 mb-4">Galeri Kegiatan</h2>
                <p class="text-slate-600 max-w-2xl mx-auto text-lg">Dokumentasi kegiatan dan momen berharga bersama keluarga di Posyandu Karanggan.</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-6">
                <div class="group relative rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300">
                    <img src="https://images.unsplash.com/photo-1576765608535-5f04d1e3f289?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Kegiatan Posyandu" class="w-full h-64 object-cover group-hover:scale-110 transition duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                        <div class="absolute bottom-4 left-4 right-4 text-white">
                            <h4 class="font-bold text-lg mb-1">Penimbangan Balita</h4>
                            <p class="text-sm text-white/90">Pemantauan pertumbuhan anak secara berkala</p>
                        </div>
                    </div>
                </div>
                
                <div class="group relative rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300">
                    <img src="https://images.unsplash.com/photo-1579684385180-1ea55c938de4?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Imunisasi" class="w-full h-64 object-cover group-hover:scale-110 transition duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                        <div class="absolute bottom-4 left-4 right-4 text-white">
                            <h4 class="font-bold text-lg mb-1">Program Imunisasi</h4>
                            <p class="text-sm text-white/90">Vaksinasi untuk kesehatan anak</p>
                        </div>
                    </div>
                </div>
                
                <div class="group relative rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300">
                    <img src="https://images.unsplash.com/photo-1559757148-5c350d0d3c56?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Edukasi" class="w-full h-64 object-cover group-hover:scale-110 transition duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                        <div class="absolute bottom-4 left-4 right-4 text-white">
                            <h4 class="font-bold text-lg mb-1">Penyuluhan Kesehatan</h4>
                            <p class="text-sm text-white/90">Edukasi gizi dan kesehatan keluarga</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 bg-lightBg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <span class="inline-block py-1 px-3 rounded-full bg-teal-100 text-primary text-sm font-semibold mb-4">Testimoni</span>
                <h2 class="text-4xl font-bold text-slate-900 mb-4">Apa Kata Mereka?</h2>
                <p class="text-slate-600 max-w-2xl mx-auto text-lg">Pengalaman nyata dari keluarga yang telah merasakan manfaat pelayanan Posyandu Karanggan.</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white rounded-2xl p-8 shadow-lg border border-slate-100">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center text-primary font-bold text-lg mr-4">
                            IB
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900">Ibu Budi</h4>
                            <p class="text-sm text-slate-500">Ibu dari Balita</p>
                        </div>
                    </div>
                    <div class="flex text-yellow-400 mb-4">
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                    </div>
                    <p class="text-slate-600 leading-relaxed">"Pelayanan di Posyandu Karanggan sangat membantu. Kader sangat ramah dan profesional. Anak saya selalu antusias datang ke posyandu karena ada PMT yang enak dan bervariasi."</p>
                </div>
                
                <div class="bg-white rounded-2xl p-8 shadow-lg border border-slate-100">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center text-primary font-bold text-lg mr-4">
                            BP
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900">Bapak Priyanto</h4>
                            <p class="text-sm text-slate-500">Anak Lansia</p>
                        </div>
                    </div>
                    <div class="flex text-yellow-400 mb-4">
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                    </div>
                    <p class="text-slate-600 leading-relaxed">"Program Posyandu Lansia sangat bermanfaat untuk ibu saya. Pemeriksaan rutin dan senam lansia membuat ibu lebih sehat dan aktif. Terima kasih kader Posyandu!"</p>
                </div>
                
                <div class="bg-white rounded-2xl p-8 shadow-lg border border-slate-100">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center text-primary font-bold text-lg mr-4">
                            SM
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900">Ibu Sari</h4>
                            <p class="text-sm text-slate-500">Ibu Hamil</p>
                        </div>
                    </div>
                    <div class="flex text-yellow-400 mb-4">
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                    </div>
                    <p class="text-slate-600 leading-relaxed">"Konsultasi gizi saat hamil sangat membantu. Saya jadi lebih paham kebutuhan nutrisi untuk janin. Kader juga selalu siap membantu kapan saja. Recommended!"</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <span class="inline-block py-1 px-3 rounded-full bg-teal-100 text-primary text-sm font-semibold mb-4">Tentang Kami</span>
                <h2 class="text-4xl font-bold text-slate-900 mb-4">Visi & Misi Posyandu Karanggan</h2>
                <p class="text-slate-600 max-w-3xl mx-auto text-lg">Komitmen kami dalam mewujudkan masyarakat sehat dan produktif melalui pelayanan kesehatan terpadu.</p>
            </div>
            
            <div class="grid md:grid-cols-2 gap-12 mb-12">
                <div class="bg-gradient-to-br from-primary/5 to-teal-50 rounded-2xl p-8 border border-primary/10">
                    <div class="w-16 h-16 bg-primary rounded-xl flex items-center justify-center text-white text-2xl mb-6">
                        <i class="fa-solid fa-eye"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-4">Visi</h3>
                    <p class="text-slate-700 leading-relaxed text-lg">
                        Menjadi posyandu terdepan dalam pelayanan kesehatan terpadu yang modern, profesional, dan terpercaya untuk mewujudkan masyarakat Desa Karanggan yang sehat, cerdas, dan produktif.
                    </p>
                </div>
                
                <div class="bg-gradient-to-br from-primary/5 to-teal-50 rounded-2xl p-8 border border-primary/10">
                    <div class="w-16 h-16 bg-primary rounded-xl flex items-center justify-center text-white text-2xl mb-6">
                        <i class="fa-solid fa-bullseye"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-4">Misi</h3>
                    <ul class="space-y-3 text-slate-700 leading-relaxed">
                        <li class="flex items-start">
                            <i class="fa-solid fa-check-circle text-primary mr-3 mt-1"></i>
                            <span>Menyelenggarakan pelayanan kesehatan terpadu yang berkualitas untuk ibu, anak, dan lansia</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-check-circle text-primary mr-3 mt-1"></i>
                            <span>Meningkatkan cakupan imunisasi dan pencegahan penyakit menular</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-check-circle text-primary mr-3 mt-1"></i>
                            <span>Melaksanakan program pencegahan stunting dan gizi buruk</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-check-circle text-primary mr-3 mt-1"></i>
                            <span>Meningkatkan pengetahuan masyarakat melalui edukasi kesehatan</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-primary to-primaryDark rounded-2xl p-8 md:p-12 text-white text-center">
                <h3 class="text-2xl font-bold mb-4">Bergabunglah dengan Kami</h3>
                <p class="text-teal-100 mb-6 max-w-2xl mx-auto text-lg">
                    Dapatkan akses penuh ke semua layanan kesehatan terpadu. Daftar sekarang dan nikmati kemudahan dalam mengelola kesehatan keluarga Anda.
                </p>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="px-8 py-3 rounded-full bg-white text-primary font-bold hover:bg-teal-50 transition shadow-lg">
                            <i class="fa-solid fa-user-plus mr-2"></i> Daftar Sekarang
                        </a>
                    @endif
                    <a href="#kontak" class="px-8 py-3 rounded-full bg-transparent border-2 border-white text-white font-bold hover:bg-white/10 transition">
                        <i class="fa-solid fa-phone mr-2"></i> Hubungi Kami
                    </a>
                </div>
            </div>
        </div>
    </section>

    <footer id="kontak" class="bg-slate-900 text-white pt-16 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 mb-12">
                <div class="col-span-1 md:col-span-1">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center text-white text-lg">
                            <i class="fa-solid fa-heart-pulse"></i>
                        </div>
                        <span class="font-bold text-xl text-white">{{ $posyandu ? $posyandu->nama_posyandu : 'Posyandu' }}</span>
                    </div>
                    <p class="text-slate-400 text-sm leading-relaxed mb-4">{{ $posyandu ? $posyandu->nama_posyandu : 'Posyandu' }} mengabdi untuk kesehatan masyarakat dengan pelayanan prima, profesional, dan sepenuh hati. Bersama mewujudkan generasi sehat dan produktif.</p>
                    <div class="flex space-x-3">
                        <a href="#" class="w-9 h-9 rounded-full bg-slate-800 flex items-center justify-center text-slate-300 hover:bg-primary hover:text-white transition"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="w-9 h-9 rounded-full bg-slate-800 flex items-center justify-center text-slate-300 hover:bg-primary hover:text-white transition"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" class="w-9 h-9 rounded-full bg-slate-800 flex items-center justify-center text-slate-300 hover:bg-primary hover:text-white transition"><i class="fa-brands fa-youtube"></i></a>
                        <a href="#" class="w-9 h-9 rounded-full bg-slate-800 flex items-center justify-center text-slate-300 hover:bg-primary hover:text-white transition"><i class="fa-brands fa-whatsapp"></i></a>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-bold text-white mb-4 text-lg">Layanan</h4>
                    <ul class="space-y-3 text-sm text-slate-400">
                        <li><a href="#layanan" class="hover:text-primary transition flex items-center"><i class="fa-solid fa-chevron-right text-xs mr-2"></i> Kesehatan Ibu & Anak</a></li>
                        <li><a href="#layanan" class="hover:text-primary transition flex items-center"><i class="fa-solid fa-chevron-right text-xs mr-2"></i> Imunisasi Lengkap</a></li>
                        <li><a href="#layanan" class="hover:text-primary transition flex items-center"><i class="fa-solid fa-chevron-right text-xs mr-2"></i> Posyandu Lansia</a></li>
                        <li><a href="#layanan" class="hover:text-primary transition flex items-center"><i class="fa-solid fa-chevron-right text-xs mr-2"></i> Konsultasi Kesehatan</a></li>
                        <li><a href="#layanan" class="hover:text-primary transition flex items-center"><i class="fa-solid fa-chevron-right text-xs mr-2"></i> Edukasi & Penyuluhan</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-white mb-4 text-lg">Informasi</h4>
                    <ul class="space-y-3 text-sm text-slate-400">
                        <li><a href="#jadwal" class="hover:text-primary transition flex items-center"><i class="fa-solid fa-chevron-right text-xs mr-2"></i> Jadwal Kegiatan</a></li>
                        <li><a href="#galeri" class="hover:text-primary transition flex items-center"><i class="fa-solid fa-chevron-right text-xs mr-2"></i> Galeri Kegiatan</a></li>
                        <li><a href="#" class="hover:text-primary transition flex items-center"><i class="fa-solid fa-chevron-right text-xs mr-2"></i> Profil Kader</a></li>
                        <li><a href="#" class="hover:text-primary transition flex items-center"><i class="fa-solid fa-chevron-right text-xs mr-2"></i> Artikel Kesehatan</a></li>
                        <li><a href="#" class="hover:text-primary transition flex items-center"><i class="fa-solid fa-chevron-right text-xs mr-2"></i> Laporan Kegiatan</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-white mb-4 text-lg">Kontak Kami</h4>
                    <ul class="space-y-3 text-sm text-slate-400">
                        <li class="flex items-start">
                            <i class="fa-solid fa-location-dot text-primary mr-3 mt-1"></i>
                            <div>
                                <p class="text-white font-medium">Alamat</p>
                                <p>Balai Desa Karanggan<br>Jl. Raya Karanggan No. 123<br>Kec. Karanggan, Kab. Bogor</p>
                            </div>
                        </li>
                        <li class="flex items-center">
                            <i class="fa-solid fa-phone text-primary mr-3"></i>
                            <div>
                                <p class="text-white font-medium">Telepon</p>
                                <p>0812-3456-7890</p>
                            </div>
                        </li>
                        <li class="flex items-center">
                            <i class="fa-solid fa-envelope text-primary mr-3"></i>
                            <div>
                                <p class="text-white font-medium">Email</p>
                                <p>info@posyandukaranggan.id</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-slate-800 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <p class="text-sm text-slate-400">&copy; {{ date('Y') }} {{ $posyandu ? $posyandu->nama_posyandu : 'Posyandu' }}. All rights reserved.</p>
                    <div class="flex gap-6 text-sm text-slate-400">
                        <a href="#" class="hover:text-primary transition">Kebijakan Privasi</a>
                        <a href="#" class="hover:text-primary transition">Syarat & Ketentuan</a>
                        <a href="#" class="hover:text-primary transition">Tentang Kami</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Mobile Menu Toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        
        if (mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
                const icon = mobileMenuBtn.querySelector('i');
                if (mobileMenu.classList.contains('hidden')) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                } else {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                }
            });
        }

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const offset = 80; // Height of fixed navbar
                    const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - offset;
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                    // Close mobile menu if open
                    if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                        mobileMenu.classList.add('hidden');
                        const icon = mobileMenuBtn.querySelector('i');
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    }
                }
            });
        });

        // Navbar background on scroll
        window.addEventListener('scroll', () => {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('bg-white', 'shadow-md');
                navbar.classList.remove('bg-white/90');
            } else {
                navbar.classList.add('bg-white/90');
                navbar.classList.remove('bg-white', 'shadow-md');
            }
        });
    </script>

</body>
</html>
