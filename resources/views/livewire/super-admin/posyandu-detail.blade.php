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
                            <p class="text-sm text-gray-600">Total Sasaran</p>
                            <p class="text-2xl font-bold text-green-600 mt-1">{{ $posyandu->sasaran->count() }}</p>
                        </div>
                        <i class="ph ph-baby text-4xl text-green-300"></i>
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
                    <button wire:click="openKaderModal()"
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
                    <span class="text-sm text-gray-500">{{ $posyandu->sasaran->count() }} sasaran</span>
                    <button wire:click="openSasaranModal()"
                            class="flex items-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="ph ph-plus-circle text-lg mr-2"></i>
                        Tambah Sasaran
                    </button>
                </div>
            </div>
            @if($posyandu->sasaran->count() > 0)
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
                                <th class="px-6 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($posyandu->sasaran as $sasaran)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $sasaran->nik_sasaran ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->nama_sasaran ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->tanggal_lahir ? \Carbon\Carbon::parse($sasaran->tanggal_lahir)->format('d/m/Y') : '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->jenis_kelamin ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $sasaran->umur_sasaran ?? '-' }} tahun</td>
                                <td class="px-6 py-4">{{ $sasaran->alamat_sasaran ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <button wire:click="editSasaran({{ $sasaran->id_sasaran }})"
                                                class="text-blue-600 hover:text-blue-800 transition-colors"
                                                title="Edit">
                                            <i class="ph ph-pencil-simple text-xl"></i>
                                        </button>
                                        <button wire:click="deleteSasaran({{ $sasaran->id_sasaran }})"
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
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeKaderModal()" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Modal Panel --}}
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit.prevent="storeKader">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                {{ $id_kader ? 'Edit Data Kader' : 'Tambah Kader Baru' }}
                            </h3>

                            <div class="grid grid-cols-1 gap-4">
                                {{-- Input NIK --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">NIK Kader <span class="text-red-500">*</span></label>
                                    <input type="number" wire:model="nik_kader" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                    @error('nik_kader') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>

                                {{-- Input User (Relasi) --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Akun User <span class="text-red-500">*</span></label>
                                    <select wire:model="id_users" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                        <option value="">Pilih User...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                        @endforeach
                                    </select>
                                    @error('id_users') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>

                                {{-- Input Jabatan --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Jabatan <span class="text-red-500">*</span></label>
                                    <select wire:model="jabatan_kader" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                        <option value="">Pilih Jabatan...</option>
                                        <option value="Ketua">Ketua</option>
                                        <option value="Sekretaris">Sekretaris</option>
                                        <option value="Bendahara">Bendahara</option>
                                        <option value="Anggota">Anggota</option>
                                    </select>
                                    @error('jabatan_kader') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>

                                {{-- Input Tanggal Lahir --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Lahir <span class="text-red-500">*</span></label>
                                    <input type="date" wire:model="tanggal_lahir" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary">
                                    @error('tanggal_lahir') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>

                                {{-- Input Alamat --}}
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Alamat Lengkap <span class="text-red-500">*</span></label>
                                    <textarea wire:model="alamat_kader" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary"></textarea>
                                    @error('alamat_kader') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        {{-- Footer Modal (Tombol) --}}
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                Simpan
                            </button>
                            <button wire:click="closeKaderModal()" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        {{-- Modal Form Sasaran --}}
        @if($isSasaranModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Background Overlay --}}
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeSasaranModal()" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Modal Panel --}}
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full max-h-[90vh] overflow-y-auto">
                    <form wire:submit.prevent="storeSasaran">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                {{ $id_sasaran ? 'Edit Data Sasaran' : 'Tambah Sasaran Baru' }}
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
                            <button wire:click="closeSasaranModal()" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
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
