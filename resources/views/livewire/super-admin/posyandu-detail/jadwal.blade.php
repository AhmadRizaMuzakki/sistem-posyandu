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
            </div>

            {{-- Tab Content: Jadwal --}}
            @if($activeTab === 'jadwal')
                @include('livewire.super-admin.posyandu-detail.partials.jadwal-calendar')
            @endif

            {{-- Tab Content: Absen --}}
            @if($activeTab === 'absen')
                @include('livewire.super-admin.posyandu-detail.partials.absen-section')
            @endif
        </div>
    </div>

    {{-- Modal Input/Edit Jadwal --}}
    @if($isModalOpen)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 animate-fadeIn" wire:click="closeModal">
            <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg mx-4 transform transition-all animate-scaleIn" wire:click.stop>
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200">
                    <h3 class="text-2xl font-bold text-gray-800 flex items-center">
                        <i class="ph ph-calendar-plus text-2xl mr-3 text-primary"></i>
                        {{ $jadwalId ? 'Edit Jadwal' : 'Tambah Jadwal' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-1 transition-colors">
                        <i class="ph ph-x text-2xl"></i>
                    </button>
                </div>

                <form wire:submit.prevent="saveJadwal">
                    <div class="space-y-4">
                        {{-- List Jadwal yang sudah ada di tanggal ini --}}
                        @if($selectedDateJadwals->isNotEmpty())
                            <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-4 mb-5 border border-gray-200">
                                <h4 class="text-sm font-bold text-gray-700 mb-3 flex items-center">
                                    <i class="ph ph-list-bullets mr-2 text-primary"></i>
                                    Jadwal di tanggal ini:
                                </h4>
                                <div class="space-y-2">
                                    @foreach($selectedDateJadwals as $existingJadwal)
                                        @php
                                            $presensiValue = strtolower($existingJadwal->presensi ?? 'belum_absen');
                                            $presensiBg = '';
                                            if($presensiValue == 'hadir') {
                                                $presensiBg = 'bg-green-50 border-green-200';
                                            } elseif($presensiValue == 'tidak_hadir') {
                                                $presensiBg = 'bg-red-50 border-red-200';
                                            } else {
                                                $presensiBg = 'bg-gray-50 border-gray-200';
                                            }
                                        @endphp
                                        <div class="flex items-center justify-between bg-white p-3 rounded-lg border {{ $presensiBg }} shadow-sm hover:shadow-md transition-all">
                                            <div class="flex-1">
                                                <span class="font-semibold text-gray-800">{{ $existingJadwal->petugasKesehatan->nama_petugas_kesehatan ?? '-' }}</span>
                                                <span class="text-xs ml-2">
                                                    @if($presensiValue == 'hadir')
                                                        <span class="text-green-700 font-medium">• Hadir</span>
                                                    @elseif($presensiValue == 'tidak_hadir')
                                                        <span class="text-red-700 font-medium">• Tidak Hadir</span>
                                                    @else
                                                        <span class="text-gray-600 font-medium">• Belum Absen</span>
                                                    @endif
                                                </span>
                                            </div>
                                            <button type="button" wire:click="openEditModal({{ $existingJadwal->id_jadwal }})" 
                                                    class="ml-3 text-primary hover:text-primary/80 hover:bg-primary/10 rounded-lg p-1.5 transition-all">
                                                <i class="ph ph-pencil text-lg"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Tanggal
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
                                Presensi <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="presensi" 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                    required>
                                <option value="belum_absen">Belum Absen</option>
                                <option value="hadir">Hadir</option>
                                <option value="tidak_hadir">Tidak Hadir</option>
                            </select>
                            @error('presensi') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
                                {{ $jadwalId ? 'Update' : 'Tambah' }} Jadwal
                            </button>
                            @if($jadwalId)
                                <button type="button" wire:click="deleteJadwal({{ $jadwalId }})" wire:confirm="Apakah Anda yakin ingin menghapus jadwal ini?"
                                        class="px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-all shadow-md hover:shadow-lg">
                                    <i class="ph ph-trash text-lg"></i>
                                </button>
                            @endif
                            <button type="button" wire:click="closeModal"
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
