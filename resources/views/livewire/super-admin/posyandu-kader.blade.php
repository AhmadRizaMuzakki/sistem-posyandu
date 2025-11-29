<div>
    <div class="space-y-6">
        {{-- Header --}}
        @include('livewire.super-admin.posyandu-detail.header')

        {{-- Daftar Kader --}}
        @include('livewire.super-admin.posyandu-detail.kader-list')

        {{-- Pesan Sukses --}}
        @include('livewire.super-admin.posyandu-detail.message-alert')

        {{-- Modal Form Kader --}}
        @include('livewire.super-admin.posyandu-detail.modals.kader-modal')
    </div>
</div>

{{-- Scripts --}}
@include('livewire.super-admin.posyandu-detail.scripts')

