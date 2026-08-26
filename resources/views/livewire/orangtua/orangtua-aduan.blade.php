<div class="space-y-6">
    @php
        use App\Helpers\AduanOptions;
    @endphp
    {{-- Header --}}
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-xl font-semibold text-gray-800 flex items-center">
            <i class="ph ph-megaphone text-2xl mr-3 text-primary"></i>
            Posyandu 6 SPM
        </h2>
        <p class="text-sm text-gray-500 mt-2">Lihat riwayat 6 SPM yang telah dibuat oleh keluarga Anda.</p>
    </div>

    {{-- Daftar 6 SPM --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 sm:px-8 pt-6 sm:pt-7 pb-5 sm:pb-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 leading-snug">Riwayat Posyandu 6 SPM</h3>
            <p class="text-sm text-gray-500 mt-2.5 leading-relaxed">
                @if($totalBaris > 0)
                    Menampilkan {{ $firstItem }}–{{ $lastItem }} dari {{ $totalBaris }} 6 SPM
                @else
                    0 data 6 SPM ditemukan
                @endif
                @if($filterAktif)
                    <span class="inline-flex items-center mt-2 sm:mt-0 sm:ml-2 px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">
                        Filter aktif
                        @if($periodeLabel)
                            · {{ $periodeLabel }}
                        @endif
                    </span>
                @endif
            </p>
        </div>

        {{-- Filter --}}
        <div class="px-6 sm:px-8 py-5 sm:py-6 bg-gray-50/80 border-b border-gray-100">
            <div class="flex items-center gap-2 mb-4">
                <i class="ph ph-funnel text-primary text-base"></i>
                <span class="text-sm font-medium text-gray-700">Filter 6 SPM</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 sm:gap-5">
                <div>
                    <label for="filter-status" class="block text-xs font-medium text-gray-500 mb-2">Status</label>
                    <select id="filter-status"
                            wire:model.live="filterStatus"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm bg-white shadow-sm">
                        <option value="">Semua Status</option>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filter-kategori" class="block text-xs font-medium text-gray-500 mb-2">Bidang SPM</label>
                    <select id="filter-kategori"
                            wire:model.live="filterKategori"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm bg-white shadow-sm">
                        <option value="">Semua Bidang SPM</option>
                        @foreach($kategoriOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filter-bulan" class="block text-xs font-medium text-gray-500 mb-2">Bulan</label>
                    <select id="filter-bulan"
                            wire:model.live="filterBulan"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm bg-white shadow-sm">
                        <option value="">Semua Bulan</option>
                        @foreach(['1'=>'Januari','2'=>'Februari','3'=>'Maret','4'=>'April','5'=>'Mei','6'=>'Juni','7'=>'Juli','8'=>'Agustus','9'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $v => $l)
                            <option value="{{ $v }}">{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filter-tahun" class="block text-xs font-medium text-gray-500 mb-2">Tahun</label>
                    <select id="filter-tahun"
                            wire:model.live="filterTahun"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm bg-white shadow-sm">
                        <option value="">Semua Tahun</option>
                        @for($y = now()->year; $y >= now()->year - 5; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label for="filter-limit" class="block text-xs font-medium text-gray-500 mb-2">Per halaman</label>
                    <select id="filter-limit"
                            wire:model.live="filterLimit"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm bg-white shadow-sm">
                        @foreach([5, 10, 25, 50] as $n)
                            <option value="{{ $n }}">{{ $n }} baris</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        @if($totalBaris > 0)
            <div class="p-6 sm:p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($aduanList as $aduan)
                        @php
                            $kategoriClasses = AduanOptions::kategoriBadgeClasses($aduan->kategori);
                            $kategoriLabel = AduanOptions::kategoriLabel($aduan->kategori);
                            $statusIcon = AduanOptions::statusIcon($aduan->status);
                            $statusBorder = AduanOptions::statusCardBorderClass($aduan->status);
                            $statusIconWrap = AduanOptions::statusIconWrapClasses($aduan->status);
                        @endphp
                        <article class="group bg-white border border-gray-100 border-l-4 {{ $statusBorder }} rounded-xl shadow-sm hover:shadow-md hover:border-gray-200 transition-all duration-200 overflow-hidden flex flex-col">
                            <div class="p-5 sm:p-6 flex-1 flex flex-col">
                                {{-- Header --}}
                                <div class="flex items-start gap-5">
                                    <div class="shrink-0 w-11 h-11 rounded-xl ring-1 flex items-center justify-center {{ $statusIconWrap }}">
                                        <i class="ph {{ $statusIcon }} text-xl"></i>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-0.5">
                                        <h4 class="text-base font-semibold text-gray-900 leading-snug line-clamp-2 group-hover:text-primary transition-colors pr-1">
                                            {{ $aduan->judul }}
                                        </h4>
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1.5 mt-2">
                                            <p class="text-xs text-gray-400 flex items-center gap-1">
                                                <i class="ph ph-calendar-blank"></i>
                                                {{ $aduan->tanggal_aduan?->locale('id')->translatedFormat('d F Y') ?? '-' }}
                                            </p>
                                            <x-status-aduan-badge :status="$aduan->status" class="shrink-0" />
                                        </div>
                                    </div>
                                </div>

                                {{-- Meta --}}
                                <div class="mt-4 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium ring-1 ring-inset {{ $kategoriClasses }}">
                                        <i class="ph ph-tag text-sm opacity-70"></i>
                                        {{ $kategoriLabel }}
                                    </span>
                                    @if($aduan->posyandu)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-100">
                                            <i class="ph ph-house-line text-sm opacity-70"></i>
                                            {{ $aduan->posyandu->nama_posyandu }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Isi aduan --}}
                                <div class="mt-4 rounded-xl bg-gray-50/80 border border-gray-100 p-4 flex-1">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1.5">Isi 6 SPM</p>
                                    <p class="text-sm text-gray-700 leading-relaxed line-clamp-3">
                                        {{ $aduan->isi_aduan }}
                                    </p>
                                </div>

                                {{-- Progres --}}
                                <div class="mt-4 pt-4 border-t border-gray-100">
                                    <x-aduan-progress-bar
                                        :status="$aduan->status"
                                        label="Progres Penanganan"
                                    />
                                </div>

                                @if($aduan->tanggapan)
                                    <div class="mt-4 rounded-xl border border-blue-100 bg-gradient-to-br from-blue-50/80 to-white p-4">
                                        <div class="flex items-center gap-2 mb-2">
                                            <div class="w-7 h-7 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                                <i class="ph ph-chat-circle-text text-sm"></i>
                                            </div>
                                            <p class="text-xs font-semibold text-blue-800 uppercase tracking-wide">Tanggapan Posyandu</p>
                                        </div>
                                        <p class="text-sm text-gray-700 leading-relaxed pl-9">
                                            {{ $aduan->tanggapan }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            @if($hasPages)
                <div class="px-6 sm:px-8 py-4 sm:py-5 border-t border-gray-100 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs sm:text-sm text-gray-500">
                        Menampilkan
                        <span class="font-medium text-gray-700">{{ $firstItem }}</span>
                        –
                        <span class="font-medium text-gray-700">{{ $lastItem }}</span>
                        dari
                        <span class="font-medium text-gray-700">{{ $totalBaris }}</span>
                        6 SPM
                    </p>
                    <div class="flex items-center gap-1.5 flex-wrap justify-center sm:justify-end">
                        <button wire:click="previousPage"
                                @if($currentPage <= 1) disabled @endif
                                class="inline-flex items-center justify-center w-9 h-9 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                                aria-label="Sebelumnya">
                            <i class="ph ph-caret-left text-base"></i>
                        </button>

                        @for($page = max(1, $currentPage - 2); $page <= min($lastPage, $currentPage + 2); $page++)
                            <button wire:click="gotoPage({{ $page }})"
                                    class="inline-flex items-center justify-center min-w-[2.25rem] h-9 px-2.5 text-sm font-medium rounded-lg transition-colors
                                    {{ $page == $currentPage
                                        ? 'bg-primary text-white border border-primary shadow-sm'
                                        : 'text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 hover:border-gray-300' }}">
                                {{ $page }}
                            </button>
                        @endfor

                        <button wire:click="nextPage"
                                @if($currentPage >= $lastPage) disabled @endif
                                class="inline-flex items-center justify-center w-9 h-9 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                                aria-label="Berikutnya">
                            <i class="ph ph-caret-right text-base"></i>
                        </button>
                    </div>
                </div>
            @endif
        @else
            <div class="text-center py-16 sm:py-20 px-6 sm:px-8">
                <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                    <i class="ph ph-megaphone text-2xl text-gray-300"></i>
                </div>
                <h4 class="text-base font-semibold text-gray-700 mb-2">Belum Ada 6 SPM</h4>
                <p class="text-sm text-gray-500 max-w-md mx-auto leading-relaxed">
                    @if($filterAktif)
                        Tidak ada data 6 SPM yang sesuai dengan filter yang dipilih.
                    @else
                        Belum ada data 6 SPM yang tercatat untuk keluarga Anda.
                    @endif
                </p>
            </div>
        @endif
    </div>

    <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 sm:p-6">
        <div class="flex items-start gap-3">
            <i class="ph ph-info text-amber-600 text-lg mt-0.5"></i>
            <div>
                <h4 class="text-sm font-semibold text-amber-800 mb-1">Informasi</h4>
                <p class="text-sm text-amber-700 leading-relaxed">
                    Halaman ini hanya untuk melihat riwayat Posyandu 6 SPM. Status 6 SPM akan diperbarui oleh kader atau admin posyandu. Jika memerlukan bantuan segera, silakan hubungi kader posyandu terdekat.
                </p>
            </div>
        </div>
    </div>
</div>
