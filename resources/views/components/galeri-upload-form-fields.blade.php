{{-- Urutan: Foto → Tanggal → Keterangan --}}
<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Foto <span class="text-red-500">*</span> (bisa pilih banyak)</label>
        <div x-data="{ dragging: false }"
             @dragover.prevent="dragging = true"
             @dragleave.prevent="dragging = false"
             @drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change', { bubbles: true }))"
             class="relative border-2 border-dashed rounded-xl p-8 text-center transition-colors cursor-pointer aspect-square max-w-md mx-auto flex items-center justify-center"
             :class="dragging ? 'border-primary bg-primary/10' : 'border-gray-300 hover:border-primary/50 hover:bg-gray-50'">
            <input type="file" x-ref="fileInput" wire:model="fotoFiles" accept="image/*" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
            <div class="pointer-events-none">
                <i class="ph ph-cloud-arrow-up text-5xl text-gray-400 mb-3 block"></i>
                <p class="text-sm text-gray-600">Drag & drop foto di sini atau <span class="text-primary font-medium">klik untuk memilih</span></p>
                <p class="text-xs text-gray-400 mt-1">JPG, PNG, GIF, WebP • Maks. 2 MB per foto • Bisa banyak sekaligus</p>
            </div>
        </div>
        @if(!empty($fotoFiles) && count($fotoFiles) > 0)
            <p class="mt-2 text-sm text-primary font-medium"><i class="ph ph-check-circle mr-1"></i> {{ count($fotoFiles) }} foto dipilih</p>
        @endif
        @error('fotoFiles') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        @error('fotoFiles.*') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            <i class="ph ph-calendar-blank mr-1 text-primary"></i>
            Tanggal Foto <span class="text-gray-400 font-normal">(opsional)</span>
        </label>
        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
            <div class="relative flex-1">
                <i class="ph ph-calendar absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg pointer-events-none"></i>
                <input type="date"
                       wire:model.live="tanggal_foto"
                       max="{{ date('Y-m-d') }}"
                       class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2.5 text-sm text-gray-800 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all [color-scheme:light]">
            </div>
            <button type="button"
                    wire:click="$set('tanggal_foto', '{{ date('Y-m-d') }}')"
                    class="shrink-0 inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium text-primary border border-primary rounded-lg hover:bg-primary hover:text-white transition-colors whitespace-nowrap">
                <i class="ph ph-calendar-check mr-1.5 text-base"></i>
                Hari ini
            </button>
        </div>
        @if($tanggal_foto)
            <p class="mt-1.5 text-xs text-primary font-medium">
                <i class="ph ph-check-circle mr-0.5"></i>
                {{ \Carbon\Carbon::parse($tanggal_foto)->translatedFormat('d F Y') }}
            </p>
        @else
            <p class="mt-1.5 text-xs text-gray-500">Kosongkan jika tanggal foto tidak perlu dicatat.</p>
        @endif
        @error('tanggal_foto') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan (opsional)</label>
        <input type="text" wire:model="caption" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-primary focus:border-primary" placeholder="Deskripsi foto">
        @error('caption') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
    </div>
</div>
