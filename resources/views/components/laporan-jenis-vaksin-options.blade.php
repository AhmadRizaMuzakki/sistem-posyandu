@php
    use App\Helpers\ImunisasiOptions;
@endphp

@foreach(ImunisasiOptions::jadwalBayiBalita() as $usia => $vaksins)
    <optgroup label="Usia {{ $usia }}">
        @foreach($vaksins as $vaksin)
            <option value="{{ $vaksin }}">{{ $vaksin }}</option>
        @endforeach
    </optgroup>
@endforeach
