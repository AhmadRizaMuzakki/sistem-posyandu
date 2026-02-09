@extends('errors.layout')

@section('title', 'Sesi Berakhir')

@section('content')
    <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-amber-100 text-amber-600 mb-6">
        <i class="fa-solid fa-clock-rotate-left text-4xl"></i>
    </div>
    <h1 class="text-7xl font-extrabold text-slate-800 mb-2">419</h1>
    <p class="text-xl font-semibold text-slate-700 mb-2">Sesi Berakhir</p>
    <p class="text-slate-600">Sesi Anda telah berakhir karena lama tidak aktif. Silakan muat ulang halaman dan coba lagi.</p>
@endsection
