{{-- Modal Form Kader --}}
@if($isKaderModalOpen)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        {{-- Background Overlay --}}
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeKaderModal" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        {{-- Modal Panel --}}
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form wire:submit.prevent="storeKader">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                        {{ $id_kader ? 'Edit Data Kader' : 'Tambah Kader Baru' }}
                    </h3>

                    <div class="grid grid-cols-1 gap-4">
                        {{-- Nama Kader (User) --}}
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Nama Kader <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="nama_kader" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Nama Lengkap Kader">
                            @error('nama_kader') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        {{-- Email Kader (User) --}}
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Email (Untuk Login) <span class="text-red-500">*</span></label>
                            <input type="email" wire:model="email_kader" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Email">
                            @error('email_kader') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        {{-- Password (User) --}}
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Password <span class="text-red-500">*</span></label>
                            <input type="password" wire:model="password_kader" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Password">
                            @error('password_kader') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <hr class="my-2" />

                        {{-- NIK Kader --}}
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">NIK Kader <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="nik_kader" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="NIK Kader">
                            @error('nik_kader') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>

                        {{-- Tanggal Lahir --}}
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Lahir <span class="text-red-500">*</span></label>
                            <input type="date" wire:model="tanggal_lahir" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                            @error('tanggal_lahir') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>

                        {{-- Alamat Kader --}}
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Alamat Kader <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="alamat_kader" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Alamat Kader">
                            @error('alamat_kader') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>

                        {{-- Jabatan Kader --}}
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Jabatan Kader <span class="text-red-500">*</span></label>
                            <select wire:model="jabatan_kader" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                <option value="">Pilih Jabatan...</option>
                                <option value="Ketua">Ketua</option>
                                <option value="Sekretaris">Sekretaris</option>
                                <option value="Bendahara">Bendahara</option>
                                <option value="Anggota">Anggota</option>
                            </select>
                            @error('jabatan_kader') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>

                        {{-- Pilih Posyandu --}}
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Tugaskan di Posyandu Mana? <span class="text-red-500">*</span></label>
                            <select wire:model="posyandu_id_kader" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                <option value="">Pilih Posyandu...</option>
                                @foreach($dataPosyandu as $posyanduOpt)
                                    <option value="{{ is_array($posyanduOpt) ? ($posyanduOpt['id_posyandu'] ?? $posyanduOpt['id'] ?? '') : ($posyanduOpt->id_posyandu ?? $posyanduOpt->id ?? '') }}">
                                        {{ is_array($posyanduOpt) ? ($posyanduOpt['nama_posyandu'] ?? '') : ($posyanduOpt->nama_posyandu ?? '') }}
                                    </option>
                                @endforeach
                            </select>
                            @error('posyandu_id_kader') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Simpan Data
                    </button>
                    <button type="button" wire:click="closeKaderModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

