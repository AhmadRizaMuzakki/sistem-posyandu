@php
    $isMobile = ($variant ?? 'desktop') === 'mobile';
    $iconSize = $isMobile ? 'h-9 w-9' : 'h-8 w-8';
    $iconInner = $isMobile ? 'text-lg' : 'text-base';
    $titleSize = $isMobile ? 'text-base' : 'text-sm';
    $padding = $isMobile ? 'px-5 py-3.5' : 'px-6 py-4';
    $closePadding = $isMobile ? 'p-2' : 'p-1.5';
    $closeIcon = $isMobile ? 'text-xl' : 'text-lg';
@endphp

<div class="sasaran-view-modal-header flex items-center justify-between gap-3 {{ $padding }} border-b border-gray-100 bg-white shrink-0">
    <h3
        @if($isMobile) id="sasaran-view-modal-title" @endif
        class="sasaran-view-modal-header__title min-w-0 flex-1 {{ $titleSize }} font-bold text-gray-900 leading-snug"
    >
        <span class="sasaran-view-modal-header__icon flex {{ $iconSize }} shrink-0 items-center justify-center rounded-lg bg-primary text-white">
            <i class="ph ph-user-circle {{ $iconInner }}"></i>
        </span>
        <span class="min-w-0 break-words">{{ $viewTitle }}</span>
    </h3>
    <button
        type="button"
        wire:click="closeSasaranViewModal"
        class="sasaran-view-modal-header__close shrink-0 {{ $closePadding }} rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
        aria-label="Tutup"
    >
        <i class="ph ph-x {{ $closeIcon }}"></i>
    </button>
</div>
