<div>
    <div class="space-y-6 max-w-full min-w-0 overflow-x-hidden">
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

