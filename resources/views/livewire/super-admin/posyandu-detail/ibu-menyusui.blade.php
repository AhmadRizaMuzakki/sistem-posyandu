<div>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">{{ $posyandu->nama_posyandu }}</h1>
                    <p class="text-gray-500 mt-1">Absensi Kehadiran</p>
                </div>
            </div>
        </div>

        {{-- Daftar Absensi (Kunjungan Balita ke Posyandu) --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="ph ph-baby text-2xl mr-3 text-primary"></i>
                    Absensi Kehadiran
                </h2>
                <div class="flex gap-2">
                    <button wire:click="openInputKunjunganModal"
                            class="flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <i class="ph ph-calendar-check text-lg mr-2"></i>
                        Input Kunjungan Per Bulan
                    </button>
                </div>
            </div>

            {{-- Filter Tahun --}}
            <div class="mb-4 flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700">Tahun:</label>
                    <select wire:model.live="tahunFilter" 
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        @for($i = date('Y'); $i >= 2020; $i--)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="flex-1">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <i class="ph ph-magnifying-glass text-lg"></i>
                        </span>
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               class="w-full py-2 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                               placeholder="Cari berdasarkan nama ibu, suami, atau bayi...">
                    </div>
                </div>
            </div>

            @if($ibuMenyusuiList->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th rowspan="2" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r">No</th>
                                <th colspan="6" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-r">Sasaran Balita</th>
                                <th colspan="12" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Absensi Kehadiran Per Bulan</th>
                            </tr>
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r">Nama Ibu</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r">Nama Suami</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r">Nama Bayi</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r">Petugas Penanggung Jawab</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r">Petugas Imunisasi</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r">Petugas Input</th>
                                @foreach($bulanList as $bulanNum => $bulanNama)
                                    <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">{{ substr($bulanNama, 0, 3) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($ibuMenyusuiList as $index => $ibu)
                                @php
                                    $kunjunganByBulan = [];
                                    foreach($ibu->kunjungan as $kunjungan) {
                                        $kunjunganByBulan[$kunjungan->bulan] = $kunjungan;
                                    }
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 text-center border-r">{{ $index + 1 }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border-r">{{ $ibu->nama_ibu }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600 border-r">{{ $ibu->nama_suami ?? '-' }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600 border-r">{{ $ibu->nama_bayi ?? '-' }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600 border-r">
                                        @php
                                            // Ambil petugas dari kunjungan bulan pertama yang ada di tahun filter
                                            $petugasPJ = '-';
                                            foreach ($kunjunganByBulan as $kunjungan) {
                                                if ($kunjungan->petugasPenanggungJawab) {
                                                    $petugasPJ = $kunjungan->petugasPenanggungJawab->nama_petugas_kesehatan;
                                                    break;
                                                }
                                            }
                                        @endphp
                                        {{ $petugasPJ }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600 border-r">
                                        @php
                                            // Ambil petugas dari kunjungan bulan pertama yang ada di tahun filter
                                            $petugasImunisasi = '-';
                                            foreach ($kunjunganByBulan as $kunjungan) {
                                                if ($kunjungan->petugasImunisasi) {
                                                    $petugasImunisasi = $kunjungan->petugasImunisasi->nama_petugas_kesehatan;
                                                    break;
                                                }
                                            }
                                        @endphp
                                        {{ $petugasImunisasi }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600 border-r">
                                        @php
                                            // Ambil petugas input dari kunjungan bulan pertama yang ada di tahun filter
                                            $petugasInput = '-';
                                            foreach ($kunjunganByBulan as $kunjungan) {
                                                if ($kunjungan->petugasInput) {
                                                    $petugasInput = $kunjungan->petugasInput->nama_petugas_kesehatan;
                                                    break;
                                                }
                                            }
                                        @endphp
                                        {{ $petugasInput }}
                                    </td>
                                    @for($bulan = 1; $bulan <= 12; $bulan++)
                                        <td class="px-2 py-4 text-center">
                                            @php
                                                $hasKunjungan = isset($kunjunganByBulan[$bulan]) && $kunjunganByBulan[$bulan]->status == 'success';
                                                $tanggalKunjungan = isset($kunjunganByBulan[$bulan]) && $kunjunganByBulan[$bulan]->tanggal_kunjungan 
                                                    ? \Carbon\Carbon::parse($kunjunganByBulan[$bulan]->tanggal_kunjungan)->format('d/m/Y') 
                                                    : date('d/m/Y');
                                            @endphp
                                            <input type="checkbox" 
                                                   wire:change="toggleKunjungan({{ $ibu->id_ibu_menyusui }}, {{ $bulan }}, {{ $tahunFilter }}, $event.target.checked)"
                                                   @if($hasKunjungan) checked @endif
                                                   class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500 cursor-pointer"
                                                   title="@if($hasKunjungan) Kunjungan: {{ $tanggalKunjungan }} @else Klik untuk menandai kunjungan @endif">
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12 text-gray-500">
                    <i class="ph ph-baby text-4xl mb-2"></i>
                    <p>Belum ada data absensi (sasaran balita)</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Form Absensi --}}
    @include('livewire.super-admin.posyandu-detail.modals.ibu-menyusui-modal')

    {{-- Modal Form Kunjungan --}}
    @include('livewire.super-admin.posyandu-detail.modals.kunjungan-ibu-menyusui-modal')

    {{-- Modal Input Kunjungan Per Bulan --}}
    @include('livewire.super-admin.posyandu-detail.modals.input-kunjungan-bulan-modal')
</div>
