{{-- Tab Jadwal: Tabel List Jadwal --}}
<div class="p-6">
    <div class="mb-6 pb-4 border-b border-gray-200 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-bold text-gray-800 flex items-center">
                <i class="ph ph-calendar-blank text-3xl mr-3 text-primary"></i>
                Daftar Jadwal Petugas
            </h2>
            <p class="text-gray-600 mt-2">Kelola jadwal dan presensi petugas kesehatan</p>
        </div>
        <button wire:click="openAddJadwalModal" 
                class="px-4 py-2 text-sm font-medium bg-primary text-white rounded-lg hover:bg-primary/90 transition-all shadow-sm hover:shadow-md flex items-center">
            <i class="ph ph-plus-circle mr-2"></i>
            Tambah Jadwal
        </button>
    </div>

    @if($allJadwals->isEmpty())
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-8 text-center">
            <i class="ph ph-calendar-x text-5xl text-gray-400 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Belum Ada Jadwal</h3>
            <p class="text-gray-600">Belum ada jadwal petugas. Klik tanggal di kalender untuk menambahkan jadwal.</p>
        </div>
    @else
        <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
            <table class="w-full border-collapse bg-white">
                <thead>
                    <tr class="bg-gradient-to-r from-primary/10 to-primary/5">
                        <th class="border-r border-gray-200 px-4 py-3 text-left font-bold text-gray-700 text-sm">Tanggal</th>
                        <th class="border-r border-gray-200 px-4 py-3 text-left font-bold text-gray-700 text-sm">Nama Petugas</th>
                        <th class="border-r border-gray-200 px-4 py-3 text-left font-bold text-gray-700 text-sm">Jenis Petugas</th>
                        <th class="border-r border-gray-200 px-4 py-3 text-left font-bold text-gray-700 text-sm">Status Presensi</th>
                        <th class="px-4 py-3 text-left font-bold text-gray-700 text-sm">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allJadwals as $jadwal)
                        @if($jadwal->petugasKesehatan)
                            @php
                                $presensiValue = strtolower($jadwal->presensi ?? 'belum_hadir');
                            @endphp
                            <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                <td class="border-r border-gray-200 px-4 py-3">
                                    <span class="font-medium text-gray-800">
                                        {{ \Carbon\Carbon::parse($jadwal->tanggal)->locale('id')->translatedFormat('d F Y') }}
                                    </span>
                                </td>
                                <td class="border-r border-gray-200 px-4 py-3">
                                    <span class="font-semibold text-gray-800">{{ $jadwal->petugasKesehatan->nama_petugas_kesehatan }}</span>
                                </td>
                                <td class="border-r border-gray-200 px-4 py-3">
                                    <span class="text-gray-700">
                                        {{ $jadwal->petugasKesehatan->bidan ?? '-' }}
                                    </span>
                                </td>
                                <td class="border-r border-gray-200 px-4 py-3">
                                    <select wire:change="updatePresensi({{ $jadwal->id_jadwal }}, $event.target.value)" 
                                            class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm font-medium focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all
                                            @if($presensiValue == 'hadir') bg-green-50 text-green-700 border-green-300
                                            @elseif($presensiValue == 'tidak_hadir') bg-red-50 text-red-700 border-red-300
                                            @else bg-gray-50 text-gray-700 border-gray-300
                                            @endif">
                                        <option value="belum_hadir" {{ $presensiValue == 'belum_hadir' ? 'selected' : '' }}>Belum Hadir</option>
                                        <option value="hadir" {{ $presensiValue == 'hadir' ? 'selected' : '' }}>Hadir</option>
                                        <option value="tidak_hadir" {{ $presensiValue == 'tidak_hadir' ? 'selected' : '' }}>Tidak Hadir</option>
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <button wire:click="openEditModal({{ $jadwal->id_jadwal }})" 
                                            class="text-primary hover:text-primary/80 hover:bg-primary/10 rounded-lg p-2 transition-all"
                                            title="Edit Jadwal">
                                        <i class="ph ph-pencil text-lg"></i>
                                    </button>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
