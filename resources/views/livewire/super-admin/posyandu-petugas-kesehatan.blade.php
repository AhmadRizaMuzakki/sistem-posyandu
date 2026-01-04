<div>
    <div class="space-y-6">
        {{-- Header --}}
        @include('livewire.super-admin.posyandu-detail.header')

        {{-- Daftar Petugas Kesehatan --}}
        @include('livewire.super-admin.posyandu-detail.petugas-kesehatan-list')

        {{-- Pesan Sukses --}}
        @include('livewire.super-admin.posyandu-detail.message-alert')

        {{-- Modal Form Petugas Kesehatan --}}
        @include('livewire.super-admin.posyandu-detail.modals.petugas-kesehatan-modal')
    </div>
</div>

{{-- Scripts --}}
@include('livewire.super-admin.posyandu-detail.scripts')
