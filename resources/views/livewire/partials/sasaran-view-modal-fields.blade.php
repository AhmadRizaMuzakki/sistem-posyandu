@php
    $isMobile = ($layout ?? 'desktop') === 'mobile';
    $gridClass = $isMobile ? 'grid grid-cols-1 gap-3' : 'grid grid-cols-2 gap-2';
    $fieldClass = $isMobile
        ? 'sasaran-view-field bg-gray-50 rounded-xl px-4 py-3 border border-gray-100'
        : 'sasaran-view-field bg-gray-50 rounded-lg px-2.5 py-2';
    $labelClass = $isMobile
        ? 'sasaran-view-field-label text-xs text-gray-500 mb-1'
        : 'sasaran-view-field-label text-[11px] text-gray-500 mb-0.5';
    $valueClass = $isMobile
        ? 'sasaran-view-field-value text-sm font-medium text-gray-900 break-words'
        : 'sasaran-view-field-value text-xs font-medium text-gray-900 break-words';
    $sectionTitleClass = $isMobile
        ? 'sasaran-view-section-title text-sm font-bold text-gray-800 mb-3 flex items-center'
        : 'sasaran-view-section-title text-xs font-bold text-gray-800 mb-2 flex items-center';
    $sectionIconClass = $isMobile ? 'text-base mr-2' : 'text-sm mr-1.5';
    $sectionSpacing = $isMobile ? 'space-y-5' : 'space-y-4';
    $colSpanFull = $isMobile ? '' : ' sm:col-span-2';
@endphp

<div class="{{ $sectionSpacing }} pb-2">
    <div>
        <h4 class="{{ $sectionTitleClass }}">
            <i class="ph ph-identification-card {{ $sectionIconClass }} text-primary shrink-0"></i>
            Data Pribadi
        </h4>
        <div class="{{ $gridClass }}">
            <div class="{{ $fieldClass }}">
                <p class="{{ $labelClass }}">NIK</p>
                <p class="{{ $valueClass }} break-all">{{ $nik }}</p>
            </div>
            <div class="{{ $fieldClass }}">
                <p class="{{ $labelClass }}">No. KK</p>
                <p class="{{ $valueClass }} break-all">{{ $noKk }}</p>
            </div>
            <div class="{{ $fieldClass }}">
                <p class="{{ $labelClass }}">Tempat Lahir</p>
                <p class="{{ $valueClass }}">{{ $tempatLahir }}</p>
            </div>
            <div class="{{ $fieldClass }}">
                <p class="{{ $labelClass }}">Tanggal Lahir</p>
                <p class="{{ $valueClass }}">{{ $tanggalLahir }}</p>
            </div>
            <div class="{{ $fieldClass }}">
                <p class="{{ $labelClass }}">Jenis Kelamin</p>
                <p class="{{ $valueClass }}">{{ $jenisKelamin }}</p>
            </div>
            @if($hasStatusKeluarga)
                <div class="{{ $fieldClass }}">
                    <p class="{{ $labelClass }}">Status Keluarga</p>
                    <p class="{{ $valueClass }}">{{ $statusKeluarga }}</p>
                </div>
            @endif
            <div class="{{ $fieldClass }}">
                <p class="{{ $labelClass }}">Umur</p>
                <p class="{{ $valueClass }}">{{ $umur }}</p>
            </div>
            @if($hasMingguKandungan)
                <div class="{{ $fieldClass }}">
                    <p class="{{ $labelClass }}">Minggu Kandungan</p>
                    <p class="{{ $valueClass }}">{{ $s->minggu_kandungan ? $s->minggu_kandungan . ' minggu' : '-' }}</p>
                </div>
            @endif
            @if($hasPendidikan)
                <div class="{{ $fieldClass }}">
                    <p class="{{ $labelClass }}">Pendidikan</p>
                    <p class="{{ $valueClass }}">{{ $s->pendidikan ?? '-' }}</p>
                </div>
            @endif
            @if($hasPekerjaan)
                <div class="{{ $fieldClass }}">
                    <p class="{{ $labelClass }}">Pekerjaan</p>
                    <p class="{{ $valueClass }}">{{ $s->pekerjaan ?? '-' }}</p>
                </div>
            @endif
        </div>
    </div>

    <div>
        <h4 class="{{ $sectionTitleClass }}">
            <i class="ph ph-map-pin {{ $sectionIconClass }} text-emerald-600 shrink-0"></i>
            Alamat & Kontak
        </h4>
        <div class="{{ $gridClass }}">
            <div class="{{ $fieldClass }}{{ $colSpanFull }}">
                <p class="{{ $labelClass }}">Alamat</p>
                <p class="{{ $valueClass }}">{{ $alamat }}</p>
            </div>
            @if($hasRtRw)
                <div class="{{ $fieldClass }}">
                    <p class="{{ $labelClass }}">RT</p>
                    <p class="{{ $valueClass }}">{{ $s->rt ?? '-' }}</p>
                </div>
                <div class="{{ $fieldClass }}">
                    <p class="{{ $labelClass }}">RW</p>
                    <p class="{{ $valueClass }}">{{ $s->rw ?? '-' }}</p>
                </div>
            @endif
            <div class="{{ $fieldClass }}">
                <p class="{{ $labelClass }}">Kepesertaan BPJS</p>
                <p class="{{ $valueClass }}">{{ $s->kepersertaan_bpjs ?? '-' }}</p>
            </div>
            <div class="{{ $fieldClass }}">
                <p class="{{ $labelClass }}">Nomor BPJS</p>
                <p class="{{ $valueClass }} break-all">{{ $s->nomor_bpjs ?? '-' }}</p>
            </div>
            @if($hasTelepon)
                <div class="{{ $fieldClass }}">
                    <p class="{{ $labelClass }}">Nomor Telepon</p>
                    <p class="{{ $valueClass }} break-all">{{ $s->nomor_telepon ?? '-' }}</p>
                </div>
            @endif
        </div>
    </div>

    @if($hasSuami)
        <div>
            <h4 class="{{ $sectionTitleClass }}">
                <i class="ph ph-heart {{ $sectionIconClass }} text-pink-600 shrink-0"></i>
                Data Suami
            </h4>
            <div class="{{ $gridClass }}">
                <div class="{{ $fieldClass }}">
                    <p class="{{ $labelClass }}">Nama Suami</p>
                    <p class="{{ $valueClass }}">{{ $s->nama_suami ?? '-' }}</p>
                </div>
                <div class="{{ $fieldClass }}">
                    <p class="{{ $labelClass }}">NIK Suami</p>
                    <p class="{{ $valueClass }} break-all">{{ $s->nik_suami ?? '-' }}</p>
                </div>
                <div class="{{ $fieldClass }}">
                    <p class="{{ $labelClass }}">Tempat Lahir Suami</p>
                    <p class="{{ $valueClass }}">{{ $s->tempat_lahir_suami ?? '-' }}</p>
                </div>
                <div class="{{ $fieldClass }}">
                    <p class="{{ $labelClass }}">Tanggal Lahir Suami</p>
                    <p class="{{ $valueClass }}">{{ $s->tanggal_lahir_suami ? \Carbon\Carbon::parse($s->tanggal_lahir_suami)->format('d/m/Y') : '-' }}</p>
                </div>
                <div class="{{ $fieldClass }}">
                    <p class="{{ $labelClass }}">Pekerjaan Suami</p>
                    <p class="{{ $valueClass }}">{{ $s->pekerjaan_suami ?? '-' }}</p>
                </div>
                <div class="{{ $fieldClass }}">
                    <p class="{{ $labelClass }}">Status Keluarga Suami</p>
                    <p class="{{ $valueClass }}">{{ $s->status_keluarga_suami ? ucfirst($s->status_keluarga_suami) : '-' }}</p>
                </div>
            </div>
        </div>
    @endif

    @if($showOrangtuaSection)
        <div>
            <h4 class="{{ $sectionTitleClass }}">
                <i class="ph ph-users {{ $sectionIconClass }} text-indigo-600 shrink-0"></i>
                Data Orang Tua
            </h4>
            <div class="{{ $gridClass }}">
                <div class="{{ $fieldClass }}">
                    <p class="{{ $labelClass }}">Nama Orang Tua</p>
                    <p class="{{ $valueClass }}">{{ $ortuNama }}</p>
                </div>
                <div class="{{ $fieldClass }}">
                    <p class="{{ $labelClass }}">NIK Orang Tua</p>
                    <p class="{{ $valueClass }} break-all">{{ $ortuNik }}</p>
                </div>
                <div class="{{ $fieldClass }}">
                    <p class="{{ $labelClass }}">No. KK Orang Tua</p>
                    <p class="{{ $valueClass }} break-all">{{ $ortuNoKk }}</p>
                </div>
                <div class="{{ $fieldClass }}">
                    <p class="{{ $labelClass }}">Tempat Lahir</p>
                    <p class="{{ $valueClass }}">{{ $ortuTempatLahir }}</p>
                </div>
                <div class="{{ $fieldClass }}">
                    <p class="{{ $labelClass }}">Tanggal Lahir</p>
                    <p class="{{ $valueClass }}">{{ $ortuTanggalLahir }}</p>
                </div>
                <div class="{{ $fieldClass }}">
                    <p class="{{ $labelClass }}">Jenis Kelamin</p>
                    <p class="{{ $valueClass }}">{{ $ortuKelamin }}</p>
                </div>
                <div class="{{ $fieldClass }}">
                    <p class="{{ $labelClass }}">Umur</p>
                    <p class="{{ $valueClass }}">{{ $ortuUmur }}</p>
                </div>
                <div class="{{ $fieldClass }}">
                    <p class="{{ $labelClass }}">Pekerjaan</p>
                    <p class="{{ $valueClass }}">{{ $ortuPekerjaan }}</p>
                </div>
                <div class="{{ $fieldClass }}">
                    <p class="{{ $labelClass }}">Pendidikan</p>
                    <p class="{{ $valueClass }}">{{ $ortuPendidikan }}</p>
                </div>
                <div class="{{ $fieldClass }}{{ $colSpanFull }}">
                    <p class="{{ $labelClass }}">Alamat</p>
                    <p class="{{ $valueClass }}">{{ $ortuAlamat }}</p>
                </div>
                <div class="{{ $fieldClass }}">
                    <p class="{{ $labelClass }}">Kepesertaan BPJS</p>
                    <p class="{{ $valueClass }}">{{ $ortuBpjs }}</p>
                </div>
                <div class="{{ $fieldClass }}">
                    <p class="{{ $labelClass }}">Nomor BPJS</p>
                    <p class="{{ $valueClass }} break-all">{{ $ortuNomorBpjs }}</p>
                </div>
                <div class="{{ $fieldClass }}">
                    <p class="{{ $labelClass }}">Nomor Telepon</p>
                    <p class="{{ $valueClass }} break-all">{{ $ortuTelepon }}</p>
                </div>
            </div>
        </div>
    @endif
</div>
