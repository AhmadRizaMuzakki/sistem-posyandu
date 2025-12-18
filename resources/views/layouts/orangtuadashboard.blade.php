<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard Orangtua' }} - {{ config('app.name', 'Sistem Posyandu') }}</title>

    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

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
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="{{ route('orangtua.dashboard') }}" class="flex items-center">
                            <i class="ph ph-heart text-2xl text-primary mr-2"></i>
                            <span class="text-xl font-bold text-gray-800">Dashboard Orangtua</span>
                        </a>
                    </div>

                    <div class="flex items-center space-x-4">
                        <a href="{{ route('orangtua.dashboard') }}"
                           class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-primary {{ request()->routeIs('orangtua.dashboard') ? 'text-primary border-b-2 border-primary' : '' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('orangtua.imunisasi') }}"
                           class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-primary {{ request()->routeIs('orangtua.imunisasi') ? 'text-primary border-b-2 border-primary' : '' }}">
                            <i class="ph ph-syringe mr-1"></i>
                            Imunisasi
                        </a>

                        <div class="flex items-center space-x-2 border-l border-gray-200 pl-4">
                            <span class="text-sm text-gray-600">{{ Auth::user()->name }}</span>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="text-sm text-gray-600 hover:text-red-600">
                                    <i class="ph ph-sign-out"></i>
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

    @livewireScripts
</body>
</html>

