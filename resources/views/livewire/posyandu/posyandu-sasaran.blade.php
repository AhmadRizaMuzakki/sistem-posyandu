<div>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">{{ $posyandu->nama_posyandu }}</h1>
                <p class="text-gray-500 mt-1">Detail informasi Sasaran Posyandu</p>
            </div>
            <a href="{{ route('adminPosyandu.dashboard') }}"
               class="flex items-center px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <i class="ph ph-arrow-left text-lg mr-2"></i>
                Kembali
            </a>
        </div>

        {{-- Daftar Sasaran --}}
        @include('livewire.super-admin.posyandu-detail.sasaran-list')

        {{-- Pesan Sukses --}}
        @include('livewire.super-admin.posyandu-detail.message-alert')

        {{-- Modal Form Sasaran --}}
        @include('livewire.super-admin.posyandu-detail.modals.all-modals')
    </div>
</div>

{{-- Scripts --}}
@include('livewire.super-admin.posyandu-detail.scripts')

