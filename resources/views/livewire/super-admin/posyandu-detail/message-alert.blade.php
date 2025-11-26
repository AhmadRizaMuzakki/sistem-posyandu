{{-- Pesan Sukses --}}
@if (session()->has('message'))
    <div class="fixed top-20 right-6 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-lg" role="alert">
        <div class="flex items-center">
            <i class="ph ph-check-circle text-xl mr-2"></i>
            <span>{{ session('message') }}</span>
        </div>
    </div>
@endif

