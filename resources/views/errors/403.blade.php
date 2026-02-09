@extends('errors.layout')

@section('title', 'Akses Ditolak')

@section('content')
    <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-amber-100 text-amber-600 mb-6">
        <i class="fa-solid fa-lock text-4xl"></i>
    </div>
    <h1 class="text-7xl font-extrabold text-slate-800 mb-2">403</h1>
    <p class="text-xl font-semibold text-slate-700 mb-2">Akses Ditolak</p>
    <p class="text-slate-600">Anda tidak memiliki izin untuk mengakses halaman ini. Jika Anda merasa ini salah, hubungi administrator.</p>
@endsection
