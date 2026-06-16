<option value="">Pilih Pekerjaan...</option>
@foreach (\App\Helpers\EnumConstants::pekerjaan() as $pekerjaan)
    <option value="{{ $pekerjaan }}">{{ $pekerjaan }}</option>
@endforeach
