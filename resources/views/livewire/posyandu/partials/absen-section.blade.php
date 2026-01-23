{{-- Section Absen --}}
<div class="p-6">
    {{-- Header Section --}}
    <div class="mb-6">
        <div class="bg-gradient-to-r from-primary/10 via-primary/5 to-transparent rounded-xl p-6 mb-6 border border-primary/20">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800 flex items-center mb-2">
                        <i class="ph ph-clipboard-text text-3xl mr-3 text-primary"></i>
                        Absensi Petugas
                    </h2>
                    <p class="text-gray-600 text-sm">Kelola presensi petugas kesehatan berdasarkan tanggal</p>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 mt-4">
                <div class="flex-1 max-w-xs w-full">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="ph ph-calendar-blank mr-1"></i>
                        Pilih Tanggal
                    </label>
                    <input type="date" 
                           wire:model="absenDate" 
                           class="w-full border-2 border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all shadow-sm">
                </div>
                <div class="pt-7 sm:pt-0">
                    <button wire:click="$set('absenDate', '{{ date('Y-m-d') }}')" 
                            class="px-5 py-2.5 text-sm font-semibold bg-primary text-white rounded-lg hover:bg-primary/90 transition-all shadow-md hover:shadow-lg flex items-center">
                        <i class="ph ph-calendar-check mr-2"></i>
                        Hari Ini
                    </button>
                </div>
            </div>
        </div>

        @if($absenDate)
            @php
                $selectedDateFormatted = \Carbon\Carbon::parse($absenDate)->locale('id')->translatedFormat('l, d F Y');
                $isToday = \Carbon\Carbon::parse($absenDate)->isToday();
            @endphp
            <div class="bg-blue-50 border-l-4 border-primary rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <i class="ph ph-calendar-check text-2xl text-primary mr-3"></i>
                    <div>
                        <p class="text-sm font-medium text-gray-700">Tanggal yang dipilih:</p>
                        <p class="text-lg font-bold text-gray-800">{{ $selectedDateFormatted }}</p>
                    </div>
                    @if($isToday)
                        <span class="ml-auto px-3 py-1 bg-primary text-white text-xs font-semibold rounded-full">Hari Ini</span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    @if($absenJadwals->isEmpty())
        <div class="bg-gradient-to-br from-yellow-50 to-orange-50 border-2 border-yellow-200 rounded-2xl p-12 text-center shadow-sm">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-yellow-100 rounded-full mb-4">
                <i class="ph ph-calendar-x text-4xl text-yellow-600"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-2">Tidak Ada Jadwal</h3>
            <p class="text-gray-600 max-w-md mx-auto">Belum ada jadwal petugas untuk tanggal yang dipilih. Silakan tambahkan jadwal terlebih dahulu di tab Jadwal.</p>
        </div>
    @else
        @php
            $totalJadwal = $absenJadwals->count();
            $hadirCount = $absenJadwals->where('presensi', 'hadir')->count();
            $tidakHadirCount = $absenJadwals->where('presensi', 'tidak_hadir')->count();
            $belumHadirCount = $absenJadwals->where('presensi', 'belum_hadir')->count();
        @endphp

        {{-- Statistik Ringkasan --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-600 uppercase">Total</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $totalJadwal }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="ph ph-users text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-600 uppercase">Hadir</p>
                        <p class="text-2xl font-bold text-green-600 mt-1">{{ $hadirCount }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="ph ph-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-600 uppercase">Tidak Hadir</p>
                        <p class="text-2xl font-bold text-red-600 mt-1">{{ $tidakHadirCount }}</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="ph ph-x-circle text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-gray-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-600 uppercase">Belum Hadir</p>
                        <p class="text-2xl font-bold text-gray-600 mt-1">{{ $belumHadirCount }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                        <i class="ph ph-clock text-gray-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- List Petugas --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($absenJadwals as $jadwal)
                @if($jadwal->petugasKesehatan)
                    @php
                        $presensiValue = strtolower($jadwal->presensi ?? 'belum_hadir');
                        $cardGradient = '';
                        $borderColor = '';
                        $iconBg = '';
                        $iconColor = '';
                        switch($presensiValue) {
                            case 'hadir':
                                $cardGradient = 'from-green-50 to-emerald-50';
                                $borderColor = 'border-green-300';
                                $iconBg = 'bg-green-500';
                                $iconColor = 'text-green-600';
                                break;
                            case 'tidak_hadir':
                                $cardGradient = 'from-red-50 to-rose-50';
                                $borderColor = 'border-red-300';
                                $iconBg = 'bg-red-500';
                                $iconColor = 'text-red-600';
                                break;
                            default:
                                $cardGradient = 'from-gray-50 to-slate-50';
                                $borderColor = 'border-gray-300';
                                $iconBg = 'bg-gray-400';
                                $iconColor = 'text-gray-600';
                        }
                    @endphp
                    <div class="bg-gradient-to-br {{ $cardGradient }} rounded-2xl shadow-lg border-2 {{ $borderColor }} p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                        {{-- Header Card --}}
                        <div class="flex items-start justify-between mb-5">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-12 h-12 {{ $iconBg }} rounded-xl flex items-center justify-center shadow-md">
                                        @if($presensiValue == 'hadir')
                                            <i class="ph ph-check text-white text-2xl"></i>
                                        @elseif($presensiValue == 'tidak_hadir')
                                            <i class="ph ph-x text-white text-2xl"></i>
                                        @else
                                            <i class="ph ph-clock text-white text-2xl"></i>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-lg font-bold text-gray-800 leading-tight">
                                            {{ $jadwal->petugasKesehatan->nama_petugas_kesehatan }}
                                        </h3>
                                        @if($jadwal->petugasKesehatan->bidan)
                                            <p class="text-sm text-gray-600 mt-1 flex items-center">
                                                <i class="ph ph-stethoscope mr-1.5"></i>
                                                {{ $jadwal->petugasKesehatan->bidan }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Status Badge --}}
                        <div class="mb-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                @if($presensiValue == 'hadir') bg-green-100 text-green-700
                                @elseif($presensiValue == 'tidak_hadir') bg-red-100 text-red-700
                                @else bg-gray-100 text-gray-700
                                @endif">
                                @if($presensiValue == 'hadir')
                                    <i class="ph ph-check-circle mr-1.5"></i>Hadir
                                @elseif($presensiValue == 'tidak_hadir')
                                    <i class="ph ph-x-circle mr-1.5"></i>Tidak Hadir
                                @else
                                    <i class="ph ph-clock mr-1.5"></i>Belum Hadir
                                @endif
                            </span>
                        </div>

                        {{-- Dropdown Presensi --}}
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="ph ph-clipboard-text mr-1"></i>
                                Update Presensi
                            </label>
                            <select wire:change="updatePresensi({{ $jadwal->id_jadwal }}, $event.target.value)" 
                                    class="w-full px-4 py-2.5 border-2 rounded-xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all shadow-sm
                                    @if($presensiValue == 'hadir') bg-green-50 text-green-700 border-green-300
                                    @elseif($presensiValue == 'tidak_hadir') bg-red-50 text-red-700 border-red-300
                                    @else bg-gray-50 text-gray-700 border-gray-300
                                    @endif">
                                <option value="belum_hadir" {{ $presensiValue == 'belum_hadir' ? 'selected' : '' }}>Belum Hadir</option>
                                <option value="hadir" {{ $presensiValue == 'hadir' ? 'selected' : '' }}>Hadir</option>
                                <option value="tidak_hadir" {{ $presensiValue == 'tidak_hadir' ? 'selected' : '' }}>Tidak Hadir</option>
                            </select>
                        </div>

                        {{-- Keterangan --}}
                        @if($jadwal->keterangan)
                            <div class="pt-4 border-t border-gray-200/50">
                                <p class="text-xs text-gray-600 flex items-start">
                                    <i class="ph ph-note text-sm mr-2 mt-0.5"></i>
                                    <span class="italic">{{ $jadwal->keterangan }}</span>
                                </p>
                            </div>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    @endif
</div>
