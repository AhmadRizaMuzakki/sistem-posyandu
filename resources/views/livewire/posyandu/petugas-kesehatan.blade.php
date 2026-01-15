<div>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">{{ $posyandu->nama_posyandu }}</h1>
                    <p class="text-gray-500 mt-1">Manajemen Data Petugas Kesehatan</p>
                </div>
            </div>
        </div>

        {{-- Daftar Petugas Kesehatan --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="ph ph-user-nurse text-2xl mr-3 text-primary"></i>
                    Daftar Petugas Kesehatan
                </h2>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-500">{{ $posyandu->petugas_kesehatan->count() }} petugas kesehatan</span>
                    @php
                        $user = auth()->user();
                        $isSuperadmin = $user->hasRole('superadmin');
                        $isKetua = false;
                        if (!$isSuperadmin) {
                            $kaderUser = \App\Models\Kader::where('id_users', $user->id)
                                ->where('id_posyandu', $posyandu->id_posyandu)
                                ->where('jabatan_kader', 'Ketua')
                                ->first();
                            $isKetua = $kaderUser !== null;
                        }
                    @endphp
                    @if($isSuperadmin || $isKetua)
                        <button wire:click="openPetugasKesehatanModal"
                                class="flex items-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-indigo-700 transition-colors">
                            <i class="ph ph-plus-circle text-lg mr-2"></i>
                            Tambah Petugas Kesehatan
                        </button>
                    @endif
                </div>
            </div>
            @if($posyandu->petugas_kesehatan->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">NIK</th>
                                <th class="px-6 py-3">Nama</th>
                                <th class="px-6 py-3">Tanggal Lahir</th>
                                <th class="px-6 py-3">Alamat</th>
                                <th class="px-6 py-3">Jenis Petugas</th>
                                <th class="px-6 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($posyandu->petugas_kesehatan as $petugasKesehatan)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $petugasKesehatan->nik_petugas_kesehatan ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $petugasKesehatan->nama_petugas_kesehatan ?? $petugasKesehatan->user->name ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $petugasKesehatan->tanggal_lahir ? \Carbon\Carbon::parse($petugasKesehatan->tanggal_lahir)->format('d/m/Y') : '-' }}</td>
                                <td class="px-6 py-4">{{ $petugasKesehatan->alamat_petugas_kesehatan ?? '-' }}</td>
                                @if($petugasKesehatan->bidan)
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $petugasKesehatan->bidan }}
                                        </span>
                                    </td>
                                @else
                                    <td class="px-6 py-4">-</td>
                                @endif
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        @if($isSuperadmin || $isKetua)
                                            <button wire:click="editPetugasKesehatan({{ $petugasKesehatan->id_petugas_kesehatan }})"
                                                    class="text-blue-600 hover:text-blue-800 transition-colors"
                                                    title="Edit">
                                                <i class="ph ph-pencil-simple text-xl"></i>
                                            </button>
                                            <button wire:click="deletePetugasKesehatan({{ $petugasKesehatan->id_petugas_kesehatan }})"
                                                    wire:confirm="Apakah Anda yakin ingin menghapus petugas kesehatan ini?"
                                                    class="text-red-600 hover:text-red-800 transition-colors"
                                                    title="Hapus">
                                                <i class="ph ph-trash text-xl"></i>
                                            </button>
                                        @else
                                            <span class="text-gray-400 text-sm">Tidak ada akses</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <i class="ph ph-user-nurse text-4xl mb-2"></i>
                    <p>Belum ada petugas kesehatan terdaftar</p>
                </div>
            @endif
        </div>

        {{-- Notification Modal --}}
        @include('components.notification-modal')

        {{-- Modal Form Petugas Kesehatan --}}
        @include('livewire.posyandu.modals.petugas-kesehatan-modal')
    </div>
</div>

