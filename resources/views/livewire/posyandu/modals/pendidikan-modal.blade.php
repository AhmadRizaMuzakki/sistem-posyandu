{{-- Modal Form Pendidikan --}}
@if($isPendidikanModalOpen)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closePendidikanModal" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <form wire:submit.prevent="storePendidikan">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                        {{ $id_pendidikan ? 'Edit Data Pendidikan' : 'Tambah Data Pendidikan Baru' }}
                    </h3>

                    <div class="grid grid-cols-1 gap-4">
                        {{-- Pilih Sasaran (Searchable) --}}
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Sasaran <span class="text-red-500">*</span></label>
                            <div class="relative" x-data="{
                                open: false,
                                searchText: '',
                                selectedId: @entangle('id_sasaran_pendidikan'),
                                selectedKategori: @entangle('kategori_sasaran_pendidikan'),
                                selectedName: '',
                                sasaranList: @js($sasaranList)
                            }" x-init="
                                function findSasaran(id, kategori) {
                                    if (!id) return null;
                                    if (kategori) {
                                        return sasaranList.find(s => s.id == id && s.kategori == kategori);
                                    }
                                    return sasaranList.find(s => s.id == id);
                                }
                                
                                $watch('selectedId', value => {
                                    if (value) {
                                        const sasaran = findSasaran(value, selectedKategori);
                                        if (sasaran) {
                                            selectedName = sasaran.nama + ' (' + sasaran.nik + ') - ' + sasaran.kategori.charAt(0).toUpperCase() + sasaran.kategori.slice(1);
                                            searchText = selectedName;
                                            if (sasaran.kategori !== selectedKategori) {
                                                $wire.set('kategori_sasaran_pendidikan', sasaran.kategori);
                                            }
                                        }
                                    } else {
                                        selectedName = '';
                                        searchText = '';
                                    }
                                });
                                
                                if (selectedId) {
                                    const sasaran = findSasaran(selectedId, selectedKategori);
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
                                     x-transition
                                     class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
                                     style="display: none;">
                                    <ul class="py-1">
                                        <li @click="selectedId = ''; searchText = ''; open = false; $wire.set('id_sasaran_pendidikan', '')"
                                            class="px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 cursor-pointer">
                                            -- Pilih Sasaran --
                                        </li>
                                        @if(!empty($sasaranList))
                                            @foreach($sasaranList as $sasaran)
                                                <li @click="
                                                    selectedId = '{{ $sasaran['id'] }}';
                                                    selectedKategori = '{{ $sasaran['kategori'] }}';
                                                    selectedName = '{{ $sasaran['nama'] }} ({{ $sasaran['nik'] }}) - {{ ucfirst($sasaran['kategori']) }}';
                                                    searchText = selectedName;
                                                    open = false;
                                                    $wire.set('kategori_sasaran_pendidikan', '{{ $sasaran['kategori'] }}');
                                                    $wire.set('id_sasaran_pendidikan', '{{ $sasaran['id'] }}');
                                                    $wire.set('nama_pendidikan', '{{ $sasaran['nama'] }}');
                                                    $wire.set('rt_pendidikan', '{{ $sasaran['rt'] ?? '' }}');
                                                    $wire.set('rw_pendidikan', '{{ $sasaran['rw'] ?? '' }}');
                                                "
                                                    x-show="!searchText || '{{ strtolower($sasaran['nama'] . ' ' . $sasaran['nik'] . ' ' . $sasaran['kategori']) }}'.includes(searchText.toLowerCase())"
                                                    class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer">
                                                    {{ $sasaran['nama'] }} ({{ $sasaran['nik'] }}) - {{ ucfirst($sasaran['kategori']) }}
                                                </li>
                                            @endforeach
                                        @endif
                                    </ul>
                                </div>
                            </div>
                            @error('id_sasaran_pendidikan') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>

                        {{-- Kategori Sasaran (Auto-filled) --}}
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Kategori Sasaran <span class="text-red-500">*</span></label>
                            <input type="text"
                                   wire:model="kategori_sasaran_pendidikan"
                                   readonly
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none focus:ring-primary focus:border-primary"
                                   placeholder="Akan terisi otomatis">
                            @error('kategori_sasaran_pendidikan') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>

                        {{-- Nama (Auto-filled dari sasaran, bisa diubah) --}}
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Nama <span class="text-red-500">*</span></label>
                            <input type="text"
                                   wire:model="nama_pendidikan"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary"
                                   placeholder="Akan terisi otomatis dari sasaran yang dipilih">
                            @error('nama_pendidikan') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                            <p class="text-xs text-gray-500 mt-1">Nama akan terisi otomatis dari sasaran yang dipilih, atau bisa diubah manual</p>
                        </div>

                        {{-- NIK --}}
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">NIK</label>
                            <input type="text"
                                   wire:model="nik_pendidikan"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary"
                                   placeholder="Nomor Induk Kependudukan">
                            @error('nik_pendidikan') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>

                        {{-- Tanggal Lahir --}}
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Lahir <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <select wire:model="hari_lahir_pendidikan"
                                            wire:change="updatedHariLahirPendidikan"
                                            class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                        <option value="">Hari</option>
                                        @for($i = 1; $i <= 31; $i++)
                                            <option value="{{ $i }}" @if($hari_lahir_pendidikan == $i) selected @endif>{{ $i }}</option>
                                        @endfor
                                    </select>
                                    @error('hari_lahir_pendidikan') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <select wire:model="bulan_lahir_pendidikan"
                                            wire:change="updatedBulanLahirPendidikan"
                                            class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                        <option value="">Bulan</option>
                                        <option value="1" @if($bulan_lahir_pendidikan == 1) selected @endif>Januari</option>
                                        <option value="2" @if($bulan_lahir_pendidikan == 2) selected @endif>Februari</option>
                                        <option value="3" @if($bulan_lahir_pendidikan == 3) selected @endif>Maret</option>
                                        <option value="4" @if($bulan_lahir_pendidikan == 4) selected @endif>April</option>
                                        <option value="5" @if($bulan_lahir_pendidikan == 5) selected @endif>Mei</option>
                                        <option value="6" @if($bulan_lahir_pendidikan == 6) selected @endif>Juni</option>
                                        <option value="7" @if($bulan_lahir_pendidikan == 7) selected @endif>Juli</option>
                                        <option value="8" @if($bulan_lahir_pendidikan == 8) selected @endif>Agustus</option>
                                        <option value="9" @if($bulan_lahir_pendidikan == 9) selected @endif>September</option>
                                        <option value="10" @if($bulan_lahir_pendidikan == 10) selected @endif>Oktober</option>
                                        <option value="11" @if($bulan_lahir_pendidikan == 11) selected @endif>November</option>
                                        <option value="12" @if($bulan_lahir_pendidikan == 12) selected @endif>Desember</option>
                                    </select>
                                    @error('bulan_lahir_pendidikan') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <input type="number"
                                           wire:model="tahun_lahir_pendidikan"
                                           wire:change="updatedTahunLahirPendidikan"
                                           min="1900"
                                           max="{{ date('Y') }}"
                                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary"
                                           placeholder="Tahun">
                                    @error('tahun_lahir_pendidikan') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            @error('tanggal_lahir_pendidikan') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>

                        {{-- Jenis Kelamin & Umur --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                                <select wire:model="jenis_kelamin_pendidikan"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                    <option value="">Pilih Jenis Kelamin...</option>
                                    <option value="Laki-laki" @if($jenis_kelamin_pendidikan == 'Laki-laki') selected @endif>Laki-laki</option>
                                    <option value="Perempuan" @if($jenis_kelamin_pendidikan == 'Perempuan') selected @endif>Perempuan</option>
                                </select>
                                @error('jenis_kelamin_pendidikan') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Umur</label>
                                <div class="flex">
                                    <input type="number"
                                           min="0"
                                           max="150"
                                           wire:model="umur_pendidikan"
                                           readonly
                                           class="shadow appearance-none border border-r-0 rounded-l w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none focus:ring-primary focus:border-primary cursor-not-allowed"
                                           placeholder="Akan terisi otomatis">
                                    <span class="inline-flex items-center px-3 border border-l-0 rounded-r bg-gray-50 text-gray-500 text-sm">
                                        tahun
                                    </span>
                                </div>
                                @error('umur_pendidikan') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        {{-- RT dan RW --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">RT</label>
                                <input type="text"
                                       wire:model="rt_pendidikan"
                                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary"
                                       placeholder="RT">
                                @error('rt_pendidikan') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">RW</label>
                                <input type="text"
                                       wire:model="rw_pendidikan"
                                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary"
                                       placeholder="RW">
                                @error('rw_pendidikan') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        {{-- Pendidikan Terakhir --}}
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Pendidikan Terakhir <span class="text-red-500">*</span></label>
                            <select wire:model="pendidikan_terakhir_pendidikan"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                <option value="">Pilih Pendidikan Terakhir...</option>
                                <option value="Tidak/Belum Sekolah" @if($pendidikan_terakhir_pendidikan == 'Tidak/Belum Sekolah') selected @endif>Tidak/Belum Sekolah</option>
                                <option value="PAUD" @if($pendidikan_terakhir_pendidikan == 'PAUD') selected @endif>PAUD</option>
                                <option value="TK" @if($pendidikan_terakhir_pendidikan == 'TK') selected @endif>TK</option>
                                <option value="Tidak Tamat SD/Sederajat" @if($pendidikan_terakhir_pendidikan == 'Tidak Tamat SD/Sederajat') selected @endif>Tidak Tamat SD/Sederajat</option>
                                <option value="Tamat SD/Sederajat" @if($pendidikan_terakhir_pendidikan == 'Tamat SD/Sederajat') selected @endif>Tamat SD/Sederajat</option>
                                <option value="SLTP/Sederajat" @if($pendidikan_terakhir_pendidikan == 'SLTP/Sederajat') selected @endif>SLTP/Sederajat</option>
                                <option value="SLTA/Sederajat" @if($pendidikan_terakhir_pendidikan == 'SLTA/Sederajat') selected @endif>SLTA/Sederajat</option>
                                <option value="Diploma I/II" @if($pendidikan_terakhir_pendidikan == 'Diploma I/II') selected @endif>Diploma I/II</option>
                                <option value="Akademi/Diploma III/Sarjana Muda" @if($pendidikan_terakhir_pendidikan == 'Akademi/Diploma III/Sarjana Muda') selected @endif>Akademi/Diploma III/Sarjana Muda</option>
                                <option value="Diploma IV/Strata I" @if($pendidikan_terakhir_pendidikan == 'Diploma IV/Strata I') selected @endif>Diploma IV/Strata I</option>
                                <option value="Strata II" @if($pendidikan_terakhir_pendidikan == 'Strata II') selected @endif>Strata II</option>
                                <option value="Strata III" @if($pendidikan_terakhir_pendidikan == 'Strata III') selected @endif>Strata III</option>
                            </select>
                            @error('pendidikan_terakhir_pendidikan') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Simpan Data
                    </button>
                    <button type="button"
                            wire:click="closePendidikanModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

