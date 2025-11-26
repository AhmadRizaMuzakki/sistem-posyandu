{{-- Header --}}
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">{{ $posyandu->nama_posyandu }}</h1>
        <p class="text-gray-500 mt-1">Detail informasi Posyandu</p>
    </div>
    <a href="{{ route('admin.dashboard') }}"
       class="flex items-center px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
        <i class="ph ph-arrow-left text-lg mr-2"></i>
        Kembali
    </a>
</div>

