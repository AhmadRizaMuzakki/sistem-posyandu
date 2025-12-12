{{-- Modal Form Sasaran Pralansia --}}
@if($isSasaranPralansiaModalOpen)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closePralansiaModal()" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full max-h-[90vh] overflow-y-auto">
            <form wire:submit.prevent="storePralansia">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                        {{ $id_sasaran_pralansia ? 'Edit Data Sasaran Pralansia' : 'Tambah Sasaran Pralansia Baru' }}
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Nama Sasaran <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="nama_sasaran_pralansia" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Masukkan nama lengkap sasaran">
                            @error('nama_sasaran_pralansia') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">NIK Sasaran <span class="text-red-500">*</span></label>
                            <input type="number" wire:model="nik_sasaran_pralansia" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="NIK Sasaran">
                            @error('nik_sasaran_pralansia') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">No. KK</label>
                            <input type="number" wire:model="no_kk_sasaran_pralansia" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="No. KK (opsional)">
                            @error('no_kk_sasaran_pralansia') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Tempat Lahir</label>
                            <input type="text" wire:model="tempat_lahir_pralansia" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Contoh: Bandung">
                            @error('tempat_lahir_pralansia') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Lahir <span class="text-red-500">*</span></label>
                            <input type="date" wire:model="tanggal_lahir_pralansia" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                            @error('tanggal_lahir_pralansia') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                            <select wire:model="jenis_kelamin_pralansia" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                <option value="">Pilih Jenis Kelamin...</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                            @error('jenis_kelamin_pralansia') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Umur (tahun)</label>
                            <input type="number" wire:model="umur_sasaran_pralansia" readonly class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary bg-gray-100 cursor-not-allowed">
                            @error('umur_sasaran_pralansia') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Pekerjaan</label>
                            <select wire:model="pekerjaan_pralansia" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                <option value="">Pilih Pekerjaan...</option>
                                <option value="Belum/Tidak Bekerja">Belum/Tidak Bekerja</option>
                                <option value="Mengurus Rumah Tangga">Mengurus Rumah Tangga</option>
                                <option value="Pelajar/Mahasiswa">Pelajar/Mahasiswa</option>
                                <option value="Pensiunan">Pensiunan</option>
                                <option value="Pegawai Negeri Sipil">Pegawai Negeri Sipil</option>
                                <option value="Tentara Nasional Indonesia">Tentara Nasional Indonesia</option>
                                <option value="Kepolisian RI">Kepolisian RI</option>
                                <option value="Perdagangan">Perdagangan</option>
                                <option value="Petani/Pekebun">Petani/Pekebun</option>
                                <option value="Peternak">Peternak</option>
                                <option value="Nelayan/Perikanan">Nelayan/Perikanan</option>
                                <option value="Industri">Industri</option>
                                <option value="Konstruksi">Konstruksi</option>
                                <option value="Transportasi">Transportasi</option>
                                <option value="Karyawan Swasta">Karyawan Swasta</option>
                                <option value="Karyawan BUMN">Karyawan BUMN</option>
                                <option value="Karyawan BUMD">Karyawan BUMD</option>
                                <option value="Karyawan Honorer">Karyawan Honorer</option>
                                <option value="Buruh Harian Lepas">Buruh Harian Lepas</option>
                                <option value="Buruh Tani/Perkebunan">Buruh Tani/Perkebunan</option>
                                <option value="Buruh Nelayan/Perikanan">Buruh Nelayan/Perikanan</option>
                                <option value="Buruh Peternakan">Buruh Peternakan</option>
                                <option value="Pembantu Rumah Tangga">Pembantu Rumah Tangga</option>
                                <option value="Tukang Cukur">Tukang Cukur</option>
                                <option value="Tukang Listrik">Tukang Listrik</option>
                                <option value="Tukang Batu">Tukang Batu</option>
                                <option value="Tukang Kayu">Tukang Kayu</option>
                                <option value="Tukang Sol Sepatu">Tukang Sol Sepatu</option>
                                <option value="Tukang Las/Pandai Besi">Tukang Las/Pandai Besi</option>
                                <option value="Tukang Jahit">Tukang Jahit</option>
                                <option value="Tukang Gigi">Tukang Gigi</option>
                                <option value="Penata Rias">Penata Rias</option>
                                <option value="Penata Busana">Penata Busana</option>
                                <option value="Penata Rambut">Penata Rambut</option>
                                <option value="Mekanik">Mekanik</option>
                                <option value="Seniman">Seniman</option>
                                <option value="Tabib">Tabib</option>
                                <option value="Paraji">Paraji</option>
                                <option value="Perancang Busana">Perancang Busana</option>
                                <option value="Penterjemah">Penterjemah</option>
                                <option value="Imam Masjid">Imam Masjid</option>
                                <option value="Pendeta">Pendeta</option>
                                <option value="Pastor">Pastor</option>
                                <option value="Wartawan">Wartawan</option>
                                <option value="Ustadz/Mubaligh">Ustadz/Mubaligh</option>
                                <option value="Juru Masak">Juru Masak</option>
                                <option value="Promotor Acara">Promotor Acara</option>
                                <option value="Anggota DPR-RI">Anggota DPR-RI</option>
                                <option value="Anggota DPD">Anggota DPD</option>
                                <option value="Anggota BPK">Anggota BPK</option>
                                <option value="Presiden">Presiden</option>
                                <option value="Wakil Presiden">Wakil Presiden</option>
                                <option value="Anggota Mahkamah Konstitusi">Anggota Mahkamah Konstitusi</option>
                                <option value="Anggota Kabinet/Kementerian">Anggota Kabinet/Kementerian</option>
                                <option value="Duta Besar">Duta Besar</option>
                                <option value="Gubernur">Gubernur</option>
                                <option value="Wakil Gubernur">Wakil Gubernur</option>
                                <option value="Bupati">Bupati</option>
                                <option value="Wakil Bupati">Wakil Bupati</option>
                                <option value="Walikota">Walikota</option>
                                <option value="Wakil Walikota">Wakil Walikota</option>
                                <option value="Anggota DPRD Provinsi">Anggota DPRD Provinsi</option>
                                <option value="Anggota DPRD Kabupaten/Kota">Anggota DPRD Kabupaten/Kota</option>
                                <option value="Dosen">Dosen</option>
                                <option value="Guru">Guru</option>
                                <option value="Pilot">Pilot</option>
                                <option value="Pengacara">Pengacara</option>
                                <option value="Notaris">Notaris</option>
                                <option value="Arsitek">Arsitek</option>
                                <option value="Akuntan">Akuntan</option>
                                <option value="Konsultan">Konsultan</option>
                                <option value="Dokter">Dokter</option>
                                <option value="Bidan">Bidan</option>
                                <option value="Perawat">Perawat</option>
                                <option value="Apoteker">Apoteker</option>
                                <option value="Psikiater/Psikolog">Psikiater/Psikolog</option>
                                <option value="Penyiar Televisi">Penyiar Televisi</option>
                                <option value="Penyiar Radio">Penyiar Radio</option>
                                <option value="Pelaut">Pelaut</option>
                                <option value="Peneliti">Peneliti</option>
                                <option value="Sopir">Sopir</option>
                                <option value="Pialang">Pialang</option>
                                <option value="Paranormal">Paranormal</option>
                                <option value="Pedagang">Pedagang</option>
                                <option value="Perangkat Desa">Perangkat Desa</option>
                                <option value="Kepala Desa">Kepala Desa</option>
                                <option value="Biarawati">Biarawati</option>
                                <option value="Wiraswasta">Wiraswasta</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                            @error('pekerjaan_pralansia') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">NIK Orangtua</label>
                            <input type="number" wire:model="nik_orangtua_pralansia" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="NIK salah satu orangtua">
                            @error('nik_orangtua_pralansia') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Alamat Lengkap <span class="text-red-500">*</span></label>
                            <textarea wire:model="alamat_sasaran_pralansia" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Alamat lengkap sasaran"></textarea>
                            @error('alamat_sasaran_pralansia') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Kepersertaan BPJS</label>
                            <select wire:model="kepersertaan_bpjs_pralansia" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                <option value="">Pilih...</option>
                                <option value="PBI">PBI</option>
                                <option value="NON PBI">NON PBI</option>
                            </select>
                            @error('kepersertaan_bpjs_pralansia') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Nomor BPJS</label>
                            <input type="text" wire:model="nomor_bpjs_pralansia" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Nomor kartu BPJS">
                            @error('nomor_bpjs_pralansia') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Nomor Telepon</label>
                            <input type="text" wire:model="nomor_telepon_pralansia" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Nomor WA/Telp">
                            @error('nomor_telepon_pralansia') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Akun User (Opsional)</label>
                            <div class="relative" x-data="{
                                open: false,
                                searchText: '',
                                selectedId: @entangle('id_users_sasaran_pralansia'),
                                selectedName: ''
                            }" x-init="
                                $watch('selectedId', value => {
                                    if (value) {
                                        const user = @js($users->toArray()).find(u => u.id == value);
                                        if (user) {
                                            selectedName = user.name + ' (' + user.email + ')';
                                            searchText = selectedName;
                                        }
                                    } else {
                                        selectedName = '';
                                        searchText = '';
                                    }
                                });
                                if (selectedId) {
                                    const user = @js($users->toArray()).find(u => u.id == selectedId);
                                    if (user) {
                                        selectedName = user.name + ' (' + user.email + ')';
                                        searchText = selectedName;
                                    }
                                }
                            ">
                                <input
                                    type="text"
                                    x-model="searchText"
                                    @focus="open = true"
                                    @input="open = true; $wire.set('searchUser', searchText)"
                                    @keydown.escape="open = false"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary"
                                    placeholder="Ketik untuk mencari user..."
                                    autocomplete="off">
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
                                        <li @click="selectedId = ''; searchText = ''; open = false; $wire.set('id_users_sasaran_pralansia', '')"
                                            class="px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 cursor-pointer">
                                            -- Pilih User --
                                        </li>
                                        @if($users->count() > 0)
                                            @foreach($users as $user)
                                                <li @click="selectedId = '{{ $user->id }}'; selectedName = '{{ $user->name }} ({{ $user->email }})'; searchText = selectedName; open = false; $wire.set('id_users_sasaran_pralansia', '{{ $user->id }}')"
                                                    x-show="!searchText || '{{ strtolower($user->name . ' (' . $user->email . ')') }}'.includes(searchText.toLowerCase())"
                                                    class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer"
                                                    :class="selectedId == '{{ $user->id }}' ? 'bg-blue-50 font-medium' : ''">
                                                    {{ $user->name }} ({{ $user->email }})
                                                </li>
                                            @endforeach
                                        @else
                                            <li class="px-4 py-2 text-sm text-gray-500">Tidak ada user ditemukan</li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                            @error('id_users_sasaran_pralansia') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Simpan
                    </button>
                    <button wire:click="closePralansiaModal()" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

