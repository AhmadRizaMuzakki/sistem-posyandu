@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        {{-- Teks info --}}
        <p class="text-sm text-slate-600 order-2 sm:order-1">
            Menampilkan
            @if ($paginator->firstItem())
                <span class="font-semibold text-slate-800">{{ $paginator->firstItem() }}</span>
                –
                <span class="font-semibold text-slate-800">{{ $paginator->lastItem() }}</span>
            @else
                {{ $paginator->count() }}
            @endif
            dari
            <span class="font-semibold text-slate-800">{{ $paginator->total() }}</span>
            acara
        </p>

        {{-- Tombol halaman --}}
        <div class="inline-flex items-center rounded-xl border border-slate-200 bg-white p-1 shadow-sm order-1 sm:order-2">
            {{-- Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-slate-400 bg-slate-50 rounded-lg cursor-not-allowed border border-slate-100">
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                    Sebelumnya
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-slate-700 bg-white rounded-lg border border-slate-200 hover:bg-primary hover:text-white hover:border-primary transition">
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                    Sebelumnya
                </a>
            @endif

            <span class="mx-2 w-px h-6 bg-slate-200" aria-hidden="true"></span>

            {{-- Nomor halaman --}}
            <div class="flex items-center gap-1">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="px-3 py-2 text-sm text-slate-500">…</span>
                    @endif
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="min-w-[2.5rem] inline-flex items-center justify-center px-3 py-2.5 text-sm font-semibold text-white bg-primary rounded-lg shadow-sm" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="min-w-[2.5rem] inline-flex items-center justify-center px-3 py-2.5 text-sm font-medium text-slate-700 bg-white rounded-lg border border-slate-200 hover:bg-primary hover:text-white hover:border-primary transition" aria-label="Ke halaman {{ $page }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            <span class="mx-2 w-px h-6 bg-slate-200" aria-hidden="true"></span>

            {{-- Selanjutnya --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-slate-700 bg-white rounded-lg border border-slate-200 hover:bg-primary hover:text-white hover:border-primary transition">
                    Selanjutnya
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </a>
            @else
                <span class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-slate-400 bg-slate-50 rounded-lg cursor-not-allowed border border-slate-100">
                    Selanjutnya
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </span>
            @endif
        </div>
    </nav>
@endif
