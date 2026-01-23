<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posyandu Karanggan - Sehat Bersama, Tumbuh Bahagia</title>
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
                    <a href="#beranda" class="font-bold text-xl tracking-tight text-slate-800 hover:text-primary transition">Posyandu <span class="text-primary">Karanggan</span></a>
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
                ✨ Melayani Sepenuh Hati untuk Keluarga Sehat
            </span>
            <h1 class="text-4xl md:text-6xl font-extrabold text-slate-900 tracking-tight mb-6 leading-tight fade-in-up" style="animation-delay: 0.1s;">
                Mewujudkan Generasi Sehat <br>
                <span class="text-primary">Mulai dari Karanggan</span>
            </h1>
            <p class="mt-4 max-w-3xl mx-auto text-xl text-slate-600 mb-10 fade-in-up" style="animation-delay: 0.2s;">
                Posyandu Karanggan hadir sebagai mitra terpercaya keluarga dalam menjaga kesehatan ibu, anak, dan lansia. Dengan pelayanan terpadu, profesional, dan ramah, kami berkomitmen mewujudkan masyarakat yang sehat dan produktif.
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
                    <div class="text-4xl font-bold mb-1">150+</div>
                    <div class="text-teal-100 text-sm font-medium">Balita Sehat</div>
                </div>
                <div>
                    <div class="text-4xl font-bold mb-1">80+</div>
                    <div class="text-teal-100 text-sm font-medium">Lansia Aktif</div>
                </div>
                <div>
                    <div class="text-4xl font-bold mb-1">15</div>
                    <div class="text-teal-100 text-sm font-medium">Kader Terlatih</div>
                </div>
                <div>
                    <div class="text-4xl font-bold mb-1">100%</div>
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

    <section id="jadwal" class="py-20 bg-gradient-to-b from-white to-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <span class="inline-block py-1 px-3 rounded-full bg-teal-100 text-primary text-sm font-semibold mb-4">Jadwal Kegiatan</span>
                <h2 class="text-4xl font-bold text-slate-900 mb-4">Jadwal Posyandu Bulan Ini</h2>
                <p class="text-slate-600 max-w-2xl mx-auto text-lg">Jangan lewatkan jadwal penimbangan dan imunisasi. Pastikan membawa buku KIA/KMS dan kartu imunisasi anak Anda.</p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100 hover:shadow-xl transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-calendar-days text-primary text-xl"></i>
                        </div>
                        <span class="text-xs font-semibold text-primary bg-primary/10 px-3 py-1 rounded-full">Balita & Ibu</span>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Posyandu Melati 1</h3>
                    <div class="space-y-2 text-slate-600 mb-4">
                        <p class="flex items-center"><i class="fa-solid fa-clock text-primary mr-2"></i> Senin, 12 Februari 2024</p>
                        <p class="flex items-center"><i class="fa-solid fa-location-dot text-primary mr-2"></i> Balai RW 01</p>
                        <p class="flex items-center"><i class="fa-solid fa-clock text-primary mr-2"></i> 08:00 - 12:00 WIB</p>
                    </div>
                    <div class="pt-4 border-t border-slate-100">
                        <p class="text-sm text-slate-500">Layanan: Penimbangan, Imunisasi, PMT</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100 hover:shadow-xl transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-calendar-days text-primary text-xl"></i>
                        </div>
                        <span class="text-xs font-semibold text-orange-500 bg-orange-100 px-3 py-1 rounded-full">Lansia</span>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Posyandu Mawar 2</h3>
                    <div class="space-y-2 text-slate-600 mb-4">
                        <p class="flex items-center"><i class="fa-solid fa-clock text-primary mr-2"></i> Kamis, 15 Februari 2024</p>
                        <p class="flex items-center"><i class="fa-solid fa-location-dot text-primary mr-2"></i> Balai RW 02</p>
                        <p class="flex items-center"><i class="fa-solid fa-clock text-primary mr-2"></i> 08:00 - 11:00 WIB</p>
                    </div>
                    <div class="pt-4 border-t border-slate-100">
                        <p class="text-sm text-slate-500">Layanan: Pemeriksaan Tensi, Gula Darah, Senam</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100 hover:shadow-xl transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-calendar-days text-primary text-xl"></i>
                        </div>
                        <span class="text-xs font-semibold text-purple-500 bg-purple-100 px-3 py-1 rounded-full">Edukasi</span>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Penyuluhan Gizi</h3>
                    <div class="space-y-2 text-slate-600 mb-4">
                        <p class="flex items-center"><i class="fa-solid fa-clock text-primary mr-2"></i> Sabtu, 17 Februari 2024</p>
                        <p class="flex items-center"><i class="fa-solid fa-location-dot text-primary mr-2"></i> Aula Posyandu</p>
                        <p class="flex items-center"><i class="fa-solid fa-clock text-primary mr-2"></i> 09:00 - 11:00 WIB</p>
                    </div>
                    <div class="pt-4 border-t border-slate-100">
                        <p class="text-sm text-slate-500">Topik: Gizi Seimbang untuk Keluarga</p>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <p class="text-slate-600 mb-4">Ingin mendapatkan notifikasi jadwal? <a href="{{ Route::has('register') ? route('register') : '#' }}" class="text-primary font-semibold hover:underline">Daftar sekarang</a></p>
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
                        <span class="font-bold text-xl text-white">Posyandu <span class="text-primary">Karanggan</span></span>
                    </div>
                    <p class="text-slate-400 text-sm leading-relaxed mb-4">Mengabdi untuk kesehatan masyarakat desa dengan pelayanan prima, profesional, dan sepenuh hati. Bersama mewujudkan generasi sehat dan produktif.</p>
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
                    <p class="text-sm text-slate-400">&copy; {{ date('Y') }} Posyandu Karanggan. All rights reserved.</p>
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
