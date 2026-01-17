{{-- Modal Konfirmasi --}}
@if(isset($showConfirmModal) && $showConfirmModal)
<div x-data="{ 
    show: @entangle('showConfirmModal')
}" 
x-show="show"
x-cloak
class="fixed inset-0 z-50 overflow-y-auto"
style="display: none;"
x-on:close-modal.window="show = false">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        {{-- Overlay --}}
        <div x-show="show" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
             x-on:click="show = false"></div>

        {{-- Modal Panel --}}
        <div x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full"
             x-on:click.away="show = false">
            
            {{-- Header --}}
            <div class="px-6 py-4 flex items-center justify-between bg-yellow-500">
                <div class="flex items-center">
                    <i class="ph ph-warning text-2xl text-white mr-3"></i>
                    <h3 class="text-lg font-semibold text-white">
                        Konfirmasi
                    </h3>
                </div>
                <button wire:click="closeConfirmModal" class="text-white hover:text-gray-200">
                    <i class="ph ph-x text-xl"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="bg-white px-6 py-4">
                <p class="text-gray-700">
                    {{ $confirmMessage ?? 'Apakah Anda yakin?' }}
                </p>
            </div>

            {{-- Footer --}}
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button 
                    wire:click="closeConfirmModal"
                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    Batal
                </button>
                <button 
                    wire:click="{{ $confirmAction ?? 'executeConfirmAction' }}"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>
</div>
@endif
