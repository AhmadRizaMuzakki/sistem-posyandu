{{-- Modal Form Petugas Kesehatan --}}
@if($isPetugasKesehatanModalOpen)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        {{-- Background Overlay --}}
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closePetugasKesehatanModal" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        {{-- Modal Panel --}}
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form wire:submit.prevent="storePetugasKesehatan">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                        {{ $id_petugas_kesehatan ? 'Edit Data Petugas Kesehatan' : 'Tambah Petugas Kesehatan Baru' }}
                    </h3>

                    <div class="grid grid-cols-1 gap-4">
                        {{-- Nama Petugas Kesehatan --}}
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Nama Petugas Kesehatan <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="nama_petugas_kesehatan" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Nama Lengkap Petugas Kesehatan">
                            @error('nama_petugas_kesehatan') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>

                        {{-- NIK Petugas Kesehatan --}}
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">NIK Petugas Kesehatan <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="nik_petugas_kesehatan" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="NIK Petugas Kesehatan">
                            @error('nik_petugas_kesehatan') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>

                        {{-- Tanggal Lahir (Hari, Bulan, Tahun Terpisah) --}}
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Lahir <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Hari</label>
                                    <input type="number" wire:model="hari_lahir" min="1" max="31" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Hari">
                                    @error('hari_lahir') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Bulan</label>
                                    <select wire:model="bulan_lahir" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                        <option value="">Pilih...</option>
                                        <option value="1">Januari</option>
                                        <option value="2">Februari</option>
                                        <option value="3">Maret</option>
                                        <option value="4">April</option>
                                        <option value="5">Mei</option>
                                        <option value="6">Juni</option>
                                        <option value="7">Juli</option>
                                        <option value="8">Agustus</option>
                                        <option value="9">September</option>
                                        <option value="10">Oktober</option>
                                        <option value="11">November</option>
                                        <option value="12">Desember</option>
                                    </select>
                                    @error('bulan_lahir') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Tahun</label>
                                    <input type="number" wire:model="tahun_lahir" min="1900" max="{{ date('Y') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Tahun">
                                    @error('tahun_lahir') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            @error('tanggal_lahir') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>

                        {{-- Alamat Petugas Kesehatan --}}
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Alamat Petugas Kesehatan <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="alamat_petugas_kesehatan" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Alamat Petugas Kesehatan">
                            @error('alamat_petugas_kesehatan') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>

                        {{-- Bidan/Dokter Desa --}}
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Jenis Petugas Kesehatan <span class="text-red-500">*</span></label>
                            <select wire:model="bidan" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                <option value="">Pilih Jenis...</option>
                                <option value="Bidan Desa">Bidan Desa</option>
                                <option value="Dokter Desa">Dokter Desa</option>
                            </select>
                            @error('bidan') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>

                        {{-- Pilih Posyandu (Read-only) --}}
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Tugaskan di Posyandu Mana? <span class="text-red-500">*</span></label>
                            <input type="text" 
                                   value="{{ $posyandu->nama_posyandu ?? '' }}" 
                                   readonly
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none focus:ring-primary focus:border-primary cursor-not-allowed">
                            <input type="hidden" wire:model="posyandu_id_petugas_kesehatan" value="{{ $posyandu->id_posyandu ?? $posyanduId ?? '' }}">
                            @error('posyandu_id_petugas_kesehatan') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Simpan Data
                    </button>
                    <button type="button" wire:click="closePetugasKesehatanModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

