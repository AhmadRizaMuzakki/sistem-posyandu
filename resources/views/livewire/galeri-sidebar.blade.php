{{-- Widget Galeri: 9 foto 3x3, format 4:3 --}}
<div class="mt-4 pt-4 border-t border-gray-100">
    <div class="grid grid-cols-3 gap-1.5">
        @foreach($items->pad(9, null) as $index => $item)
            <a href="{{ $galeriUrl }}" class="block aspect-[4/3] rounded overflow-hidden bg-gray-100 border border-gray-200">
                @if($item)
                    <img src="{{ uploads_asset($item->path) }}" alt="{{ $item->caption ?? '' }}" class="w-full h-full object-cover">
                @else
                    <span class="flex w-full h-full items-center justify-center text-gray-300 text-xs">
                        <i class="ph ph-image text-2xl"></i>
                    </span>
                @endif
            </a>
        @endforeach
    </div>
</div>
