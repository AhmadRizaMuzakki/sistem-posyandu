<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin Dashboard' }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4F46E5',
                        secondary: '#64748B',
                    }
                }
            }
        }
    </script>

    @livewireStyles
</head>

<body class="bg-gray-50 text-gray-800">

    <div class="flex h-screen overflow-hidden">

        <aside id="sidebar"
            class="fixed inset-y-0 left-0 z-30 w-64 transform -translate-x-full bg-white border-r border-gray-200 transition-transform duration-300 ease-in-out md:relative md:translate-x-0 shadow-lg md:shadow-none">
            <div class="flex items-center justify-center h-16 border-b border-gray-100">
                <span class="text-2xl font-bold text-primary">Posyandu Admin</span>
            </div>

            <nav class="mt-6 px-4 space-y-2">
                {{-- 1. DASHBOARD UTAMA --}}
                <a href="{{ route('dashboard') }}"
                    class="flex items-center px-4 py-3 text-white bg-primary rounded-lg transition-colors">
                    <i class="ph ph-squares-four text-xl mr-3"></i>
                    <span class="font-medium">Dashboard</span>
                </a>

                <h3 class="mt-6 mb-1 pt-4 text-xs font-semibold text-gray-400 uppercase border-t border-gray-100">
                    Manajemen Data</h3>

                    {{-- 2. DATA POSYANDU --}}
                @if (Auth::user()->hasRole('superadmin'))
                    <a href="{{ route('supervisor') }}"
                    class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                    <i class="ph ph-buildings text-xl mr-3"></i>
                    <span class="font-medium">Posyandu</span>
                    </a>
                @elseif (Auth::user()->hasRole('adminPosyandu'))
                    <a href="{{ route('adminPosyandu.dashboard') }}"
                    class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                    <i class="ph ph-buildings text-xl mr-3"></i>
                    <span class="font-medium">Posyandu</span>
                    </a>
                @endif

                {{-- @if (Auth::user()->hasRole('superadmin')) --}}

                {{-- 3. DATA KADER --}}
                <a href="{{ route('adminPosyandu.kader') }}"
                    class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                    <i class="ph ph-users text-xl mr-3"></i>
                    <span class="font-medium">Kader</span>
                </a>

                {{-- 4. DATA SASARAN --}}
                <a href="{{ route('login') }}"
                    class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                    <i class="ph ph-baby text-xl mr-3"></i>
                    <span class="font-medium">Sasaran & Anak</span>
                </a>

                {{-- 5. LAPORAN --}}
                <a href="{{ route('login') }}"
                    class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                    <i class="ph ph-chart-bar text-xl mr-3"></i>
                    <span class="font-medium">Laporan</span>
                </a>

                <div class="pt-4 mt-4 border-t border-gray-100">
                    {{-- PENGATURAN --}}
                    <a href="{{ route('login') }}"
                        class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="ph ph-gear text-xl mr-3"></i>
                        <span class="font-medium">Pengaturan</span>
                    </a>
                </div>
            </nav>
        </aside>


        <div class="flex flex-col flex-1 w-0 overflow-hidden">

            <header
                class="relative z-10 flex items-center justify-between h-16 px-6 bg-white border-b border-gray-200 shadow-sm">
                <button id="menuBtn"
                    class="p-1 text-gray-400 rounded-md md:hidden hover:text-gray-500 focus:outline-none">
                    <i class="ph ph-list text-2xl"></i>
                </button>

                <div class="hidden md:flex relative mx-4 w-full max-w-md">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <i class="ph ph-magnifying-glass text-lg"></i>
                    </span>
                    <input type="text"
                        class="w-full py-2 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                        placeholder="Cari data...">
                </div>

                <div class="flex items-center space-x-4">
                    <button class="relative p-1 text-gray-400 hover:text-gray-500">
                        <i class="ph ph-bell text-xl"></i>
                        <span
                            class="absolute top-0 right-0 block w-2 h-2 bg-red-500 rounded-full ring-2 ring-white"></span>
                    </button>
                    <div class="relative flex items-center space-x-2 cursor-pointer">
                        <img class="w-8 h-8 rounded-full"
                            src="https://ui-avatars.com/api/?name=Admin+User&background=0D8ABC&color=fff"
                            alt="Admin">
                        <span class="hidden md:block text-sm font-medium text-gray-700">Admin User</span>
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

    @livewireScripts
</body>

</html>
