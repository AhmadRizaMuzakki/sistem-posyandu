{{-- Card terpisah: Input Petugas yang bekerja. --}}
<div class="bg-white rounded-xl shadow-lg border border-green-200 overflow-hidden">
    <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
        <h3 class="text-lg font-bold text-white flex items-center">
            <i class="ph ph-users-three text-xl mr-2"></i>
            Input Petugas yang Bekerja
        </h3>
        <p class="text-green-100 text-sm mt-0.5">Tambah petugas yang bekerja di tanggal tertentu</p>
    </div>
    <div class="p-6 space-y-6">
        <form wire:submit.prevent="saveJadwal" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" wire:model.live="tanggal"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('tanggal') border-red-500 @enderror"
                           required>
                    @error('tanggal') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Petugas Kesehatan <span class="text-red-500">*</span></label>
                    <select wire:model.live="id_petugas_kesehatan"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('id_petugas_kesehatan') border-red-500 @enderror"
                            required>
                        <option value="">Pilih Petugas</option>
                        @foreach($petugasKesehatanList as $p)
                            <option value="{{ $p->id_petugas_kesehatan }}">{{ $p->nama_petugas_kesehatan }}{{ $p->bidan ? ' - ' . $p->bidan : '' }}</option>
                        @endforeach
                    </select>
                    @error('id_petugas_kesehatan') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                <textarea wire:model.live="keterangan"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('keterangan') border-red-500 @enderror"
                          rows="2" placeholder="Opsional"></textarea>
                @error('keterangan') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div class="flex items-center justify-end gap-3">
                <button type="submit"
                        class="px-6 py-3 bg-primary text-white rounded-lg font-medium hover:bg-primary/90 transition shadow-sm flex items-center">
                    <span wire:loading.remove wire:target="saveJadwal">
                        <i class="ph ph-plus-circle mr-2 text-lg"></i>Tambah Petugas
                    </span>
                    <span wire:loading wire:target="saveJadwal" class="flex items-center">
                        <i class="ph ph-spinner animate-spin mr-2 text-lg"></i>Menyimpan...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
