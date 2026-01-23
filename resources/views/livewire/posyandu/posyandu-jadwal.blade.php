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
        <div class="bg-gradient-to-r from-primary to-primary/90 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">{{ $posyandu->nama_posyandu }}</h1>
                    <p class="text-white/90 mt-1 flex items-center">
                        <i class="ph ph-calendar-check mr-2"></i>
                        Jadwal Petugas Posyandu
                    </p>
                </div>
            </div>
        </div>

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
            </div>

            {{-- Tab Content: Jadwal --}}
            @if($activeTab === 'jadwal')
                @include('livewire.posyandu.partials.jadwal-calendar')
            @endif

            {{-- Tab Content: Absen --}}
            @if($activeTab === 'absen')
                @include('livewire.posyandu.partials.absen-section')
            @endif
        </div>
    </div>

    {{-- Modal: Lihat Petugas yang Berjadwal di Tanggal Tertentu (ketika klik tanggal) --}}
    @if($isModalOpen)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 animate-fadeIn" wire:click="closeModal">
            <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg mx-4 transform transition-all animate-scaleIn" wire:click.stop>
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200">
                    <h3 class="text-2xl font-bold text-gray-800 flex items-center">
                        <i class="ph ph-calendar-check text-2xl mr-3 text-primary"></i>
                        Petugas Berjadwal - {{ \Carbon\Carbon::parse($selectedDate)->locale('id')->translatedFormat('d F Y') }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-1 transition-colors">
                        <i class="ph ph-x text-2xl"></i>
                    </button>
                </div>

                <div class="space-y-4">
                    {{-- List Petugas yang sudah berjadwal di tanggal ini --}}
                    @if($selectedDateJadwals->isNotEmpty())
                        <div class="space-y-3">
                            @foreach($selectedDateJadwals as $existingJadwal)
                                @if($existingJadwal->petugasKesehatan)
                                    @php
                                        $presensiValue = strtolower($existingJadwal->presensi ?? 'belum_hadir');
                                    @endphp
                                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-all">
                                        <div class="flex items-start justify-between mb-3">
                                            <div class="flex-1">
                                                <h5 class="font-semibold text-gray-800 text-lg mb-1">
                                                    {{ $existingJadwal->petugasKesehatan->nama_petugas_kesehatan }}
                                                </h5>
                                                <p class="text-sm text-gray-600">
                                                    <i class="ph ph-stethoscope mr-1"></i>
                                                    {{ $existingJadwal->petugasKesehatan->bidan ?? '-' }}
                                                </p>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Status Presensi</label>
                                            <select wire:change="updatePresensi({{ $existingJadwal->id_jadwal }}, $event.target.value)" 
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all
                                                    @if($presensiValue == 'hadir') bg-green-50 text-green-700 border-green-300
                                                    @elseif($presensiValue == 'tidak_hadir') bg-red-50 text-red-700 border-red-300
                                                    @else bg-gray-50 text-gray-700 border-gray-300
                                                    @endif">
                                                <option value="belum_hadir" {{ $presensiValue == 'belum_hadir' ? 'selected' : '' }}>Belum Hadir</option>
                                                <option value="hadir" {{ $presensiValue == 'hadir' ? 'selected' : '' }}>Hadir</option>
                                                <option value="tidak_hadir" {{ $presensiValue == 'tidak_hadir' ? 'selected' : '' }}>Tidak Hadir</option>
                                            </select>
                                        </div>
                                        @if($existingJadwal->keterangan)
                                            <div class="mt-3 pt-3 border-t border-gray-200">
                                                <p class="text-xs text-gray-600">
                                                    <i class="ph ph-note mr-1"></i>
                                                    <span class="italic">{{ $existingJadwal->keterangan }}</span>
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-center">
                            <i class="ph ph-info text-yellow-600 text-2xl mb-2"></i>
                            <p class="text-sm text-gray-700">
                                Belum ada petugas yang berjadwal di tanggal ini.
                            </p>
                        </div>
                    @endif

                    <div class="flex gap-3 pt-4 border-t border-gray-200">
                        <button type="button" wire:click="closeModal"
                                class="flex-1 px-5 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-all font-medium">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Tambah Jadwal Baru --}}
    @if($isAddJadwalModalOpen)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 animate-fadeIn" wire:click="closeAddJadwalModal">
            <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg mx-4 transform transition-all animate-scaleIn" wire:click.stop>
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200">
                    <h3 class="text-2xl font-bold text-gray-800 flex items-center">
                        <i class="ph ph-calendar-plus text-2xl mr-3 text-primary"></i>
                        Tambah Jadwal Baru
                    </h3>
                    <button wire:click="closeAddJadwalModal" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-1 transition-colors">
                        <i class="ph ph-x text-2xl"></i>
                    </button>
                </div>

                <form wire:submit.prevent="saveJadwal">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Tanggal <span class="text-red-500">*</span>
                            </label>
                            <input type="date" wire:model="tanggal" 
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                   required>
                            @error('tanggal') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Petugas Kesehatan <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="id_petugas_kesehatan" 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                    required>
                                <option value="">Pilih Petugas Kesehatan</option>
                                @foreach($petugasKesehatanList as $petugas)
                                    <option value="{{ $petugas->id_petugas_kesehatan }}">
                                        {{ $petugas->nama_petugas_kesehatan }}
                                        @if($petugas->bidan)
                                            - {{ $petugas->bidan }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('id_petugas_kesehatan') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Keterangan
                            </label>
                            <textarea wire:model="keterangan" 
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                      rows="3"
                                      placeholder="Masukkan keterangan (opsional)"></textarea>
                            @error('keterangan') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex gap-3 pt-4 border-t border-gray-200">
                            <button type="submit"
                                    class="flex-1 bg-primary text-white px-5 py-3 rounded-lg hover:bg-primary/90 transition-all shadow-md hover:shadow-lg font-semibold flex items-center justify-center">
                                <i class="ph ph-floppy-disk mr-2 text-lg"></i>
                                Tambah Jadwal
                            </button>
                            <button type="button" wire:click="closeAddJadwalModal"
                                    class="px-5 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-all font-medium">
                                Batal
                            </button>
                        </div>
                        <div class="text-xs text-gray-500 mt-3 flex items-center bg-blue-50 p-2 rounded-lg">
                            <i class="ph ph-info text-primary mr-2"></i>
                            <span>Anda bisa menambahkan lebih dari satu petugas di tanggal yang sama</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
