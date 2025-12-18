{{-- Pesan Sukses/Error --}}
@if (session()->has('message'))
    <div class="fixed top-4 right-4 z-50 animate-slide-in-right">
        <div class="bg-white rounded-lg shadow-lg border-l-4 {{ session('messageType') === 'error' ? 'border-red-500' : 'border-green-500' }} p-4 max-w-md">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    @if (session('messageType') === 'error')
                        <i class="ph ph-x-circle text-red-500 text-2xl"></i>
                    @else
                        <i class="ph ph-check-circle text-green-500 text-2xl"></i>
                    @endif
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium {{ session('messageType') === 'error' ? 'text-red-800' : 'text-green-800' }}">
                        {{ session('message') }}
                    </p>
                </div>
                <div class="ml-auto pl-3">
                    <button wire:click="$dispatch('dismissFlash')" class="text-gray-400 hover:text-gray-600">
                        <i class="ph ph-x"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

