<div>
    <div class="space-y-6 max-w-full min-w-0 overflow-x-hidden">
        {{-- Header --}}
        @include('livewire.super-admin.posyandu-detail.header')

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

