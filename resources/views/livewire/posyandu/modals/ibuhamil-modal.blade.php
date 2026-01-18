{{-- Modal Form Sasaran Ibu Hamil --}}
@if($isSasaranIbuHamilModalOpen)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeIbuHamilModal()" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full max-h-[90vh] overflow-y-auto">
            <form wire:submit.prevent="storeIbuHamil">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                        {{ $id_sasaran_ibuhamil ? 'Edit Data Sasaran Ibu Hamil' : 'Tambah Sasaran Ibu Hamil Baru' }}
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Nama Sasaran <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="nama_sasaran_ibuhamil" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Masukkan nama lengkap sasaran">
                            @error('nama_sasaran_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">NIK Sasaran <span class="text-red-500">*</span></label>
                            <div class="relative" x-data="{
                                open: false,
                                searchText: '',
                                selectedNik: @entangle('nik_sasaran_ibuhamil'),
                                nikList: @js($this->getNikListIbuHamil())
                            }" x-init="
                                $watch('selectedNik', value => {
                                    if (value) {
                                        const item = nikList.find(item => item.nik == value);
                                        if (item) {
                                            searchText = item.nik + ' - ' + item.nama + ' (' + item.kategori + ')';
                                            // Auto-fill data ketika NIK dipilih
                                            setTimeout(() => {
                                                $wire.call('loadDataIbuHamilByNik', value);
                                            }, 150);
                                        } else {
                                            searchText = value;
                                        }
                                    } else {
                                        searchText = '';
                                    }
                                });
                                
                                // Inisialisasi saat pertama kali load
                                if (selectedNik) {
                                    const item = nikList.find(item => item.nik == selectedNik);
                                    if (item) {
                                        searchText = item.nik + ' - ' + item.nama + ' (' + item.kategori + ')';
                                    } else {
                                        searchText = selectedNik;
                                    }
                                }
                            ">
                                <input
                                    type="text"
                                    x-model="searchText"
                                    @focus="open = true"
                                    @input="
                                        open = true;
                                        // Jika user mengetik manual, ekstrak hanya angka (NIK)
                                        const match = searchText.match(/^\d+/);
                                        if (match && !nikList.find(item => item.nik == match[0])) {
                                            selectedNik = match[0];
                                            $wire.set('nik_sasaran_ibuhamil', match[0]);
                                        }
                                    "
                                    @keydown.enter.prevent="
                                        const match = searchText.match(/^\d+/);
                                        if (match) {
                                            selectedNik = match[0];
                                            $wire.set('nik_sasaran_ibuhamil', match[0]);
                                            open = false;
                                        }
                                    "
                                    @keydown.escape="open = false"
                                    @blur="setTimeout(() => {
                                        open = false;
                                        // Pastikan nilai yang tersimpan hanya NIK
                                        if (selectedNik && searchText) {
                                            const match = searchText.match(/^\d+/);
                                            if (match && match[0] != selectedNik) {
                                                selectedNik = match[0];
                                                $wire.set('nik_sasaran_ibuhamil', match[0]);
                                            }
                                        }
                                    }, 200)"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary"
                                    placeholder="Ketik untuk mencari NIK atau pilih dari dropdown..."
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
                                        <li @click="selectedNik = ''; searchText = ''; open = false; $wire.set('nik_sasaran_ibuhamil', '')"
                                            class="px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 cursor-pointer">
                                            -- Pilih NIK --
                                        </li>
                                        @if(count($this->getNikListIbuHamil()) > 0)
                                            @foreach($this->getNikListIbuHamil() as $item)
                                                <li @click="
                                                    const nik = '{{ $item['nik'] }}';
                                                    selectedNik = nik;
                                                    searchText = '{{ $item['nik'] }} - {{ $item['nama'] }} ({{ $item['kategori'] }})';
                                                    open = false;
                                                    $wire.set('nik_sasaran_ibuhamil', nik);
                                                    // Panggil method untuk auto-fill data
                                                    setTimeout(() => {
                                                        $wire.call('loadDataIbuHamilByNik', nik);
                                                    }, 200);
                                                "
                                                    x-show="!searchText || '{{ strtolower($item['nik'] . ' ' . $item['nama'] . ' ' . $item['kategori']) }}'.includes(searchText.toLowerCase())"
                                                    class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer"
                                                    :class="selectedNik == '{{ $item['nik'] }}' ? 'bg-blue-50 font-medium' : ''">
                                                    <div class="font-medium">{{ $item['nik'] }} - {{ $item['nama'] }}</div>
                                                    <div class="text-xs text-gray-500">{{ $item['kategori'] }} | {{ $item['jenis_kelamin'] }} | {{ $item['status_keluarga'] ? ucfirst($item['status_keluarga']) : '-' }}</div>
                                                </li>
                                            @endforeach
                                        @else
                                            <li class="px-4 py-2 text-sm text-gray-500">
                                                Tidak ada data NIK tersedia
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                            @error('nik_sasaran_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                            <p class="text-xs text-gray-500 mt-1">Pilih NIK dari sasaran dewasa/pralansia/lansia untuk auto-fill data</p>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">No. KK</label>
                            <input type="number" wire:model="no_kk_sasaran_ibuhamil" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="No. KK (opsional)">
                            @error('no_kk_sasaran_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Tempat Lahir</label>
                            <input type="text" wire:model="tempat_lahir_ibuhamil" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Contoh: Bandung">
                            @error('tempat_lahir_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Lahir <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <select wire:model="hari_lahir_ibuhamil" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                        <option value="">Hari</option>
                                        @for($i = 1; $i <= 31; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                    @error('hari_lahir_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <select wire:model="bulan_lahir_ibuhamil" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
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
                                    @error('bulan_lahir_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <input type="number" wire:model="tahun_lahir_ibuhamil" min="1900" max="{{ date('Y') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Tahun">
                                    @error('tahun_lahir_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            @error('tanggal_lahir_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                            <select wire:model="jenis_kelamin_ibuhamil" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                <option value="">Pilih Jenis Kelamin...</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                            @error('jenis_kelamin_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Status Keluarga</label>
                            <select wire:model="status_keluarga_ibuhamil" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                <option value="">Pilih Status Keluarga...</option>
                                <option value="kepala keluarga">Kepala Keluarga</option>
                                <option value="istri">Istri</option>
                                <option value="anak">Anak</option>
                            </select>
                            @error('status_keluarga_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Umur (tahun)</label>
                            <input type="number" wire:model="umur_sasaran_ibuhamil" readonly class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary bg-gray-100 cursor-not-allowed">
                            @error('umur_sasaran_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Minggu Kandungan</label>
                            <input type="number" wire:model="minggu_kandungan_ibuhamil" min="1" max="40" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Masukkan minggu kandungan (1-40)">
                            @error('minggu_kandungan_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Pekerjaan</label>
                            <select wire:model="pekerjaan_ibuhamil" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
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
                            @error('pekerjaan_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Pendidikan</label>
                            <select wire:model="pendidikan_ibuhamil" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                <option value="">Pilih Pendidikan...</option>
                                <option value="Tidak/Belum Sekolah">Tidak/Belum Sekolah</option>
                                <option value="PAUD">PAUD</option>
                                <option value="TK">TK</option>
                                <option value="Tidak Tamat SD/Sederajat">Tidak Tamat SD/Sederajat</option>
                                <option value="Tamat SD/Sederajat">Tamat SD/Sederajat</option>
                                <option value="SLTP/Sederajat">SLTP/Sederajat</option>
                                <option value="SLTA/Sederajat">SLTA/Sederajat</option>
                                <option value="Diploma I/II">Diploma I/II</option>
                                <option value="Akademi/Diploma III/Sarjana Muda">Akademi/Diploma III/Sarjana Muda</option>
                                <option value="Diploma IV/Strata I">Diploma IV/Strata I</option>
                                <option value="Strata II">Strata II</option>
                                <option value="Strata III">Strata III</option>
                            </select>
                            @error('pendidikan_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Alamat Lengkap <span class="text-red-500">*</span></label>
                            <textarea wire:model="alamat_sasaran_ibuhamil" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Alamat lengkap sasaran"></textarea>
                            @error('alamat_sasaran_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">RT</label>
                            <input type="text" wire:model="rt_ibuhamil" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="RT">
                            @error('rt_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">RW</label>
                            <input type="text" wire:model="rw_ibuhamil" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="RW">
                            @error('rw_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Kepersertaan BPJS</label>
                            <select wire:model="kepersertaan_bpjs_ibuhamil" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                <option value="">Pilih...</option>
                                <option value="PBI">PBI</option>
                                <option value="NON PBI">NON PBI</option>
                            </select>
                            @error('kepersertaan_bpjs_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Nomor BPJS</label>
                            <input type="text" wire:model="nomor_bpjs_ibuhamil" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Nomor kartu BPJS">
                            @error('nomor_bpjs_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Nomor Telepon</label>
                            <input type="text" wire:model="nomor_telepon_ibuhamil" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Nomor WA/Telp">
                            @error('nomor_telepon_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    {{-- Bagian Biodata Suami --}}
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h4 class="text-md font-semibold text-gray-800 mb-4">Biodata Suami</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Nama Suami</label>
                                <input type="text" wire:model="nama_suami_ibuhamil" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Masukkan nama lengkap suami">
                                @error('nama_suami_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">NIK Suami</label>
                                <div class="relative" x-data="{
                                    open: false,
                                    searchText: '',
                                    selectedNik: @entangle('nik_suami_ibuhamil'),
                                    nikList: @js($this->getNikListSuami())
                                }" x-init="
                                    $watch('selectedNik', value => {
                                        if (value) {
                                            const item = nikList.find(item => item.nik == value);
                                            if (item) {
                                                searchText = item.nik + ' - ' + item.nama + ' (' + item.kategori + ')';
                                                // Auto-fill data suami ketika NIK dipilih
                                                setTimeout(() => {
                                                    $wire.call('loadDataSuamiByNik', value);
                                                }, 150);
                                            } else {
                                                searchText = value;
                                            }
                                        } else {
                                            searchText = '';
                                        }
                                    });
                                    
                                    // Inisialisasi saat pertama kali load
                                    if (selectedNik) {
                                        const item = nikList.find(item => item.nik == selectedNik);
                                        if (item) {
                                            searchText = item.nik + ' - ' + item.nama + ' (' + item.kategori + ')';
                                        } else {
                                            searchText = selectedNik;
                                        }
                                    }
                                ">
                                    <input
                                        type="text"
                                        x-model="searchText"
                                        @focus="open = true"
                                        @input="
                                            open = true;
                                            // Jika user mengetik manual, ekstrak hanya angka (NIK)
                                            const match = searchText.match(/^\d+/);
                                            if (match && !nikList.find(item => item.nik == match[0])) {
                                                selectedNik = match[0];
                                                $wire.set('nik_suami_ibuhamil', match[0]);
                                            }
                                        "
                                        @keydown.enter.prevent="
                                            const match = searchText.match(/^\d+/);
                                            if (match) {
                                                selectedNik = match[0];
                                                $wire.set('nik_suami_ibuhamil', match[0]);
                                                open = false;
                                            }
                                        "
                                        @keydown.escape="open = false"
                                        @blur="setTimeout(() => {
                                            open = false;
                                            // Pastikan nilai yang tersimpan hanya NIK
                                            if (selectedNik && searchText) {
                                                const match = searchText.match(/^\d+/);
                                                if (match && match[0] != selectedNik) {
                                                    selectedNik = match[0];
                                                    $wire.set('nik_suami_ibuhamil', match[0]);
                                                }
                                            }
                                        }, 200)"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary"
                                        placeholder="Ketik untuk mencari NIK atau pilih dari dropdown..."
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
                                            <li @click="selectedNik = ''; searchText = ''; open = false; $wire.set('nik_suami_ibuhamil', '')"
                                                class="px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 cursor-pointer">
                                                -- Pilih NIK Suami --
                                            </li>
                                            @if(count($this->getNikListSuami()) > 0)
                                                @foreach($this->getNikListSuami() as $item)
                                                    <li @click="
                                                        const nik = '{{ $item['nik'] }}';
                                                        selectedNik = nik;
                                                        searchText = '{{ $item['nik'] }} - {{ $item['nama'] }} ({{ $item['kategori'] }})';
                                                        open = false;
                                                        $wire.set('nik_suami_ibuhamil', nik);
                                                        // Panggil method untuk auto-fill data suami
                                                        setTimeout(() => {
                                                            $wire.call('loadDataSuamiByNik', nik);
                                                        }, 200);
                                                    "
                                                        x-show="!searchText || '{{ strtolower($item['nik'] . ' ' . $item['nama'] . ' ' . $item['kategori']) }}'.includes(searchText.toLowerCase())"
                                                        class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer"
                                                        :class="selectedNik == '{{ $item['nik'] }}' ? 'bg-blue-50 font-medium' : ''">
                                                        <div class="font-medium">{{ $item['nik'] }} - {{ $item['nama'] }}</div>
                                                        <div class="text-xs text-gray-500">{{ $item['kategori'] }} | {{ $item['jenis_kelamin'] }} | {{ $item['status_keluarga'] ? ucfirst($item['status_keluarga']) : '-' }}</div>
                                                    </li>
                                                @endforeach
                                            @else
                                                <li class="px-4 py-2 text-sm text-gray-500">
                                                    Tidak ada data NIK tersedia
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                                @error('nik_suami_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                <p class="text-xs text-gray-500 mt-1">Pilih NIK dari sasaran dewasa/pralansia/lansia untuk auto-fill biodata suami</p>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Tempat Lahir Suami</label>
                                <input type="text" wire:model="tempat_lahir_suami_ibuhamil" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Contoh: Bandung">
                                @error('tempat_lahir_suami_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Lahir Suami</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <select wire:model="hari_lahir_suami_ibuhamil" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                            <option value="">Hari</option>
                                            @for($i = 1; $i <= 31; $i++)
                                                <option value="{{ $i }}">{{ $i }}</option>
                                            @endfor
                                        </select>
                                        @error('hari_lahir_suami_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                    </div>
                                    <div>
                                        <select wire:model="bulan_lahir_suami_ibuhamil" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
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
                                        @error('bulan_lahir_suami_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                    </div>
                                    <div>
                                        <input type="number" wire:model="tahun_lahir_suami_ibuhamil" min="1900" max="{{ date('Y') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Tahun">
                                        @error('tahun_lahir_suami_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                @error('tanggal_lahir_suami_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Pekerjaan Suami</label>
                                <select wire:model="pekerjaan_suami_ibuhamil" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
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
                                @error('pekerjaan_suami_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Status Keluarga Suami</label>
                                <select wire:model="status_keluarga_suami_ibuhamil" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                    <option value="">Pilih Status Keluarga...</option>
                                    <option value="kepala keluarga">Kepala Keluarga</option>
                                    <option value="istri">Istri</option>
                                    <option value="anak">Anak</option>
                                </select>
                                @error('status_keluarga_suami_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Simpan
                    </button>
                    <button wire:click="closeIbuHamilModal()" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

