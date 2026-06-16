<option value="">Pilih Pendidikan...</option>
@foreach (\App\Helpers\EnumConstants::pendidikan() as $pendidikan)
    <option value="{{ $pendidikan }}">{{ $pendidikan }}</option>
@endforeach
