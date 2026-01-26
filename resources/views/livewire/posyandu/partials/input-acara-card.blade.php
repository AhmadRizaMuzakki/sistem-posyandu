{{-- Card terpisah: Input Acara/Kegiatan. Nama acara wajib di tanggal. --}}
<div class="bg-white rounded-xl shadow-lg border border-amber-200 overflow-hidden">
    <div class="bg-gradient-to-r from-amber-500 to-amber-600 px-6 py-4">
        <h3 class="text-lg font-bold text-white flex items-center">
            <i class="ph ph-calendar-dots text-xl mr-2"></i>
            Input Acara / Kegiatan
        </h3>
        <p class="text-amber-100 text-sm mt-0.5">Nama acara wajib disertai tanggal</p>
    </div>
    <div class="p-6 space-y-6">
        <form wire:submit.prevent="saveKegiatan" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" wire:model.live="tanggal_acara"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 @error('tanggal_acara') border-red-500 @enderror"
                           required>
                    @error('tanggal_acara') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama acara <span class="text-red-500">*</span></label>
                    <input type="text" wire:model.live="nama_kegiatan"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 @error('nama_kegiatan') border-red-500 @enderror"
                           placeholder="Contoh: Imunisasi Campak, Penimbangan"
                           required>
                    @error('nama_kegiatan') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tempat</label>
                <input type="text" wire:model.live="tempat"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 @error('tempat') border-red-500 @enderror"
                       placeholder="Contoh: Posyandu, Balai Desa, dll">
                @error('tempat') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jam mulai</label>
                    <input type="time" wire:model.live="jam_mulai"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 @error('jam_mulai') border-red-500 @enderror">
                    @error('jam_mulai') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jam selesai</label>
                    <input type="time" wire:model.live="jam_selesai"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 @error('jam_selesai') border-red-500 @enderror">
                    @error('jam_selesai') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea wire:model.live="deskripsi_kegiatan"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 @error('deskripsi_kegiatan') border-red-500 @enderror"
                          rows="2" placeholder="Opsional"></textarea>
                @error('deskripsi_kegiatan') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div class="flex items-center justify-end gap-3">
                <button type="submit"
                        class="px-6 py-3 bg-primary text-white rounded-lg font-medium hover:bg-primary/90 transition shadow-sm flex items-center">
                    <span wire:loading.remove wire:target="saveKegiatan">
                        <i class="ph ph-plus-circle mr-2 text-lg"></i>Tambah Acara
                    </span>
                    <span wire:loading wire:target="saveKegiatan" class="flex items-center">
                        <i class="ph ph-spinner animate-spin mr-2 text-lg"></i>Menyimpan...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
