<div>
    <div class="space-y-6">
        {{-- Header --}}
        @include('livewire.posyandu.header')

        {{-- Daftar Sasaran --}}
        @include('livewire.posyandu.sasaran-list')

        {{-- Pesan Sukses --}}
        @include('livewire.posyandu.message-alert')

        {{-- Modal Form Sasaran --}}
        @include('livewire.posyandu.modals.all-modals')
    </div>
</div>

{{-- Scripts --}}
@include('livewire.posyandu.scripts')

