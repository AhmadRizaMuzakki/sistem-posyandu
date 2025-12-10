{{-- Modal Form Orangtua --}}
@if($isOrangtuaModalOpen)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeOrangtuaModal()" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full max-h-[90vh] overflow-y-auto">
            <form wire:submit.prevent="storeOrangtua">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                        {{ $nik_orangtua ? 'Edit Data Orangtua' : 'Tambah Orangtua Baru' }}
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">NIK <span class="text-red-500">*</span></label>
                            <input type="number" wire:model="nik_orangtua" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Masukkan NIK" {{ $nik_orangtua ? 'readonly' : '' }}>
                            @error('nik_orangtua') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="nama_orangtua" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Masukkan nama lengkap">
                            @error('nama_orangtua') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Tempat Lahir <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="tempat_lahir_orangtua" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Contoh: Bandung">
                            @error('tempat_lahir_orangtua') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Lahir <span class="text-red-500">*</span></label>
                            <input type="date" wire:model="tanggal_lahir_orangtua" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                            @error('tanggal_lahir_orangtua') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Pekerjaan</label>
                            <input type="text" wire:model="pekerjaan_orangtua" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Contoh: Pegawai Negeri">
                            @error('pekerjaan_orangtua') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                            <select wire:model="kelamin_orangtua" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                <option value="">Pilih Jenis Kelamin...</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                            @error('kelamin_orangtua') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm">
                        {{ $nik_orangtua ? 'Update' : 'Simpan' }}
                    </button>
                    <button type="button" wire:click="closeOrangtuaModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
