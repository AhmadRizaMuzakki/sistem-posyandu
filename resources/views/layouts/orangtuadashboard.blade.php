<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard Orangtua' }} - {{ config('app.name', 'Sistem Posyandu') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/home.png') }}">

    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .nav-tabs-scroll {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .nav-tabs-scroll::-webkit-scrollbar {
            display: none;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 text-gray-800">
    <div class="min-h-screen">
        {{-- Navigation --}}
        <nav class="bg-white border-b border-gray-200 shadow-sm">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
                {{-- Baris 1: brand + user --}}
                <div class="flex items-center justify-between gap-2 min-h-12 sm:min-h-14">
                    <a href="{{ route('orangtua.dashboard') }}" class="flex items-center gap-1.5 sm:gap-2 min-w-0">
                        <i class="ph ph-heart text-xl sm:text-2xl text-primary shrink-0"></i>
                        <span class="text-base sm:text-xl font-bold text-gray-800 truncate">
                            <span class="sm:hidden">Orangtua</span>
                            <span class="hidden sm:inline">Dashboard Orangtua</span>
                        </span>
                    </a>

                    <div class="flex items-center gap-1.5 sm:gap-2 shrink-0 pl-2 border-l border-gray-200">
                        <span class="text-xs sm:text-sm text-gray-600 truncate max-w-[5.5rem] sm:max-w-[10rem] lg:max-w-xs"
                              title="{{ Auth::user()->name }}">
                            {{ Auth::user()->name }}
                        </span>
                        <form method="POST" action="{{ route('logout') }}" class="inline shrink-0">
                            @csrf
                            <button type="submit"
                                    class="p-1.5 text-gray-500 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors"
                                    title="Keluar">
                                <i class="ph ph-sign-out text-lg"></i>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Baris 2: tab navigasi (scroll horizontal di mobile) --}}
                <div class="nav-tabs-scroll -mx-3 sm:mx-0 overflow-x-auto">
                    <div class="flex items-center gap-0.5 px-3 sm:px-0 min-w-max sm:min-w-0 sm:justify-start -mb-px">
                        <a href="{{ route('orangtua.dashboard') }}"
                           @class([
                               'inline-flex items-center gap-1 sm:gap-1.5 px-2.5 sm:px-4 py-2.5 text-xs sm:text-sm font-medium whitespace-nowrap border-b-2 transition-colors',
                               'text-primary border-primary' => request()->routeIs('orangtua.dashboard'),
                               'text-gray-600 border-transparent hover:text-primary hover:border-gray-300' => !request()->routeIs('orangtua.dashboard'),
                           ])>
                            <i class="ph ph-house text-sm sm:text-base"></i>
                            Dashboard
                        </a>
                        <a href="{{ route('orangtua.imunisasi') }}"
                           @class([
                               'inline-flex items-center gap-1 sm:gap-1.5 px-2.5 sm:px-4 py-2.5 text-xs sm:text-sm font-medium whitespace-nowrap border-b-2 transition-colors',
                               'text-primary border-primary' => request()->routeIs('orangtua.imunisasi'),
                               'text-gray-600 border-transparent hover:text-primary hover:border-gray-300' => !request()->routeIs('orangtua.imunisasi'),
                           ])>
                            <i class="ph ph-syringe text-sm sm:text-base"></i>
                            Imunisasi
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        {{-- Main Content --}}
        <main class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8">
            {{ $slot }}
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @livewireScriptConfig
    @livewireScripts
    @stack('scripts')
</body>
</html>

