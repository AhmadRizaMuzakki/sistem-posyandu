@if($showImportModal)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeImportModal"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="ph ph-upload-simple text-xl mr-2 text-primary"></i>
                    Import Sasaran {{ $importKategori ? (($importKategoriLabels[$importKategori] ?? ucfirst($importKategori))) : '' }}
                </h3>
                <p class="text-sm text-gray-500 mb-4">
                    Upload file CSV (Excel & Google Sheets: File → Simpan/Download sebagai CSV). Baris pertama = header. Jika NIK + posyandu sudah ada, baris akan dilewati (tidak duplikat).
                </p>
                <p class="text-xs text-gray-500 mb-3">
                    Kolom minimal: nik_sasaran, nama_sasaran, no_kk_sasaran, tanggal_lahir, jenis_kelamin, alamat_sasaran. Format tanggal: YYYY-MM-DD atau DD/MM/YYYY.
                </p>
                <div class="space-y-2">
                    <input type="file"
                           wire:model="importFile"
                           accept=".csv,.txt"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary file:text-white hover:file:bg-primary/90">
                    @error('importFile')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @if($importFile)
                        <p class="text-xs text-green-600">File dipilih: {{ $importFile->getClientOriginalName() }}</p>
                    @endif
                </div>
                @if($importResult)
                <div class="mt-4 p-3 bg-gray-50 rounded-lg text-sm">
                    <p class="font-medium text-gray-700">Hasil Import:</p>
                    <p>Ditambahkan: {{ $importResult['added'] }}, Dilewati (duplikat): {{ $importResult['skipped'] }}, Error: {{ $importResult['errors'] }}</p>
                </div>
                @endif
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                <button type="button"
                        wire:click="importSasaran"
                        wire:loading.attr="disabled"
                        class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    <span wire:loading.remove wire:target="importSasaran">Import</span>
                    <span wire:loading wire:target="importSasaran">Memproses...</span>
                </button>
                <button type="button"
                        wire:click="closeImportModal"
                        class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endif
