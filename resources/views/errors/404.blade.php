@extends('errors.layout')

@section('title', 'Halaman Tidak Ditemukan')

@section('content')
    <div class="error-icon-wrap inline-flex items-center justify-center w-24 h-24 rounded-full bg-teal-100 text-[#0D9488] mb-6">
        <i class="fa-solid fa-magnifying-glass text-4xl" aria-hidden="true"></i>
    </div>
    <h1 class="error-code text-7xl font-extrabold text-slate-800 mb-2">404</h1>
    <p class="error-title text-xl font-semibold text-slate-700 mb-2">Halaman Tidak Ditemukan</p>
    <p class="error-desc text-slate-600">Halaman yang Anda cari tidak ada atau telah dipindahkan. Cek kembali URL atau gunakan menu untuk navigasi.</p>
@endsection
