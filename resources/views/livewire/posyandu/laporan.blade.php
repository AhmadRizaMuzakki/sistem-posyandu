<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 flex items-center gap-2">
                <i class="ph ph-chart-bar text-2xl text-primary"></i>
                Laporan Posyandu
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Cetak laporan kegiatan imunisasi untuk Posyandu Anda dalam bentuk PDF.
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
                    {{ now()->format('d F Y') }}
                </p>
            </div>
        </div>

        <div class="pt-4 border-t border-dashed border-gray-200 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <p class="text-sm text-gray-500 max-w-xl">
                Laporan PDF akan berisi daftar lengkap data imunisasi pada Posyandu ini,
                termasuk tanggal, jenis imunisasi, kategori sasaran, nama sasaran, dan petugas.
            </p>

            <a href="{{ route('adminPosyandu.laporan.pdf') }}"
               target="_blank"
               class="inline-flex items-center px-5 py-2.5 rounded-lg bg-primary text-white text-sm font-semibold shadow-sm hover:bg-indigo-700 transition-colors">
                <i class="ph ph-file-pdf text-lg mr-2"></i>
                Download Laporan PDF
            </a>
        </div>
    </div>
</div>


