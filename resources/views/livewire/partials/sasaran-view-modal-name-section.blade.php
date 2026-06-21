@php
    $isMobile = ($variant ?? 'desktop') === 'mobile';
    $padding = $isMobile ? 'px-5 py-4' : 'px-6 py-3.5';
    $labelSize = $isMobile ? 'text-xs' : 'text-[11px]';
    $nameSize = $isMobile ? 'text-lg' : 'text-base';
@endphp

<section class="sasaran-view-modal-name shrink-0 text-center {{ $padding }} bg-gradient-to-b from-primary/5 to-white border-b border-gray-100" aria-label="Nama sasaran">
    <p class="sasaran-view-modal-name__label {{ $labelSize }} font-medium uppercase tracking-wide text-gray-500 mb-1.5">Nama Sasaran</p>
    <p class="sasaran-view-modal-name__value {{ $nameSize }} font-bold text-gray-900 break-words leading-snug">{{ $nama }}</p>
</section>
