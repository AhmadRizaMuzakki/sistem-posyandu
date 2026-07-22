{{-- Header --}}
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between min-w-0">
    <div class="min-w-0">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 break-words leading-snug">{{ $posyandu->nama_posyandu }}</h1>
        <p class="text-gray-500 mt-1 text-sm sm:text-base">Detail informasi Posyandu</p>
    </div>
    <a href="{{ route('adminPosyandu.dashboard') }}"
       class="inline-flex items-center justify-center self-start sm:self-auto shrink-0 px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition-colors border border-gray-200 sm:border-0">
        <i class="ph ph-arrow-left text-lg mr-2"></i>
        Kembali
    </a>
</div>

