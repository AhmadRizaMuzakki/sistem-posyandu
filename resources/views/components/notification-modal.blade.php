{{-- Modal notifikasi Livewire — selaras warna dashboard (primary / amber / red) --}}
@if(isset($showNotificationModal))
@php
    $type = $notificationType ?? 'success';
    $headerClass = match ($type) {
        'warning' => 'bg-amber-500',
        'error' => 'bg-red-600',
        default => 'bg-primary',
    };
    $iconClass = match ($type) {
        'warning' => 'ph-warning',
        'error' => 'ph-x-circle',
        default => 'ph-check-circle',
    };
    $ringClass = match ($type) {
        'warning' => 'bg-amber-50 text-amber-600 ring-amber-100',
        'error' => 'bg-red-50 text-red-600 ring-red-100',
        default => 'bg-teal-50 text-primary ring-teal-100',
    };
    $btnClass = match ($type) {
        'warning' => 'bg-amber-500 hover:bg-amber-600 focus:ring-amber-300',
        'error' => 'bg-red-600 hover:bg-red-700 focus:ring-red-300',
        default => 'bg-primary hover:bg-primaryDark focus:ring-teal-300',
    };
    $title = $notificationTitle ?? match ($type) {
        'warning' => 'Peringatan',
        'error' => 'Terjadi Kesalahan',
        default => 'Berhasil',
    };
@endphp
@teleport('body')
<div
    x-data="{ show: @entangle('showNotificationModal').live }"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-6"
    role="dialog"
    aria-modal="true"
    aria-labelledby="notification-modal-title"
>
    <div
        x-show="show"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-[2px]"
        wire:click="closeNotificationModal"
        aria-hidden="true"
    ></div>

    <div
        x-show="show"
        x-transition:enter="ease-out duration-250"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="relative w-full max-w-sm sm:max-w-md bg-white rounded-2xl shadow-2xl ring-1 ring-black/5 overflow-hidden"
        wire:click.stop
    >
        <div class="{{ $headerClass }} px-5 py-3 flex items-center justify-between">
            <p class="text-sm font-semibold text-white tracking-wide uppercase opacity-95">
                {{ $title }}
            </p>
            <button
                type="button"
                wire:click="closeNotificationModal"
                class="p-1.5 rounded-lg text-white/90 hover:text-white hover:bg-white/15 transition-colors"
                aria-label="Tutup"
            >
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>

        <div class="px-6 pt-8 pb-6 text-center">
            <div
                class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full ring-8 {{ $ringClass }}"
            >
                <i class="ph {{ $iconClass }} text-3xl"></i>
            </div>

            <h2 id="notification-modal-title" class="text-xl font-bold text-gray-900 mb-2">
                {{ $title }}
            </h2>

            <p class="text-gray-600 text-sm sm:text-base leading-relaxed max-w-xs mx-auto">
                {{ $notificationMessage }}
            </p>
        </div>

        <div class="px-6 pb-6">
            <button
                type="button"
                wire:click="closeNotificationModal"
                class="w-full py-3 px-4 rounded-xl text-white text-sm font-semibold shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $btnClass }}"
            >
                Oke, mengerti
            </button>
        </div>
    </div>
</div>
@endteleport
@endif
