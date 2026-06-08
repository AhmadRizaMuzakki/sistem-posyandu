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
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 text-gray-800">
    <div class="min-h-screen">
        {{-- Navigation --}}
        <nav class="bg-white border-b border-gray-200 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 sm:gap-4 py-3 sm:py-0 sm:min-h-16">
                    {{-- Brand + user (mobile) --}}
                    <div class="flex items-center justify-between gap-3 min-w-0">
                        <a href="{{ route('orangtua.dashboard') }}" class="flex items-center gap-2 min-w-0 shrink">
                            <i class="ph ph-heart text-2xl text-primary shrink-0"></i>
                            <span class="text-lg sm:text-xl font-bold text-gray-800 whitespace-nowrap truncate">
                                Dashboard Orangtua
                            </span>
                        </a>

                        <div class="flex sm:hidden items-center gap-2 shrink-0 border-l border-gray-200 pl-3">
                            <span class="text-sm text-gray-600 whitespace-nowrap truncate max-w-[7rem]"
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

                    {{-- Nav + user (desktop) --}}
                    <div class="flex items-center justify-between sm:justify-end gap-3 sm:gap-6 min-w-0">
                        <div class="flex items-center gap-1 -mb-px">
                            <a href="{{ route('orangtua.dashboard') }}"
                               @class([
                                   'inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 text-sm font-medium whitespace-nowrap border-b-2 transition-colors',
                                   'text-primary border-primary' => request()->routeIs('orangtua.dashboard'),
                                   'text-gray-600 border-transparent hover:text-primary hover:border-gray-300' => !request()->routeIs('orangtua.dashboard'),
                               ])>
                                <i class="ph ph-house text-base"></i>
                                Dashboard
                            </a>
                            <a href="{{ route('orangtua.imunisasi') }}"
                               @class([
                                   'inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 text-sm font-medium whitespace-nowrap border-b-2 transition-colors',
                                   'text-primary border-primary' => request()->routeIs('orangtua.imunisasi'),
                                   'text-gray-600 border-transparent hover:text-primary hover:border-gray-300' => !request()->routeIs('orangtua.imunisasi'),
                               ])>
                                <i class="ph ph-syringe text-base"></i>
                                Imunisasi
                            </a>
                        </div>

                        <div class="hidden sm:flex items-center gap-2 shrink-0 border-l border-gray-200 pl-4">
                            <span class="text-sm text-gray-600 whitespace-nowrap truncate max-w-[10rem] lg:max-w-xs"
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
                </div>
            </div>
        </nav>

        {{-- Main Content --}}
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            {{ $slot }}
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @livewireScriptConfig
    @livewireScripts
    @stack('scripts')
</body>
</html>

