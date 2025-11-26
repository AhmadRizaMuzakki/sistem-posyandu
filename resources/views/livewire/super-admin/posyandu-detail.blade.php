<div>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">{{ $posyandu->nama_posyandu }}</h1>
                <p class="text-gray-500 mt-1">Detail informasi Posyandu</p>
            </div>
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <i class="ph ph-arrow-left text-lg mr-2"></i>
                Kembali
            </a>
        </div>

        {{-- Informasi Utama --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Card Informasi Posyandu --}}
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="ph ph-info text-2xl mr-3 text-primary"></i>
                    Informasi Posyandu
                </h2>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Nama Posyandu</label>
                        <p class="text-gray-800 mt-1">{{ $posyandu->nama_posyandu }}</p>
                    </div>
                    @if($posyandu->alamat_posyandu)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Alamat</label>
                        <p class="text-gray-800 mt-1">{{ $posyandu->alamat_posyandu }}</p>
                    </div>
                    @endif
                    @if($posyandu->domisili_posyandu)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Domisili</label>
                        <p class="text-gray-800 mt-1">{{ $posyandu->domisili_posyandu }}</p>
                    </div>
                    @endif
                    @if($posyandu->jumlah_sasaran)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Jumlah Sasaran</label>
                        <p class="text-gray-800 mt-1">{{ number_format($posyandu->jumlah_sasaran, 0, ',', '.') }} orang</p>
                    </div>
                    @endif
                    @if($posyandu->sk_posyandu)
                    <div>
                        <label class="text-sm font-medium text-gray-500">SK Posyandu</label>
                        <p class="text-gray-800 mt-1">{{ $posyandu->sk_posyandu }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Card Statistik --}}
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="ph ph-chart-bar text-2xl mr-3 text-primary"></i>
                    Statistik
                </h2>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                        <div>
                            <p class="text-sm text-gray-600">Total Kader</p>
                            <p class="text-2xl font-bold text-primary mt-1">{{ $posyandu->kader->count() }}</p>
                        </div>
                        <i class="ph ph-users text-4xl text-blue-300"></i>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg">
                        <div>
                            <p class="text-sm text-gray-600">Total Sasaran Bayi/Balita</p>
                            <p class="text-2xl font-bold text-green-600 mt-1">{{ $posyandu->sasaran_bayibalita->count() }}</p>
                        </div>
                        <i class="ph ph-baby text-4xl text-green-300"></i>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-purple-50 rounded-lg">
                        <div>
                            <p class="text-sm text-gray-600">Total Sasaran Remaja</p>
                            <p class="text-2xl font-bold text-purple-600 mt-1">{{ $posyandu->sasaran_remaja->count() }}</p>
                        </div>
                        <i class="ph ph-user text-4xl text-purple-300"></i>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-orange-50 rounded-lg">
                        <div>
                            <p class="text-sm text-gray-600">Total Sasaran Dewasa</p>
                            <p class="text-2xl font-bold text-orange-600 mt-1">{{ $posyandu->sasaran_dewasa->count() }}</p>
                        </div>
                        <i class="ph ph-users text-4xl text-orange-300"></i>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-pink-50 rounded-lg">
                        <div>
                            <p class="text-sm text-gray-600">Total Ibu Hamil</p>
                            <p class="text-2xl font-bold text-pink-600 mt-1">{{ $posyandu->sasaran_ibuhamil->count() }}</p>
                        </div>
                        <i class="ph ph-heart text-4xl text-pink-300"></i>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-yellow-50 rounded-lg">
                        <div>
                            <p class="text-sm text-gray-600">Total Pralansia</p>
                            <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $posyandu->sasaran_pralansia->count() }}</p>
                        </div>
                        <i class="ph ph-user-circle text-4xl text-yellow-300"></i>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-indigo-50 rounded-lg">
                        <div>
                            <p class="text-sm text-gray-600">Total Lansia</p>
                            <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $posyandu->sasaran_lansia->count() }}</p>
                        </div>
                        <i class="ph ph-user-gear text-4xl text-indigo-300"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Daftar Kader --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="ph ph-users text-2xl mr-3 text-primary"></i>
                    Daftar Kader
                </h2>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-500">{{ $posyandu->kader->count() }} kader</span>
                    <button wire:click="openKaderModal"
                            class="flex items-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="ph ph-plus-circle text-lg mr-2"></i>
                        Tambah Kader
                    </button>
                </div>
            </div>
            @if($posyandu->kader->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">NIK</th>
                                <th class="px-6 py-3">Nama</th>
                                <th class="px-6 py-3">Jabatan</th>
                                <th class="px-6 py-3">Tanggal Lahir</th>
                                <th class="px-6 py-3">Alamat</th>
                                <th class="px-6 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($posyandu->kader as $kader)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $kader->nik_kader ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $kader->user->name ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ $kader->jabatan_kader ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">{{ $kader->tanggal_lahir ? \Carbon\Carbon::parse($kader->tanggal_lahir)->format('d/m/Y') : '-' }}</td>
                                <td class="px-6 py-4">{{ $kader->alamat_kader ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <button wire:click="editKader({{ $kader->id_kader }})"
                                                class="text-blue-600 hover:text-blue-800 transition-colors"
                                                title="Edit">
                                            <i class="ph ph-pencil-simple text-xl"></i>
                                        </button>
                                        <button wire:click="deleteKader({{ $kader->id_kader }})"
                                                wire:confirm="Apakah Anda yakin ingin menghapus kader ini?"
                                                class="text-red-600 hover:text-red-800 transition-colors"
                                                title="Hapus">
                                            <i class="ph ph-trash text-xl"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <i class="ph ph-users text-4xl mb-2"></i>
                    <p>Belum ada kader terdaftar</p>
                </div>
            @endif
        </div>

        {{-- Daftar Sasaran --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="ph ph-baby text-2xl mr-3 text-primary"></i>
                    Daftar Sasaran
                </h2>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-500">{{ $posyandu->sasaran_bayibalita->count() }} sasaran</span>
                    <button wire:click="openBalitaModal()"
                            class="flex items-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="ph ph-plus-circle text-lg mr-2"></i>
                        Tambah Sasaran
                    </button>
                </div>
            </div>
            @if($posyandu->sasaran_bayibalita->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">NIK</th>
                                <th class="px-6 py-3">Nama</th>
                                <th class="px-6 py-3">Tanggal Lahir</th>
                                <th class="px-6 py-3">Jenis Kelamin</th>
                                <th class="px-6 py-3">Umur</th>
                                <th class="px-6 py-3">Alamat</th>
                                <th class="px-6 py-3">Kepersertaan BPJS</th>
                                <th class="px-6 py-3">Nomor BPJS</th>
                                <th class="px-6 py-3">Nomor Telepon</th>
                                <th class="px-6 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($posyandu->sasaran_bayibalita as $sasaran)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $sasaran->nik_sasaran ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->nama_sasaran ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->tanggal_lahir ? \Carbon\Carbon::parse($sasaran->tanggal_lahir)->format('d/m/Y') : '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->jenis_kelamin ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->umur_sasaran ?? '-' }} tahun</td>
                                <td class="px-6 py-4">{{ $sasaran->alamat_sasaran ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    @if($sasaran->kepersertaan_bpjs)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $sasaran->kepersertaan_bpjs == 'PBI' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $sasaran->kepersertaan_bpjs }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">{{ $sasaran->nomor_bpjs ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->nomor_telepon ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <button wire:click="editBalita({{ $sasaran->id_sasaran }})"
                                                class="text-blue-600 hover:text-blue-800 transition-colors"
                                                title="Edit">
                                            <i class="ph ph-pencil-simple text-xl"></i>
                                        </button>
                                        <button wire:click="deleteBalita({{ $sasaran->id_sasaran }})"
                                                wire:confirm="Apakah Anda yakin ingin menghapus sasaran ini?"
                                                class="text-red-600 hover:text-red-800 transition-colors"
                                                title="Hapus">
                                            <i class="ph ph-trash text-xl"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <i class="ph ph-baby text-4xl mb-2"></i>
                    <p>Belum ada sasaran terdaftar</p>
                </div>
            @endif
        </div>

        {{-- Daftar Sasaran Remaja --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="ph ph-user text-2xl mr-3 text-primary"></i>
                    Daftar Sasaran Remaja
                </h2>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-500">{{ $posyandu->sasaran_remaja->count() }} sasaran</span>
                    <button wire:click="openRemajaModal()"
                            class="flex items-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="ph ph-plus-circle text-lg mr-2"></i>
                        Tambah Sasaran
                    </button>
                </div>
            </div>
            @if($posyandu->sasaran_remaja->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">NIK</th>
                                <th class="px-6 py-3">Nama</th>
                                <th class="px-6 py-3">Tanggal Lahir</th>
                                <th class="px-6 py-3">Jenis Kelamin</th>
                                <th class="px-6 py-3">Umur</th>
                                <th class="px-6 py-3">Alamat</th>
                                <th class="px-6 py-3">Kepersertaan BPJS</th>
                                <th class="px-6 py-3">Nomor BPJS</th>
                                <th class="px-6 py-3">Nomor Telepon</th>
                                <th class="px-6 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($posyandu->sasaran_remaja as $sasaran)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $sasaran->nik_sasaran ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->nama_sasaran ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->tanggal_lahir ? \Carbon\Carbon::parse($sasaran->tanggal_lahir)->format('d/m/Y') : '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->jenis_kelamin ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->umur_sasaran ?? '-' }} tahun</td>
                                <td class="px-6 py-4">{{ $sasaran->alamat_sasaran ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    @if($sasaran->kepersertaan_bpjs)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $sasaran->kepersertaan_bpjs == 'PBI' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $sasaran->kepersertaan_bpjs }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">{{ $sasaran->nomor_bpjs ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->nomor_telepon ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <button wire:click="editRemaja({{ $sasaran->id_sasaran }})"
                                                class="text-blue-600 hover:text-blue-800 transition-colors"
                                                title="Edit">
                                            <i class="ph ph-pencil-simple text-xl"></i>
                                        </button>
                                        <button wire:click="deleteRemaja({{ $sasaran->id_sasaran }})"
                                                wire:confirm="Apakah Anda yakin ingin menghapus sasaran ini?"
                                                class="text-red-600 hover:text-red-800 transition-colors"
                                                title="Hapus">
                                            <i class="ph ph-trash text-xl"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <i class="ph ph-user text-4xl mb-2"></i>
                    <p>Belum ada sasaran remaja terdaftar</p>
                </div>
            @endif
        </div>

        {{-- Daftar Sasaran Dewasa --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="ph ph-users text-2xl mr-3 text-primary"></i>
                    Daftar Sasaran Dewasa
                </h2>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-500">{{ $posyandu->sasaran_dewasa->count() }} sasaran</span>
                    <button wire:click="openDewasaModal()"
                            class="flex items-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="ph ph-plus-circle text-lg mr-2"></i>
                        Tambah Sasaran
                    </button>
                </div>
            </div>
            @if($posyandu->sasaran_dewasa->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">NIK</th>
                                <th class="px-6 py-3">Nama</th>
                                <th class="px-6 py-3">Tanggal Lahir</th>
                                <th class="px-6 py-3">Jenis Kelamin</th>
                                <th class="px-6 py-3">Umur</th>
                                <th class="px-6 py-3">Alamat</th>
                                <th class="px-6 py-3">Kepersertaan BPJS</th>
                                <th class="px-6 py-3">Nomor BPJS</th>
                                <th class="px-6 py-3">Nomor Telepon</th>
                                <th class="px-6 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($posyandu->sasaran_dewasa as $sasaran)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $sasaran->nik_sasaran ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->nama_sasaran ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->tanggal_lahir ? \Carbon\Carbon::parse($sasaran->tanggal_lahir)->format('d/m/Y') : '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->jenis_kelamin ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->umur_sasaran ?? '-' }} tahun</td>
                                <td class="px-6 py-4">{{ $sasaran->alamat_sasaran ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    @if($sasaran->kepersertaan_bpjs)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $sasaran->kepersertaan_bpjs == 'PBI' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $sasaran->kepersertaan_bpjs }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">{{ $sasaran->nomor_bpjs ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->nomor_telepon ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <button wire:click="editDewasa({{ $sasaran->id_sasaran }})"
                                                class="text-blue-600 hover:text-blue-800 transition-colors"
                                                title="Edit">
                                            <i class="ph ph-pencil-simple text-xl"></i>
                                        </button>
                                        <button wire:click="deleteDewasa({{ $sasaran->id_sasaran }})"
                                                wire:confirm="Apakah Anda yakin ingin menghapus sasaran ini?"
                                                class="text-red-600 hover:text-red-800 transition-colors"
                                                title="Hapus">
                                            <i class="ph ph-trash text-xl"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <i class="ph ph-users text-4xl mb-2"></i>
                    <p>Belum ada sasaran dewasa terdaftar</p>
                </div>
            @endif
        </div>

        {{-- Daftar Sasaran Ibu Hamil --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="ph ph-heart text-2xl mr-3 text-primary"></i>
                    Daftar Sasaran Ibu Hamil
                </h2>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-500">{{ $posyandu->sasaran_ibuhamil->count() }} sasaran</span>
                    <button wire:click="openIbuHamilModal()"
                            class="flex items-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="ph ph-plus-circle text-lg mr-2"></i>
                        Tambah Sasaran
                    </button>
                </div>
            </div>
            @if($posyandu->sasaran_ibuhamil->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">NIK</th>
                                <th class="px-6 py-3">Nama</th>
                                <th class="px-6 py-3">Tanggal Lahir</th>
                                <th class="px-6 py-3">Jenis Kelamin</th>
                                <th class="px-6 py-3">Umur</th>
                                <th class="px-6 py-3">Alamat</th>
                                <th class="px-6 py-3">Kepersertaan BPJS</th>
                                <th class="px-6 py-3">Nomor BPJS</th>
                                <th class="px-6 py-3">Nomor Telepon</th>
                                <th class="px-6 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($posyandu->sasaran_ibuhamil as $sasaran)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $sasaran->nik_sasaran ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->nama_sasaran ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->tanggal_lahir ? \Carbon\Carbon::parse($sasaran->tanggal_lahir)->format('d/m/Y') : '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->jenis_kelamin ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->umur_sasaran ?? '-' }} tahun</td>
                                <td class="px-6 py-4">{{ $sasaran->alamat_sasaran ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    @if($sasaran->kepersertaan_bpjs)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $sasaran->kepersertaan_bpjs == 'PBI' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $sasaran->kepersertaan_bpjs }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">{{ $sasaran->nomor_bpjs ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->nomor_telepon ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <button wire:click="editIbuHamil({{ $sasaran->id_sasaran }})"
                                                class="text-blue-600 hover:text-blue-800 transition-colors"
                                                title="Edit">
                                            <i class="ph ph-pencil-simple text-xl"></i>
                                        </button>
                                        <button wire:click="deleteIbuHamil({{ $sasaran->id_sasaran }})"
                                                wire:confirm="Apakah Anda yakin ingin menghapus sasaran ini?"
                                                class="text-red-600 hover:text-red-800 transition-colors"
                                                title="Hapus">
                                            <i class="ph ph-trash text-xl"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <i class="ph ph-heart text-4xl mb-2"></i>
                    <p>Belum ada sasaran ibu hamil terdaftar</p>
                </div>
            @endif
        </div>

        {{-- Daftar Sasaran Pralansia --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="ph ph-user-circle text-2xl mr-3 text-primary"></i>
                    Daftar Sasaran Pralansia
                </h2>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-500">{{ $posyandu->sasaran_pralansia->count() }} sasaran</span>
                    <button wire:click="openPralansiaModal()"
                            class="flex items-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="ph ph-plus-circle text-lg mr-2"></i>
                        Tambah Sasaran
                    </button>
                </div>
            </div>
            @if($posyandu->sasaran_pralansia->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">NIK</th>
                                <th class="px-6 py-3">Nama</th>
                                <th class="px-6 py-3">Tanggal Lahir</th>
                                <th class="px-6 py-3">Jenis Kelamin</th>
                                <th class="px-6 py-3">Umur</th>
                                <th class="px-6 py-3">Alamat</th>
                                <th class="px-6 py-3">Kepersertaan BPJS</th>
                                <th class="px-6 py-3">Nomor BPJS</th>
                                <th class="px-6 py-3">Nomor Telepon</th>
                                <th class="px-6 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($posyandu->sasaran_pralansia as $sasaran)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $sasaran->nik_sasaran ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->nama_sasaran ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->tanggal_lahir ? \Carbon\Carbon::parse($sasaran->tanggal_lahir)->format('d/m/Y') : '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->jenis_kelamin ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->umur_sasaran ?? '-' }} tahun</td>
                                <td class="px-6 py-4">{{ $sasaran->alamat_sasaran ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    @if($sasaran->kepersertaan_bpjs)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $sasaran->kepersertaan_bpjs == 'PBI' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $sasaran->kepersertaan_bpjs }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">{{ $sasaran->nomor_bpjs ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->nomor_telepon ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <button wire:click="editPralansia({{ $sasaran->id_sasaran }})"
                                                class="text-blue-600 hover:text-blue-800 transition-colors"
                                                title="Edit">
                                            <i class="ph ph-pencil-simple text-xl"></i>
                                        </button>
                                        <button wire:click="deletePralansia({{ $sasaran->id_sasaran }})"
                                                wire:confirm="Apakah Anda yakin ingin menghapus sasaran ini?"
                                                class="text-red-600 hover:text-red-800 transition-colors"
                                                title="Hapus">
                                            <i class="ph ph-trash text-xl"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <i class="ph ph-user-circle text-4xl mb-2"></i>
                    <p>Belum ada sasaran pralansia terdaftar</p>
                </div>
            @endif
        </div>

        {{-- Daftar Sasaran Lansia --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="ph ph-user-gear text-2xl mr-3 text-primary"></i>
                    Daftar Sasaran Lansia
                </h2>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-500">{{ $posyandu->sasaran_lansia->count() }} sasaran</span>
                    <button wire:click="openLansiaModal()"
                            class="flex items-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="ph ph-plus-circle text-lg mr-2"></i>
                        Tambah Sasaran
                    </button>
                </div>
            </div>
            @if($posyandu->sasaran_lansia->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">NIK</th>
                                <th class="px-6 py-3">Nama</th>
                                <th class="px-6 py-3">Tanggal Lahir</th>
                                <th class="px-6 py-3">Jenis Kelamin</th>
                                <th class="px-6 py-3">Umur</th>
                                <th class="px-6 py-3">Alamat</th>
                                <th class="px-6 py-3">Kepersertaan BPJS</th>
                                <th class="px-6 py-3">Nomor BPJS</th>
                                <th class="px-6 py-3">Nomor Telepon</th>
                                <th class="px-6 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($posyandu->sasaran_lansia as $sasaran)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $sasaran->nik_sasaran ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->nama_sasaran ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->tanggal_lahir ? \Carbon\Carbon::parse($sasaran->tanggal_lahir)->format('d/m/Y') : '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->jenis_kelamin ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->umur_sasaran ?? '-' }} tahun</td>
                                <td class="px-6 py-4">{{ $sasaran->alamat_sasaran ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    @if($sasaran->kepersertaan_bpjs)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $sasaran->kepersertaan_bpjs == 'PBI' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $sasaran->kepersertaan_bpjs }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">{{ $sasaran->nomor_bpjs ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->nomor_telepon ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <button wire:click="editLansia({{ $sasaran->id_sasaran }})"
                                                class="text-blue-600 hover:text-blue-800 transition-colors"
                                                title="Edit">
                                            <i class="ph ph-pencil-simple text-xl"></i>
                                        </button>
                                        <button wire:click="deleteLansia({{ $sasaran->id_sasaran }})"
                                                wire:confirm="Apakah Anda yakin ingin menghapus sasaran ini?"
                                                class="text-red-600 hover:text-red-800 transition-colors"
                                                title="Hapus">
                                            <i class="ph ph-trash text-xl"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <i class="ph ph-user-gear text-4xl mb-2"></i>
                    <p>Belum ada sasaran lansia terdaftar</p>
                </div>
            @endif
        </div>

        {{-- Pesan Sukses --}}
        @if (session()->has('message'))
            <div class="fixed top-20 right-6 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-lg" role="alert">
                <div class="flex items-center">
                    <i class="ph ph-check-circle text-xl mr-2"></i>
                    <span>{{ session('message') }}</span>
                </div>
            </div>
        @endif

        {{-- Modal Form Kader --}}
        @if($isKaderModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Background Overlay --}}
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeKaderModal" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Modal Panel --}}
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit.prevent="storeKader">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                {{ $id_kader ? 'Edit Data Kader' : 'Tambah Kader Baru' }}
                            </h3>

                            <div class="grid grid-cols-1 gap-4">
                                {{-- Nama Kader (User) --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Nama Kader <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="nama_kader" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Nama Lengkap Kader">
                                    @error('nama_kader') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                {{-- Email Kader (User) --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Email (Untuk Login) <span class="text-red-500">*</span></label>
                                    <input type="email" wire:model="email_kader" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Email">
                                    @error('email_kader') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                {{-- Password (User) --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Password <span class="text-red-500">*</span></label>
                                    <input type="password" wire:model="password_kader" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Password">
                                    @error('password_kader') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <hr class="my-2" />

                                {{-- NIK Kader --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">NIK Kader <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="nik_kader" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="NIK Kader">
                                    @error('nik_kader') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>

                                {{-- Tanggal Lahir --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Lahir <span class="text-red-500">*</span></label>
                                    <input type="date" wire:model="tanggal_lahir_kader" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                    @error('tanggal_lahir_kader') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>

                                {{-- Alamat Kader --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Alamat Kader <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="alamat_kader" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Alamat Kader">
                                    @error('alamat_kader') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>

                                {{-- Jabatan Kader --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Jabatan Kader <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="jabatan_kader" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Jabatan Kader">
                                    @error('jabatan_kader') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>

                                {{-- Pilih Posyandu --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Tugaskan di Posyandu Mana? <span class="text-red-500">*</span></label>
                                    <select wire:model="posyandu_id_kader" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                        <option value="">Pilih Posyandu...</option>
                                        @foreach($dataPosyandu as $posyanduOpt)
                                            <option value="{{ $posyanduOpt->id_posyandu ?? $posyanduOpt->id }}">{{ $posyanduOpt->nama_posyandu }}</option>
                                        @endforeach
                                    </select>
                                    @error('posyandu_id_kader') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                Simpan Data
                            </button>
                            <button type="button" wire:click="closeKaderModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        {{-- Modal Form Sasaran --}}
        @if($isSasaranBalitaModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Background Overlay --}}
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeBalitaModal()" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Modal Panel --}}
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full max-h-[90vh] overflow-y-auto">
                    <form wire:submit.prevent="storeBalita">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                {{ $id_sasaran_bayi_balita ? 'Edit Data Sasaran' : 'Tambah Sasaran Baru' }}
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Input Nama Sasaran --}}
                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Nama Sasaran <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="nama_sasaran" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Masukkan nama lengkap sasaran">
                                    @error('nama_sasaran') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>

                                {{-- Input NIK Sasaran --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">NIK Sasaran <span class="text-red-500">*</span></label>
                                    <input type="number" wire:model="nik_sasaran" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="NIK Sasaran">
                                    @error('nik_sasaran') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>

                                {{-- Input No KK --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">No. KK</label>
                                    <input type="number" wire:model="no_kk_sasaran" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="No. KK (opsional)">
                                    @error('no_kk_sasaran') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>

                                {{-- Input Tempat Lahir --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Tempat Lahir</label>
                                    <input type="text" wire:model="tempat_lahir" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Contoh: Bandung">
                                    @error('tempat_lahir') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>

                                {{-- Input Tanggal Lahir --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Lahir <span class="text-red-500">*</span></label>
                                    <input type="date" wire:model="tanggal_lahir_sasaran" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                    @error('tanggal_lahir_sasaran') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>

                                {{-- Input Jenis Kelamin --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                                    <select wire:model="jenis_kelamin" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                        <option value="">Pilih Jenis Kelamin...</option>
                                        <option value="Laki-laki">Laki-laki</option>
                                        <option value="Perempuan">Perempuan</option>
                                    </select>
                                    @error('jenis_kelamin') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>

                                {{-- Input Umur --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Umur (tahun)</label>
                                    <input type="number" wire:model="umur_sasaran" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary bg-gray-100" id="input-umur-sasaran">
                                    @error('umur_sasaran') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>

                                {{-- Input NIK Orangtua --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">NIK Orangtua</label>
                                    <input type="number" wire:model="nik_orangtua" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="NIK salah satu orangtua">
                                    @error('nik_orangtua') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>

                                {{-- Input Alamat --}}
                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Alamat Lengkap <span class="text-red-500">*</span></label>
                                    <textarea wire:model="alamat_sasaran" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Alamat lengkap sasaran"></textarea>
                                    @error('alamat_sasaran') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>

                                {{-- Input Kepersertaan BPJS --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Kepersertaan BPJS</label>
                                    <select wire:model="kepersertaan_bpjs" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                        <option value="">Pilih...</option>
                                        <option value="PBI">PBI</option>
                                        <option value="NON PBI">NON PBI</option>
                                    </select>
                                    @error('kepersertaan_bpjs') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>

                                {{-- Input Nomor BPJS --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Nomor BPJS</label>
                                    <input type="text" wire:model="nomor_bpjs" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Nomor kartu BPJS">
                                    @error('nomor_bpjs') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>

                                {{-- Input Nomor Telepon --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Nomor Telepon</label>
                                    <input type="text" wire:model="nomor_telepon" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Nomor WA/Telp">
                                    @error('nomor_telepon') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>

                                {{-- Input User (Optional) --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Akun User (Opsional)</label>
                                    <select wire:model="id_users_sasaran" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                        <option value="">Pilih User...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                        @endforeach
                                    </select>
                                    @error('id_users_sasaran') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        {{-- Footer Modal (Tombol) --}}
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                Simpan
                            </button>
                            <button wire:click="closeBalitaModal()" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        {{-- Modal Form Sasaran Remaja --}}
        @if($isSasaranRemajaModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeRemajaModal()" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full max-h-[90vh] overflow-y-auto">
                    <form wire:submit.prevent="storeRemaja">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                {{ $id_sasaran_remaja ? 'Edit Data Sasaran Remaja' : 'Tambah Sasaran Remaja Baru' }}
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Nama Sasaran <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="nama_sasaran_remaja" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Masukkan nama lengkap sasaran">
                                    @error('nama_sasaran_remaja') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">NIK Sasaran <span class="text-red-500">*</span></label>
                                    <input type="number" wire:model="nik_sasaran_remaja" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="NIK Sasaran">
                                    @error('nik_sasaran_remaja') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">No. KK</label>
                                    <input type="number" wire:model="no_kk_sasaran_remaja" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="No. KK (opsional)">
                                    @error('no_kk_sasaran_remaja') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Tempat Lahir</label>
                                    <input type="text" wire:model="tempat_lahir_remaja" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Contoh: Bandung">
                                    @error('tempat_lahir_remaja') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Lahir <span class="text-red-500">*</span></label>
                                    <input type="date" wire:model="tanggal_lahir_remaja" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                    @error('tanggal_lahir_remaja') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                                    <select wire:model="jenis_kelamin_remaja" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                        <option value="">Pilih Jenis Kelamin...</option>
                                        <option value="Laki-laki">Laki-laki</option>
                                        <option value="Perempuan">Perempuan</option>
                                    </select>
                                    @error('jenis_kelamin_remaja') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Umur (tahun)</label>
                                    <input type="number" wire:model="umur_sasaran_remaja" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary bg-gray-100">
                                    @error('umur_sasaran_remaja') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">NIK Orangtua</label>
                                    <input type="number" wire:model="nik_orangtua_remaja" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="NIK salah satu orangtua">
                                    @error('nik_orangtua_remaja') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Alamat Lengkap <span class="text-red-500">*</span></label>
                                    <textarea wire:model="alamat_sasaran_remaja" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Alamat lengkap sasaran"></textarea>
                                    @error('alamat_sasaran_remaja') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Kepersertaan BPJS</label>
                                    <select wire:model="kepersertaan_bpjs_remaja" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                        <option value="">Pilih...</option>
                                        <option value="PBI">PBI</option>
                                        <option value="NON PBI">NON PBI</option>
                                    </select>
                                    @error('kepersertaan_bpjs_remaja') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Nomor BPJS</label>
                                    <input type="text" wire:model="nomor_bpjs_remaja" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Nomor kartu BPJS">
                                    @error('nomor_bpjs_remaja') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Nomor Telepon</label>
                                    <input type="text" wire:model="nomor_telepon_remaja" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Nomor WA/Telp">
                                    @error('nomor_telepon_remaja') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Akun User (Opsional)</label>
                                    <select wire:model="id_users_sasaran_remaja" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                        <option value="">Pilih User...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                        @endforeach
                                    </select>
                                    @error('id_users_sasaran_remaja') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                Simpan
                            </button>
                            <button wire:click="closeRemajaModal()" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        {{-- Modal Form Sasaran Dewasa --}}
        @if($isSasaranDewasaModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeDewasaModal()" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full max-h-[90vh] overflow-y-auto">
                    <form wire:submit.prevent="storeDewasa">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                {{ $id_sasaran_dewasa ? 'Edit Data Sasaran Dewasa' : 'Tambah Sasaran Dewasa Baru' }}
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Nama Sasaran <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="nama_sasaran_dewasa" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Masukkan nama lengkap sasaran">
                                    @error('nama_sasaran_dewasa') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">NIK Sasaran <span class="text-red-500">*</span></label>
                                    <input type="number" wire:model="nik_sasaran_dewasa" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="NIK Sasaran">
                                    @error('nik_sasaran_dewasa') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">No. KK</label>
                                    <input type="number" wire:model="no_kk_sasaran_dewasa" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="No. KK (opsional)">
                                    @error('no_kk_sasaran_dewasa') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Tempat Lahir</label>
                                    <input type="text" wire:model="tempat_lahir_dewasa" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Contoh: Bandung">
                                    @error('tempat_lahir_dewasa') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Lahir <span class="text-red-500">*</span></label>
                                    <input type="date" wire:model="tanggal_lahir_dewasa" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                    @error('tanggal_lahir_dewasa') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                                    <select wire:model="jenis_kelamin_dewasa" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                        <option value="">Pilih Jenis Kelamin...</option>
                                        <option value="Laki-laki">Laki-laki</option>
                                        <option value="Perempuan">Perempuan</option>
                                    </select>
                                    @error('jenis_kelamin_dewasa') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Umur (tahun)</label>
                                    <input type="number" wire:model="umur_sasaran_dewasa" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary bg-gray-100">
                                    @error('umur_sasaran_dewasa') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">NIK Orangtua</label>
                                    <input type="number" wire:model="nik_orangtua_dewasa" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="NIK salah satu orangtua">
                                    @error('nik_orangtua_dewasa') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Alamat Lengkap <span class="text-red-500">*</span></label>
                                    <textarea wire:model="alamat_sasaran_dewasa" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Alamat lengkap sasaran"></textarea>
                                    @error('alamat_sasaran_dewasa') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Kepersertaan BPJS</label>
                                    <select wire:model="kepersertaan_bpjs_dewasa" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                        <option value="">Pilih...</option>
                                        <option value="PBI">PBI</option>
                                        <option value="NON PBI">NON PBI</option>
                                    </select>
                                    @error('kepersertaan_bpjs_dewasa') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Nomor BPJS</label>
                                    <input type="text" wire:model="nomor_bpjs_dewasa" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Nomor kartu BPJS">
                                    @error('nomor_bpjs_dewasa') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Nomor Telepon</label>
                                    <input type="text" wire:model="nomor_telepon_dewasa" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Nomor WA/Telp">
                                    @error('nomor_telepon_dewasa') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Akun User (Opsional)</label>
                                    <select wire:model="id_users_sasaran_dewasa" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                        <option value="">Pilih User...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                        @endforeach
                                    </select>
                                    @error('id_users_sasaran_dewasa') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                Simpan
                            </button>
                            <button wire:click="closeDewasaModal()" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

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
                                    <input type="number" wire:model="nik_sasaran_ibuhamil" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="NIK Sasaran">
                                    @error('nik_sasaran_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
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
                                    <input type="date" wire:model="tanggal_lahir_ibuhamil" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
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
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Umur (tahun)</label>
                                    <input type="number" wire:model="umur_sasaran_ibuhamil" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary bg-gray-100">
                                    @error('umur_sasaran_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">NIK Orangtua</label>
                                    <input type="number" wire:model="nik_orangtua_ibuhamil" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="NIK salah satu orangtua">
                                    @error('nik_orangtua_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Alamat Lengkap <span class="text-red-500">*</span></label>
                                    <textarea wire:model="alamat_sasaran_ibuhamil" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Alamat lengkap sasaran"></textarea>
                                    @error('alamat_sasaran_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
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
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Akun User (Opsional)</label>
                                    <select wire:model="id_users_sasaran_ibuhamil" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                        <option value="">Pilih User...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                        @endforeach
                                    </select>
                                    @error('id_users_sasaran_ibuhamil') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
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
                                    <input type="number" wire:model="umur_sasaran_pralansia" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary bg-gray-100">
                                    @error('umur_sasaran_pralansia') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
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
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Akun User (Opsional)</label>
                                    <select wire:model="id_users_sasaran_pralansia" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                        <option value="">Pilih User...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                        @endforeach
                                    </select>
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

        {{-- Modal Form Sasaran Lansia --}}
        @if($isSasaranLansiaModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeLansiaModal()" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full max-h-[90vh] overflow-y-auto">
                    <form wire:submit.prevent="storeLansia">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                {{ $id_sasaran_lansia ? 'Edit Data Sasaran Lansia' : 'Tambah Sasaran Lansia Baru' }}
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Nama Sasaran <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="nama_sasaran_lansia" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Masukkan nama lengkap sasaran">
                                    @error('nama_sasaran_lansia') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">NIK Sasaran <span class="text-red-500">*</span></label>
                                    <input type="number" wire:model="nik_sasaran_lansia" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="NIK Sasaran">
                                    @error('nik_sasaran_lansia') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">No. KK</label>
                                    <input type="number" wire:model="no_kk_sasaran_lansia" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="No. KK (opsional)">
                                    @error('no_kk_sasaran_lansia') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Tempat Lahir</label>
                                    <input type="text" wire:model="tempat_lahir_lansia" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Contoh: Bandung">
                                    @error('tempat_lahir_lansia') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Lahir <span class="text-red-500">*</span></label>
                                    <input type="date" wire:model="tanggal_lahir_lansia" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                    @error('tanggal_lahir_lansia') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                                    <select wire:model="jenis_kelamin_lansia" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                        <option value="">Pilih Jenis Kelamin...</option>
                                        <option value="Laki-laki">Laki-laki</option>
                                        <option value="Perempuan">Perempuan</option>
                                    </select>
                                    @error('jenis_kelamin_lansia') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Umur (tahun)</label>
                                    <input type="number" wire:model="umur_sasaran_lansia" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary bg-gray-100">
                                    @error('umur_sasaran_lansia') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">NIK Orangtua</label>
                                    <input type="number" wire:model="nik_orangtua_lansia" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="NIK salah satu orangtua">
                                    @error('nik_orangtua_lansia') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Alamat Lengkap <span class="text-red-500">*</span></label>
                                    <textarea wire:model="alamat_sasaran_lansia" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Alamat lengkap sasaran"></textarea>
                                    @error('alamat_sasaran_lansia') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Kepersertaan BPJS</label>
                                    <select wire:model="kepersertaan_bpjs_lansia" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                        <option value="">Pilih...</option>
                                        <option value="PBI">PBI</option>
                                        <option value="NON PBI">NON PBI</option>
                                    </select>
                                    @error('kepersertaan_bpjs_lansia') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Nomor BPJS</label>
                                    <input type="text" wire:model="nomor_bpjs_lansia" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Nomor kartu BPJS">
                                    @error('nomor_bpjs_lansia') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Nomor Telepon</label>
                                    <input type="text" wire:model="nomor_telepon_lansia" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary" placeholder="Nomor WA/Telp">
                                    @error('nomor_telepon_lansia') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Akun User (Opsional)</label>
                                    <select wire:model="id_users_sasaran_lansia" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                        <option value="">Pilih User...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                        @endforeach
                                    </select>
                                    @error('id_users_sasaran_lansia') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                Simpan
                            </button>
                            <button wire:click="closeLansiaModal()" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
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

<script>
    // Auto-hide pesan sukses setelah 5 detik
    document.addEventListener('livewire:init', () => {
        Livewire.on('message', () => {
            setTimeout(() => {
                const messageEl = document.querySelector('[role="alert"]');
                if (messageEl) {
                    messageEl.style.transition = 'opacity 0.5s';
                    messageEl.style.opacity = '0';
                    setTimeout(() => messageEl.remove(), 500);
                }
            }, 5000);
        });
    });
</script>
