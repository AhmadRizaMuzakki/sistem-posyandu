{{-- Modal Upload Gambar Posyandu --}}
<div x-data="{ 
    show: @entangle('showGambarModal'),
    isDragging: false,
    fileName: '',
    fileSize: '',
    error: '',
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }
}" 
x-show="show"
x-cloak
class="fixed inset-0 z-50 overflow-y-auto"
style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="show" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" x-on:click="show = false"></div>
        <div x-show="show" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" x-on:click.away="show = false">
            <div class="bg-primary px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white">Upload Gambar Posyandu</h3>
                <button type="button" x-on:click="show = false" class="text-white hover:text-gray-200"><i class="ph ph-x text-xl"></i></button>
            </div>
            <div class="bg-white px-6 py-4">
                <p class="text-sm text-gray-600 mb-4">Gambar ditampilkan di halaman detail posyandu (publik) di atas peta. Format: JPEG, PNG, JPG (Maks. 2MB).</p>
                <form wire:submit.prevent="uploadGambar">
                    <div 
                        x-on:dragover.prevent="isDragging = true"
                        x-on:dragleave.prevent="isDragging = false"
                        x-on:drop.prevent="
                            isDragging = false;
                            if ($event.dataTransfer.files.length > 0) {
                                const file = $event.dataTransfer.files[0];
                                if (!file.type.match(/^image\/(jpeg|png|jpg)$/)) { error = 'Hanya JPEG, PNG, JPG'; return; }
                                if (file.size > 2097152) { error = 'Maksimal 2MB'; return; }
                                fileName = file.name; fileSize = formatFileSize(file.size); error = '';
                                const dt = new DataTransfer(); dt.items.add(file);
                                $refs.gambarInput.files = dt.files;
                                $refs.gambarInput.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        "
                        :class="isDragging ? 'border-primary bg-primary/5' : 'border-gray-300'"
                        class="border-2 border-dashed rounded-lg p-8 text-center cursor-pointer hover:border-primary hover:bg-gray-50"
                        x-on:click="$refs.gambarInput.click()">
                        <input type="file" wire:model="gambarFile" x-ref="gambarInput" accept="image/jpeg,image/png,image/jpg" class="hidden"
                            x-on:change="
                                if ($event.target.files.length > 0) {
                                    const file = $event.target.files[0];
                                    if (!file.type.match(/^image\/(jpeg|png|jpg)$/)) { error = 'Hanya JPEG, PNG, JPG'; fileName = ''; return; }
                                    if (file.size > 2097152) { error = 'Maksimal 2MB'; fileName = ''; return; }
                                    fileName = file.name; fileSize = formatFileSize(file.size); error = '';
                                }
                            ">
                        <div x-show="!fileName" class="space-y-2">
                            <i class="ph ph-image text-6xl text-gray-400"></i>
                            <p class="text-gray-600 font-medium">Seret gambar ke sini atau klik untuk memilih</p>
                            <p class="text-xs text-gray-400">JPEG, PNG, JPG (Maks. 2MB)</p>
                        </div>
                        <div x-show="fileName" class="space-y-2">
                            <i class="ph ph-image text-6xl text-primary"></i>
                            <p class="text-gray-800 font-medium" x-text="fileName"></p>
                            <p class="text-gray-500 text-sm" x-text="fileSize"></p>
                        </div>
                    </div>
                    <div x-show="error" class="mt-3 p-2 bg-red-50 border border-red-200 rounded text-red-600 text-sm" x-text="error"></div>
                    @error('gambarFile') <div class="mt-3 p-2 bg-red-50 border border-red-200 rounded text-red-600 text-sm">{{ $message }}</div> @enderror
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" x-on:click="show = false" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Batal</button>
                        <button type="submit" :disabled="!fileName" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark disabled:opacity-50" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="uploadGambar"><i class="ph ph-upload mr-2"></i> Upload</span>
                            <span wire:loading wire:target="uploadGambar"><i class="ph ph-spinner ph-spin mr-2"></i> Mengupload...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
