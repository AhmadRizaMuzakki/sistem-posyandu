@if($this->isSasaranViewModalOpen && $this->viewSasaran)
    @php
        $s = $this->viewSasaran;
        $kategori = $this->viewSasaranKategori;
        $isBalita = $kategori === 'bayibalita';
        $isIbuHamil = $kategori === 'ibuhamil';
        $isOrangtuaView = $kategori === 'orangtua';
        $hasPendidikan = in_array($kategori, ['remaja', 'dewasa', 'pralansia', 'lansia', 'ibuhamil', 'orangtua'], true);
        $hasPekerjaan = in_array($kategori, ['dewasa', 'pralansia', 'lansia', 'ibuhamil', 'orangtua'], true);
        $hasMingguKandungan = $isIbuHamil;
        $hasSuami = $isIbuHamil;
        $hasTelepon = !$isBalita;
        $hasRtRw = !$isOrangtuaView;
        $hasStatusKeluarga = !$isOrangtuaView;
        $showOrangtuaSection = in_array($kategori, ['bayibalita', 'remaja'], true);

        $nama = $isOrangtuaView ? ($s->nama ?? '-') : ($s->nama_sasaran ?? '-');
        $nik = $isOrangtuaView ? ($s->nik ?? '-') : ($s->nik_sasaran ?? '-');
        $noKk = $isOrangtuaView ? ($s->no_kk ?? '-') : ($s->no_kk_sasaran ?? '-');
        $tempatLahir = $s->tempat_lahir ?? '-';
        $tanggalLahir = $s->tanggal_lahir ? \Carbon\Carbon::parse($s->tanggal_lahir)->format('d/m/Y') : '-';
        $jenisKelamin = $isOrangtuaView ? ($s->kelamin ?? '-') : ($s->jenis_kelamin ?? '-');
        $statusKeluarga = $hasStatusKeluarga && ($s->status_keluarga ?? null) ? ucfirst($s->status_keluarga) : '-';
        $umur = '-';
        if ($s->tanggal_lahir ?? null) {
            $dob = \Carbon\Carbon::parse($s->tanggal_lahir);
            $umur = $isBalita
                ? ((int) $dob->diffInMonths(now()) . ' bln')
                : ((int) $dob->diffInYears(now()) . ' th');
        } elseif (!is_null($s->umur_sasaran ?? null)) {
            $umur = (int) $s->umur_sasaran . ' th';
        } elseif (!is_null($s->umur ?? null)) {
            $umur = (int) $s->umur . ' th';
        }
        $alamat = $isOrangtuaView ? ($s->alamat ?? '-') : ($s->alamat_sasaran ?? '-');
        $viewTitle = $this->viewSasaranTitle;

        $ortu = $showOrangtuaSection ? $this->viewSasaranOrangtua : null;
        $ortuNik = $ortu?->nik ?? ($s->nik_orangtua ?? '-');
        $ortuNama = $ortu?->nama ?? '-';
        $ortuNoKk = $ortu?->no_kk ?? '-';
        $ortuTempatLahir = $ortu?->tempat_lahir ?? '-';
        $ortuTanggalLahir = ($ortu && $ortu->tanggal_lahir)
            ? \Carbon\Carbon::parse($ortu->tanggal_lahir)->format('d/m/Y')
            : '-';
        $ortuKelamin = $ortu?->kelamin ?? '-';
        $ortuUmur = ($ortu && $ortu->tanggal_lahir)
            ? ((int) \Carbon\Carbon::parse($ortu->tanggal_lahir)->diffInYears(now()) . ' th')
            : '-';
        $ortuPekerjaan = $ortu?->pekerjaan ?? '-';
        $ortuPendidikan = $ortu?->pendidikan ?? '-';
        $ortuAlamat = $ortu?->alamat ?? '-';
        $ortuBpjs = $ortu?->kepersertaan_bpjs ?? '-';
        $ortuNomorBpjs = $ortu?->nomor_bpjs ?? '-';
        $ortuTelepon = $ortu?->nomor_telepon ?? '-';

        $fieldVars = compact(
            's', 'isBalita', 'isIbuHamil', 'isOrangtuaView', 'hasPendidikan', 'hasPekerjaan',
            'hasMingguKandungan', 'hasSuami', 'hasTelepon', 'hasRtRw', 'hasStatusKeluarga',
            'showOrangtuaSection', 'nama', 'nik', 'noKk', 'tempatLahir', 'tanggalLahir',
            'jenisKelamin', 'statusKeluarga', 'umur', 'alamat',
            'ortuNik', 'ortuNama', 'ortuNoKk', 'ortuTempatLahir', 'ortuTanggalLahir',
            'ortuKelamin', 'ortuUmur', 'ortuPekerjaan', 'ortuPendidikan', 'ortuAlamat',
            'ortuBpjs', 'ortuNomorBpjs', 'ortuTelepon'
        );
    @endphp

    @teleport('body')
    <div class="sasaran-view-modal fixed inset-0 z-[9999]" role="dialog" aria-modal="true" aria-labelledby="sasaran-view-modal-title">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeSasaranViewModal" aria-hidden="true"></div>

        {{-- Mobile: bottom sheet --}}
        <div class="sasaran-view-modal-mobile md:hidden absolute inset-x-0 bottom-0 pointer-events-none">
            <div class="pointer-events-auto relative bg-white shadow-2xl w-full h-[92dvh] max-h-[92dvh] flex flex-col overflow-hidden rounded-t-2xl" wire:click.stop>
                <div class="flex justify-center pt-2.5 pb-1 shrink-0">
                    <span class="w-10 h-1 rounded-full bg-gray-300" aria-hidden="true"></span>
                </div>

                @include('livewire.partials.sasaran-view-modal-header', [
                    'variant' => 'mobile',
                    'viewTitle' => $viewTitle,
                ])

                @include('livewire.partials.sasaran-view-modal-name-section', [
                    'variant' => 'mobile',
                    'nama' => $nama,
                ])

                <div class="sasaran-view-modal-scroll flex-1 basis-0 min-h-0 overflow-y-auto overflow-x-hidden touch-pan-y overscroll-contain px-5 py-4 pb-8">
                    @include('livewire.partials.sasaran-view-modal-fields', array_merge($fieldVars, ['layout' => 'mobile']))
                </div>

                <div class="px-5 py-3.5 border-t border-gray-200 bg-gray-50 shrink-0 pb-[max(0.75rem,env(safe-area-inset-bottom))]">
                    <button type="button" wire:click="closeSasaranViewModal"
                            class="sasaran-view-close-btn w-full min-h-[44px] px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        {{-- Desktop: centered dialog --}}
        <div class="sasaran-view-modal-desktop hidden md:flex absolute inset-0 items-center justify-center p-6 pointer-events-none">
            <div class="pointer-events-auto relative bg-white shadow-2xl w-full max-w-2xl h-[85vh] max-h-[85vh] flex flex-col overflow-hidden rounded-lg" wire:click.stop>
                @include('livewire.partials.sasaran-view-modal-header', [
                    'variant' => 'desktop',
                    'viewTitle' => $viewTitle,
                ])

                @include('livewire.partials.sasaran-view-modal-name-section', [
                    'variant' => 'desktop',
                    'nama' => $nama,
                ])

                <div class="sasaran-view-modal-scroll flex-1 basis-0 min-h-0 overflow-y-auto overflow-x-hidden overscroll-contain px-8 py-5 pb-10">
                    @include('livewire.partials.sasaran-view-modal-fields', array_merge($fieldVars, ['layout' => 'desktop']))
                </div>

                <div class="px-8 py-3 border-t border-gray-200 bg-gray-50 shrink-0">
                    <button type="button" wire:click="closeSasaranViewModal"
                            class="sasaran-view-close-btn px-4 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-100 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endteleport
@endif
