{{-- Kalender: grid + petugas + kegiatan per hari --}}
<div class="p-6">
    <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200">
        <h2 class="text-2xl font-bold text-gray-800 flex items-center">
            <i class="ph ph-calendar-blank text-2xl mr-3 text-primary"></i>
            {{ $monthName }}
        </h2>
        <div class="flex items-center gap-2 flex-wrap">
            <button wire:click="goToToday"
                    class="px-4 py-2 text-sm font-medium bg-primary text-white rounded-lg hover:bg-primary/90 transition shadow-sm">
                <i class="ph ph-calendar-check mr-2"></i>Hari Ini
            </button>
            <button wire:click="openAddJadwalModal"
                    class="px-4 py-2 text-sm font-medium bg-green-600 text-white rounded-lg hover:bg-green-700 transition shadow-sm">
                <i class="ph ph-plus-circle mr-2"></i>Tambah Jadwal
            </button>
            <button wire:click="previousMonth" class="p-2.5 text-gray-600 hover:bg-gray-100 rounded-lg transition">
                <i class="ph ph-caret-left text-xl"></i>
            </button>
            <button wire:click="nextMonth" class="p-2.5 text-gray-600 hover:bg-gray-100 rounded-lg transition">
                <i class="ph ph-caret-right text-xl"></i>
            </button>
        </div>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
        <table class="w-full border-collapse bg-white">
            <thead>
                <tr class="bg-gradient-to-r from-primary/10 to-primary/5">
                    @foreach(['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $d)
                        <th class="border-r border-gray-200 px-2 py-3 text-center font-bold text-gray-700 text-sm last:border-r-0">{{ $d }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php $weeks = array_chunk($calendarDays, 7); @endphp
                @foreach($weeks as $week)
                    <tr class="border-b border-gray-200 last:border-b-0">
                        @foreach($week as $day)
                            @php
                                $isOther = $day['isOtherMonth'] ?? false;
                                $jadwals = $day['jadwals'] ?? collect();
                                $kegiatans = $day['kegiatans'] ?? collect();
                            @endphp
                            <td class="border-r border-gray-200 p-2 align-top min-h-[80px] h-[80px] w-[14.28%] {{ $isOther ? 'bg-gray-50/50' : 'bg-white hover:bg-gray-50/80 cursor-pointer' }} {{ $day['isToday'] ? 'ring-2 ring-primary/30 bg-blue-50/50' : '' }}"
                                @if(!$isOther) wire:click="selectDate('{{ $day['date'] }}')" @endif>
                                <div class="text-base font-bold {{ $isOther ? 'text-gray-300' : ($day['isToday'] ? 'text-primary' : 'text-gray-800') }}">{{ $day['day'] }}</div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
