@php
    use App\Helpers\ImunisasiOptions;

    $currentValue = $jenis_imunisasi ?? '';
    $allValues = ImunisasiOptions::allOptionValues();
    $isLegacyValue = $currentValue !== '' && ! in_array($currentValue, $allValues, true);
    $jadwal = ImunisasiOptions::jadwalBayiBalita();
@endphp

<div class="relative"
     x-data="{
        open: false,
        selected: @entangle('jenis_imunisasi'),
        label() {
            return this.selected ? this.selected : 'Pilih Jenis Imunisasi...';
        }
     }"
     @keydown.escape.window="open = false">
    <button type="button"
            @click="open = !open"
            {{ $attributes->merge(['class' => 'shadow appearance-none border rounded w-full py-2 px-3 text-left text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary bg-white flex items-center justify-between gap-2']) }}>
        <span class="truncate" :class="selected ? 'text-gray-800' : 'text-gray-400'" x-text="label()"></span>
        <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="open"
         @click.outside="open = false"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute z-50 mt-1 w-full min-w-[20rem] sm:min-w-[28rem] rounded-md border border-gray-300 bg-white shadow-lg"
         style="display: none;">
        <div class="max-h-72 overflow-y-auto p-2">
            @if($isLegacyValue)
                <button type="button"
                        @click="selected = '{{ $currentValue }}'; open = false"
                        class="mb-2 w-full rounded border border-amber-200 bg-amber-50 px-2 py-1.5 text-left text-xs text-amber-800 hover:bg-amber-100">
                    {{ $currentValue }} (data lama)
                </button>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach($jadwal as $usia => $vaksins)
                    <div class="rounded border border-gray-100 bg-gray-50 p-2">
                        <p class="mb-1 text-xs font-bold text-gray-600">Usia {{ $usia }}</p>
                        <div class="flex flex-col gap-0.5">
                            @foreach($vaksins as $vaksin)
                                <button type="button"
                                        @click="selected = @js($vaksin); open = false"
                                        class="rounded px-2 py-1 text-left text-sm text-gray-700 hover:bg-white hover:text-primary"
                                        :class="selected === @js($vaksin) ? 'bg-teal-50 font-medium text-primary ring-1 ring-teal-200' : ''">
                                    {{ $vaksin }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
