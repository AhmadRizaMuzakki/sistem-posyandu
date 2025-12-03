<div>
    <div class="p-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Dashboard Super Admin</h1>
        <p class="text-gray-500 mb-6">Selamat datang di halaman utama Dashboard Super Admin</p>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
            <!-- Card Total Posyandu -->
            <a href="{{ route('posyandu.list') }}" class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center hover:shadow-lg transition-shadow cursor-pointer">
                <div class="bg-blue-100 p-4 rounded-full mb-4">
                    <i class="ph ph-buildings text-4xl text-blue-600"></i>
                </div>
                <span class="text-2xl font-bold text-gray-700">{{ $totalPosyandu ?? '0' }}</span>
                <span class="text-sm text-gray-500 mt-1">Total Posyandu</span>
                <span class="text-xs text-primary mt-2 hover:underline">Kelola Posyandu →</span>
            </a>
            <!-- Card Total Kader -->
            <div class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center">
                <div class="bg-green-100 p-4 rounded-full mb-4">
                    <i class="ph ph-user-switch text-4xl text-green-600"></i>
                </div>
                <span class="text-2xl font-bold text-gray-700">{{ $totalKader ?? '0' }}</span>
                <span class="text-sm text-gray-500 mt-1">Total Kader</span>
            </div>
            <!-- Card Total Sasaran -->
            <div class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center">
                <div class="bg-yellow-100 p-4 rounded-full mb-4">
                    <i class="ph ph-users-three text-4xl text-yellow-600"></i>
                </div>
                <span class="text-2xl font-bold text-gray-700">{{ $totalSasaran ?? '0' }}</span>
                <span class="text-sm text-gray-500 mt-1">Total Sasaran</span>
            </div>
        </div>
    </div>

</div>
