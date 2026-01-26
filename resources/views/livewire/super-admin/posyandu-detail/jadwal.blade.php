<div>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes scaleIn {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .animate-fadeIn {
            animation: fadeIn 0.2s ease-out;
        }
        .animate-scaleIn {
            animation: scaleIn 0.2s ease-out;
        }
    </style>
    <div class="space-y-6">
        {{-- Header --}}
        @include('livewire.super-admin.posyandu-detail.header')

        {{-- Message Alert --}}
        @if (session()->has('message'))
            <div class="bg-{{ session('messageType') === 'success' ? 'green' : 'red' }}-50 border-l-4 border-{{ session('messageType') === 'success' ? 'green' : 'red' }}-500 text-{{ session('messageType') === 'success' ? 'green' : 'red' }}-700 px-4 py-3 rounded-lg shadow-sm flex items-center">
                <i class="ph ph-{{ session('messageType') === 'success' ? 'check-circle' : 'x-circle' }} text-xl mr-3"></i>
                <span class="font-medium">{{ session('message') }}</span>
            </div>
        @endif

        {{-- Tab Navigation --}}
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="flex border-b border-gray-200">
                <button wire:click="$set('activeTab', 'jadwal')" 
                        class="flex-1 px-6 py-4 font-semibold text-sm transition-all
                        {{ $activeTab === 'jadwal' ? 'bg-primary text-white border-b-2 border-primary' : 'text-gray-600 hover:bg-gray-50' }}">
                    <i class="ph ph-calendar-blank mr-2"></i>
                    Jadwal
                </button>
                <button wire:click="$set('activeTab', 'absen')" 
                        class="flex-1 px-6 py-4 font-semibold text-sm transition-all
                        {{ $activeTab === 'absen' ? 'bg-primary text-white border-b-2 border-primary' : 'text-gray-600 hover:bg-gray-50' }}">
                    <i class="ph ph-clipboard-text mr-2"></i>
                    Absen
                </button>
                <button wire:click="$set('activeTab', 'acara')" 
                        class="flex-1 px-6 py-4 font-semibold text-sm transition-all
                        {{ $activeTab === 'acara' ? 'bg-primary text-white border-b-2 border-primary' : 'text-gray-600 hover:bg-gray-50' }}">
                    <i class="ph ph-calendar-dots mr-2"></i>
                    Acara
                </button>
            </div>

            {{-- Tab Content: Jadwal --}}
            @if($activeTab === 'jadwal')
                @include('livewire.super-admin.posyandu-detail.partials.jadwal-calendar')
            @endif

            {{-- Tab Content: Absen --}}
            @if($activeTab === 'absen')
                @include('livewire.super-admin.posyandu-detail.partials.absen-section')
            @endif

            {{-- Tab Content: Acara --}}
            @if($activeTab === 'acara')
                @include('livewire.posyandu.partials.acara-section')
            @endif
        </div>

        @if($activeTab === 'jadwal')
            <div class="mt-6 space-y-6">
                @include('livewire.posyandu.partials.input-petugas-card')
                @include('livewire.posyandu.partials.input-acara-card')
            </div>
        @endif
    </div>

    {{-- Modal Jadwal (Petugas saja) --}}
    @if($isModalOpen)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 animate-fadeIn p-4" wire:click="closeModal">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col" wire:click.stop>
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 shrink-0">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="ph ph-calendar-plus text-2xl mr-3 text-primary"></i>
                        {{ $selectedDate ? \Carbon\Carbon::parse($selectedDate)->locale('id')->translatedFormat('d F Y') : 'Jadwal' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-1.5 transition-colors">
                        <i class="ph ph-x text-xl"></i>
                    </button>
                </div>

                <div class="overflow-y-auto px-6 py-4">
                    <div>
                        <h4 class="text-sm font-bold text-gray-700 mb-2 flex items-center">
                            <i class="ph ph-users-three mr-2 text-primary"></i>Petugas yang bekerja
                        </h4>
                        @if($selectedDateJadwals->isNotEmpty())
                            <div class="space-y-2 mb-4">
                                @foreach($selectedDateJadwals as $existingJadwal)
                                    @php
                                        $presensiValue = strtolower($existingJadwal->presensi ?? 'belum_hadir');
                                        $presensiBg = $presensiValue === 'hadir' ? 'bg-green-50 border-green-200' : ($presensiValue === 'tidak_hadir' ? 'bg-red-50 border-red-200' : 'bg-gray-50 border-gray-200');
                                    @endphp
                                    <div class="flex items-center justify-between bg-white p-3 rounded-lg border {{ $presensiBg }} shadow-sm">
                                        <div class="flex-1">
                                            <span class="font-semibold text-gray-800">{{ $existingJadwal->petugasKesehatan->nama_petugas_kesehatan ?? '-' }}</span>
                                            <span class="text-xs ml-2">
                                                @if($presensiValue == 'hadir') <span class="text-green-700 font-medium">• Hadir</span>
                                                @elseif($presensiValue == 'tidak_hadir') <span class="text-red-700 font-medium">• Tidak Hadir</span>
                                                @else <span class="text-gray-600 font-medium">• Belum</span>
                                                @endif
                                            </span>
                                        </div>
                                        <button type="button" wire:click="openEditModal({{ $existingJadwal->id_jadwal }})" class="text-primary hover:bg-primary/10 rounded p-1.5">
                                            <i class="ph ph-pencil"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 mb-4">Belum ada petugas.</p>
                        @endif
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 shrink-0 flex gap-3">
                    <button type="button" wire:click="openAddJadwalModal" class="flex-1 px-5 py-3 bg-primary text-white rounded-lg hover:bg-primary/90 transition font-medium">
                        <i class="ph ph-plus-circle mr-2"></i>Tambah Petugas
                    </button>
                    <button type="button" wire:click="closeModal" class="flex-1 px-5 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">Tutup</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Container Info: Detail Hari (Petugas + Acara) - Read Only --}}
    @if($isDetailModalOpen && $detailDate)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 animate-fadeIn p-4" wire:click="closeDetailModal">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[85vh] overflow-hidden flex flex-col animate-scaleIn" wire:click.stop>
                {{-- Header --}}
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 bg-gradient-to-r from-primary/5 to-transparent shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center">
                            <i class="ph ph-calendar-dots text-xl text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">
                                {{ \Carbon\Carbon::parse($detailDate)->locale('id')->translatedFormat('d F Y') }}
                            </h3>
                            <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($detailDate)->locale('id')->translatedFormat('l') }}</p>
                        </div>
                    </div>
                    <button wire:click="closeDetailModal" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg p-1.5 transition-colors">
                        <i class="ph ph-x text-xl"></i>
                    </button>
                </div>

                {{-- Content --}}
                <div class="overflow-y-auto px-5 py-4 space-y-5">
                    {{-- Petugas yang Bekerja --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-base font-bold text-gray-800 flex items-center">
                                <i class="ph ph-users-three text-lg mr-2 text-primary"></i>
                                Petugas yang Bekerja
                            </h4>
                            <span class="text-xs text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full">{{ $detailDateJadwals->count() }} petugas</span>
                        </div>
                        @if($detailDateJadwals->isNotEmpty())
                            <div class="space-y-2">
                                @foreach($detailDateJadwals as $j)
                                    @if($j->petugasKesehatan)
                                        @php 
                                            $pv = strtolower($j->presensi ?? 'belum_hadir');
                                            $presensiBg = $pv === 'hadir' ? 'bg-green-50 border-green-200' : 
                                                          ($pv === 'tidak_hadir' ? 'bg-red-50 border-red-200' : 'bg-gray-50 border-gray-200');
                                            $presensiText = $pv === 'hadir' ? 'text-green-700' : 
                                                           ($pv === 'tidak_hadir' ? 'text-red-700' : 'text-gray-600');
                                            $presensiBadge = $pv === 'hadir' ? 'Hadir' : 
                                                           ($pv === 'tidak_hadir' ? 'Tidak Hadir' : 'Belum');
                                        @endphp
                                        <div class="flex items-center gap-3 p-3 rounded-lg border {{ $presensiBg }}">
                                            <div class="w-8 h-8 {{ $pv === 'hadir' ? 'bg-green-200' : ($pv === 'tidak_hadir' ? 'bg-red-200' : 'bg-gray-200') }} rounded-lg flex items-center justify-center flex-shrink-0">
                                                <i class="ph ph-user text-sm {{ $presensiText }}"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="font-semibold text-gray-800">{{ $j->petugasKesehatan->nama_petugas_kesehatan }}</div>
                                                @if($j->petugasKesehatan->bidan)
                                                    <div class="text-xs text-gray-500 mt-0.5">{{ $j->petugasKesehatan->bidan }}</div>
                                                @endif
                                            </div>
                                            <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $presensiText }} bg-white/50">
                                                {{ $presensiBadge }}
                                            </span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-6 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                                <i class="ph ph-users-three text-3xl text-gray-400 mb-2"></i>
                                <p class="text-sm text-gray-500">Belum ada petugas yang ditugaskan</p>
                            </div>
                        @endif
                    </div>

                    {{-- Acara/Kegiatan --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-base font-bold text-gray-800 flex items-center">
                                <i class="ph ph-calendar-dots text-lg mr-2 text-amber-600"></i>
                                Acara / Kegiatan
                            </h4>
                            <span class="text-xs text-gray-500 bg-amber-100 px-2.5 py-1 rounded-full">{{ $detailDateKegiatans->count() }} acara</span>
                        </div>
                        @if($detailDateKegiatans->isNotEmpty())
                            <div class="space-y-2">
                                @foreach($detailDateKegiatans as $k)
                                    <div class="p-3 rounded-lg border border-amber-200 bg-gradient-to-r from-amber-50 to-orange-50">
                                        <div class="flex items-start gap-2 mb-2">
                                            <i class="ph ph-calendar-dots text-amber-600 text-base mt-0.5 flex-shrink-0"></i>
                                            <h5 class="font-bold text-gray-800">{{ $k->nama_kegiatan }}</h5>
                                        </div>
                                        @if($k->deskripsi_kegiatan)
                                            <p class="text-xs text-gray-600 mb-2 ml-6">{{ $k->deskripsi_kegiatan }}</p>
                                        @endif
                                        <div class="flex flex-wrap items-center gap-3 text-xs ml-6">
                                            @if($k->tempat)
                                                <div class="flex items-center text-gray-700">
                                                    <i class="ph ph-map-pin text-primary mr-1"></i>
                                                    <span>{{ $k->tempat }}</span>
                                                </div>
                                            @endif
                                            @if($k->jam_mulai || $k->jam_selesai)
                                                <div class="flex items-center text-gray-700">
                                                    <i class="ph ph-clock text-primary mr-1"></i>
                                                    <span>
                                                        @if($k->jam_mulai && $k->jam_selesai)
                                                            {{ \Carbon\Carbon::parse($k->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($k->jam_selesai)->format('H:i') }}
                                                        @elseif($k->jam_mulai)
                                                            {{ \Carbon\Carbon::parse($k->jam_mulai)->format('H:i') }}
                                                        @elseif($k->jam_selesai)
                                                            Sampai {{ \Carbon\Carbon::parse($k->jam_selesai)->format('H:i') }}
                                                        @endif
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-6 bg-amber-50 rounded-lg border border-dashed border-amber-300">
                                <i class="ph ph-calendar-x text-3xl text-amber-400 mb-2"></i>
                                <p class="text-sm text-gray-500">Belum ada acara untuk hari ini</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-5 py-3 border-t border-gray-200 bg-gray-50 shrink-0">
                    <button type="button" wire:click="closeDetailModal" 
                            class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium text-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
