@extends('errors.layout')

@section('title', 'Status Sistem')

@section('content')
    <div class="error-icon-wrap inline-flex items-center justify-center w-24 h-24 rounded-full bg-amber-100 text-amber-600 mb-6">
        <i class="fa-solid fa-circle-info text-4xl"></i>
    </div>
    <p class="error-code text-7xl font-extrabold text-slate-800 mb-2">Status</p>
    <p class="error-title text-xl font-semibold text-slate-700 mb-2">Layanan sementara tidak tersedia</p>
    <p class="error-desc text-slate-600">
        {{ $message ?? 'Sistem sedang mengalami gangguan atau pemeliharaan. Silakan coba beberapa saat lagi.' }}
    </p>
    <p class="text-sm text-slate-500 mt-4">
        Jika masalah berlanjut, hubungi kader atau admin Posyandu Karanggan.
    </p>
@endsection
