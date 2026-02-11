{{-- Modal Upload Gambar Posyandu (Multiple) --}}
<div x-data="{ 
    show: @entangle('showGambarModal'),
    isDragging: false,
    files: [],
    error: '',
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    },
    validateFiles(fileList) {
        this.error = '';
        const validFiles = [];
        for (let i = 0; i < fileList.length; i++) {
            const file = fileList[i];
            if (!file.type.match(/^image\/(jpeg|png|jpg)$/)) {
                this.error = 'Hanya format JPEG, PNG, JPG yang diperbolehkan';
                continue;
            }
            if (file.size > 2097152) {
                this.error = 'Ukuran file maksimal 2MB per gambar';
                continue;
            }
            validFiles.push({ name: file.name, size: this.formatFileSize(file.size) });
        }
        return validFiles;
    },
    handleFiles(fileList) {
        const validated = this.validateFiles(fileList);
        if (validated.length > 0) {
            this.files = validated;
            const dt = new DataTransfer();
            for (let i = 0; i < fileList.length; i++) {
                if (fileList[i].type.match(/^image\/(jpeg|png|jpg)$/) && fileList[i].size <= 2097152) {
                    dt.items.add(fileList[i]);
                }
            }
            $refs.gambarInput.files = dt.files;
            $refs.gambarInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
    },
    removeFile(index) {
        this.files.splice(index, 1);
        const dt = new DataTransfer();
        const currentFiles = $refs.gambarInput.files;
        for (let i = 0; i < currentFiles.length; i++) {
            if (i !== index) dt.items.add(currentFiles[i]);
        }
        $refs.gambarInput.files = dt.files;
        $refs.gambarInput.dispatchEvent(new Event('change', { bubbles: true }));
    },
    resetModal() {
        this.files = [];
        this.error = '';
    }
}" 
x-show="show"
x-cloak
x-on:close-modal.window="resetModal()"
class="fixed inset-0 z-50 overflow-y-auto"
style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="show" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" x-on:click="show = false; resetModal()"></div>
        <div x-show="show" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-primary px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white">Upload Gambar Posyandu</h3>
                <button type="button" x-on:click="show = false; resetModal()" class="text-white hover:text-gray-200"><i class="ph ph-x text-xl"></i></button>
            </div>
            <div class="bg-white px-6 py-4">
                <p class="text-sm text-gray-600 mb-4">Upload satu atau lebih gambar untuk galeri posyandu. Format: JPEG, PNG, JPG (Maks. 2MB per gambar).</p>
                <form wire:submit.prevent="uploadGambar">
                    <div 
                        x-on:dragover.prevent="isDragging = true"
                        x-on:dragleave.prevent="isDragging = false"
                        x-on:drop.prevent="isDragging = false; handleFiles($event.dataTransfer.files)"
                        :class="isDragging ? 'border-primary bg-primary/5' : 'border-gray-300'"
                        class="border-2 border-dashed rounded-lg p-6 text-center cursor-pointer hover:border-primary hover:bg-gray-50"
                        x-on:click="$refs.gambarInput.click()">
                        <input type="file" wire:model="gambarFiles" x-ref="gambarInput" accept="image/jpeg,image/png,image/jpg" multiple class="hidden"
                            x-on:change="handleFiles($event.target.files)">
                        <div x-show="files.length === 0" class="space-y-2">
                            <i class="ph ph-images text-5xl text-gray-400"></i>
                            <p class="text-gray-600 font-medium">Seret gambar ke sini atau klik untuk memilih</p>
                            <p class="text-xs text-gray-400">JPEG, PNG, JPG (Maks. 2MB per file) - Bisa pilih banyak</p>
                        </div>
                        <div x-show="files.length > 0" class="space-y-2">
                            <i class="ph ph-images text-5xl text-primary"></i>
                            <p class="text-primary font-medium" x-text="files.length + ' gambar dipilih'"></p>
                        </div>
                    </div>

                    {{-- Preview files --}}
                    <div x-show="files.length > 0" class="mt-4 space-y-2 max-h-40 overflow-y-auto">
                        <template x-for="(file, index) in files" :key="index">
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-2 min-w-0">
                                    <i class="ph ph-image text-primary"></i>
                                    <span class="text-sm text-gray-700 truncate" x-text="file.name"></span>
                                    <span class="text-xs text-gray-500" x-text="'(' + file.size + ')'"></span>
                                </div>
                                <button type="button" x-on:click.stop="removeFile(index)" class="text-red-500 hover:text-red-700 p-1">
                                    <i class="ph ph-x"></i>
                                </button>
                            </div>
                        </template>
                    </div>

                    {{-- Caption input --}}
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan (opsional)</label>
                        <input type="text" wire:model="gambarCaption" placeholder="Contoh: Suasana kegiatan posyandu" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm">
                    </div>

                    <div x-show="error" class="mt-3 p-2 bg-red-50 border border-red-200 rounded text-red-600 text-sm" x-text="error"></div>
                    @error('gambarFiles') <div class="mt-3 p-2 bg-red-50 border border-red-200 rounded text-red-600 text-sm">{{ $message }}</div> @enderror
                    @error('gambarFiles.*') <div class="mt-3 p-2 bg-red-50 border border-red-200 rounded text-red-600 text-sm">{{ $message }}</div> @enderror
                    
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" x-on:click="show = false; resetModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Batal</button>
                        <button type="submit" :disabled="files.length === 0" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark disabled:opacity-50" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="uploadGambar"><i class="ph ph-upload mr-2"></i> Upload</span>
                            <span wire:loading wire:target="uploadGambar"><i class="ph ph-spinner ph-spin mr-2"></i> Mengupload...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
