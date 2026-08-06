@php
    use App\Helpers\ImunisasiOptions;

    $currentValue = $jenis_imunisasi ?? '';
    $allValues = ImunisasiOptions::allOptionValues();
    $isLegacyValue = $currentValue !== '' && ! in_array($currentValue, $allValues, true);
    $jadwal = ImunisasiOptions::jadwalBayiBalita();
    $lainLain = ImunisasiOptions::LAIN_LAIN;
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
            aria-required="true"
            {{ $attributes->merge(['class' => 'shadow appearance-none border rounded w-full py-2 px-3 text-left text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary bg-white flex items-center justify-between gap-2' . ($errors->has('jenis_imunisasi') ? ' border-red-500' : '')]) }}>
        <span class="truncate" :class="selected ? 'text-gray-800' : 'text-gray-400'" x-text="label()"></span>
        <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="open"
         @click.outside="open = false"
         x-cloak
         class="absolute z-[60] mt-1 w-full min-w-[20rem] sm:min-w-[28rem] rounded-md border border-gray-300 bg-white shadow-lg flex flex-col"
         style="display: none; max-height: min(20rem, 55vh);">
        <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain p-2"
             style="-webkit-overflow-scrolling: touch;"
             @wheel.stop>
            @if($isLegacyValue)
                <button type="button"
                        @click="selected = @js($currentValue); open = false"
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

        <div class="shrink-0 border-t border-gray-200 bg-white p-2">
            <button type="button"
                    @click="selected = @js($lainLain); open = false"
                    class="w-full rounded px-2 py-2 text-left text-sm font-medium text-gray-700 hover:bg-teal-50 hover:text-primary"
                    :class="selected === @js($lainLain) ? 'bg-teal-50 text-primary ring-1 ring-teal-200' : ''">
                {{ $lainLain }}
            </button>
        </div>
    </div>
</div>
