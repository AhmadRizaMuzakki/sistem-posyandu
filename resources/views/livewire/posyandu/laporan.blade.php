<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 flex items-center gap-2">
                <i class="ph ph-chart-bar text-2xl text-primary"></i>
                Laporan Posyandu
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Cetak laporan kegiatan imunisasi dan pendidikan Posyandu Anda dalam bentuk PDF.
            </p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
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
                {{-- Filter Kategori Sasaran --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Filter berdasarkan Kategori Sasaran</label>
                    <select id="filterKategori" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoriSasaranList as $kategori)
                            <option value="{{ route('adminPosyandu.laporan.pdf.kategori', $kategori) }}">{{ $kategoriLabels[$kategori] ?? ucfirst($kategori) }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Filter Jenis Vaksin --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Filter berdasarkan Jenis Vaksin</label>
                    <select id="filterJenisVaksin" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                        <option value="">Semua Jenis Vaksin</option>
                        @foreach($jenisVaksinList as $jenisVaksin)
                            <option value="{{ route('adminPosyandu.laporan.pdf.jenis-vaksin', urlencode($jenisVaksin)) }}">{{ $jenisVaksin }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Filter Nama Sasaran --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Filter berdasarkan Nama Sasaran</label>
                    <select id="filterNamaSasaran" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                        <option value="">Semua Nama Sasaran</option>
                        @foreach($namaSasaranList as $nama)
                            <option value="{{ route('adminPosyandu.laporan.pdf.nama', urlencode($nama)) }}">{{ $nama }}</option>
                        @endforeach
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
                {{-- Filter Pendidikan --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Filter berdasarkan Pendidikan</label>
                    <select id="filterPendidikan" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                        <option value="">Semua Pendidikan</option>
                        @foreach($kategoriPendidikanList as $pendidikan)
                            <option value="{{ route('adminPosyandu.pendidikan.pdf.kategori', urlencode($pendidikan)) }}">{{ $pendidikan }}</option>
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
    </div>

    <script>
        function exportFilteredImunisasi() {
            const kategori = document.getElementById('filterKategori').value;
            const jenisVaksin = document.getElementById('filterJenisVaksin').value;
            const namaSasaran = document.getElementById('filterNamaSasaran').value;
            
            if (kategori) {
                window.open(kategori, '_blank');
            } else if (jenisVaksin) {
                window.open(jenisVaksin, '_blank');
            } else if (namaSasaran) {
                window.open(namaSasaran, '_blank');
            } else {
                alert('Pilih salah satu filter terlebih dahulu');
            }
        }

        function exportFilteredPendidikan() {
            const pendidikan = document.getElementById('filterPendidikan').value;
            
            if (pendidikan) {
                window.open(pendidikan, '_blank');
            } else {
                alert('Pilih filter pendidikan terlebih dahulu');
            }
        }
    </script>
</div>


