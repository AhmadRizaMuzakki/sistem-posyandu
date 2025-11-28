{{-- Modal Upload SK --}}
<div x-data="{ 
    show: @entangle('showUploadModal'),
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
        {{-- Overlay --}}
        <div x-show="show" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
             x-on:click="show = false"></div>

        {{-- Modal Panel --}}
        <div x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
             x-on:click.away="show = false">
            
            {{-- Header --}}
            <div class="bg-primary px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white">Upload File SK Posyandu</h3>
                <button x-on:click="show = false" class="text-white hover:text-gray-200">
                    <i class="ph ph-x text-xl"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="bg-white px-6 py-4">
                <form wire:submit.prevent="uploadSk">
                    {{-- Drag and Drop Area --}}
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
                                
                                // Validasi ukuran
                                if (file.size > 5242880) {
                                    error = 'Ukuran file melebihi 5MB';
                                    fileName = '';
                                    fileSize = '';
                                    return;
                                }
                                
                                // Validasi ekstensi
                                const ext = file.name.split('.').pop().toLowerCase();
                                if (!['pdf', 'doc', 'docx'].includes(ext)) {
                                    error = 'Format file tidak diizinkan. Hanya PDF, DOC, atau DOCX.';
                                    fileName = '';
                                    fileSize = '';
                                    return;
                                }
                                
                                // Set file ke Livewire
                                const dataTransfer = new DataTransfer();
                                dataTransfer.items.add(file);
                                $refs.fileInput.files = dataTransfer.files;
                                $refs.fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        "
                        :class="isDragging ? 'border-primary bg-blue-50' : 'border-gray-300'"
                        class="border-2 border-dashed rounded-lg p-8 text-center transition-colors cursor-pointer hover:border-primary hover:bg-gray-50"
                        x-on:click="$refs.fileInput.click()">
                        
                        <input 
                            type="file" 
                            wire:model="skFile"
                            x-ref="fileInput"
                            accept=".pdf,.doc,.docx"
                            class="hidden"
                            x-on:change="
                                if ($event.target.files.length > 0) {
                                    const file = $event.target.files[0];
                                    fileName = file.name;
                                    fileSize = formatFileSize(file.size);
                                    error = '';
                                    
                                    // Validasi ukuran
                                    if (file.size > 5242880) {
                                        error = 'Ukuran file melebihi 5MB';
                                        fileName = '';
                                        fileSize = '';
                                        $refs.fileInput.value = '';
                                        return;
                                    }
                                    
                                    // Validasi ekstensi
                                    const ext = file.name.split('.').pop().toLowerCase();
                                    if (!['pdf', 'doc', 'docx'].includes(ext)) {
                                        error = 'Format file tidak diizinkan. Hanya PDF, DOC, atau DOCX.';
                                        fileName = '';
                                        fileSize = '';
                                        $refs.fileInput.value = '';
                                        return;
                                    }
                                }
                            ">
                        
                        <div x-show="!fileName" class="space-y-4">
                            <div class="flex justify-center">
                                <i class="ph ph-cloud-arrow-up text-6xl text-gray-400"></i>
                            </div>
                            <div>
                                <p class="text-gray-600 font-medium">Drag & drop file di sini</p>
                                <p class="text-gray-400 text-sm mt-2">atau</p>
                                <button type="button" class="mt-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                                    Pilih File
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 mt-4">
                                Format yang didukung: PDF, DOC, DOCX (Maks. 5MB)
                            </p>
                        </div>

                        {{-- File Preview --}}
                        <div x-show="fileName" class="space-y-4">
                            <div class="flex items-center justify-center">
                                <i class="ph ph-file text-6xl text-primary"></i>
                            </div>
                            <div>
                                <p class="text-gray-800 font-medium" x-text="fileName"></p>
                                <p class="text-gray-500 text-sm mt-1" x-text="fileSize"></p>
                            </div>
                            <button 
                                type="button"
                                x-on:click="fileName = ''; fileSize = ''; error = ''; $refs.fileInput.value = ''; @this.set('skFile', null);"
                                class="text-red-600 hover:text-red-700 text-sm">
                                <i class="ph ph-trash mr-1"></i> Hapus
                            </button>
                        </div>
                    </div>

                    {{-- Error Message --}}
                    <div x-show="error" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-red-600 text-sm" x-text="error"></p>
                    </div>

                    @error('skFile')
                        <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        </div>
                    @enderror

                    {{-- Actions --}}
                    <div class="mt-6 flex justify-end space-x-3">
                        <button 
                            type="button"
                            x-on:click="show = false"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <button 
                            type="submit"
                            :disabled="!fileName || error"
                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="uploadSk">
                                <i class="ph ph-upload mr-2"></i> Upload
                            </span>
                            <span wire:loading wire:target="uploadSk">
                                <i class="ph ph-spinner ph-spin mr-2"></i> Mengupload...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


