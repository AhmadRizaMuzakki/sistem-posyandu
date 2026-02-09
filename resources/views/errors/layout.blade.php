<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Error') - Posyandu Karanggan</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/home.jpeg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .error-fade-in { animation: errorFadeIn 0.6s ease-out; }
        @keyframes errorFadeIn {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }
        /* Fallback agar tombol & tampilan tetap rapi jika Tailwind belum load */
        .error-page { min-height: 100vh; background: linear-gradient(to bottom right, #F0FDFA, #fff, rgba(240,253,250,0.3)); color: #1e293b; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 3rem 1rem; box-sizing: border-box; }
        .error-logo { position: absolute; top: 1.5rem; left: 50%; transform: translateX(-50%); display: flex; align-items: center; gap: 0.5rem; color: #475569; text-decoration: none; font-weight: 700; font-size: 1.125rem; }
        .error-logo:hover { color: #0D9488; }
        .error-logo img { width: 2.5rem; height: 2.5rem; border-radius: 9999px; object-fit: cover; border: 2px solid rgba(13,148,136,0.2); }
        .error-main { width: 100%; max-width: 32rem; text-align: center; }
        .error-actions { margin-top: 2.5rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 0.75rem; }
        .error-btn-primary { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; border-radius: 9999px; background: #0D9488; color: #fff !important; font-weight: 600; text-decoration: none; box-shadow: 0 10px 15px -3px rgba(13,148,136,0.25); transition: background 0.2s; }
        .error-btn-primary:hover { background: #0F766E; }
        .error-btn-secondary { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; border-radius: 9999px; border: 2px solid #0D9488; color: #0D9488 !important; font-weight: 600; text-decoration: none; transition: background 0.2s, color 0.2s; }
        .error-btn-secondary:hover { background: #0D9488; color: #fff !important; }
        .error-icon-wrap { display: inline-flex; align-items: center; justify-content: center; width: 6rem; height: 6rem; border-radius: 9999px; margin-bottom: 1.5rem; background: #ccfbf1; color: #0D9488; }
        .error-icon-wrap i { font-size: 2.25rem; }
        .error-code { font-size: 4rem; font-weight: 800; color: #1e293b; margin-bottom: 0.5rem; line-height: 1; }
        .error-title { font-size: 1.25rem; font-weight: 600; color: #334155; margin-bottom: 0.5rem; }
        .error-desc { color: #64748b; line-height: 1.6; }
    </style>
</head>
<body class="error-page min-h-screen bg-gradient-to-br from-[#F0FDFA] via-white to-teal-50/30 text-slate-800 antialiased flex flex-col items-center justify-center px-4 py-12">
    <a href="{{ url('/') }}" class="error-logo absolute top-6 left-1/2 -translate-x-1/2 flex items-center gap-2 text-slate-600 hover:text-[#0D9488] transition">
        <img src="{{ asset('images/home.jpeg') }}" alt="Logo" class="w-10 h-10 rounded-full object-cover ring-2 ring-[#0D9488]/20">
        <span class="font-bold text-lg">Posyandu Karanggan</span>
    </a>

    <main class="error-main w-full max-w-lg text-center error-fade-in">
        @yield('content')
    </main>

    <div class="error-actions mt-10 flex flex-wrap items-center justify-center gap-3">
        <a href="{{ url('/') }}" class="error-btn-primary inline-flex items-center gap-2 px-6 py-3 rounded-full bg-[#0D9488] text-white font-semibold shadow-lg shadow-teal-500/25 hover:bg-[#0F766E] transition">
            <i class="fa-solid fa-house" aria-hidden="true"></i> <span>Kembali ke Beranda</span>
        </a>
        @if(Route::has('login') && !auth()->check())
            <a href="{{ route('login') }}" class="error-btn-secondary inline-flex items-center gap-2 px-6 py-3 rounded-full border-2 border-[#0D9488] text-[#0D9488] font-semibold hover:bg-[#0D9488] hover:text-white transition">
                <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i> <span>Masuk</span>
            </a>
        @endif
    </div>
</body>
</html>
