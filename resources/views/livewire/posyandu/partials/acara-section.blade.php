{{-- Section Acara --}}
<div class="p-6">
    {{-- Header Section --}}
    <div class="mb-6">
        <div class="bg-gradient-to-r from-primary/10 via-primary/5 to-transparent rounded-xl p-6 mb-6 border border-primary/20">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800 flex items-center mb-2">
                        <i class="ph ph-calendar-dots text-3xl mr-3 text-primary"></i>
                        Daftar Acara / Kegiatan
                    </h2>
                    <p class="text-gray-600 text-sm">Kelola acara dan kegiatan posyandu yang akan ditampilkan di halaman depan</p>
                </div>
            </div>
        </div>
    </div>

    @if($kegiatansList->isEmpty())
        <div class="bg-gradient-to-br from-yellow-50 to-orange-50 border-2 border-yellow-200 rounded-2xl p-12 text-center shadow-sm">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-yellow-100 rounded-full mb-4">
                <i class="ph ph-calendar-x text-4xl text-yellow-600"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-2">Belum Ada Acara</h3>
            <p class="text-gray-600 max-w-md mx-auto">Belum ada acara untuk bulan ini. Silakan tambahkan acara di tab Jadwal.</p>
        </div>
    @else
        @php
            $totalAcara = $kegiatansList->total();
            // Hitung statistik dari semua data bulan ini (bukan hanya halaman saat ini)
            $startOfMonth = \Carbon\Carbon::now()->startOfMonth();
            $endOfMonth = \Carbon\Carbon::now()->endOfMonth();
            $posyanduIdForQuery = $posyandu->id_posyandu ?? null;
            
            if ($posyanduIdForQuery) {
                $allKegiatans = \App\Models\JadwalKegiatan::where('id_posyandu', $posyanduIdForQuery)
                    ->whereBetween('tanggal', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
                    ->get();
                
                $acaraHariIni = $allKegiatans->filter(function($acara) {
                    return \Carbon\Carbon::parse($acara->tanggal)->isToday();
                })->count();
                $acaraMendatang = $allKegiatans->filter(function($acara) {
                    return \Carbon\Carbon::parse($acara->tanggal)->isFuture();
                })->count();
                $acaraLewat = $allKegiatans->filter(function($acara) {
                    $tanggal = \Carbon\Carbon::parse($acara->tanggal);
                    return $tanggal->isPast() && !$tanggal->isToday();
                })->count();
            } else {
                // Fallback jika posyandu tidak tersedia
                $acaraHariIni = 0;
                $acaraMendatang = 0;
                $acaraLewat = 0;
            }
        @endphp

        {{-- Statistik Ringkasan --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-600 uppercase">Total Acara</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $totalAcara }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="ph ph-calendar-dots text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-600 uppercase">Hari Ini</p>
                        <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $acaraHariIni }}</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <i class="ph ph-star text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-600 uppercase">Mendatang</p>
                        <p class="text-2xl font-bold text-green-600 mt-1">{{ $acaraMendatang }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="ph ph-arrow-up text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-gray-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-600 uppercase">Terlampaui</p>
                        <p class="text-2xl font-bold text-gray-600 mt-1">{{ $acaraLewat }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                        <i class="ph ph-history text-gray-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabel Acara --}}
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-amber-50 to-orange-50 border-b border-amber-200">
                <h3 class="text-lg font-bold text-gray-800 flex items-center">
                    <i class="ph ph-list text-xl mr-2 text-amber-600"></i>
                    Acara Bulan Ini ({{ $totalAcara }})
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-amber-50 border-b border-amber-200">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Nama Acara</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Tempat</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Jam</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-amber-100">
                        @foreach($kegiatansList as $acara)
                            @php
                                $tanggal = \Carbon\Carbon::parse($acara->tanggal);
                                $isToday = $tanggal->isToday();
                                $isPast = $tanggal->isPast() && !$isToday;
                                $isFuture = $tanggal->isFuture();
                            @endphp
                            <tr class="hover:bg-amber-50/50 transition-colors {{ $isPast ? 'opacity-75' : '' }}">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 {{ $isToday ? 'bg-yellow-100' : ($isFuture ? 'bg-green-100' : 'bg-gray-100') }} rounded-lg flex items-center justify-center mr-3">
                                            <i class="ph ph-calendar {{ $isToday ? 'text-yellow-600' : ($isFuture ? 'text-green-600' : 'text-gray-400') }} text-sm"></i>
                                        </div>
                                        <div>
                                            <div class="text-gray-800 font-medium">{{ $tanggal->locale('id')->translatedFormat('d M Y') }}</div>
                                            <div class="text-xs text-gray-500">{{ $tanggal->locale('id')->translatedFormat('l') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-gray-800">{{ $acara->nama_kegiatan }}</div>
                                    @if($acara->deskripsi_kegiatan)
                                        <div class="text-xs text-gray-500 mt-1">{{ Str::limit($acara->deskripsi_kegiatan, 50) }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    @if($acara->tempat)
                                        <div class="flex items-center">
                                            <i class="ph ph-map-pin text-primary mr-1.5 text-xs"></i>
                                            <span>{{ $acara->tempat }}</span>
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center text-gray-600">
                                    @if($acara->jam_mulai || $acara->jam_selesai)
                                        <div class="flex items-center justify-center">
                                            <i class="ph ph-clock text-primary mr-1.5 text-xs"></i>
                                            <span>
                                                @if($acara->jam_mulai && $acara->jam_selesai)
                                                    {{ \Carbon\Carbon::parse($acara->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($acara->jam_selesai)->format('H:i') }}
                                                @elseif($acara->jam_mulai)
                                                    {{ \Carbon\Carbon::parse($acara->jam_mulai)->format('H:i') }}
                                                @elseif($acara->jam_selesai)
                                                    Sampai {{ \Carbon\Carbon::parse($acara->jam_selesai)->format('H:i') }}
                                                @endif
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button type="button" 
                                                wire:click="editKegiatan({{ $acara->id_jadwal_kegiatan }})" 
                                                class="text-primary hover:bg-primary/10 rounded-lg p-2 transition" 
                                                title="Edit">
                                            <i class="ph ph-pencil text-base"></i>
                                        </button>
                                        <button type="button" 
                                                wire:click="deleteKegiatan({{ $acara->id_jadwal_kegiatan }})" 
                                                wire:confirm="Hapus acara ini?"
                                                class="text-red-600 hover:bg-red-50 rounded-lg p-2 transition" 
                                                title="Hapus">
                                            <i class="ph ph-trash text-base"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            @if($kegiatansList->hasPages())
                <div class="px-6 py-4 border-t border-amber-200 bg-amber-50/50">
                    {{ $kegiatansList->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
