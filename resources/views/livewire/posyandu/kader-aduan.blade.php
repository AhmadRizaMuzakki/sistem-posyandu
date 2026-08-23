<div class="kader-dashboard-content">
    @php
        use App\Helpers\AduanOptions;
    @endphp

    <div class="space-y-6">
        {{-- Header --}}
        <div class="bg-white rounded-lg shadow-sm p-4 md:p-6">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">{{ $posyandu->nama_posyandu }}</h1>
            <p class="text-gray-500 mt-1 text-sm md:text-base">Manajemen Aduan Keluarga (6 SPM)</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 md:p-6 overflow-hidden">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center min-w-0">
                    <i class="ph ph-megaphone text-2xl mr-3 text-primary shrink-0"></i>
                    Daftar Aduan
                </h2>
                <div class="flex flex-wrap items-center gap-3">
                    <button type="button"
                            wire:click="openCreateModal"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="ph ph-plus-circle text-lg mr-2"></i>
                        Tambah Aduan
                    </button>
                    <div class="inline-flex items-center rounded-lg border border-gray-200 p-1 bg-gray-50">
                        <button type="button"
                                wire:click="setViewMode('table')"
                                @class([
                                    'inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-md transition-colors',
                                    'bg-white text-primary shadow-sm' => $viewMode === 'table',
                                    'text-gray-600 hover:text-gray-800' => $viewMode !== 'table',
                                ])>
                            <i class="ph ph-table text-base"></i>
                            Tabel
                        </button>
                        <button type="button"
                                wire:click="setViewMode('card')"
                                @class([
                                    'inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-md transition-colors',
                                    'bg-white text-primary shadow-sm' => $viewMode === 'card',
                                    'text-gray-600 hover:text-gray-800' => $viewMode !== 'card',
                                ])>
                            <i class="ph ph-squares-four text-base"></i>
                            Card
                        </button>
                    </div>
                </div>
            </div>

            {{-- Search --}}
            <div class="mb-4">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <i class="ph ph-magnifying-glass text-lg"></i>
                    </span>
                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           class="w-full py-2.5 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                           placeholder="Cari judul, isi aduan, atau No. KK...">
                </div>
            </div>

            {{-- Filter --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-2">Status</label>
                    <select wire:model.live="filterStatus"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        <option value="">Semua Status</option>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-2">Bidang SPM</label>
                    <select wire:model.live="filterKategori"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        <option value="">Semua Bidang SPM</option>
                        @foreach($kategoriOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-2">Bulan</label>
                    <select wire:model.live="filterBulan"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        <option value="">Semua Bulan</option>
                        @foreach(['1'=>'Januari','2'=>'Februari','3'=>'Maret','4'=>'April','5'=>'Mei','6'=>'Juni','7'=>'Juli','8'=>'Agustus','9'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $v => $l)
                            <option value="{{ $v }}">{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-2">Tahun</label>
                    <select wire:model.live="filterTahun"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        <option value="">Semua Tahun</option>
                        @for($y = now()->year; $y >= now()->year - 5; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            @if($aduanList->total() > 0)
                @if($viewMode === 'table')
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Judul</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keluarga</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bidang SPM</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach($aduanList as $aduan)
                                    @php
                                        $orangtua = $orangtuaMap->get($aduan->no_kk);
                                        $kategoriLabel = AduanOptions::kategoriLabel($aduan->kategori);
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ $aduanList->firstItem() + $loop->index }}</td>
                                        <td class="px-4 py-4 text-sm font-medium text-gray-900 max-w-[200px]">
                                            <span class="line-clamp-2">{{ $aduan->judul }}</span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600">
                                            <div>{{ $orangtua?->nama ?? '-' }}</div>
                                            <div class="text-xs text-gray-400">KK: {{ $aduan->no_kk }}</div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-medium {{ AduanOptions::kategoriBadgeClasses($aduan->kategori) }}">
                                                {{ $kategoriLabel }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <x-status-aduan-badge :status="$aduan->status" />
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ $aduan->tanggal_aduan?->format('d/m/Y') ?? '-' }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-1.5">
                                                <button type="button"
                                                        wire:click="viewAduan({{ $aduan->id_aduan }})"
                                                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-primary border border-primary/30 hover:bg-primary hover:text-white transition-colors"
                                                        title="Lihat detail">
                                                    <i class="ph ph-eye text-lg"></i>
                                                </button>
                                                <button type="button"
                                                        wire:click="openEditModal({{ $aduan->id_aduan }})"
                                                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-amber-600 border border-amber-200 hover:bg-amber-500 hover:text-white transition-colors"
                                                        title="Edit aduan">
                                                    <i class="ph ph-pencil-simple text-lg"></i>
                                                </button>
                                                <button type="button"
                                                        wire:click="hapusAduan({{ $aduan->id_aduan }})"
                                                        wire:confirm="Yakin ingin menghapus aduan ini?"
                                                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-red-600 border border-red-200 hover:bg-red-500 hover:text-white transition-colors"
                                                        title="Hapus aduan">
                                                    <i class="ph ph-trash text-lg"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        @foreach($aduanList as $aduan)
                            @php
                                $orangtua = $orangtuaMap->get($aduan->no_kk);
                                $kategoriLabel = AduanOptions::kategoriLabel($aduan->kategori);
                            @endphp
                            <article class="border border-gray-100 rounded-xl shadow-sm hover:shadow-md transition-shadow overflow-hidden flex flex-col">
                                <div class="p-5 flex-1 flex flex-col">
                                    <div class="flex items-start justify-between gap-3 mb-3">
                                        <div class="min-w-0 flex-1">
                                            <h4 class="text-base font-semibold text-gray-900 leading-snug line-clamp-2">{{ $aduan->judul }}</h4>
                                            <p class="text-xs text-gray-400 mt-1.5">
                                                {{ $aduan->tanggal_aduan?->locale('id')->translatedFormat('d F Y') ?? '-' }}
                                            </p>
                                        </div>
                                        <x-status-aduan-badge :status="$aduan->status" class="shrink-0" />
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2 mb-3">
                                        <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-medium {{ AduanOptions::kategoriBadgeClasses($aduan->kategori) }}">
                                            {{ $kategoriLabel }}
                                        </span>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-50 text-gray-600">
                                            <i class="ph ph-users mr-1"></i>
                                            {{ $orangtua?->nama ?? 'Keluarga' }}
                                        </span>
                                    </div>

                                    <p class="text-sm text-gray-600 leading-relaxed line-clamp-3 flex-1">{{ $aduan->isi_aduan }}</p>

                                    <div class="mt-4 pt-4 border-t border-gray-100 flex flex-wrap justify-end gap-2">
                                        <button type="button"
                                                wire:click="viewAduan({{ $aduan->id_aduan }})"
                                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-primary border border-primary/30 rounded-lg hover:bg-primary hover:text-white transition-colors">
                                            <i class="ph ph-eye text-lg"></i>
                                            Detail
                                        </button>
                                        <button type="button"
                                                wire:click="openEditModal({{ $aduan->id_aduan }})"
                                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-amber-600 border border-amber-200 rounded-lg hover:bg-amber-500 hover:text-white transition-colors">
                                            <i class="ph ph-pencil-simple text-lg"></i>
                                            Edit
                                        </button>
                                        <button type="button"
                                                wire:click="hapusAduan({{ $aduan->id_aduan }})"
                                                wire:confirm="Yakin ingin menghapus aduan ini?"
                                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-500 hover:text-white transition-colors">
                                            <i class="ph ph-trash text-lg"></i>
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif

                <div class="mt-6">
                    {{ $aduanList->links() }}
                </div>
            @else
                <div class="text-center py-16">
                    <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                        <i class="ph ph-megaphone text-2xl text-gray-300"></i>
                    </div>
                    <h4 class="text-base font-semibold text-gray-700 mb-2">Belum Ada Aduan</h4>
                    <p class="text-sm text-gray-500 mb-4">Belum ada aduan dari keluarga sasaran posyandu ini.</p>
                    <button type="button"
                            wire:click="openCreateModal"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="ph ph-plus-circle text-lg mr-2"></i>
                        Tambah Aduan Pertama
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Detail --}}
    @if($showDetailModal && $selectedAduan)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 py-6">
                <div class="fixed inset-0 bg-gray-500/75" wire:click="closeDetailModal"></div>
                <div class="relative bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
                    <div class="px-6 py-5 border-b border-gray-100 flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h3 class="text-lg font-semibold text-gray-900 leading-snug">{{ $selectedAduan->judul }}</h3>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ $selectedAduan->tanggal_aduan?->locale('id')->translatedFormat('d F Y') ?? '-' }}
                            </p>
                        </div>
                        <button type="button" wire:click="closeDetailModal" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 shrink-0">
                            <i class="ph ph-x text-xl"></i>
                        </button>
                    </div>

                    <div class="px-6 py-5 overflow-y-auto flex-1 space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase mb-1">Keluarga</p>
                                <p class="text-sm text-gray-900">{{ $detailOrangtua?->nama ?? '-' }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">No. KK: {{ $selectedAduan->no_kk }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase mb-1">Bidang SPM</p>
                                <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-medium {{ AduanOptions::kategoriBadgeClasses($selectedAduan->kategori) }}">
                                    {{ AduanOptions::kategoriLabel($selectedAduan->kategori) }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase mb-2">Isi Aduan</p>
                            <p class="text-sm text-gray-700 leading-relaxed bg-gray-50 rounded-lg p-4 whitespace-pre-line">{{ $selectedAduan->isi_aduan }}</p>
                        </div>

                        <form wire:submit="simpanTanggapan" class="space-y-4 border-t border-gray-100 pt-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                                <select wire:model="statusUpdate"
                                        class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                    @foreach($statusOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('statusUpdate')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Tanggapan Posyandu</label>
                                <textarea wire:model="tanggapan"
                                          rows="4"
                                          placeholder="Tulis tanggapan untuk keluarga..."
                                          class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm resize-y focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
                                @error('tanggapan')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" wire:click="closeDetailModal"
                                        class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Tutup
                                </button>
                                <button type="submit"
                                        wire:loading.attr="disabled"
                                        class="px-4 py-2.5 bg-primary text-white rounded-lg text-sm font-medium hover:bg-indigo-700 disabled:opacity-60">
                                    <span wire:loading.remove wire:target="simpanTanggapan">Simpan Tanggapan</span>
                                    <span wire:loading wire:target="simpanTanggapan">Menyimpan...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Buat Aduan --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 py-6">
                <div class="fixed inset-0 bg-gray-500/75" wire:click="closeCreateModal"></div>
                <div class="relative bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
                    <div class="px-6 py-5 border-b border-gray-100 flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Tambah Aduan Baru</h3>
                            <p class="text-sm text-gray-500 mt-1">Buat aduan atas nama keluarga sasaran posyandu.</p>
                        </div>
                        <button type="button" wire:click="closeCreateModal" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 shrink-0">
                            <i class="ph ph-x text-xl"></i>
                        </button>
                    </div>

                    <form wire:submit="simpanAduan" class="px-6 py-5 overflow-y-auto flex-1 space-y-4">
                        <div class="relative" wire:click.outside="hideKeluargaDropdown">
                            <label for="aduan-keluarga" class="block text-sm font-medium text-gray-700 mb-1.5">Keluarga</label>
                            @if($keluargaList->count() > 0)
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 pointer-events-none">
                                        <i class="ph ph-magnifying-glass text-lg"></i>
                                    </span>
                                    <input id="aduan-keluarga"
                                           type="text"
                                           wire:model.live.debounce.200ms="keluargaSearch"
                                           wire:focus="onKeluargaFocus"
                                           autocomplete="off"
                                           placeholder="Ketik nama atau No. KK..."
                                           @class([
                                               'w-full py-2.5 pl-10 pr-10 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary',
                                               'border-primary bg-primary/5' => $noKk !== '',
                                               'border-gray-200 bg-white' => $noKk === '',
                                           ])>
                                    @if($noKk !== '')
                                        <button type="button"
                                                wire:click="clearKeluarga"
                                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-red-600">
                                            <i class="ph ph-x text-lg"></i>
                                        </button>
                                    @endif
                                </div>

                                @if($showKeluargaDropdown && trim($keluargaSearch) !== '')
                                    <div class="absolute z-20 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-52 overflow-y-auto">
                                        @if($filteredKeluargaList->count() > 0)
                                            <ul class="py-1">
                                                @foreach($filteredKeluargaList as $keluarga)
                                                    <li>
                                                        <button type="button"
                                                                wire:click="selectKeluarga('{{ $keluarga['no_kk'] }}')"
                                                                class="w-full text-left px-4 py-2.5 hover:bg-gray-50 transition-colors">
                                                            <p class="text-sm font-medium text-gray-900">{{ $keluarga['nama'] }}</p>
                                                            <p class="text-xs text-gray-500">No. KK: {{ $keluarga['no_kk'] }}</p>
                                                        </button>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="px-4 py-3 text-sm text-gray-500">Tidak ada keluarga yang cocok.</p>
                                        @endif
                                    </div>
                                @endif

                                @if($noKk !== '')
                                    <p class="text-xs text-green-600 mt-1.5 flex items-center gap-1">
                                        <i class="ph ph-check-circle"></i>
                                        Keluarga terpilih — KK: {{ $noKk }}
                                    </p>
                                @endif
                            @else
                                <p class="text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg p-3">
                                    Belum ada keluarga terdaftar di posyandu ini. Tambahkan sasaran terlebih dahulu.
                                </p>
                            @endif
                            @error('noKk')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="aduan-judul" class="block text-sm font-medium text-gray-700 mb-1.5">Judul Aduan</label>
                            <input id="aduan-judul"
                                   type="text"
                                   wire:model="judul"
                                   placeholder="Contoh: Keluhan layanan imunisasi"
                                   class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            @error('judul')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="aduan-kategori" class="block text-sm font-medium text-gray-700 mb-1.5">Bidang SPM</label>
                            <select id="aduan-kategori"
                                    wire:model="kategori"
                                    class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                @foreach($kategoriOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('kategori')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="aduan-isi" class="block text-sm font-medium text-gray-700 mb-1.5">Isi Aduan</label>
                            <textarea id="aduan-isi"
                                      wire:model="isiAduan"
                                      rows="4"
                                      placeholder="Jelaskan aduan secara detail..."
                                      class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm resize-y focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
                            @error('isiAduan')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                            <button type="button" wire:click="closeCreateModal"
                                    class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Batal
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    @if($keluargaList->count() === 0) disabled @endif
                                    class="px-4 py-2.5 bg-primary text-white rounded-lg text-sm font-medium hover:bg-indigo-700 disabled:opacity-60">
                                <span wire:loading.remove wire:target="simpanAduan">Simpan Aduan</span>
                                <span wire:loading wire:target="simpanAduan">Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Edit Aduan --}}
    @if($showEditModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 py-6">
                <div class="fixed inset-0 bg-gray-500/75" wire:click="closeEditModal"></div>
                <div class="relative bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
                    <div class="px-6 py-5 border-b border-gray-100 flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Edit Aduan</h3>
                            <p class="text-sm text-gray-500 mt-1">Perbarui data aduan keluarga.</p>
                        </div>
                        <button type="button" wire:click="closeEditModal" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 shrink-0">
                            <i class="ph ph-x text-xl"></i>
                        </button>
                    </div>

                    <form wire:submit="updateAduan" class="px-6 py-5 overflow-y-auto flex-1 space-y-4">
                        <div class="relative" wire:click.outside="hideKeluargaDropdown">
                            <label for="edit-aduan-keluarga" class="block text-sm font-medium text-gray-700 mb-1.5">Keluarga</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 pointer-events-none">
                                    <i class="ph ph-magnifying-glass text-lg"></i>
                                </span>
                                <input id="edit-aduan-keluarga"
                                       type="text"
                                       wire:model.live.debounce.200ms="keluargaSearch"
                                       wire:focus="onKeluargaFocus"
                                       autocomplete="off"
                                       placeholder="Ketik nama atau No. KK..."
                                       @class([
                                           'w-full py-2.5 pl-10 pr-10 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary',
                                           'border-primary bg-primary/5' => $noKk !== '',
                                           'border-gray-200 bg-white' => $noKk === '',
                                       ])>
                                @if($noKk !== '')
                                    <button type="button"
                                            wire:click="clearKeluarga"
                                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-red-600">
                                        <i class="ph ph-x text-lg"></i>
                                    </button>
                                @endif
                            </div>

                            @if($showKeluargaDropdown && trim($keluargaSearch) !== '')
                                <div class="absolute z-20 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-52 overflow-y-auto">
                                    @if($filteredKeluargaList->count() > 0)
                                        <ul class="py-1">
                                            @foreach($filteredKeluargaList as $keluarga)
                                                <li>
                                                    <button type="button"
                                                            wire:click="selectKeluarga('{{ $keluarga['no_kk'] }}')"
                                                            class="w-full text-left px-4 py-2.5 hover:bg-gray-50 transition-colors">
                                                        <p class="text-sm font-medium text-gray-900">{{ $keluarga['nama'] }}</p>
                                                        <p class="text-xs text-gray-500">No. KK: {{ $keluarga['no_kk'] }}</p>
                                                    </button>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="px-4 py-3 text-sm text-gray-500">Tidak ada keluarga yang cocok.</p>
                                    @endif
                                </div>
                            @endif
                            @error('noKk')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="edit-aduan-judul" class="block text-sm font-medium text-gray-700 mb-1.5">Judul Aduan</label>
                            <input id="edit-aduan-judul"
                                   type="text"
                                   wire:model="judul"
                                   class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            @error('judul')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="edit-aduan-kategori" class="block text-sm font-medium text-gray-700 mb-1.5">Bidang SPM</label>
                                <select id="edit-aduan-kategori"
                                        wire:model="kategori"
                                        class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                    @foreach($kategoriOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('kategori')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="edit-aduan-status" class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                                <select id="edit-aduan-status"
                                        wire:model="statusUpdate"
                                        class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                    @foreach($statusOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('statusUpdate')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="edit-aduan-isi" class="block text-sm font-medium text-gray-700 mb-1.5">Isi Aduan</label>
                            <textarea id="edit-aduan-isi"
                                      wire:model="isiAduan"
                                      rows="4"
                                      class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm resize-y focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
                            @error('isiAduan')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                            <button type="button" wire:click="closeEditModal"
                                    class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Batal
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="px-4 py-2.5 bg-primary text-white rounded-lg text-sm font-medium hover:bg-indigo-700 disabled:opacity-60">
                                <span wire:loading.remove wire:target="updateAduan">Simpan Perubahan</span>
                                <span wire:loading wire:target="updateAduan">Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @include('components.notification-modal')
</div>
