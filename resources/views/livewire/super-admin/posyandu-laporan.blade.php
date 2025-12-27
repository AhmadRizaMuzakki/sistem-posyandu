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
                        Super Admin dapat mencetak laporan kegiatan imunisasi Posyandu ini dalam bentuk PDF.
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
                    Laporan PDF akan berisi daftar lengkap data imunisasi pada Posyandu ini,
                    termasuk tanggal, jenis imunisasi, kategori sasaran, nama sasaran, tinggi/berat badan, petugas, dan keterangan.
                </p>
            </div>
        </div>

        {{-- Card Export berdasarkan Kategori Sasaran --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-2 mb-4">
                <i class="ph ph-users text-xl text-primary"></i>
                <h3 class="text-lg font-semibold text-gray-800">Export berdasarkan Kategori Sasaran</h3>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('superadmin.posyandu.laporan.pdf', $posyandu->id_posyandu) }}"
                   target="_blank"
                   class="inline-flex items-center px-4 py-2 rounded-lg bg-primary text-white text-sm font-medium shadow-sm hover:bg-indigo-700 transition-colors">
                    <i class="ph ph-file-pdf text-lg mr-2"></i>
                    Semua Kategori
                </a>
                @foreach($kategoriSasaranList as $kategori)
                    <a href="{{ route('superadmin.posyandu.laporan.pdf.kategori', ['id' => $posyandu->id_posyandu, 'kategori' => $kategori]) }}"
                       target="_blank"
                       class="inline-flex items-center px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium shadow-sm hover:bg-gray-200 transition-colors border border-gray-300">
                        <i class="ph ph-file-pdf text-lg mr-2"></i>
                        {{ $kategoriLabels[$kategori] ?? ucfirst($kategori) }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Card Export berdasarkan Jenis Vaksin --}}
        @if(!empty($jenisVaksinList))
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-2 mb-4">
                <i class="ph ph-syringe text-xl text-blue-600"></i>
                <h3 class="text-lg font-semibold text-gray-800">Export berdasarkan Jenis Vaksin</h3>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach($jenisVaksinList as $jenisVaksin)
                    <a href="{{ route('superadmin.posyandu.laporan.pdf.jenis-vaksin', ['id' => $posyandu->id_posyandu, 'jenisVaksin' => urlencode($jenisVaksin)]) }}"
                       target="_blank"
                       class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-100 text-blue-700 text-sm font-medium shadow-sm hover:bg-blue-200 transition-colors border border-blue-300">
                        <i class="ph ph-file-pdf text-lg mr-2"></i>
                        {{ $jenisVaksin }}
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Card Export berdasarkan Nama Sasaran --}}
        @if(!empty($namaSasaranList))
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-2 mb-4">
                <i class="ph ph-user text-xl text-green-600"></i>
                <h3 class="text-lg font-semibold text-gray-800">Export berdasarkan Nama Sasaran</h3>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach($namaSasaranList as $nama)
                    <a href="{{ route('superadmin.posyandu.laporan.pdf.nama', ['id' => $posyandu->id_posyandu, 'nama' => urlencode($nama)]) }}"
                       target="_blank"
                       class="inline-flex items-center px-4 py-2 rounded-lg bg-green-100 text-green-700 text-sm font-medium shadow-sm hover:bg-green-200 transition-colors border border-green-300">
                        <i class="ph ph-file-pdf text-lg mr-2"></i>
                        {{ $nama }}
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Pesan Sukses --}}
        @include('livewire.super-admin.posyandu-detail.message-alert')
    </div>
</div>

{{-- Scripts --}}
@include('livewire.super-admin.posyandu-detail.scripts')


