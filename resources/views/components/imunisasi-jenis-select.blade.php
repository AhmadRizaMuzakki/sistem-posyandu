@php
    use App\Helpers\ImunisasiOptions;

    $currentValue = $jenis_imunisasi ?? '';
    $allValues = ImunisasiOptions::allOptionValues();
    $isLegacyValue = $currentValue !== '' && ! in_array($currentValue, $allValues, true);
@endphp

<select wire:model="jenis_imunisasi"
        {{ $attributes->merge(['class' => 'shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary']) }}>
    <option value="">Pilih Jenis Imunisasi...</option>
    @if($isLegacyValue)
        <option value="{{ $currentValue }}">{{ $currentValue }} (data lama)</option>
    @endif
    @foreach(ImunisasiOptions::jadwalBayiBalita() as $usia => $vaksins)
        <optgroup label="Usia {{ $usia }}">
            @foreach($vaksins as $vaksin)
                <option value="{{ $vaksin }}">{{ $vaksin }}</option>
            @endforeach
        </optgroup>
    @endforeach
</select>
