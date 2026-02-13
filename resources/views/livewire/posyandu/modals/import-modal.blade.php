@if($showImportModal)
@php
    $isMaster = ($importKategori ?? '') === 'master';
@endphp
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeImportModal"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full">
            <div class="bg-white px-5 pt-6 pb-4 sm:p-6 sm:pb-5">
                <h3 class="text-xl font-semibold text-gray-900 mb-5 flex items-center">
                    <i class="ph ph-upload-simple text-2xl mr-2 text-primary"></i>
                    @if($isMaster)
                        Import Master (Semua Kategori)
                    @else
                        Import Sasaran {{ $importKategori ? (($importKategoriLabels[$importKategori] ?? ucfirst($importKategori))) : '' }}
                    @endif
                </h3>
                @if($isMaster)
                <div class="space-y-4">
                    <div class="rounded-lg bg-blue-50 border border-blue-100 p-4">
                        <p class="text-sm text-gray-800">
                            <strong>Excel:</strong> Satu file berisi <strong>6 sheet</strong> (Bayi Balita, Remaja, Dewasa, Ibu Hamil, Pralansia, Lansia). Satu sheet = satu kategori; kolom per sheet sama seperti form kategori tersebut.
                        </p>
                        <p class="text-sm text-gray-800 mt-2">
                            <strong>CSV:</strong> Satu file dengan kolom <strong>kategori</strong> di baris pertama. Setiap baris isi kategori (bayibalita, remaja, dewasa, ibuhamil, pralansia, lansia) plus kolom data sasaran.
                        </p>
                        <p class="text-xs text-gray-600 mt-2">File Excel (.xlsx, .xls) atau CSV (.csv, .txt). Maksimal 5 MB.</p>
                    </div>
                    <div>
                        <a href="{{ route('adminPosyandu.sasaran.template-import', ['kategori' => 'master']) }}"
                           target="_blank"
                           rel="noopener"
                           class="inline-flex items-center gap-2 text-sm font-medium text-primary hover:underline">
                            <i class="ph ph-download-simple"></i>
                            Unduh template Excel Master (.xlsx)
                        </a>
                        <p class="text-xs text-gray-500 mt-1">Isi tiap sheet lalu simpan dan upload di bawah.</p>
                    </div>
                </div>
                @else
                <div class="space-y-4">
                    <div class="rounded-lg bg-gray-50 border border-gray-100 p-4">
                        <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide mb-2">Format & nilai yang diterima</p>
                        <ul class="text-sm text-gray-700 space-y-1 list-disc list-inside">
                            <li><strong>Tanggal:</strong> YYYY-MM-DD, DD/MM/YYYY, atau angka tanggal Excel (otomatis dikonversi)</li>
                            <li><strong>Jenis kelamin:</strong> Laki-laki / Perempuan (singkatan L, P, LK, PR juga diterima)</li>
                            <li><strong>Pendidikan:</strong> SMP, SMA, S1, D3, dll. (singkatan otomatis dipetakan)</li>
                            <li><strong>Pekerjaan:</strong> PNS, TNI, Wiraswasta, dll. (singkatan otomatis dipetakan)</li>
                            <li><strong>Status keluarga (opsional):</strong> kepala keluarga, istri, anak</li>
                            <li><strong>Kepersertaan BPJS (opsional):</strong> PBI atau NON PBI</li>
                        </ul>
                        <p class="text-xs text-gray-500 mt-2">Nama header boleh pakai spasi (mis. "NIK Sasaran"); sistem menyesuaikan ke nama kolom.</p>
                    </div>
                    <div>
                        <a href="{{ route('adminPosyandu.sasaran.template-import', ['kategori' => $importKategori ?: 'dewasa']) }}"
                           target="_blank"
                           rel="noopener"
                           class="inline-flex items-center gap-2 text-sm font-medium text-primary hover:underline">
                            <i class="ph ph-download-simple"></i>
                            Unduh template Excel {{ $importKategori ? (($importKategoriLabels[$importKategori] ?? ucfirst($importKategori))) : 'Dewasa' }} (.xlsx)
                        </a>
                        <p class="text-xs text-gray-500 mt-1">File CSV atau Excel (.xlsx, .xls), maksimal 5 MB.</p>
                    </div>
                </div>
                @endif

                <div class="mt-5 pt-4 border-t border-gray-100">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih file</label>
                    <input type="file"
                           wire:model="importFile"
                           accept=".csv,.txt,.xlsx,.xls"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary file:text-white hover:file:bg-primary/90 border border-gray-200 rounded-lg p-2 bg-gray-50">
                    @error('importFile')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                    @if($importFile)
                        <p class="text-xs text-green-600 mt-2 flex items-center gap-1"><i class="ph ph-check-circle"></i> {{ $importFile->getClientOriginalName() }}</p>
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
                <div class="mt-5 p-4 rounded-xl text-sm {{ $allSuccess ? 'bg-green-50 border border-green-200' : ($allError ? 'bg-red-50 border border-red-200' : 'bg-amber-50 border border-amber-200') }}">
                    <p class="font-semibold {{ $allSuccess ? 'text-green-800' : ($allError ? 'text-red-800' : 'text-amber-800') }}">
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
                        <div class="mt-3 text-xs text-red-700 space-y-2 max-h-40 overflow-y-auto">
                            @foreach(array_slice($importResult['errorDetails'] ?? [], 0, 8) as $detail)
                                <p class="leading-relaxed">{{ $detail }}</p>
                            @endforeach
                            @if(count($importResult['errorDetails'] ?? []) > 8)
                                <p class="text-red-600/80">... dan {{ count($importResult['errorDetails']) - 8 }} baris lainnya gagal.</p>
                            @endif
                            <p class="text-red-600/90 mt-2 font-medium">Tips: Gunakan format pendidikan (SMP, SMA, S1) dan pekerjaan (PNS, Wiraswasta) sesuai template. Kepersertaan BPJS: PBI atau NON PBI.</p>
                        </div>
                    @elseif($allError && $total > 0)
                        <p class="mt-2 text-xs text-red-700">Periksa kolom wajib dan format data (tanggal, jenis kelamin, pendidikan, pekerjaan).</p>
                    @endif
                    @if($allSkipped)
                        <p class="mt-2 text-xs text-amber-700">Semua baris sudah terdaftar (NIK + posyandu sama). Tidak ada data baru.</p>
                    @endif
                </div>
                @endif
            </div>
            <div class="bg-gray-50 px-5 py-4 sm:px-6 sm:flex sm:flex-row-reverse gap-3 rounded-b-xl">
                <button type="button"
                        wire:click="importSasaran"
                        wire:loading.attr="disabled"
                        class="w-full inline-flex justify-center items-center rounded-lg border border-transparent shadow-sm px-4 py-2.5 bg-primary text-sm font-medium text-white hover:bg-primary/90 focus:outline-none sm:w-auto">
                    <span wire:loading.remove wire:target="importSasaran">Import</span>
                    <span wire:loading wire:target="importSasaran" class="flex items-center gap-2"><i class="ph ph-spinner ph-spin"></i> Memproses...</span>
                </button>
                <button type="button"
                        wire:click="closeImportModal"
                        class="w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2.5 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:w-auto">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endif
