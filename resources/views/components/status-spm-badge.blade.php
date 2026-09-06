@props(['status' => null])

@php
    use App\Helpers\SpmOptions;
    $label = SpmOptions::statusLabel($status);
    $classes = SpmOptions::statusBadgeClasses($status);
@endphp

@if($status)
    <span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$classes}"]) }}>
        {{ $label }}
    </span>
@else
    <span {{ $attributes->merge(['class' => 'text-sm text-gray-500']) }}>-</span>
@endif
