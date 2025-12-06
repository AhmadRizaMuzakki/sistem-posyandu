<div>
    <div class="space-y-6">
        {{-- Header --}}
        @include('livewire.super-admin.posyandu-detail.header')

        {{-- Daftar Imunisasi --}}
        @include('livewire.super-admin.posyandu-detail.imunisasi-list')

        {{-- Pesan Sukses --}}
        @include('livewire.super-admin.posyandu-detail.message-alert')

        {{-- Modal Form Imunisasi --}}
        @include('livewire.super-admin.posyandu-detail.modals.imunisasi-modal')
    </div>
</div>

{{-- Scripts --}}
@include('livewire.super-admin.posyandu-detail.scripts')
