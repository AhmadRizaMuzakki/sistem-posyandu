@if($showImportModal)
@php
    $isMaster = ($importKategori ?? '') === 'master';
    $templateRoute = $importTemplateRoute ?? 'adminPosyandu.sasaran.template-import';
    $kategoriLabels = $importKategoriLabels ?? [
        'bayibalita' => 'Bayi/Balita',
        'remaja' => 'Remaja',
        'dewasa' => 'Dewasa',
        'ibuhamil' => 'Ibu Hamil',
        'pralansia' => 'Pralansia',
        'lansia' => 'Lansia',
    ];
    $failedRows = $importResult['failedRows'] ?? [];
    if (empty($failedRows) && !empty($importResult['errorDetails'] ?? [])) {
        $failedRows = array_map(
            fn ($msg) => ['row' => null, 'sheet' => null, 'kategori' => null, 'nik' => '', 'nama' => '', 'message' => $msg],
            $importResult['errorDetails']
        );
    }
@endphp
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-[1px] transition-opacity" wire:click="closeImportModal"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full ring-1 ring-black/5">
            {{-- Header --}}
            <div class="flex items-start justify-between px-6 pt-6 pb-4 border-b border-gray-100">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2" id="modal-title">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <i class="ph ph-upload-simple text-xl"></i>
                        </span>
                        @if($isMaster)
                            Import Master
                        @else
                            Import {{ $importKategori ? ($kategoriLabels[$importKategori] ?? ucfirst($importKategori)) : 'Sasaran' }}
                        @endif
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 ml-11">
                        @if($isMaster)
                            Satu file Excel/CSV untuk semua kategori sasaran
                        @else
                            Upload file Excel atau CSV sesuai template
                        @endif
                    </p>
                </div>
                <button type="button" wire:click="closeImportModal" class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors" aria-label="Tutup">
                    <i class="ph ph-x text-lg"></i>
                </button>
            </div>

            <div class="px-6 py-5 space-y-5">
                @if(!$importResult)
                    {{-- Template download --}}
                    <a href="{{ route($templateRoute, ['kategori' => $isMaster ? 'master' : ($importKategori ?: 'dewasa')]) }}"
                       target="_blank"
                       rel="noopener"
                       class="flex items-center gap-4 p-4 rounded-xl border border-primary/20 bg-primary/5 hover:bg-primary/10 transition-colors group">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary text-white group-hover:scale-105 transition-transform">
                            <i class="ph ph-download-simple text-xl"></i>
                        </span>
                        <div class="text-left min-w-0">
                            <p class="text-sm font-medium text-gray-900">
                                Unduh template Excel
                                @if($isMaster)
                                    Master
                                @else
                                    {{ $importKategori ? ($kategoriLabels[$importKategori] ?? ucfirst($importKategori)) : 'Dewasa' }}
                                @endif
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                @if($isMaster)
                                    6 sheet: Bayi Balita, Remaja, Dewasa, Ibu Hamil, Pralansia, Lansia
                                @else
                                    Format kolom sama seperti form input sasaran
                                @endif
                            </p>
                        </div>
                        <i class="ph ph-arrow-square-out text-gray-400 ml-auto shrink-0"></i>
                    </a>

                    {{-- Petunjuk (collapsible) --}}
                    <details class="group rounded-xl border border-gray-200 bg-gray-50/50">
                        <summary class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-medium text-gray-700 select-none list-none [&::-webkit-details-marker]:hidden">
                            <span class="flex items-center gap-2">
                                <i class="ph ph-info text-primary"></i>
                                Petunjuk format file
                            </span>
                            <i class="ph ph-caret-down text-gray-400 transition-transform group-open:rotate-180"></i>
                        </summary>
                        <div class="px-4 pb-4 text-sm text-gray-600 space-y-2 border-t border-gray-100 pt-3">
                            @if($isMaster)
                                <p><span class="font-medium text-gray-800">Excel:</span> Satu file, 6 sheet (satu sheet per kategori). Isi kolom mengikuti template unduhan.</p>
                                <p><span class="font-medium text-gray-800">CSV:</span> Kolom kategori wajib ada. Nilai: bayibalita, remaja, dewasa, ibuhamil, pralansia, lansia.</p>
                            @else
                                <ul class="space-y-1 list-disc list-inside text-gray-600">
                                    <li>Tanggal: YYYY-MM-DD, DD/MM/YYYY, atau format Excel</li>
                                    <li>Jenis kelamin: Laki-laki / Perempuan (L, P, LK, PR)</li>
                                    <li>Pendidikan & pekerjaan: singkatan otomatis dipetakan</li>
                                    <li>BPJS (opsional): PBI atau NON PBI</li>
                                </ul>
                            @endif
                            <p class="text-xs text-gray-500 pt-1">.xlsx, .xls, .csv, .txt — maks. 5 MB</p>
                        </div>
                    </details>

                    {{-- File upload --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih file</label>
                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-200 rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100/80 hover:border-primary/40 transition-colors">
                            <div class="flex flex-col items-center justify-center py-4 px-4 text-center">
                                <i class="ph ph-file-arrow-up text-3xl text-gray-400 mb-2"></i>
                                <p class="text-sm text-gray-600">
                                    <span class="font-medium text-primary">Klik untuk memilih</span> atau seret file ke sini
                                </p>
                                <p class="text-xs text-gray-400 mt-1">Excel atau CSV, maks. 5 MB</p>
                            </div>
                            <input type="file"
                                   wire:model="importFile"
                                   accept=".csv,.txt,.xlsx,.xls"
                                   class="hidden">
                        </label>
                        @error('importFile')
                            <p class="text-sm text-red-600 mt-2 flex items-center gap-1"><i class="ph ph-warning-circle"></i> {{ $message }}</p>
                        @enderror
                        @if($importFile)
                            <p class="text-sm text-green-700 mt-2 flex items-center gap-2 px-3 py-2 rounded-lg bg-green-50 border border-green-100">
                                <i class="ph ph-check-circle text-green-600"></i>
                                <span class="truncate">{{ $importFile->getClientOriginalName() }}</span>
                            </p>
                        @endif
                        <div wire:loading wire:target="importFile" class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                            <i class="ph ph-spinner ph-spin"></i> Mengunggah file...
                        </div>
                    </div>
                @else
                    {{-- Hasil import --}}
                    @php
                        $added = $importResult['added'] ?? 0;
                        $skipped = $importResult['skipped'] ?? 0;
                        $errors = $importResult['errors'] ?? 0;
                        $total = $added + $skipped + $errors;
                        $allSuccess = $errors === 0 && $added > 0;
                        $partial = $errors > 0 && $added > 0;
                        $allError = $errors > 0 && $added === 0;
                        $allSkipped = $total > 0 && $added === 0 && $errors === 0;
                    @endphp

                    <div class="rounded-xl border {{ $allSuccess ? 'border-green-200 bg-green-50/50' : ($allError ? 'border-red-200 bg-red-50/50' : 'border-amber-200 bg-amber-50/50') }} p-4">
                        <div class="flex items-center gap-3">
                            @if($allSuccess)
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 text-green-600"><i class="ph ph-check-circle text-xl"></i></span>
                                <div>
                                    <p class="font-semibold text-green-800">Import berhasil</p>
                                    <p class="text-sm text-green-700">{{ $added }} data berhasil ditambahkan</p>
                                </div>
                            @elseif($allError)
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 text-red-600"><i class="ph ph-x-circle text-xl"></i></span>
                                <div>
                                    <p class="font-semibold text-red-800">Import gagal</p>
                                    <p class="text-sm text-red-700">Tidak ada data yang berhasil diimport</p>
                                </div>
                            @elseif($allSkipped)
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-amber-600"><i class="ph ph-info text-xl"></i></span>
                                <div>
                                    <p class="font-semibold text-amber-800">Tidak ada data baru</p>
                                    <p class="text-sm text-amber-700">Semua baris sudah terdaftar (duplikat NIK)</p>
                                </div>
                            @else
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-amber-600"><i class="ph ph-warning text-xl"></i></span>
                                <div>
                                    <p class="font-semibold text-amber-800">Sebagian berhasil</p>
                                    <p class="text-sm text-amber-700">{{ $added }} berhasil, {{ $errors }} gagal</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Statistik ringkas --}}
                    <div class="grid grid-cols-3 gap-3">
                        <div class="text-center rounded-xl border border-green-100 bg-green-50 px-3 py-3">
                            <p class="text-2xl font-bold text-green-700">{{ $added }}</p>
                            <p class="text-xs text-green-600 mt-0.5">Ditambahkan</p>
                        </div>
                        <div class="text-center rounded-xl border border-gray-100 bg-gray-50 px-3 py-3">
                            <p class="text-2xl font-bold text-gray-600">{{ $skipped }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">Duplikat</p>
                        </div>
                        <div class="text-center rounded-xl border border-red-100 bg-red-50 px-3 py-3">
                            <p class="text-2xl font-bold text-red-600">{{ $errors }}</p>
                            <p class="text-xs text-red-500 mt-0.5">Gagal</p>
                        </div>
                    </div>

                    {{-- Daftar data gagal --}}
                    @if(!empty($failedRows))
                        <div class="rounded-xl border border-red-200 overflow-hidden">
                            <div class="flex items-center justify-between px-4 py-3 bg-red-50 border-b border-red-100">
                                <h4 class="text-sm font-semibold text-red-800 flex items-center gap-2">
                                    <i class="ph ph-warning-circle"></i>
                                    Data yang gagal ({{ count($failedRows) }})
                                </h4>
                            </div>
                            <div class="max-h-52 overflow-y-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50 sticky top-0">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Lokasi</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Data</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Alasan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($failedRows as $failed)
                                            <tr class="hover:bg-red-50/30">
                                                <td class="px-3 py-2.5 text-gray-600 whitespace-nowrap align-top">
                                                    @if(!empty($failed['sheet']))
                                                        <span class="block text-xs font-medium text-gray-800">{{ $failed['sheet'] }}</span>
                                                    @endif
                                                    @if(!empty($failed['row']))
                                                        <span class="text-xs text-gray-500">Baris {{ $failed['row'] }}</span>
                                                    @endif
                                                    @if(empty($failed['sheet']) && empty($failed['row']))
                                                        <span class="text-xs text-gray-400">—</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2.5 align-top">
                                                    @if(!empty($failed['kategori']))
                                                        <span class="inline-block text-xs px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 mb-1">
                                                            {{ $kategoriLabels[$failed['kategori']] ?? $failed['kategori'] }}
                                                        </span>
                                                    @endif
                                                    @if(!empty($failed['nama']))
                                                        <span class="block text-xs font-medium text-gray-800">{{ $failed['nama'] }}</span>
                                                    @endif
                                                    @if(!empty($failed['nik']))
                                                        <span class="block text-xs text-gray-500 font-mono">{{ $failed['nik'] }}</span>
                                                    @endif
                                                    @if(empty($failed['nama']) && empty($failed['nik']) && empty($failed['kategori']))
                                                        <span class="text-xs text-gray-400">—</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2.5 text-xs text-red-700 align-top leading-relaxed">
                                                    {{ $failed['message'] }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                @endif
            </div>

            {{-- Footer --}}
            <div class="bg-gray-50 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t border-gray-100">
                <button type="button"
                        wire:click="closeImportModal"
                        class="w-full sm:w-auto inline-flex justify-center items-center rounded-lg border border-gray-300 px-4 py-2.5 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    {{ $importResult ? 'Tutup' : 'Batal' }}
                </button>
                @if(!$importResult)
                    <button type="button"
                            wire:click="importSasaran"
                            wire:loading.attr="disabled"
                            wire:target="importSasaran,importFile"
                            @disabled(!$importFile)
                            class="w-full sm:w-auto inline-flex justify-center items-center rounded-lg px-4 py-2.5 bg-primary text-sm font-medium text-white hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
                        <span wire:loading.remove wire:target="importSasaran">
                            <i class="ph ph-upload-simple mr-1.5"></i> Import
                        </span>
                        <span wire:loading wire:target="importSasaran" class="flex items-center gap-2">
                            <i class="ph ph-spinner ph-spin"></i> Memproses...
                        </span>
                    </button>
                @elseif(($importResult['errors'] ?? 0) > 0)
                    <button type="button"
                            wire:click="$set('importResult', null)"
                            class="w-full sm:w-auto inline-flex justify-center items-center rounded-lg px-4 py-2.5 bg-primary text-sm font-medium text-white hover:bg-primary/90 transition-colors shadow-sm">
                        <i class="ph ph-arrow-counter-clockwise mr-1.5"></i> Import ulang
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
@endif
