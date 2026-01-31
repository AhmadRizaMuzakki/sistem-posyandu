{{-- Daftar Kader --}}
<div class="bg-white rounded-lg shadow-sm p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-gray-800 flex items-center">
            <i class="ph ph-users text-2xl mr-3 text-primary"></i>
            Daftar Kader
        </h2>
        <div class="flex items-center gap-4">
            <span class="text-sm text-gray-500">{{ $posyandu->kader->count() }} kader</span>
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
                <button wire:click="openKaderModal"
                        class="flex items-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="ph ph-plus-circle text-lg mr-2"></i>
                    Tambah Kader
                </button>
            @endif
        </div>
    </div>
    @if($posyandu->kader->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">Foto</th>
                        <th class="px-6 py-3">NIK</th>
                        <th class="px-6 py-3">Nama</th>
                        <th class="px-6 py-3">Tanggal Lahir</th>
                        <th class="px-6 py-3">Alamat</th>
                        <th class="px-6 py-3">Jabatan</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($posyandu->kader as $kader)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4">
                            @if($kader->foto_kader)
                                <img src="{{ uploads_asset($kader->foto_kader) }}" alt="" class="w-10 h-10 rounded-full object-cover border">
                            @else
                                <span class="flex w-10 h-10 rounded-full bg-gray-200 items-center justify-center text-gray-500 text-xs">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $kader->nik_kader ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $kader->nama_kader ?? $kader->user->name ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $kader->tanggal_lahir ? \Carbon\Carbon::parse($kader->tanggal_lahir)->format('d/m/Y') : '-' }}</td>
                        <td class="px-6 py-4">{{ $kader->alamat_kader ?? '-' }}</td>
                        @if($kader->jabatan_kader)
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    {{ $kader->jabatan_kader }}
                                </span>
                            </td>
                        @endif
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

