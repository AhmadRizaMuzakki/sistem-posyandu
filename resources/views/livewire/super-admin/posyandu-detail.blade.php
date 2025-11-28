<div>
    <div class="space-y-6">
        {{-- Header --}}
        @include('livewire.super-admin.posyandu-detail.header')

        {{-- Informasi Utama --}}
        @include('livewire.super-admin.posyandu-detail.info-cards')

        {{-- Daftar Kader --}}
        @include('livewire.super-admin.posyandu-detail.kader-list')

        {{-- Daftar Sasaran --}}
        @include('livewire.super-admin.posyandu-detail.sasaran-list')

        {{-- Pesan Sukses --}}
        @include('livewire.super-admin.posyandu-detail.message-alert')

        {{-- Modal Form Kader --}}
        @include('livewire.super-admin.posyandu-detail.modals.kader-modal')

        {{-- Modal Form Sasaran --}}
        @include('livewire.super-admin.posyandu-detail.modals.all-modals')

        {{-- Modal Upload SK --}}
        @include('livewire.super-admin.posyandu-detail.partials.upload-sk')
    </div>
</div>

{{-- Scripts --}}
@include('livewire.super-admin.posyandu-detail.scripts')
