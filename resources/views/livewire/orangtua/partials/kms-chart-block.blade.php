@php
    $compact = $compact ?? false;
@endphp
<div>
    @if(!$compact)
        <div class="flex flex-wrap items-center gap-2 mb-2">
            <h3 class="text-sm font-semibold text-gray-800">{{ $kms['title'] ?? $badge }}</h3>
            <span class="px-2 py-0.5 text-xs rounded-full border {{ $badgeClass }}">{{ $badge }}</span>
        </div>
        @if(!empty($kms['subtitle']))
            <p class="text-xs text-gray-500 mb-3">{{ $kms['subtitle'] }}</p>
        @endif
        @if(!empty($kms['legend']))
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2 mb-4">
                @foreach($kms['legend'] as $leg)
                    <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-2">
                        <span class="shrink-0 w-3.5 h-3.5 rounded-sm border border-gray-300" style="background: {{ $leg['color'] }}"></span>
                        <div class="min-w-0 leading-tight">
                            <div class="text-[11px] font-semibold text-gray-800 tabular-nums">{{ $leg['range'] ?? ($leg['label'] ?? '') }}</div>
                            <div class="text-[10px] text-gray-500">{{ $leg['status'] ?? '' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @else
        <div class="flex items-center gap-2 mb-2">
            <h4 class="text-sm font-semibold text-gray-800">{{ $shortTitle ?? $badge }}</h4>
            <span class="px-2 py-0.5 text-xs rounded-full border {{ $badgeClass }}">{{ $badge }}</span>
        </div>
    @endif
    <div wire:ignore class="relative {{ $compact ? 'h-80 sm:h-96' : 'h-[28rem] sm:h-[32rem]' }} w-full">
        <canvas
            class="orangtua-grafik-canvas w-full h-full"
            data-mode="{{ $mode }}"
            data-labels='@json($labels)'
            data-berat='@json($berat)'
            data-tinggi='@json($tinggi)'
            data-kms='@json($kms)'
        ></canvas>
    </div>
</div>
