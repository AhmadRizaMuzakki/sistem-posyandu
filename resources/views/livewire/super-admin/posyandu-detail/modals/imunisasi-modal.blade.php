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
                        {{-- Pilih Posyandu (Read-only) --}}
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Posyandu <span class="text-red-500">*</span></label>
                            @php
                                $userPosyandu = auth()->user()->kader->first()->posyandu ?? null;
                                if ($userPosyandu) {
                                    $this->id_posyandu_imunisasi = $userPosyandu->id_posyandu;
                                    $this->loadSasaranList();
                                }
                            @endphp
                            <input type="text"
                                   value="{{ $userPosyandu ? $userPosyandu->nama_posyandu : ($posyandu->nama_posyandu ?? '') }}"
                                   readonly
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none focus:ring-primary focus:border-primary cursor-not-allowed">
                            @if(!$userPosyandu && !isset($posyandu))
                                <select wire:model="id_posyandu_imunisasi"
                                        wire:change="updatedIdPosyanduImunisasi"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                    <option value="">Pilih Posyandu...</option>
                                    @foreach($dataPosyandu as $posyanduOpt)
                                        <option value="{{ is_array($posyanduOpt) ? ($posyanduOpt['id_posyandu'] ?? $posyanduOpt['id'] ?? '') : ($posyanduOpt->id_posyandu ?? $posyanduOpt->id ?? '') }}">
                                            {{ is_array($posyanduOpt) ? ($posyanduOpt['nama_posyandu'] ?? '') : ($posyanduOpt->nama_posyandu ?? '') }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                            @error('id_posyandu_imunisasi') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>

                        {{-- Pilih Sasaran (Searchable) --}}
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Sasaran <span class="text-red-500">*</span></label>
                            <div class="relative" x-data="{
                                open: false,
                                searchText: '',
                                selectedId: @entangle('id_sasaran_imunisasi'),
                                selectedName: ''
                            }" x-init="
                                $watch('selectedId', value => {
                                    if (value) {
                                        const sasaran = @js($sasaranList).find(s => s.id == value);
                                        if (sasaran) {
                                            selectedName = sasaran.nama + ' (' + sasaran.nik + ') - ' + sasaran.kategori.charAt(0).toUpperCase() + sasaran.kategori.slice(1);
                                            searchText = selectedName;
                                        }
                                    } else {
                                        selectedName = '';
                                        searchText = '';
                                    }
                                });
                                if (selectedId) {
                                    const sasaran = @js($sasaranList).find(s => s.id == selectedId);
                                    if (sasaran) {
                                        selectedName = sasaran.nama + ' (' + sasaran.nik + ') - ' + sasaran.kategori.charAt(0).toUpperCase() + sasaran.kategori.slice(1);
                                        searchText = selectedName;
                                    }
                                }
                            ">
                                <input
                                    type="text"
                                    x-model="searchText"
                                    @focus="open = true"
                                    @input="open = true"
                                    @keydown.escape="open = false"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary"
                                    placeholder="Ketik untuk mencari sasaran..."
                                    autocomplete="off"
                                    @if(empty($sasaranList)) disabled @endif>
                                <div x-show="open"
                                     @click.outside="open = false"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
                                     style="display: none;">
                                    <ul class="py-1">
                                        <li @click="selectedId = ''; searchText = ''; open = false; $wire.set('id_sasaran_imunisasi', '')"
                                            class="px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 cursor-pointer">
                                            -- Pilih Sasaran --
                                        </li>
                                        @if(!empty($sasaranList))
                                            @foreach($sasaranList as $sasaran)
                                                <li @click="selectedId = '{{ $sasaran['id'] }}'; selectedName = '{{ $sasaran['nama'] }} ({{ $sasaran['nik'] }}) - {{ ucfirst($sasaran['kategori']) }}'; searchText = selectedName; open = false; $wire.set('id_sasaran_imunisasi', '{{ $sasaran['id'] }}'); $wire.updatedIdSasaranImunisasi('{{ $sasaran['id'] }}')"
                                                    x-show="!searchText || '{{ strtolower($sasaran['nama'] . ' ' . $sasaran['nik'] . ' ' . $sasaran['kategori']) }}'.includes(searchText.toLowerCase())"
                                                    class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer"
                                                    :class="selectedId == '{{ $sasaran['id'] }}' ? 'bg-blue-50 font-medium' : ''">
                                                    {{ $sasaran['nama'] }} ({{ $sasaran['nik'] }}) - {{ ucfirst($sasaran['kategori']) }}
                                                </li>
                                            @endforeach
                                        @else
                                            <li class="px-4 py-2 text-sm text-gray-500">Tidak ada sasaran ditemukan</li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                            @error('id_sasaran_imunisasi') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                            @if(empty($sasaranList) && $id_posyandu_imunisasi)
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
                            <input type="date"
                                   wire:model="tanggal_imunisasi"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                            @error('tanggal_imunisasi') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
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

