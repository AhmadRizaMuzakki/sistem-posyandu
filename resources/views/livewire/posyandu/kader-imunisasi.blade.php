<div>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">{{ $posyandu->nama_posyandu }}</h1>
                    <p class="text-gray-500 mt-1">Manajemen Data Imunisasi</p>
                </div>
            </div>
        </div>

        {{-- Daftar Imunisasi --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="ph ph-syringe text-2xl mr-3 text-primary"></i>
                    Daftar Imunisasi
                </h2>
                <div class="flex items-center gap-3">
                    <a href="{{ route('adminPosyandu.laporan.pdf') }}"
                       target="_blank"
                       class="flex items-center px-4 py-2 text-sm font-medium text-primary border border-primary rounded-lg hover:bg-primary hover:text-white transition-colors">
                        <i class="ph ph-file-pdf text-lg mr-2"></i>
                        Export PDF
                    </a>
                    <button wire:click="openImunisasiModal"
                            class="flex items-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="ph ph-plus text-lg mr-2"></i>
                        Tambah Imunisasi
                    </button>
                </div>
            </div>

            {{-- Search Bar --}}
            <div class="mb-4">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <i class="ph ph-magnifying-glass text-lg"></i>
                    </span>
                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           class="w-full py-2 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                           placeholder="Cari berdasarkan nama sasaran, jenis imunisasi, kategori, atau keterangan...">
                </div>
            </div>

            @if($imunisasiList->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sasaran</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Imunisasi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tensi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Petugas Kesehatan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($imunisasiList as $index => $imunisasi)
                                @php
                                    $sasaran = $imunisasi->sasaran;
                                    $sasaranNama = $sasaran ? $sasaran->nama_sasaran : 'Tidak ditemukan';
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $sasaranNama }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 capitalize">
                                            {{ $imunisasi->kategori_sasaran }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $imunisasi->jenis_imunisasi }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $imunisasi->tanggal_imunisasi ? \Carbon\Carbon::parse($imunisasi->tanggal_imunisasi)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        @if($imunisasi->sistol !== null && $imunisasi->diastol !== null)
                                            {{ $imunisasi->sistol }}/{{ $imunisasi->diastol }} <span class="text-gray-400 text-xs">mmHg</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $imunisasi->petugasKesehatan->nama_petugas_kesehatan ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $imunisasi->keterangan ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button wire:click="openImunisasiModal({{ $imunisasi->id_imunisasi }})"
                                                class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            <i class="ph ph-pencil"></i>
                                        </button>
                                        <button wire:click="deleteImunisasi({{ $imunisasi->id_imunisasi }})"
                                                wire:confirm="Apakah Anda yakin ingin menghapus data imunisasi ini?"
                                                class="text-red-600 hover:text-red-900">
                                            <i class="ph ph-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12 text-gray-500">
                    <i class="ph ph-syringe text-4xl mb-2"></i>
                    <p>Belum ada data imunisasi</p>
                    <button wire:click="openImunisasiModal"
                            class="mt-4 px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        Tambah Imunisasi Pertama
                    </button>
                </div>
            @endif

            {{-- Card Keterangan: per kategori X dari Y sasaran (di bawah tabel) --}}
            @if(isset($imunisasiKeteranganPerKategori))
            <div class="mt-5 pt-5 border-t border-gray-100">
                {{-- Header --}}
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                        <i class="ph ph-chart-pie-slice text-primary text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Keterangan Imunisasi</h3>
                        <p class="text-xs text-gray-500">Per kategori · Tahun {{ date('Y') }}</p>
                    </div>
                </div>

                {{-- Kartu per kategori --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 xl:grid-cols-6 gap-2">
                    @php
                        $kategoriStyles = [
                            'bayibalita' => ['bar' => 'bg-amber-500', 'icon' => 'bg-amber-500/15', 'text' => 'text-amber-700', 'ph' => 'ph-baby'],
                            'remaja'     => ['bar' => 'bg-violet-500', 'icon' => 'bg-violet-500/15', 'text' => 'text-violet-700', 'ph' => 'ph-person'],
                            'dewasa'     => ['bar' => 'bg-emerald-500', 'icon' => 'bg-emerald-500/15', 'text' => 'text-emerald-700', 'ph' => 'ph-users-three'],
                            'pralansia'  => ['bar' => 'bg-orange-500', 'icon' => 'bg-orange-500/15', 'text' => 'text-orange-700', 'ph' => 'ph-user-circle'],
                            'lansia'     => ['bar' => 'bg-indigo-500', 'icon' => 'bg-indigo-500/15', 'text' => 'text-indigo-700', 'ph' => 'ph-user-gear'],
                        ];
                    @endphp
                    @foreach($imunisasiKeteranganPerKategori as $row)
                        @if($row['total_sasaran'] > 0)
                        @php
                            $c = $kategoriStyles[$row['kategori']] ?? ['bar' => 'bg-primary', 'icon' => 'bg-primary/15', 'text' => 'text-gray-700', 'ph' => 'ph-syringe'];
                            $persen = $row['total_sasaran'] > 0 ? round(($row['sudah_imunisasi'] / $row['total_sasaran']) * 100) : 0;
                        @endphp
                        <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-100 hover:shadow transition-shadow">
                            <div class="flex items-center justify-between gap-2 mb-1.5">
                                <div class="w-7 h-7 rounded-md {{ $c['icon'] }} flex items-center justify-center flex-shrink-0">
                                    <i class="ph {{ $c['ph'] }} text-sm {{ $c['text'] }}"></i>
                                </div>
                                <span class="text-[10px] font-bold {{ $c['text'] }}">{{ $persen }}%</span>
                            </div>
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">{{ $row['label'] }}</p>
                            <p class="text-base font-bold text-gray-800 tabular-nums leading-tight">
                                <span class="{{ $c['text'] }}">{{ $row['sudah_imunisasi'] }}</span><span class="text-gray-300">/</span><span class="text-gray-600">{{ $row['total_sasaran'] }}</span>
                            </p>
                            <div class="h-1 bg-gray-100 rounded-full overflow-hidden mt-1.5">
                                <div class="h-full {{ $c['bar'] }} rounded-full transition-all duration-500" style="width: {{ min($persen, 100) }}%"></div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>

                {{-- Total ringkasan --}}
                @php
                    $totalPersen = $totalSemuaSasaran > 0 ? round(($totalSudahImunisasi / $totalSemuaSasaran) * 100) : 0;
                @endphp
                <div class="mt-3 px-3 py-2.5 rounded-lg bg-primary/5 border border-primary/10 flex flex-wrap items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center flex-shrink-0">
                        <i class="ph ph-clipboard-text text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Total</p>
                        <p class="text-sm font-bold text-gray-800 tabular-nums">{{ $totalSudahImunisasi }} dari {{ $totalSemuaSasaran }} sasaran <span class="text-primary font-semibold">({{ $totalPersen }}%)</span></p>
                    </div>
                    <div class="flex-1 min-w-[80px] max-w-[120px]">
                        <div class="h-1.5 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-primary rounded-full transition-all duration-500" style="width: {{ $totalPersen }}%"></div>
                        </div>
                    </div>
                    <span class="text-xs text-gray-400">Tahun {{ date('Y') }}</span>
                </div>
            </div>
            @endif
        </div>

        {{-- Notification Modal --}}
        @include('components.notification-modal')

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
                                {{-- Pilih Sasaran (Searchable) --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Sasaran <span class="text-red-500">*</span></label>
                                    <div class="relative" x-data="{
                                        open: false,
                                        searchText: '',
                                        selectedId: @entangle('id_sasaran_imunisasi'),
                                        selectedKategori: @entangle('kategori_sasaran_imunisasi'),
                                        selectedName: '',
                                        sasaranList: @js($sasaranList)
                                    }" x-init="
                                        // Fungsi untuk mencari sasaran berdasarkan ID dan kategori
                                        function findSasaran(id, kategori) {
                                            if (!id) return null;
                                            // Jika ada kategori, cari yang sesuai dengan ID dan kategori
                                            if (kategori) {
                                                return sasaranList.find(s => s.id == id && s.kategori == kategori);
                                            }
                                            // Jika tidak ada kategori, ambil yang pertama dengan ID tersebut
                                            return sasaranList.find(s => s.id == id);
                                        }

                                        $watch('selectedId', value => {
                                            if (value) {
                                                const sasaran = findSasaran(value, selectedKategori);
                                                if (sasaran) {
                                                    selectedName = sasaran.nama + ' (' + sasaran.nik + ') - ' + sasaran.kategori.charAt(0).toUpperCase() + sasaran.kategori.slice(1);
                                                    searchText = selectedName;
                                                    // Pastikan kategori sesuai
                                                    if (sasaran.kategori !== selectedKategori) {
                                                        $wire.set('kategori_sasaran_imunisasi', sasaran.kategori);
                                                    }
                                                }
                                            } else {
                                                selectedName = '';
                                                searchText = '';
                                            }
                                        });

                                        $watch('selectedKategori', value => {
                                            if (selectedId && value) {
                                                const sasaran = findSasaran(selectedId, value);
                                                if (sasaran) {
                                                    selectedName = sasaran.nama + ' (' + sasaran.nik + ') - ' + sasaran.kategori.charAt(0).toUpperCase() + sasaran.kategori.slice(1);
                                                    searchText = selectedName;
                                                }
                                            }
                                        });

                                        // Inisialisasi saat pertama kali load
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
                                            placeholder="Ketik untuk mencari sasaran (Balita, Remaja, Dewasa, Pralansia, Lansia)..."
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
                                                        <li @click="
                                                            // Update UI state
                                                            selectedId = '{{ $sasaran['id'] }}';
                                                            selectedKategori = '{{ $sasaran['kategori'] }}';
                                                            selectedName = '{{ $sasaran['nama'] }} ({{ $sasaran['nik'] }}) - {{ ucfirst($sasaran['kategori']) }}';
                                                            searchText = selectedName;
                                                            open = false;
                                                            // Set kategori dan ID secara bersamaan - kategori dulu untuk menghindari konflik
                                                            $wire.set('kategori_sasaran_imunisasi', '{{ $sasaran['kategori'] }}');
                                                            $wire.set('id_sasaran_imunisasi', '{{ $sasaran['id'] }}');
                                                        "
                                                            x-show="!searchText || '{{ strtolower($sasaran['nama'] . ' ' . $sasaran['nik'] . ' ' . $sasaran['kategori']) }}'.includes(searchText.toLowerCase())"
                                                            class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer"
                                                            :class="selectedId == '{{ $sasaran['id'] }}' && selectedKategori == '{{ $sasaran['kategori'] }}' ? 'bg-blue-50 font-medium' : ''">
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
                                    <div class="grid grid-cols-3 gap-2">
                                        <div>
                                            <select wire:model="hari_imunisasi"
                                                    class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                                <option value="">Hari</option>
                                                @for($i = 1; $i <= 31; $i++)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                            @error('hari_imunisasi') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                        </div>
                                        <div>
                                            <select wire:model="bulan_imunisasi"
                                                    class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
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
                                            @error('bulan_imunisasi') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                        </div>
                                        <div>
                                            <input type="number"
                                                   wire:model="tahun_imunisasi"
                                                   min="1900"
                                                   max="{{ date('Y') }}"
                                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary"
                                                   placeholder="Tahun">
                                            @error('tahun_imunisasi') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    @error('tanggal_imunisasi') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>

                                {{-- Tinggi & Berat Badan --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-gray-700 text-sm font-bold mb-2">Tinggi Badan</label>
                                        <div class="flex">
                                            <input type="number"
                                                   step="0.1"
                                                   min="0"
                                                   max="300"
                                                   wire:model="tinggi_badan"
                                                   class="shadow appearance-none border border-r-0 rounded-l w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary"
                                                   placeholder="Contoh: 120">
                                            <span class="inline-flex items-center px-3 border border-l-0 rounded-r bg-gray-50 text-gray-500 text-sm">
                                                cm
                                            </span>
                                        </div>
                                        @error('tinggi_badan') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 text-sm font-bold mb-2">Berat Badan</label>
                                        <div class="flex">
                                            <input type="number"
                                                   step="0.1"
                                                   min="0"
                                                   max="300"
                                                   wire:model="berat_badan"
                                                   class="shadow appearance-none border border-r-0 rounded-l w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary"
                                                   placeholder="Contoh: 25">
                                            <span class="inline-flex items-center px-3 border border-l-0 rounded-r bg-gray-50 text-gray-500 text-sm">
                                                kg
                                            </span>
                                        </div>
                                        @error('berat_badan') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                {{-- Tensi (Tekanan Darah) --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-gray-700 text-sm font-bold mb-2">Sistol (mmHg)</label>
                                        <div class="flex">
                                            <input type="number" min="50" max="300" wire:model="sistol"
                                                class="shadow appearance-none border border-r-0 rounded-l w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary"
                                                placeholder="Contoh: 120">
                                            <span class="inline-flex items-center px-3 border border-l-0 rounded-r bg-gray-50 text-gray-500 text-sm">mmHg</span>
                                        </div>
                                        @error('sistol') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 text-sm font-bold mb-2">Diastol (mmHg)</label>
                                        <div class="flex">
                                            <input type="number" min="30" max="200" wire:model="diastol"
                                                class="shadow appearance-none border border-r-0 rounded-l w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary"
                                                placeholder="Contoh: 80">
                                            <span class="inline-flex items-center px-3 border border-l-0 rounded-r bg-gray-50 text-gray-500 text-sm">mmHg</span>
                                        </div>
                                        @error('diastol') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                {{-- Petugas Kesehatan --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Petugas Kesehatan</label>
                                    <select wire:model="id_petugas_kesehatan_imunisasi" 
                                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                        <option value="">Pilih Petugas Kesehatan</option>
                                        @foreach($petugasKesehatanList as $petugas)
                                            <option value="{{ $petugas->id_petugas_kesehatan }}">{{ $petugas->nama_petugas_kesehatan }}</option>
                                        @endforeach
                                    </select>
                                    @error('id_petugas_kesehatan_imunisasi') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
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
    </div>
</div>
