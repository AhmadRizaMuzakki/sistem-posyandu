@extends('errors.layout')

@section('title', 'Terlalu Banyak Permintaan')

@section('content')
    <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-amber-100 text-amber-600 mb-6">
        <i class="fa-solid fa-gauge-high text-4xl"></i>
    </div>
    <h1 class="text-7xl font-extrabold text-slate-800 mb-2">429</h1>
    <p class="text-xl font-semibold text-slate-700 mb-2">Terlalu Banyak Permintaan</p>
    <p class="text-slate-600">Anda mengirim terlalu banyak permintaan. Mohon tunggu beberapa saat sebelum mencoba lagi.</p>
@endsection
