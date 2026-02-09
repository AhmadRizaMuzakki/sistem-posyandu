{{-- Modal Upload Gambar Posyandu --}}
<div x-data="{ 
    show: @entangle('showUploadGambarModal'),
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
style="display: none;"
x-on:close-modal.window="show = false">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="show" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
             x-on:click="show = false"></div>

        <div x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
             x-on:click.away="show = false">
            
            <div class="bg-primary px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white">Upload Gambar Posyandu</h3>
                <button x-on:click="show = false" class="text-white hover:text-gray-200">
                    <i class="ph ph-x text-xl"></i>
                </button>
            </div>

            <div class="bg-white px-6 py-4">
                <p class="text-sm text-gray-500 mb-4">Gambar ditampilkan di halaman detail posyandu (publik) di atas peta. Format: JPEG, PNG, JPG (Maks. 2MB).</p>
                <form wire:submit.prevent="uploadGambar">
                    <div 
                        x-on:dragover.prevent="isDragging = true"
                        x-on:dragleave.prevent="isDragging = false"
                        x-on:drop.prevent="
                            isDragging = false;
                            if ($event.dataTransfer.files.length > 0) {
                                const file = $event.dataTransfer.files[0];
                                fileName = file.name;
                                fileSize = formatFileSize(file.size);
                                error = '';
                                if (file.size > 2097152) { error = 'Ukuran file melebihi 2MB'; fileName = ''; fileSize = ''; return; }
                                const ext = file.name.split('.').pop().toLowerCase();
                                if (!['jpeg','jpg','png'].includes(ext)) { error = 'Format harus JPEG, PNG, atau JPG.'; fileName = ''; fileSize = ''; return; }
                                const dt = new DataTransfer();
                                dt.items.add(file);
                                $refs.fileInput.files = dt.files;
                                $refs.fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        "
                        :class="isDragging ? 'border-primary bg-blue-50' : 'border-gray-300'"
                        class="border-2 border-dashed rounded-lg p-8 text-center transition-colors cursor-pointer hover:border-primary hover:bg-gray-50"
                        x-on:click="$refs.fileInput.click()">
                        
                        <input type="file" wire:model="gambarFile" x-ref="fileInput" accept="image/jpeg,image/png,image/jpg" class="hidden"
                            x-on:change="
                                if ($event.target.files.length > 0) {
                                    const file = $event.target.files[0];
                                    fileName = file.name;
                                    fileSize = formatFileSize(file.size);
                                    error = '';
                                    if (file.size > 2097152) { error = 'Ukuran file melebihi 2MB'; fileName = ''; fileSize = ''; $refs.fileInput.value = ''; return; }
                                    const ext = file.name.split('.').pop().toLowerCase();
                                    if (!['jpeg','jpg','png'].includes(ext)) { error = 'Format harus JPEG, PNG, atau JPG.'; fileName = ''; fileSize = ''; $refs.fileInput.value = ''; return; }
                                }
                            ">
                        
                        <div x-show="!fileName" class="space-y-4">
                            <div class="flex justify-center"><i class="ph ph-image text-6xl text-gray-400"></i></div>
                            <p class="text-gray-600 font-medium">Seret gambar ke sini atau klik untuk memilih</p>
                            <p class="text-xs text-gray-400">JPEG, PNG, JPG (Maks. 2MB)</p>
                        </div>
                        <div x-show="fileName" class="space-y-4">
                            <div class="flex justify-center"><i class="ph ph-image text-6xl text-primary"></i></div>
                            <p class="text-gray-800 font-medium" x-text="fileName"></p>
                            <p class="text-gray-500 text-sm" x-text="fileSize"></p>
                            <button type="button" x-on:click="fileName = ''; fileSize = ''; error = ''; $refs.fileInput.value = ''; @this.set('gambarFile', null);" class="text-red-600 hover:text-red-700 text-sm">
                                <i class="ph ph-trash mr-1"></i> Hapus
                            </button>
                        </div>
                    </div>

                    <div x-show="error" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-red-600 text-sm" x-text="error"></p>
                    </div>
                    @error('gambarFile')
                        <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        </div>
                    @enderror

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" x-on:click="show = false" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">Batal</button>
                        <button type="submit" :disabled="!fileName || error" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors disabled:opacity-50 disabled:cursor-not-allowed" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="uploadGambar"><i class="ph ph-upload mr-2"></i> Upload</span>
                            <span wire:loading wire:target="uploadGambar"><i class="ph ph-spinner ph-spin mr-2"></i> Mengupload...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
