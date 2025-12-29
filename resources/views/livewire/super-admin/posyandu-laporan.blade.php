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

            {{-- Card Export Semua Imunisasi --}}
            <div class="mb-6">
                <div class="flex items-center gap-2 mb-3">
                    <i class="ph ph-file-pdf text-lg text-primary"></i>
                    <h3 class="text-base font-semibold text-gray-800">Export Semua Data</h3>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('superadmin.posyandu.laporan.pdf', encrypt($posyandu->id_posyandu)) }}"
                       target="_blank"
                       class="inline-flex items-center px-4 py-2 rounded-lg bg-primary text-white text-sm font-medium shadow-sm hover:bg-indigo-700 transition-colors">
                        <i class="ph ph-file-pdf text-lg mr-2"></i>
                        Export Semua Data Imunisasi
                    </a>
                </div>
            </div>

            {{-- Card Export berdasarkan Kategori Sasaran --}}
            @if(!empty($kategoriSasaranList))
            <div class="mb-6">
                <div class="flex items-center gap-2 mb-3">
                    <i class="ph ph-users text-lg text-primary"></i>
                    <h3 class="text-base font-semibold text-gray-800">Export berdasarkan Kategori Sasaran</h3>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach($kategoriSasaranList as $kategori)
                        <a href="{{ route('superadmin.posyandu.laporan.pdf.kategori', ['id' => encrypt($posyandu->id_posyandu), 'kategori' => $kategori]) }}"
                           target="_blank"
                           class="inline-flex items-center px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium shadow-sm hover:bg-gray-200 transition-colors border border-gray-300">
                            <i class="ph ph-file-pdf text-lg mr-2"></i>
                            {{ $kategoriLabels[$kategori] ?? ucfirst($kategori) }}
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Card Export berdasarkan Jenis Vaksin --}}
            @if(!empty($jenisVaksinList))
            <div class="mb-6">
                <div class="flex items-center gap-2 mb-3">
                    <i class="ph ph-syringe text-lg text-blue-600"></i>
                    <h3 class="text-base font-semibold text-gray-800">Export berdasarkan Jenis Vaksin</h3>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach($jenisVaksinList as $jenisVaksin)
                        <a href="{{ route('superadmin.posyandu.laporan.pdf.jenis-vaksin', ['id' => encrypt($posyandu->id_posyandu), 'jenisVaksin' => urlencode($jenisVaksin)]) }}"
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
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <i class="ph ph-user text-lg text-green-600"></i>
                    <h3 class="text-base font-semibold text-gray-800">Export berdasarkan Nama Sasaran</h3>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach($namaSasaranList as $nama)
                        <a href="{{ route('superadmin.posyandu.laporan.pdf.nama', ['id' => encrypt($posyandu->id_posyandu), 'nama' => urlencode($nama)]) }}"
                           target="_blank"
                           class="inline-flex items-center px-4 py-2 rounded-lg bg-green-100 text-green-700 text-sm font-medium shadow-sm hover:bg-green-200 transition-colors border border-green-300">
                            <i class="ph ph-file-pdf text-lg mr-2"></i>
                            {{ $nama }}
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Grup Laporan Pendidikan --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-200">
                <i class="ph ph-graduation-cap text-2xl text-purple-600"></i>
                <h2 class="text-xl font-semibold text-gray-800">Laporan Pendidikan</h2>
            </div>

            {{-- Card Export Semua Pendidikan --}}
            <div class="mb-6">
                <div class="flex items-center gap-2 mb-3">
                    <i class="ph ph-file-pdf text-lg text-primary"></i>
                    <h3 class="text-base font-semibold text-gray-800">Export Semua Data</h3>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('superadmin.posyandu.pendidikan.pdf', encrypt($posyandu->id_posyandu)) }}"
                       target="_blank"
                       class="inline-flex items-center px-4 py-2 rounded-lg bg-purple-600 text-white text-sm font-medium shadow-sm hover:bg-purple-700 transition-colors">
                        <i class="ph ph-file-pdf text-lg mr-2"></i>
                        Export Semua Data Pendidikan
                    </a>
                </div>
            </div>

            {{-- Card Export berdasarkan Kategori Pendidikan --}}
            @if(!empty($kategoriPendidikanList))
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <i class="ph ph-graduation-cap text-lg text-purple-600"></i>
                    <h3 class="text-base font-semibold text-gray-800">Export berdasarkan Kategori Pendidikan</h3>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach($kategoriPendidikanList as $kategoriPendidikan)
                        <a href="{{ route('superadmin.posyandu.pendidikan.pdf.kategori', ['id' => encrypt($posyandu->id_posyandu), 'kategori' => urlencode($kategoriPendidikan)]) }}"
                           target="_blank"
                           class="inline-flex items-center px-4 py-2 rounded-lg bg-purple-100 text-purple-700 text-sm font-medium shadow-sm hover:bg-purple-200 transition-colors border border-purple-300">
                            <i class="ph ph-file-pdf text-lg mr-2"></i>
                            {{ $kategoriPendidikan }}
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Pesan Sukses --}}
        @include('livewire.super-admin.posyandu-detail.message-alert')
    </div>
</div>

{{-- Scripts --}}
@include('livewire.super-admin.posyandu-detail.scripts')


