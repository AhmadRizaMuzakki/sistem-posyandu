{{-- Modal Alert â€” tengah layar --}}
<div
    x-data="{
        show: false,
        message: '',
        type: 'info'
    }"
    x-show="show"
    x-cloak
    x-on:show-alert.window="
        message = $event.detail.message;
        type = $event.detail.type || 'info';
        show = true;
        setTimeout(() => { show = false; }, 5000);
    "
    class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
    style="display: none;"
    role="dialog"
    aria-modal="true"
>
    <div
        x-show="show"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/50"
        x-on:click="show = false"
    ></div>

    <div
        x-show="show"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="relative w-full max-w-md bg-white rounded-xl shadow-2xl overflow-hidden text-left"
        x-on:click.stop
    >
        <div
            class="px-6 py-4 flex items-center justify-between"
            :class="{
                'bg-blue-500': type === 'info',
                'bg-yellow-500': type === 'warning',
                'bg-red-500': type === 'error',
                'bg-green-500': type === 'success'
            }"
        >
            <div class="flex items-center min-w-0">
                <i
                    class="ph text-2xl text-white mr-3 shrink-0"
                    :class="{
                        'ph-info': type === 'info',
                        'ph-warning': type === 'warning',
                        'ph-x-circle': type === 'error',
                        'ph-check-circle': type === 'success'
                    }"
                ></i>
                <h3 class="text-lg font-semibold text-white">
                    <span x-text="type === 'info' ? 'Informasi' : type === 'warning' ? 'Peringatan' : type === 'error' ? 'Error' : 'Berhasil'"></span>
                </h3>
            </div>
            <button type="button" x-on:click="show = false" class="text-white hover:text-gray-200 shrink-0 ml-2">
                <i class="ph ph-x text-xl"></i>
            </button>
        </div>

        <div class="px-6 py-5">
            <p class="text-gray-700 text-sm leading-relaxed" x-text="message"></p>
        </div>

        <div class="bg-gray-50 px-6 py-4 flex justify-center border-t border-gray-100">
            <button
                type="button"
                x-on:click="show = false"
                class="px-6 py-2.5 bg-gray-700 text-white rounded-lg hover:bg-gray-800 transition-colors font-medium text-sm"
            >
                Tutup
            </button>
        </div>
    </div>
</div>
