{{-- Calendar --}}
<div class="p-6">
    {{-- Calendar Header --}}
    <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200">
        <h2 class="text-3xl font-bold text-gray-800 flex items-center">
            <i class="ph ph-calendar-blank text-3xl mr-3 text-primary"></i>
            {{ $monthName }}
        </h2>
        <div class="flex items-center gap-2">
            <button wire:click="goToToday"
                    class="px-4 py-2 text-sm font-medium bg-primary text-white rounded-lg hover:bg-primary/90 transition-all shadow-sm hover:shadow-md">
                <i class="ph ph-calendar-check mr-2"></i>
                Hari Ini
            </button>
            <button wire:click="previousMonth"
                    class="p-2.5 text-gray-600 hover:bg-gray-100 rounded-lg transition-all hover:shadow-sm">
                <i class="ph ph-caret-left text-xl"></i>
            </button>
            <button wire:click="nextMonth"
                    class="p-2.5 text-gray-600 hover:bg-gray-100 rounded-lg transition-all hover:shadow-sm">
                <i class="ph ph-caret-right text-xl"></i>
            </button>
        </div>
    </div>

    {{-- Calendar Table --}}
    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
        <table class="w-full border-collapse bg-white">
            {{-- Table Header --}}
            <thead>
                <tr class="bg-gradient-to-r from-primary/10 to-primary/5">
                    @php
                        $dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    @endphp
                    @foreach($dayNames as $dayName)
                        <th class="border-r border-gray-200 px-4 py-4 text-center font-bold text-gray-700 text-sm last:border-r-0">
                            {{ $dayName }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php
                    $weeks = array_chunk($calendarDays, 7);
                @endphp
                @foreach($weeks as $week)
                    <tr class="border-b border-gray-200 last:border-b-0">
                        @foreach($week as $dayData)
                            @php
                                $isOtherMonth = $dayData['isOtherMonth'] ?? false;
                                $jadwals = $dayData['jadwals'] ?? collect();
                            @endphp
                            <td class="border-r border-gray-200 p-3 align-top min-h-[140px] h-[140px] w-[14.28%] transition-all relative
                                {{ $isOtherMonth ? 'bg-gray-50/50' : 'bg-white hover:bg-gray-50/80 cursor-pointer' }}
                                {{ $dayData['isToday'] ? 'bg-blue-50/50 ring-2 ring-primary/30' : '' }}"
                                @if(!$isOtherMonth) wire:click="openModal('{{ $dayData['date'] }}')" @endif>
                                {{-- Tanggal --}}
                                <div class="text-lg font-bold mb-2.5
                                    {{ $isOtherMonth ? 'text-gray-300' : ($dayData['isToday'] ? 'text-primary font-extrabold' : 'text-gray-800') }}">
                                    {{ $dayData['day'] }}
                                </div>
                                
                                {{-- List Petugas (bisa multiple) - Format: Nama - Status --}}
                                @if($jadwals->isNotEmpty())
                                    <div class="space-y-1.5">
                                        @foreach($jadwals as $jadwal)
                                            @if($jadwal->petugasKesehatan)
                                                @php
                                                    $presensiValue = strtolower($jadwal->presensi ?? 'belum_absen');
                                                    $presensiColor = '';
                                                    $presensiText = '';
                                                    switch($presensiValue) {
                                                        case 'hadir':
                                                            $presensiColor = 'text-green-700 font-semibold';
                                                            $presensiText = 'Hadir';
                                                            break;
                                                        case 'tidak_hadir':
                                                            $presensiColor = 'text-red-700 font-semibold';
                                                            $presensiText = 'Tidak Hadir';
                                                            break;
                                                        default:
                                                            $presensiColor = 'text-gray-600';
                                                            $presensiText = 'Belum Absen';
                                                    }
                                                @endphp
                                                <div class="text-xs leading-tight py-1 px-2 rounded-md hover:bg-gray-100 transition-colors {{ $isOtherMonth ? 'text-gray-400' : 'text-gray-800' }}" 
                                                     wire:click.stop wire:click="openEditModal({{ $jadwal->id_jadwal }})"
                                                     title="Klik untuk edit">
                                                    <span class="font-medium">{{ $jadwal->petugasKesehatan->nama_petugas_kesehatan }}</span>
                                                    <span class="mx-1.5 text-gray-400">-</span>
                                                    <span class="{{ $presensiColor }}">{{ $presensiText }}</span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @elseif(!$isOtherMonth)
                                    <div class="text-xs text-gray-400 italic mt-2">
                                        <i class="ph ph-plus-circle mr-1"></i>
                                        Klik untuk tambah
                                    </div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
