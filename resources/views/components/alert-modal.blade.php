{{-- Modal Alert --}}
<div x-data="{ 
    show: false,
    message: '',
    type: 'info'
}" 
x-show="show"
x-cloak
class="fixed inset-0 z-50 overflow-y-auto"
style="display: none;"
x-on:show-alert.window="
    message = $event.detail.message;
    type = $event.detail.type || 'info';
    show = true;
    setTimeout(() => { show = false; }, 3000);
">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="show" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
             x-on:click="show = false"></div>

        <div x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
            
            <div class="px-6 py-4 flex items-center justify-between" 
                 :class="{
                     'bg-blue-500': type === 'info',
                     'bg-yellow-500': type === 'warning',
                     'bg-red-500': type === 'error',
                     'bg-green-500': type === 'success'
                 }">
                <div class="flex items-center">
                    <i class="ph text-2xl text-white mr-3" 
                       :class="{
                           'ph-info': type === 'info',
                           'ph-warning': type === 'warning',
                           'ph-x-circle': type === 'error',
                           'ph-check-circle': type === 'success'
                       }"></i>
                    <h3 class="text-lg font-semibold text-white">
                        <span x-text="type === 'info' ? 'Informasi' : type === 'warning' ? 'Peringatan' : type === 'error' ? 'Error' : 'Berhasil'"></span>
                    </h3>
                </div>
                <button x-on:click="show = false" class="text-white hover:text-gray-200">
                    <i class="ph ph-x text-xl"></i>
                </button>
            </div>

            <div class="bg-white px-6 py-4">
                <p class="text-gray-700" x-text="message"></p>
            </div>

            <div class="bg-gray-50 px-6 py-4 flex justify-end">
                <button 
                    x-on:click="show = false"
                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
