<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin Dashboard' }}</title>

    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Custom scrollbar for sidebar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e5e7eb;
            border-radius: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="bg-gray-50 text-gray-800">

    <div class="flex h-screen overflow-hidden">

        <aside id="sidebar" class="fixed inset-y-0 left-0 z-30 w-64 transform -translate-x-full bg-white border-r border-gray-200 transition-transform duration-300 ease-in-out md:relative md:translate-x-0 shadow-lg md:shadow-none">

            {{-- Sidebar Header --}}
            <div class="flex items-center justify-center h-16 border-b border-gray-100">
                <span class="text-2xl font-bold text-primary">Posyandu Admin</span>
            </div>

            {{-- Daftar nama posyandu dari database --}}
            @php
                // Mengambil data dari database apabila tidak diberi dari controller
                if (!isset($daftarPosyandu)) {
                    $daftarPosyandu = \App\Models\Posyandu::orderBy('nama_posyandu')->get();
                }
                // Ambil posyandu yang sedang aktif dari URL jika ada
                $currentPosyanduId = null;
                // Cek semua route yang terkait dengan posyandu
                if (request()->routeIs('posyandu.detail') ||
                    request()->routeIs('posyandu.info') ||
                    request()->routeIs('posyandu.kader') ||
                    request()->routeIs('posyandu.petugas-kesehatan') ||
                    request()->routeIs('posyandu.sasaran') ||
                    request()->routeIs('posyandu.imunisasi') ||
                    request()->routeIs('posyandu.pendidikan') ||
                    request()->routeIs('posyandu.laporan')) {
                    try {
                        $encryptedId = request()->route('id');
                        if ($encryptedId) {
                            $currentPosyanduId = decrypt($encryptedId);
                        }
                    } catch (\Exception $e) {
                        // Ignore jika decrypt gagal
                    }
                }
            @endphp

            <nav class="mt-6 px-4 space-y-2 h-[calc(100vh-5rem)] overflow-y-auto custom-scrollbar" x-data="{
                selectedPosyandu: @js($currentPosyanduId),
                posyanduList: @js($daftarPosyandu->map(function($p) {
                    return [
                        'id' => $p->id_posyandu ?? $p->id ?? null,
                        'nama' => $p->nama_posyandu ?? $p->nama ?? '-',
                        'encryptedId' => ($p->id_posyandu ?? $p->id) ? encrypt($p->id_posyandu ?? $p->id) : null
                    ];
                })->filter(fn($p) => $p['id'] !== null)->values()->toArray()),
                init() {
                    // Jika selectedPosyandu belum ter-set, deteksi dari URL
                    if (!this.selectedPosyandu) {
                        const currentPath = window.location.pathname;
                        const posyanduMatch = currentPath.match(/\/supervisor\/posyandu\/([^\/]+)/);
                        if (posyanduMatch && posyanduMatch[1]) {
                            const encryptedId = posyanduMatch[1];
                            const found = this.posyanduList.find(p => p.encryptedId === encryptedId);
                            if (found) {
                                this.selectedPosyandu = found.id;
                            }
                        }
                    }
                }
            }">
                {{-- DASHBOARD UTAMA --}}
                <a
                    href="{{ route('admin.dashboard') }}"
                    class="flex items-center px-4 py-3 text-white bg-primary rounded-lg transition-colors"
                >
                    <i class="ph ph-squares-four text-xl mr-3"></i>
                    <span class="font-medium">Dashboard</span>
                </a>

                {{-- Daftar Posyandu --}}
                <a
                    href="{{ route('posyandu.list') }}"
                    class="flex items-center px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-primary rounded-lg transition-colors group mt-4"
                >
                    <i class="ph ph-list text-lg mr-3 group-hover:text-primary"></i>
                    <span class="font-medium">Daftar Posyandu</span>
                </a>

                <h3 class="mt-6 mb-1 pt-4 text-xs font-semibold text-gray-400 uppercase border-t border-gray-100">
                    Pilih Posyandu
                </h3>

                {{-- Dropdown Posyandu --}}
                <div class="relative" x-data="{ open: false }">
                    <button
                        @click="open = !open"
                        class="w-full flex items-center justify-between px-4 py-3 text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors"
                    >
                        <span class="flex items-center">
                            <i class="ph ph-house-line text-lg mr-3"></i>
                            <span x-text="selectedPosyandu ? posyanduList.find(p => p.id == selectedPosyandu)?.nama || 'Pilih Posyandu' : 'Pilih Posyandu'" class="font-medium"></span>
                        </span>
                        <i class="ph ph-caret-down text-lg" :class="open ? 'transform rotate-180' : ''"></i>
                    </button>

                    <div
                        x-show="open"
                        @click.outside="open = false"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto"
                        style="display: none;"
                    >
                        <template x-for="posyandu in posyanduList" :key="posyandu.id">
                            <a
                                :href="`/supervisor/posyandu/${posyandu.encryptedId}`"
                                @click="selectedPosyandu = posyandu.id; open = false"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-primary transition-colors"
                                :class="selectedPosyandu == posyandu.id ? 'bg-primary/10 text-primary font-medium' : ''"
                            >
                                <i class="ph ph-house-line text-lg mr-2 inline-block"></i>
                                <span x-text="posyandu.nama"></span>
                            </a>
                        </template>
                    </div>
                </div>

                {{-- Menu Pelayanan (muncul ketika posyandu dipilih) --}}
                <template x-if="selectedPosyandu">
                    <div>
                        <template x-for="posyandu in posyanduList.filter(p => p.id == selectedPosyandu)" :key="posyandu.id">
                            <div>
                                <h3 class="mt-6 mb-1 pt-4 text-xs font-semibold text-gray-400 uppercase border-t border-gray-100">
                                    Pelayanan <span class="text-primary" x-text="posyandu.nama"></span>
                                </h3>

                                <a
                                    :href="`/supervisor/posyandu/${posyandu.encryptedId}/info`"
                                    class="flex items-center px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-primary rounded-lg transition-colors group"
                                >
                                    <i class="ph ph-info text-lg mr-3 group-hover:text-primary"></i>
                                    <span class="font-medium">Info Posyandu</span>
                                </a>

                                <a
                                    :href="`/supervisor/posyandu/${posyandu.encryptedId}/kader`"
                                    class="flex items-center px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-primary rounded-lg transition-colors group"
                                >
                                    <i class="ph ph-users text-lg mr-3 group-hover:text-primary"></i>
                                    <span class="font-medium">Kader</span>
                                </a>

                                <a
                                    :href="`/supervisor/posyandu/${posyandu.encryptedId}/petugas-kesehatan`"
                                    class="flex items-center px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-primary rounded-lg transition-colors group"
                                >
                                    <i class="ph ph-stethoscope text-lg mr-3 group-hover:text-primary"></i>
                                    <span class="font-medium">Petugas Kesehatan</span>
                                </a>

                                <a
                                    :href="`/supervisor/posyandu/${posyandu.encryptedId}/sasaran`"
                                    class="flex items-center px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-primary rounded-lg transition-colors group"
                                >
                                    <i class="ph ph-baby text-lg mr-3 group-hover:text-primary"></i>
                                    <span class="font-medium">Sasaran</span>
                                </a>

                                <a
                                    :href="`/supervisor/posyandu/${posyandu.encryptedId}/imunisasi`"
                                    class="flex items-center px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-primary rounded-lg transition-colors group"
                                >
                                    <i class="ph ph-syringe text-lg mr-3 group-hover:text-primary"></i>
                                    <span class="font-medium">Imunisasi</span>
                                </a>

                                <a
                                    :href="`/supervisor/posyandu/${posyandu.encryptedId}/pendidikan`"
                                    class="flex items-center px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-primary rounded-lg transition-colors group"
                                >
                                    <i class="ph ph-graduation-cap text-lg mr-3 group-hover:text-primary"></i>
                                    <span class="font-medium">Pendidikan</span>
                                </a>

                                <a
                                    :href="`/supervisor/posyandu/${posyandu.encryptedId}/laporan`"
                                    class="flex items-center px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-primary rounded-lg transition-colors group"
                                >
                                    <i class="ph ph-chart-bar text-lg mr-3 group-hover:text-primary"></i>
                                    <span class="font-medium">Laporan</span>
                                </a>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- Menu tambahan --}}
                <div class="pt-4 mt-4 border-t border-gray-100">
                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                    >
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
                    <button class="relative p-1 text-gray-400 hover:text-gray-500 focus:outline-none">
                        <i class="ph ph-bell text-xl"></i>
                        <span
                            class="absolute top-0 right-0 block w-2 h-2 bg-red-500 rounded-full ring-2 ring-white"></span>
                    </button>
                    <div class="relative flex items-center space-x-2">
                        <img class="w-8 h-8 rounded-full"
                            src="https://ui-avatars.com/api/?name=Admin+User&background=0D8ABC&color=fff"
                            alt="Admin">
                        <span class="hidden md:block text-sm font-medium text-gray-700">{{ Auth::user()->name }}</span>
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
                    &copy; {{ now()->format('Y') }} Posyandu Admin. All rights reserved.
                </div>

            </main>
        </div>
    </div>

    <script>
        const menuBtn = document.getElementById('menuBtn');
        const sidebar = document.getElementById('sidebar');

        if (menuBtn) {
            menuBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                sidebar.classList.toggle('-translate-x-full');
            });
        }

        document.addEventListener('click', (e) => {
            // Close sidebar jika klik di luar sidebar pada mobile
            if (window.innerWidth < 768 && sidebar && !sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
                sidebar.classList.add('-translate-x-full');
            }
        });

        // Auto show sidebar di desktop
        function handleSidebar() {
            if (window.innerWidth >= 768) {
                sidebar.classList.remove('-translate-x-full');
            }
        }
        window.addEventListener('resize', handleSidebar);
        document.addEventListener('DOMContentLoaded', handleSidebar);
    </script>

    @stack('scripts')
    @livewireScripts
</body>

</html>
