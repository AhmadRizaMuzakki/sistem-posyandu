<div>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">{{ $posyandu->nama_posyandu }}</h1>
                    <p class="text-gray-500 mt-1">Manajemen Data Imunisasi</p>
                </div>
            </div>
        </div>

        {{-- Daftar Imunisasi --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="ph ph-syringe text-2xl mr-3 text-primary"></i>
                    Daftar Imunisasi
                </h2>
                <button wire:click="openImunisasiModal"
                        class="flex items-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="ph ph-plus text-lg mr-2"></i>
                    Tambah Imunisasi
                </button>
            </div>

            {{-- Search Bar --}}
            <div class="mb-4">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <i class="ph ph-magnifying-glass text-lg"></i>
                    </span>
                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           class="w-full py-2 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                           placeholder="Cari berdasarkan jenis imunisasi, kategori, atau keterangan...">
                </div>
            </div>

            @if($imunisasiList->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sasaran</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Imunisasi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($imunisasiList as $index => $imunisasi)
                                @php
                                    $sasaran = $imunisasi->sasaran;
                                    $sasaranNama = $sasaran ? $sasaran->nama_sasaran : 'Tidak ditemukan';
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $sasaranNama }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 capitalize">
                                            {{ $imunisasi->kategori_sasaran }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $imunisasi->jenis_imunisasi }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $imunisasi->tanggal_imunisasi ? \Carbon\Carbon::parse($imunisasi->tanggal_imunisasi)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $imunisasi->keterangan ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button wire:click="openImunisasiModal({{ $imunisasi->id_imunisasi }})"
                                                class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            <i class="ph ph-pencil"></i>
                                        </button>
                                        <button wire:click="deleteImunisasi({{ $imunisasi->id_imunisasi }})"
                                                wire:confirm="Apakah Anda yakin ingin menghapus data imunisasi ini?"
                                                class="text-red-600 hover:text-red-900">
                                            <i class="ph ph-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12 text-gray-500">
                    <i class="ph ph-syringe text-4xl mb-2"></i>
                    <p>Belum ada data imunisasi</p>
                    <button wire:click="openImunisasiModal"
                            class="mt-4 px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        Tambah Imunisasi Pertama
                    </button>
                </div>
            @endif
        </div>

        {{-- Pesan Sukses --}}
        @if(session()->has('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('message') }}</span>
            </div>
        @endif

        {{-- Modal Form Imunisasi --}}
        @if($isImunisasiModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Background Overlay --}}
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeImunisasiModal" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Modal Panel --}}
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form wire:submit.prevent="storeImunisasi">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                {{ $id_imunisasi ? 'Edit Data Imunisasi' : 'Tambah Imunisasi Baru' }}
                            </h3>

                            <div class="grid grid-cols-1 gap-4">
                                {{-- Pilih Sasaran --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Sasaran <span class="text-red-500">*</span></label>
                                    <select wire:model="id_sasaran_imunisasi"
                                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary"
                                            @if(empty($sasaranList)) disabled @endif>
                                        <option value="">Pilih Sasaran...</option>
                                        @foreach($sasaranList as $sasaran)
                                            <option value="{{ $sasaran['id'] }}" data-kategori="{{ $sasaran['kategori'] }}">
                                                {{ $sasaran['nama'] }} ({{ $sasaran['nik'] }}) - {{ ucfirst($sasaran['kategori']) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('id_sasaran_imunisasi') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                    @if(empty($sasaranList))
                                        <p class="text-xs text-gray-500 mt-1">Belum ada sasaran terdaftar di posyandu ini</p>
                                    @endif
                                </div>

                                {{-- Kategori Sasaran (Auto-filled) --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Kategori Sasaran <span class="text-red-500">*</span></label>
                                    <input type="text"
                                           wire:model="kategori_sasaran_imunisasi"
                                           readonly
                                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none focus:ring-primary focus:border-primary"
                                           placeholder="Akan terisi otomatis">
                                    @error('kategori_sasaran_imunisasi') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>

                                {{-- Jenis Imunisasi --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Jenis Imunisasi <span class="text-red-500">*</span></label>
                                    <select wire:model="jenis_imunisasi"
                                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                        <option value="">Pilih Jenis Imunisasi...</option>
                                        <optgroup label="Imunisasi Dasar Bayi">
                                            <option value="BCG">BCG (Bacillus Calmette-Guérin)</option>
                                            <option value="Polio 1">Polio 1</option>
                                            <option value="Polio 2">Polio 2</option>
                                            <option value="Polio 3">Polio 3</option>
                                            <option value="Polio 4">Polio 4</option>
                                            <option value="DPT-HB-Hib 1">DPT-HB-Hib 1</option>
                                            <option value="DPT-HB-Hib 2">DPT-HB-Hib 2</option>
                                            <option value="DPT-HB-Hib 3">DPT-HB-Hib 3</option>
                                            <option value="Hepatitis B 0">Hepatitis B 0</option>
                                            <option value="Hepatitis B 1">Hepatitis B 1</option>
                                            <option value="Hepatitis B 2">Hepatitis B 2</option>
                                            <option value="Campak 1">Campak 1</option>
                                            <option value="Campak 2">Campak 2</option>
                                        </optgroup>
                                        <optgroup label="Imunisasi Lanjutan">
                                            <option value="DPT-HB-Hib Booster">DPT-HB-Hib Booster</option>
                                            <option value="Campak Booster">Campak Booster</option>
                                            <option value="Polio Booster">Polio Booster</option>
                                        </optgroup>
                                        <optgroup label="Imunisasi Remaja & Dewasa">
                                            <option value="TT (Tetanus Toxoid)">TT (Tetanus Toxoid)</option>
                                            <option value="TT Booster 1">TT Booster 1</option>
                                            <option value="TT Booster 2">TT Booster 2</option>
                                            <option value="TT Booster 3">TT Booster 3</option>
                                            <option value="TT Booster 4">TT Booster 4</option>
                                            <option value="TT Booster 5">TT Booster 5</option>
                                            <option value="Hepatitis B Dewasa">Hepatitis B Dewasa</option>
                                            <option value="Influenza">Influenza</option>
                                        </optgroup>
                                        <optgroup label="Imunisasi COVID-19">
                                            <option value="COVID-19 Dosis 1">COVID-19 Dosis 1</option>
                                            <option value="COVID-19 Dosis 2">COVID-19 Dosis 2</option>
                                            <option value="COVID-19 Booster 1">COVID-19 Booster 1</option>
                                            <option value="COVID-19 Booster 2">COVID-19 Booster 2</option>
                                            <option value="COVID-19 Booster 3">COVID-19 Booster 3</option>
                                        </optgroup>
                                        <optgroup label="Imunisasi Lansia">
                                            <option value="Pneumonia">Pneumonia</option>
                                            <option value="Herpes Zoster">Herpes Zoster</option>
                                        </optgroup>
                                        <optgroup label="Lainnya">
                                            <option value="Lainnya">Lainnya (Isi di keterangan)</option>
                                        </optgroup>
                                    </select>
                                    @error('jenis_imunisasi') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                    <p class="text-xs text-gray-500 mt-1">Jika jenis imunisasi tidak ada dalam daftar, pilih "Lainnya" dan isi di keterangan</p>
                                </div>

                                {{-- Tanggal Imunisasi --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Imunisasi <span class="text-red-500">*</span></label>
                                    <div class="grid grid-cols-3 gap-2">
                                        <div>
                                            <select wire:model="hari_imunisasi"
                                                    class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                                <option value="">Hari</option>
                                                @for($i = 1; $i <= 31; $i++)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                            @error('hari_imunisasi') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                        </div>
                                        <div>
                                            <select wire:model="bulan_imunisasi"
                                                    class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                                <option value="">Bulan</option>
                                                <option value="1">Januari</option>
                                                <option value="2">Februari</option>
                                                <option value="3">Maret</option>
                                                <option value="4">April</option>
                                                <option value="5">Mei</option>
                                                <option value="6">Juni</option>
                                                <option value="7">Juli</option>
                                                <option value="8">Agustus</option>
                                                <option value="9">September</option>
                                                <option value="10">Oktober</option>
                                                <option value="11">November</option>
                                                <option value="12">Desember</option>
                                            </select>
                                            @error('bulan_imunisasi') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                        </div>
                                        <div>
                                            <input type="number"
                                                   wire:model="tahun_imunisasi"
                                                   min="1900"
                                                   max="{{ date('Y') }}"
                                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary"
                                                   placeholder="Tahun">
                                            @error('tahun_imunisasi') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    @error('tanggal_imunisasi') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>

                                {{-- Tinggi & Berat Badan --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-gray-700 text-sm font-bold mb-2">Tinggi Badan</label>
                                        <div class="flex">
                                            <input type="number"
                                                   step="0.1"
                                                   min="0"
                                                   max="300"
                                                   wire:model="tinggi_badan"
                                                   class="shadow appearance-none border border-r-0 rounded-l w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary"
                                                   placeholder="Contoh: 120">
                                            <span class="inline-flex items-center px-3 border border-l-0 rounded-r bg-gray-50 text-gray-500 text-sm">
                                                cm
                                            </span>
                                        </div>
                                        @error('tinggi_badan') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 text-sm font-bold mb-2">Berat Badan</label>
                                        <div class="flex">
                                            <input type="number"
                                                   step="0.1"
                                                   min="0"
                                                   max="300"
                                                   wire:model="berat_badan"
                                                   class="shadow appearance-none border border-r-0 rounded-l w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary"
                                                   placeholder="Contoh: 25">
                                            <span class="inline-flex items-center px-3 border border-l-0 rounded-r bg-gray-50 text-gray-500 text-sm">
                                                kg
                                            </span>
                                        </div>
                                        @error('berat_badan') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                {{-- Keterangan --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Keterangan</label>
                                    <textarea wire:model="keterangan"
                                              rows="3"
                                              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary"
                                              placeholder="Keterangan tambahan (opsional)"></textarea>
                                    @error('keterangan') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                Simpan Data
                            </button>
                            <button type="button"
                                    wire:click="closeImunisasiModal"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
