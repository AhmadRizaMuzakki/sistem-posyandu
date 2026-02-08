<div>
    <div class="p-6 space-y-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Pengaturan</h1>
            <p class="text-gray-500 mt-1">Kelola pengaturan dan lihat log aktivitas sistem</p>
        </div>

        {{-- Log Aktivitas --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="ph ph-list-checks text-xl mr-2 text-primary"></i>
                    Log Aktivitas
                </h2>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 text-gray-400">
                            <i class="ph ph-magnifying-glass text-sm"></i>
                        </span>
                        <input type="text"
                               wire:model.live.debounce.300ms="filterSearch"
                               placeholder="Cari user..."
                               class="pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-primary focus:border-primary w-48 sm:w-56">
                    </div>
                    <select wire:model.live="filterAction"
                            class="py-2 pl-3 pr-8 text-sm border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                        <option value="">Semua aksi</option>
                        @foreach($actionOptions as $opt)
                            <option value="{{ $opt }}">{{ ucfirst($opt) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="overflow-x-auto">
                @if($logs->count() > 0)
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($logs as $log)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $log->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                        {{ $log->user?->name ?? '-' }}
                                        @if($log->user)
                                            <span class="text-gray-400 block text-xs">{{ $log->user->email }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $badge = match($log->action) {
                                                'login' => 'bg-green-100 text-green-800',
                                                'logout' => 'bg-gray-100 text-gray-800',
                                                'create' => 'bg-blue-100 text-blue-800',
                                                'update' => 'bg-amber-100 text-amber-800',
                                                'delete' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800',
                                            };
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $badge }}">{{ ucfirst($log->action) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="px-6 py-3 border-t border-gray-100">
                        {{ $logs->links() }}
                    </div>
                @else
                    <div class="px-6 py-12 text-center text-gray-500">
                        <i class="ph ph-list-checks text-4xl mb-3 opacity-50"></i>
                        <p>Belum ada log aktivitas.</p>
                        <p class="text-sm mt-1">Log akan tercatat saat ada login dan aksi penting lainnya.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
