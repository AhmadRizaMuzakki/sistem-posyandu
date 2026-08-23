@props([
    'status' => null,
    'showLabel' => true,
    'label' => null,
])

@php
    use App\Helpers\AduanOptions;
    $progress = AduanOptions::statusProgress($status);
    $barClasses = AduanOptions::statusProgressBarClasses($status);
    $displayLabel = $label ?? AduanOptions::statusLabel($status);
@endphp

@if($status)
    <div {{ $attributes->merge(['class' => 'space-y-2']) }}>
        <div class="flex items-center justify-between gap-2">
            @if($showLabel)
                <span class="text-xs font-medium text-gray-500">{{ $displayLabel }}</span>
            @endif
            <span class="text-xs font-bold tabular-nums {{ $progress >= 100 ? 'text-green-600' : ($progress >= 50 ? 'text-blue-600' : ($progress >= 25 ? 'text-amber-600' : 'text-red-600')) }}">
                {{ $progress }}%
            </span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden shadow-inner">
            <div class="{{ $barClasses }} h-2.5 rounded-full transition-all duration-700 ease-out"
                 style="width: {{ $progress }}%"></div>
        </div>
    </div>
@endif
