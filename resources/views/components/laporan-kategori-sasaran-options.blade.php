@props([
    'kategoriList' => [],
    'kategoriLabels' => [],
])

<option value="">Semua Kategori</option>
@foreach($kategoriList as $kategori)
    <option value="{{ $kategori }}">{{ $kategoriLabels[$kategori] ?? ucfirst($kategori) }}</option>
@endforeach
@foreach(\App\Helpers\SasaranFilterOptions::extendedOptions() as $option)
    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
@endforeach
