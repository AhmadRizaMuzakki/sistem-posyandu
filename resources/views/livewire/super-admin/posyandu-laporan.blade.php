<div>
    <div class="space-y-6">
        {{-- Header --}}
        @include('livewire.super-admin.posyandu-detail.header')

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4" id="laporan">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800 flex items-center gap-2">
                        <i class="ph ph-chart-bar text-2xl text-primary"></i>
                        Laporan Posyandu
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Super Admin dapat mencetak laporan kegiatan imunisasi dan pendidikan Posyandu ini dalam bentuk PDF.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mt-4">
                <div>
                    <p class="text-gray-500">Nama Posyandu</p>
                    <p class="font-semibold text-gray-800">{{ $posyandu->nama_posyandu }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Alamat Posyandu</p>
                    <p class="font-medium text-gray-800">
                        {{ $posyandu->alamat_posyandu ?? '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-500">Tanggal Cetak</p>
                    <p class="font-medium text-gray-800">
                        {{ \Carbon\Carbon::now('Asia/Jakarta')->format('d F Y') }}
                    </p>
                </div>
            </div>

            <div class="pt-4 border-t border-dashed border-gray-200">
                <p class="text-sm text-gray-500 mb-6">
                    Laporan PDF akan berisi daftar lengkap data imunisasi dan pendidikan pada Posyandu ini.
                </p>
            </div>
        </div>

        {{-- Grup Laporan Imunisasi --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-200">
                <i class="ph ph-syringe text-2xl text-blue-600"></i>
                <h2 class="text-xl font-semibold text-gray-800">Laporan Imunisasi</h2>
            </div>

            {{-- Card Export dengan Dropdown Filter Imunisasi --}}
            <div class="mb-6">
                <div class="flex items-center gap-2 mb-3">
                    <i class="ph ph-file-pdf text-lg text-primary"></i>
                    <h3 class="text-base font-semibold text-gray-800">Export dengan Filter</h3>
                </div>
                <div class="space-y-4">
                    {{-- Filter Tahun & Bulan --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Filter Tahun</label>
                            <select id="filterTahunImunisasi" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                                <option value="">Semua Tahun</option>
                                @foreach(range(now()->year, now()->year - 5) as $y)
                                    <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Filter Bulan</label>
                            <select id="filterBulanImunisasi" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                                <option value="">Semua Bulan</option>
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>{{ \Carbon\Carbon::create(now()->year, $m, 1)->locale('id')->translatedFormat('F') }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    {{-- Filter Kategori Sasaran --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filter berdasarkan Kategori Sasaran</label>
                        <select id="filterKategori" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                            <x-laporan-kategori-sasaran-options :kategori-list="$kategoriSasaranList" :kategori-labels="$kategoriLabels" />
                        </select>
                    </div>
                    {{-- Filter Jenis Vaksin --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filter berdasarkan Jenis Vaksin</label>
                        <select id="filterJenisVaksin" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                            <option value="">Semua Jenis Vaksin</option>
                            @foreach($jenisVaksinList as $jenisVaksin)
                                <option value="{{ $jenisVaksin }}">{{ $jenisVaksin }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Filter Nama Sasaran --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filter berdasarkan Nama Sasaran</label>
                        <select id="filterNamaSasaran" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                            <option value="">Semua Nama Sasaran</option>
                            @foreach($namaSasaranList as $nama)
                                <option value="{{ $nama }}">{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Filter Kehadiran --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filter berdasarkan Kehadiran Imunisasi</label>
                        <select id="filterKehadiranImunisasi" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                            <option value="">Semua (Hadir &amp; Tidak Hadir)</option>
                            <option value="hadir">Hadir</option>
                            <option value="tidak_hadir">Tidak Hadir</option>
                        </select>
                    </div>
                    <div>
                        <button onclick="exportFilteredImunisasi()" class="w-full inline-flex items-center justify-center px-4 py-2 rounded-lg bg-primary text-white text-sm font-medium shadow-sm hover:bg-indigo-700 transition-colors">
                            <i class="ph ph-file-pdf text-lg mr-2"></i>
                            Export dengan Filter
                        </button>
                    </div>
                </div>
            </div>


        </div>

        {{-- Grup Laporan Pendidikan --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-200">
                <i class="ph ph-graduation-cap text-2xl text-purple-600"></i>
                <h2 class="text-xl font-semibold text-gray-800">Laporan Pendidikan</h2>
            </div>

            {{-- Card Export dengan Dropdown Filter Pendidikan --}}
            <div class="mb-6">
                <div class="flex items-center gap-2 mb-3">
                    <i class="ph ph-file-pdf text-lg text-primary"></i>
                    <h3 class="text-base font-semibold text-gray-800">Export dengan Filter</h3>
                </div>
                <div class="space-y-4">
                    {{-- Filter Kategori Sasaran --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filter berdasarkan Kategori Sasaran</label>
                        <select id="filterKategoriPendidikan" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                            <x-laporan-kategori-sasaran-options :kategori-list="$kategoriSasaranPendidikanList ?? []" :kategori-labels="$kategoriLabels" />
                        </select>
                    </div>
                    {{-- Filter Pendidikan --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filter berdasarkan Pendidikan</label>
                        <select id="filterPendidikan" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                            <option value="">Semua Pendidikan</option>
                            @foreach($kategoriPendidikanList as $pendidikan)
                                <option value="{{ $pendidikan }}">{{ $pendidikan }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Filter Nama Sasaran --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filter berdasarkan Nama Sasaran</label>
                        <select id="filterNamaSasaranPendidikan" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                            <option value="">Semua Nama Sasaran</option>
                            @foreach($namaSasaranPendidikanList ?? [] as $nama)
                                <option value="{{ $nama }}">{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button onclick="exportFilteredPendidikan()" class="w-full inline-flex items-center justify-center px-4 py-2 rounded-lg bg-purple-600 text-white text-sm font-medium shadow-sm hover:bg-purple-700 transition-colors">
                            <i class="ph ph-file-pdf text-lg mr-2"></i>
                            Export dengan Filter
                        </button>
                    </div>
                </div>
            </div>


            {{-- Card Export berdasarkan Kategori Pendidikan --}}
           
        </div>

        {{-- Pesan Sukses --}}
        @include('livewire.super-admin.posyandu-detail.message-alert')
    </div>
</div>

{{-- Scripts --}}
@include('livewire.super-admin.posyandu-detail.scripts')

<script>
    function exportFilteredImunisasi() {
        const tahun = document.getElementById('filterTahunImunisasi').value;
        const bulan = document.getElementById('filterBulanImunisasi').value;
        if (!tahun || !bulan) {
            window.dispatchEvent(new CustomEvent('show-alert', {
                detail: {
                    message: 'Tahun dan bulan wajib dipilih untuk laporan kehadiran imunisasi.',
                    type: 'warning'
                }
            }));
            return;
        }
        const url = '{{ route("superadmin.posyandu.laporan.pdf.imunisasi-kehadiran", ["id" => encrypt($posyandu->id_posyandu)]) }}';
        const params = new URLSearchParams();
        params.append('tahun', tahun);
        params.append('bulan', bulan);
        const kategori = document.getElementById('filterKategori').value;
        const jenisVaksin = document.getElementById('filterJenisVaksin').value;
        const namaSasaran = document.getElementById('filterNamaSasaran').value;
        const kehadiran = document.getElementById('filterKehadiranImunisasi').value;
        if (kategori) params.append('kategori', kategori);
        if (jenisVaksin) params.append('jenis_vaksin', jenisVaksin);
        if (namaSasaran) params.append('nama_sasaran', namaSasaran);
        if (kehadiran) params.append('kehadiran', kehadiran);
        window.open(url + '?' + params.toString(), '_blank');
    }

    function exportFilteredPendidikan() {
        const filterSasaran = document.getElementById('filterKategoriPendidikan').value;
        const pendidikan = document.getElementById('filterPendidikan').value;
        const namaSasaran = document.getElementById('filterNamaSasaranPendidikan').value;
        const url = '{{ route("superadmin.posyandu.pendidikan.pdf", ["id" => encrypt($posyandu->id_posyandu)]) }}';
        const params = new URLSearchParams();
        if (filterSasaran) params.append('filter_sasaran', filterSasaran);
        if (pendidikan) params.append('pendidikan', pendidikan);
        if (namaSasaran) params.append('nama', namaSasaran);
        window.open(url + (params.toString() ? '?' + params.toString() : ''), '_blank');
    }
</script>


