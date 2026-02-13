<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin Dashboard' }}</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/home.jpeg') }}">

    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        /* Sidebar Menu Active State */
        .sidebar-menu-item {
            transition: all 0.3s ease;
        }
        
        .sidebar-menu-item:hover {
            background-color: #f3f4f6;
        }
        
        .sidebar-menu-item.active {
            background-color: #e9d5ff !important; /* Light purple/lavender background */
            color: #7c3aed !important; /* Dark purple text */
        }
        
        .sidebar-menu-item.active i {
            color: #7c3aed !important; /* Dark purple icon */
        }
        
        .sidebar-menu-item.active span {
            color: #7c3aed !important; /* Dark purple text */
            font-weight: 600;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="bg-gray-50 text-gray-800">

    <div class="flex h-screen overflow-hidden">

        <aside id="sidebar"
            class="fixed inset-y-0 left-0 z-30 w-64 transform -translate-x-full bg-white border-r border-gray-200 transition-transform duration-300 ease-in-out md:relative md:translate-x-0 shadow-lg md:shadow-none">
            <div class="flex items-center justify-center h-16 border-b border-gray-100">
                <a href="{{ route('index') }}">
                <span class="text-2xl font-bold text-primary">Posyandu Admin</span>
                </a>
            </div>

            <nav class="mt-6 px-4 space-y-2">
                {{-- 1. DASHBOARD UTAMA --}}
                <a href="{{ route('adminPosyandu.dashboard') }}"
                    class="sidebar-menu-item flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('adminPosyandu.dashboard') ? 'active' : 'text-gray-600' }}">
                    <i class="ph ph-squares-four text-xl mr-3"></i>
                    <span class="font-medium">Dashboard</span>
                </a>

                <h3 class="mt-6 mb-1 pt-4 text-xs font-semibold text-gray-400 uppercase border-t border-gray-100">
                    Manajemen Data</h3>

                    {{-- 2. DATA POSYANDU --}}
                @if (Auth::user()->hasRole('superadmin'))
                    <a href="{{ route('supervisor') }}"
                    class="sidebar-menu-item flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('supervisor') ? 'active' : 'text-gray-600' }}">
                    <i class="ph ph-buildings text-xl mr-3"></i>
                    <span class="font-medium">Posyandu</span>
                    </a>
                @elseif (Auth::user()->hasRole('adminPosyandu'))
                    <a href="{{ route('adminPosyandu.dashboard') }}"
                    class="sidebar-menu-item flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('adminPosyandu.dashboard') ? 'active' : 'text-gray-600' }}">
                    <i class="ph ph-buildings text-xl mr-3"></i>
                    <span class="font-medium">Posyandu</span>
                    </a>
                @endif

                {{-- 8. JADWAL --}}
                <a href="{{ route('adminPosyandu.jadwal') }}"
                    class="sidebar-menu-item flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('adminPosyandu.jadwal') ? 'active' : 'text-gray-600' }}">
                    <i class="ph ph-calendar text-xl mr-3"></i>
                    <span class="font-medium">Jadwal</span>
                </a>
                
                {{-- 3. DATA PETUGAS KESEHATAN --}}
                <a href="{{ route('adminPosyandu.petugas-kesehatan') }}"
                    class="sidebar-menu-item flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('adminPosyandu.petugas-kesehatan') ? 'active' : 'text-gray-600' }}">
                    <i class="ph ph-stethoscope text-xl mr-3"></i>
                    <span class="font-medium">Petugas Kesehatan</span>
                </a>

                {{-- 4. DATA SASARAN --}}
                <a href="{{ route('adminPosyandu.sasaran') }}"
                    class="sidebar-menu-item flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('adminPosyandu.sasaran') ? 'active' : 'text-gray-600' }}">
                    <i class="ph ph-baby text-xl mr-3"></i>
                    <span class="font-medium">Sasaran & Anak</span>
                </a>

                {{-- 5. DATA IMUNISASI --}}
                <a href="{{ route('adminPosyandu.imunisasi') }}"
                    class="sidebar-menu-item flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('adminPosyandu.imunisasi') ? 'active' : 'text-gray-600' }}">
                    <i class="ph ph-syringe text-xl mr-3"></i>
                    <span class="font-medium">Imunisasi</span>
                </a>

                {{-- 6. DATA PENDIDIKAN --}}
                <a href="{{ route('adminPosyandu.pendidikan') }}"
                    class="sidebar-menu-item flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('adminPosyandu.pendidikan') ? 'active' : 'text-gray-600' }}">
                    <i class="ph ph-graduation-cap text-xl mr-3"></i>
                    <span class="font-medium">Pendidikan</span>
                </a>

                {{-- 7. LAPORAN --}}
                <a href="{{ route('adminPosyandu.laporan') }}"
                    class="sidebar-menu-item flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('adminPosyandu.laporan') ? 'active' : 'text-gray-600' }}">
                    <i class="ph ph-chart-bar text-xl mr-3"></i>
                    <span class="font-medium">Laporan</span>
                </a>

                {{-- GALERI (menu navigasi) --}}
                <a href="{{ route('adminPosyandu.galeri') }}"
                    class="sidebar-menu-item flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('adminPosyandu.galeri') ? 'active' : 'text-gray-600' }}">
                    <i class="ph ph-images text-xl mr-3"></i>
                    <span class="font-medium">Galeri</span>
                </a>

                {{-- PERPUSTAKAAN (menu navigasi) --}}
                <a href="{{ route('adminPosyandu.perpustakaan') }}"
                    class="sidebar-menu-item flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('adminPosyandu.perpustakaan') ? 'active' : 'text-gray-600' }}">
                    <i class="ph ph-books text-xl mr-3"></i>
                    <span class="font-medium">Perpustakaan</span>
                </a>

            </nav>
        </aside>


        <div class="flex flex-col flex-1 w-0 overflow-hidden">

            <header
                class="relative z-10 flex items-center justify-between h-16 px-6 bg-white border-b border-gray-200 shadow-sm">
                <button id="menuBtn"
                    class="p-1 text-gray-400 rounded-md md:hidden hover:text-gray-500 focus:outline-none">
                    <i class="ph ph-list text-2xl"></i>
                </button>

                {{-- Salam + Jam --}}
                <div class="hidden md:flex items-center flex-1 ml-4">
                    <div class="flex items-center gap-2 px-3 py-2 bg-gray-50 rounded-lg border border-gray-100">
                        <i class="ph ph-clock text-primary text-lg"></i>
                        <div>
                            @php
                                $jam = (int) now('Asia/Jakarta')->format('H');
                                $salam = ($jam >= 19 || $jam <= 2) ? 'Malam' : (($jam >= 3 && $jam <= 10) ? 'Pagi' : (($jam >= 11 && $jam <= 14) ? 'Siang' : (($jam >= 15 && $jam <= 17) ? 'Sore' : 'Magrib')));
                            @endphp
                            <p class="text-xs text-gray-500 leading-tight">Selamat {{ $salam }}</p>
                            <p class="text-sm font-medium text-gray-800" x-data="{ now: new Date() }" x-init="setInterval(() => { now = new Date() }, 1000)" x-text="now.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short' }) + ' · ' + now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' })"></p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="relative flex items-center space-x-2">
                        <img class="w-8 h-8 rounded-full"
                            src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'Admin') }}&background=0D8ABC&color=fff"
                            alt="Admin">
                        <span class="hidden md:block text-sm font-medium text-gray-700">{{ Auth::user()->name ?? 'Admin User' }}</span>
                        <!-- Dropdown -->
                        <div class="relative group">
                            <button id="userDropdownBtn" class="ml-2 p-1 rounded-full text-gray-400 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary">
                                <i class="ph ph-caret-down text-xl"></i>
                            </button>
                            <div id="userDropdownMenu" class="hidden group-hover:block absolute right-0 mt-2 w-40 bg-white border border-gray-200 rounded-lg shadow-md z-50">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-primary text-sm">
                                        <i class="ph ph-sign-out mr-2"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const btn = document.getElementById('userDropdownBtn');
                                const menu = document.getElementById('userDropdownMenu');
                                if (btn && menu) {
                                    btn.addEventListener('click', function(e) {
                                        e.stopPropagation();
                                        menu.classList.toggle('hidden');
                                    });
                                    document.addEventListener('click', function(e) {
                                        if (!btn.contains(e.target) && !menu.contains(e.target)) {
                                            menu.classList.add('hidden');
                                        }
                                    });
                                }
                            });
                        </script>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto bg-gray-50 p-6">

                {{ $slot }}


                <div class="mt-8 text-center text-sm text-gray-400">
                    &copy; 2025 Dashboard Company. All rights reserved.
                </div>

            </main>
        </div>
    </div>

    <script>
        const menuBtn = document.getElementById('menuBtn');
        const sidebar = document.getElementById('sidebar');

        menuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
        });

        document.addEventListener('click', (e) => {
            if (!sidebar.contains(e.target) && !menuBtn.contains(e.target) && window.innerWidth < 768) {
                sidebar.classList.add('-translate-x-full');
            }
        });
    </script>

    @stack('scripts')
    @livewireScripts
    @include('components.alert-modal')
</body>

</html>
