@props([
    'label' => null,
    'required' => false,
    'hari' => null,
    'bulan' => null,
    'tahun' => null,
    'errorHari' => null,
    'errorBulan' => null,
    'errorTahun' => null,
    'errorTanggal' => null,
    'placeholder' => 'tanggal/bulan/tahun',
])

@php
    $hariModel = $hari;
    $bulanModel = $bulan;
    $tahunModel = $tahun;
    $useParts = filled($hariModel) && filled($bulanModel) && filled($tahunModel);
    $wireModel = $attributes->wire('model');
    $errorField = $errorTanggal ?: ($wireModel ? $wireModel->value() : null);
    $maxYear = (int) date('Y');
@endphp

<div>
    @if($label)
        <label class="block text-gray-700 text-sm font-bold mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    @if($useParts)
        <div
            wire:ignore
            x-data="{
                raw: '',
                maxYear: {{ $maxYear }},
                init() {
                    this.readFromWire();
                },
                digitsOnly(value) {
                    return String(value || '').replace(/\D/g, '').slice(0, 8);
                },
                formatMask(digits) {
                    const d = this.digitsOnly(digits);
                    if (d.length <= 2) return d;
                    if (d.length <= 4) return d.slice(0, 2) + '/' + d.slice(2);
                    return d.slice(0, 2) + '/' + d.slice(2, 4) + '/' + d.slice(4);
                },
                onInput(e) {
                    const el = e.target;
                    const before = el.value;
                    const start = el.selectionStart ?? before.length;
                    const digitsBeforeCursor = this.digitsOnly(before.slice(0, start)).length;
                    const formatted = this.formatMask(before);
                    this.raw = formatted;

                    this.$nextTick(() => {
                        let pos = formatted.length;
                        let seen = 0;
                        for (let i = 0; i < formatted.length; i++) {
                            if (/\d/.test(formatted[i])) {
                                seen++;
                                if (seen >= digitsBeforeCursor) {
                                    pos = i + 1;
                                    break;
                                }
                            }
                        }
                        if (digitsBeforeCursor === 0) pos = 0;
                        el.setSelectionRange(pos, pos);
                    });
                },
                readFromWire() {
                    const h = $wire.get('{{ $hariModel }}');
                    const b = $wire.get('{{ $bulanModel }}');
                    const t = $wire.get('{{ $tahunModel }}');
                    if (!h || !b || !t) {
                        this.raw = '';
                        return;
                    }
                    this.raw = String(h).padStart(2, '0') + '/' + String(b).padStart(2, '0') + '/' + String(t);
                },
                commit() {
                    const digits = this.digitsOnly(this.raw);
                    if (digits === '') {
                        this.raw = '';
                        $wire.set('{{ $hariModel }}', '');
                        $wire.set('{{ $bulanModel }}', '');
                        $wire.set('{{ $tahunModel }}', '');
                        return;
                    }
                    if (digits.length !== 8) {
                        this.raw = this.formatMask(digits);
                        return;
                    }
                    const day = parseInt(digits.slice(0, 2), 10);
                    const month = parseInt(digits.slice(2, 4), 10);
                    const year = parseInt(digits.slice(4, 8), 10);
                    if (day < 1 || day > 31 || month < 1 || month > 12 || year < 1900 || year > this.maxYear) {
                        this.raw = this.formatMask(digits);
                        return;
                    }
                    const dt = new Date(year, month - 1, day);
                    if (dt.getFullYear() !== year || dt.getMonth() !== month - 1 || dt.getDate() !== day) {
                        this.raw = this.formatMask(digits);
                        return;
                    }
                    this.raw = String(day).padStart(2, '0') + '/' + String(month).padStart(2, '0') + '/' + String(year);
                    $wire.set('{{ $hariModel }}', String(day).padStart(2, '0'));
                    $wire.set('{{ $bulanModel }}', String(month).padStart(2, '0'));
                    $wire.set('{{ $tahunModel }}', String(year));
                }
            }"
        >
            <input
                type="text"
                inputmode="numeric"
                autocomplete="off"
                maxlength="10"
                x-model="raw"
                x-on:input="onInput($event)"
                x-on:blur="commit()"
                x-on:keydown.enter.prevent="commit()"
                placeholder="{{ $placeholder }}"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary"
            >
        </div>
    @else
        <div
            x-data="{
                raw: @entangle($attributes->wire('model')),
                maxYear: {{ $maxYear }},
                digitsOnly(value) {
                    return String(value || '').replace(/\D/g, '').slice(0, 8);
                },
                formatMask(digits) {
                    const d = this.digitsOnly(digits);
                    if (d.length <= 2) return d;
                    if (d.length <= 4) return d.slice(0, 2) + '/' + d.slice(2);
                    return d.slice(0, 2) + '/' + d.slice(2, 4) + '/' + d.slice(4);
                },
                onInput(e) {
                    const el = e.target;
                    const before = el.value;
                    const start = el.selectionStart ?? before.length;
                    const digitsBeforeCursor = this.digitsOnly(before.slice(0, start)).length;
                    this.raw = this.formatMask(before);

                    this.$nextTick(() => {
                        let pos = this.raw.length;
                        let seen = 0;
                        for (let i = 0; i < this.raw.length; i++) {
                            if (/\d/.test(this.raw[i])) {
                                seen++;
                                if (seen >= digitsBeforeCursor) {
                                    pos = i + 1;
                                    break;
                                }
                            }
                        }
                        if (digitsBeforeCursor === 0) pos = 0;
                        el.setSelectionRange(pos, pos);
                    });
                },
                commit() {
                    const digits = this.digitsOnly(this.raw);
                    if (digits === '') {
                        this.raw = '';
                        return;
                    }
                    if (digits.length !== 8) {
                        this.raw = this.formatMask(digits);
                        return;
                    }
                    const day = parseInt(digits.slice(0, 2), 10);
                    const month = parseInt(digits.slice(2, 4), 10);
                    const year = parseInt(digits.slice(4, 8), 10);
                    if (day < 1 || day > 31 || month < 1 || month > 12 || year < 1900 || year > this.maxYear) {
                        this.raw = this.formatMask(digits);
                        return;
                    }
                    const dt = new Date(year, month - 1, day);
                    if (dt.getFullYear() !== year || dt.getMonth() !== month - 1 || dt.getDate() !== day) {
                        this.raw = this.formatMask(digits);
                        return;
                    }
                    this.raw = String(day).padStart(2, '0') + '/' + String(month).padStart(2, '0') + '/' + String(year);
                }
            }"
        >
            <input
                type="text"
                inputmode="numeric"
                autocomplete="off"
                maxlength="10"
                x-model="raw"
                x-on:input="onInput($event)"
                x-on:blur="commit()"
                x-on:keydown.enter.prevent="commit()"
                placeholder="{{ $placeholder }}"
                {{ $attributes->except(['hari', 'bulan', 'tahun', 'label', 'required', 'placeholder', 'errorHari', 'errorBulan', 'errorTahun', 'errorTanggal', 'wire:model', 'wire:model.live', 'wire:model.blur'])->merge([
                    'class' => 'shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-primary focus:border-primary',
                ]) }}
            >
        </div>
    @endif

    @if($errorHari)
        @error($errorHari) <span class="text-red-500 text-xs block">{{ $message }}</span>@enderror
    @endif
    @if($errorBulan)
        @error($errorBulan) <span class="text-red-500 text-xs block">{{ $message }}</span>@enderror
    @endif
    @if($errorTahun)
        @error($errorTahun) <span class="text-red-500 text-xs block">{{ $message }}</span>@enderror
    @endif
    @if($errorField)
        @error($errorField) <span class="text-red-500 text-xs block">{{ $message }}</span>@enderror
    @endif
</div>
