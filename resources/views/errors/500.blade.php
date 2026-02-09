@extends('errors.layout')

@section('title', 'Kesalahan Server')

@section('content')
    <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-red-100 text-red-600 mb-6">
        <i class="fa-solid fa-triangle-exclamation text-4xl"></i>
    </div>
    <h1 class="text-7xl font-extrabold text-slate-800 mb-2">500</h1>
    <p class="text-xl font-semibold text-slate-700 mb-2">Kesalahan Server</p>
    <p class="text-slate-600">Terjadi kesalahan di pihak server. Tim kami akan segera memperbaikinya. Silakan coba lagi nanti atau kembali ke beranda.</p>
@endsection
