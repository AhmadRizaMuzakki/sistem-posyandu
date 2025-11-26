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
            @endphp

            <nav class="mt-6 px-4 space-y-2 h-[calc(100vh-5rem)] overflow-y-auto custom-scrollbar">
                {{-- DASHBOARD UTAMA --}}
                <a
                    href="{{ route('admin.dashboard') }}"
                    class="flex items-center px-4 py-3 text-white bg-primary rounded-lg transition-colors"
                >
                    <i class="ph ph-squares-four text-xl mr-3"></i>
                    <span class="font-medium">Dashboard</span>
                </a>

                <h3 class="mt-6 mb-1 pt-4 text-xs font-semibold text-gray-400 uppercase border-t border-gray-100">
                    Daftar Posyandu
                </h3>

                {{-- Daftar Posyandu --}}
                @foreach($daftarPosyandu as $posyandu)
                    @php
                        // Safety: use property names only, because $daftarPosyandu is always a collection of model objects.
                        $id = $posyandu->id_posyandu ?? $posyandu->id ?? null;
                        $nama = $posyandu->nama_posyandu ?? $posyandu->nama ?? '-';
                    @endphp
                    @if($id)
                    <a
                        href="{{ route('posyandu.detail', ['id' => encrypt($id)]) }}"
                        class="flex items-center px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-primary rounded-lg transition-colors group"
                    >
                        <i class="ph ph-house-line text-lg mr-3 group-hover:text-primary"></i>
                        <span class="font-medium">{{ e($nama) }}</span>
                    </a>
                    @else
                    <span class="flex items-center px-4 py-2 text-sm text-red-500 rounded-lg transition-colors group opacity-70 cursor-not-allowed">
                        <i class="ph ph-house-line text-lg mr-3"></i>
                        <span class="font-medium">Data tidak valid</span>
                    </span>
                    @endif
                @endforeach

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
                        <span class="hidden md:block text-sm font-medium text-gray-700">Admin User</span>
                        <!-- Dropdown -->
                        <div class="relative group">
                            <button id="userDropdownBtn" class="ml-2 p-1 rounded-full text-gray-400 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary">
                                <i class="ph ph-caret-down text-xl"></i>
                            </button>
                            <div id="userDropdownMenu" class="hidden group-hover:block absolute right-0 mt-2 w-40 bg-white border border-gray-200 rounded-lg shadow-md z-50">
                                <a href="{{ route('profile.edit') }}"
                                    class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-primary text-sm">
                                    <i class="ph ph-pencil-line mr-2"></i> Update Profil
                                </a>
                                <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Yakin ingin menghapus akun?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100 hover:text-red-700 text-sm">
                                        <i class="ph ph-trash mr-2"></i> Delete Akun
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

    @livewireScripts
</body>

</html>
