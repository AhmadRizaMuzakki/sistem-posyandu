@if($showImportModal)
@php
    $isMaster = ($importKategori ?? '') === 'master';
@endphp
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeImportModal"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="ph ph-upload-simple text-xl mr-2 text-primary"></i>
                    @if($isMaster)
                        Import Master (Semua Kategori)
                    @else
                        Import Sasaran {{ $importKategori ? (($importKategoriLabels[$importKategori] ?? ucfirst($importKategori))) : '' }}
                    @endif
                </h3>
                @if($isMaster)
                <p class="text-sm text-gray-500 mb-4">
                    Satu file Excel berisi <strong>beberapa sheet</strong>. Satu sheet = satu kategori (Bayi Balita, Remaja, Dewasa, Ibu Hamil, Pralansia, Lansia). Kolom di setiap sheet sama seperti form kategori itu—tidak ada kolom gabungan. Data di sheet Bayi/Balita masuk ke sasaran balita, dan seterusnya.
                </p>
                <p class="text-xs text-gray-500 mb-2">
                    Hanya file Excel (.xlsx, .xls). Unduh template master, isi tiap sheet sesuai kategori. NIK + posyandu yang sudah ada akan dilewati.
                </p>
                <div class="mb-4">
                    <a href="{{ route('superadmin.sasaran.template-import', ['kategori' => 'master']) }}"
                       target="_blank"
                       rel="noopener"
                       class="inline-flex items-center gap-2 text-sm font-medium text-primary hover:underline">
                        <i class="ph ph-download-simple"></i>
                        Unduh template Excel Master (.xlsx)
                    </a>
                    <p class="text-xs text-gray-500 mt-1">Template berisi 6 sheet: Bayi Balita, Remaja, Dewasa, Ibu Hamil, Pralansia, Lansia. Kolom per sheet sama seperti form masing-masing.</p>
                </div>
                @else
                <p class="text-sm text-gray-500 mb-4">
                    Upload file CSV atau Excel (.xlsx, .xls). Baris pertama = header kolom, baris berikutnya = data. Import memetakan per kolom berdasarkan nama header (urutan kolom bebas). NIK + posyandu sudah ada akan dilewati.
                </p>
                <p class="text-xs text-gray-500 mb-2">
                    Kolom wajib: nik_sasaran, nama_sasaran, no_kk_sasaran, tanggal_lahir, jenis_kelamin, alamat_sasaran. Format tanggal: <strong>YYYY-MM-DD</strong> atau DD/MM/YYYY. Jenis kelamin: <strong>Laki-laki</strong> atau <strong>Perempuan</strong>. Status keluarga: kepala keluarga, istri, anak. Kepersertaan BPJS: <strong>PBI</strong> atau <strong>NON PBI</strong>.
                </p>
                <div class="mb-4">
                    <a href="{{ route('superadmin.sasaran.template-import', ['kategori' => $importKategori ?: 'dewasa']) }}"
                       target="_blank"
                       rel="noopener"
                       class="inline-flex items-center gap-2 text-sm font-medium text-primary hover:underline">
                        <i class="ph ph-download-simple"></i>
                        Unduh template Excel (.xlsx) {{ $importKategori ? (($importKategoriLabels[$importKategori] ?? ucfirst($importKategori))) : '' }}
                    </a>
                    <p class="text-xs text-gray-500 mt-1">Template Excel menampilkan data per kolom. Isi data lalu simpan dan upload untuk import.</p>
                </div>
                @endif
                <div class="space-y-2">
                    <input type="file"
                           wire:model="importFile"
                           accept=".csv,.txt,.xlsx,.xls"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary file:text-white hover:file:bg-primary/90">
                    @error('importFile')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @if($importFile)
                        <p class="text-xs text-green-600">File dipilih: {{ $importFile->getClientOriginalName() }}</p>
                    @endif
                </div>
                @if($importResult)
                @php
                    $total = ($importResult['added'] ?? 0) + ($importResult['skipped'] ?? 0) + ($importResult['errors'] ?? 0);
                    $allSuccess = ($importResult['errors'] ?? 0) === 0 && ($importResult['added'] ?? 0) > 0;
                    $partial = ($importResult['errors'] ?? 0) > 0 && ($importResult['added'] ?? 0) > 0;
                    $allError = ($importResult['errors'] ?? 0) > 0 && ($importResult['added'] ?? 0) === 0;
                    $allSkipped = $total > 0 && ($importResult['added'] ?? 0) === 0 && ($importResult['errors'] ?? 0) === 0;
                @endphp
                <div class="mt-4 p-3 rounded-lg text-sm {{ $allSuccess ? 'bg-green-50 border border-green-200' : ($allError ? 'bg-red-50 border border-red-200' : 'bg-amber-50 border border-amber-200') }}">
                    <p class="font-medium {{ $allSuccess ? 'text-green-800' : ($allError ? 'text-red-800' : 'text-amber-800') }}">
                        @if($allSuccess)
                            <i class="ph ph-check-circle mr-1"></i> Berhasil
                        @elseif($allError)
                            <i class="ph ph-x-circle mr-1"></i> Gagal
                        @else
                            <i class="ph ph-warning mr-1"></i> Sebagian berhasil
                        @endif
                    </p>
                    <ul class="mt-2 space-y-1 text-gray-700">
                        <li><strong>Ditambahkan:</strong> {{ $importResult['added'] ?? 0 }}</li>
                        <li><strong>Dilewati (duplikat):</strong> {{ $importResult['skipped'] ?? 0 }}</li>
                        <li><strong>Gagal:</strong> {{ $importResult['errors'] ?? 0 }}</li>
                    </ul>
                    @if(($allError || $partial) && !empty($importResult['errorDetails'] ?? []))
                        <div class="mt-2 text-xs text-red-700 space-y-1">
                            @foreach(array_slice($importResult['errorDetails'] ?? [], 0, 5) as $detail)
                                <p>{{ $detail }}</p>
                            @endforeach
                            @if(count($importResult['errorDetails'] ?? []) > 5)
                                <p>... dan {{ count($importResult['errorDetails']) - 5 }} error lainnya.</p>
                            @endif
                        </div>
                    @elseif($allError && $total > 0)
                        <p class="mt-2 text-xs text-red-700">Periksa kolom NIK, nama, no_kk, tanggal_lahir, jenis_kelamin, alamat. Format tanggal: YYYY-MM-DD atau DD/MM/YYYY.</p>
                    @endif
                    @if($allSkipped)
                        <p class="mt-2 text-xs text-amber-700">Semua baris sudah terdaftar (NIK + posyandu sama). Tidak ada data baru.</p>
                    @endif
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
