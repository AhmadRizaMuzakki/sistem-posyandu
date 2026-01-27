{{-- Modal Input Kunjungan Per Bulan --}}
@if($isInputKunjunganModalOpen)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeInputKunjunganModal()" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full max-h-[90vh] overflow-y-auto">
            <form wire:submit.prevent="storeBulkKunjungan">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                        Input Kunjungan Per Bulan
                    </h3>
                    
                    {{-- Pilih Bulan dan Tahun --}}
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Pilih Bulan <span class="text-red-500">*</span></label>
                            <select wire:model.live="bulan_input_kunjungan" 
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                <option value="">Pilih Bulan</option>
                                @foreach($bulanList as $bulanNum => $bulanNama)
                                    <option value="{{ $bulanNum }}">{{ $bulanNama }}</option>
                                @endforeach
                            </select>
                            @error('bulan_input_kunjungan') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Tahun <span class="text-red-500">*</span></label>
                            <input type="number" 
                                   wire:model.live="tahun_input_kunjungan" 
                                   min="2020" 
                                   max="{{ date('Y') + 1 }}" 
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" 
                                   placeholder="Tahun">
                            @error('tahun_input_kunjungan') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    {{-- Tanggal Agenda --}}
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Agenda <span class="text-red-500">*</span></label>
                        <input type="date" 
                               wire:model="tanggal_agenda_kunjungan" 
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                        @error('tanggal_agenda_kunjungan') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                    </div>

                    {{-- Petugas Penanggung Jawab, Petugas Imunisasi, dan Petugas Input --}}
                    <div class="grid grid-cols-3 gap-4 mb-6">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Petugas Penanggung Jawab</label>
                            <select wire:model="id_petugas_penanggung_jawab_bulk" 
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                <option value="">Pilih Petugas</option>
                                @foreach($petugasKesehatanList as $petugas)
                                    <option value="{{ $petugas->id_petugas_kesehatan }}">{{ $petugas->nama_petugas_kesehatan }}</option>
                                @endforeach
                            </select>
                            @error('id_petugas_penanggung_jawab_bulk') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Petugas Imunisasi</label>
                            <select wire:model="id_petugas_imunisasi_bulk" 
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                <option value="">Pilih Petugas</option>
                                @foreach($petugasKesehatanList as $petugas)
                                    <option value="{{ $petugas->id_petugas_kesehatan }}">{{ $petugas->nama_petugas_kesehatan }}</option>
                                @endforeach
                            </select>
                            @error('id_petugas_imunisasi_bulk') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Petugas Input</label>
                            <select wire:model="id_petugas_input_bulk" 
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                <option value="">Pilih Petugas</option>
                                @foreach($petugasKesehatanList as $petugas)
                                    <option value="{{ $petugas->id_petugas_kesehatan }}">{{ $petugas->nama_petugas_kesehatan }}</option>
                                @endforeach
                            </select>
                            @error('id_petugas_input_bulk') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    {{-- List Checkbox Ibu Menyusui --}}
                    @if($bulan_input_kunjungan && $tahun_input_kunjungan)
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-3">Pilih Balita yang Hadir:</label>
                            <div class="border border-gray-300 rounded-lg p-4 max-h-96 overflow-y-auto">
                                @if($sasaranList && $sasaranList->count() > 0)
                                    <div class="space-y-2">
                                        @foreach($sasaranList as $ibu)
                                            <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                                <input type="checkbox" 
                                                       wire:model="selectedIbuMenyusui" 
                                                       value="{{ (int)$ibu['id_ibu_menyusui'] }}"
                                                       class="w-5 h-5 text-primary border-gray-300 rounded focus:ring-primary">
                                                <div class="ml-3 flex-1">
                                                    <div class="text-sm font-medium text-gray-900">{{ $ibu['nama_ibu'] }}</div>
                                                    @if($ibu['nama_suami'])
                                                        <div class="text-xs text-gray-500">Suami: {{ $ibu['nama_suami'] }}</div>
                                                    @endif
                                                    @if($ibu['nama_bayi'])
                                                        <div class="text-xs text-gray-500">Bayi: {{ $ibu['nama_bayi'] }}</div>
                                                    @endif
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500 text-center py-4">Tidak ada data balita dari sasaran</p>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <p>Pilih bulan dan tahun terlebih dahulu</p>
                        </div>
                    @endif
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm">
                        Simpan Kunjungan
                    </button>
                    <button type="button" 
                            wire:click="closeInputKunjunganModal" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
