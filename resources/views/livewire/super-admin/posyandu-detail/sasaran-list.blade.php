{{-- Import Master: satu file Excel berisi semua kategori sasaran --}}
<div class="mb-4 flex items-center justify-between rounded-lg border border-primary/30 bg-primary/5 px-4 py-3">
    <p class="text-sm text-gray-700">
        <i class="ph ph-file-xls text-primary mr-2"></i>
        Satu file Excel berisi beberapa sheet (Bayi Balita, Remaja, Dewasa, Ibu Hamil, Pralansia, Lansia). Kolom tiap sheet sama seperti form—tanpa kolom gabungan.
    </p>
    <button wire:click="openImportModal('master')"
            class="flex items-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-primary/90 transition-colors">
        <i class="ph ph-upload-simple text-lg mr-2"></i>
        Import Master Excel
    </button>
</div>

@php
    $bayibalitaData = $this->getFilteredSasaran($posyandu->sasaran_bayibalita, $search_bayibalita, $page_bayibalita);
@endphp
{{-- Daftar Sasaran Bayi/Balita --}}
@include('livewire.super-admin.posyandu-detail.sasaran-list-item', [
    'title' => 'Daftar Sasaran Bayi/Balita',
    'icon' => 'ph-baby',
    'count' => $bayibalitaData['total'],
    'sasaran' => $bayibalitaData['data'],
    'pagination' => $bayibalitaData,
    'search' => $search_bayibalita,
    'searchProperty' => 'search_bayibalita',
    'pageProperty' => 'page_bayibalita',
    'openModal' => 'openBalitaModal()',
    'editMethod' => 'editBalita',
    'deleteMethod' => 'deleteBalita',
    'emptyMessage' => 'Belum ada sasaran terdaftar',
    'importKategori' => 'bayibalita',
    'exportUrl' => route('superadmin.posyandu.sasaran.pdf', [
        'id' => $posyandu->id_posyandu,
        'kategori' => 'bayibalita',
    ]),
])

@php
    $remajaData = $this->getFilteredSasaran($posyandu->sasaran_remaja, $search_remaja, $page_remaja);
@endphp
{{-- Daftar Sasaran Remaja --}}
@include('livewire.super-admin.posyandu-detail.sasaran-list-item', [
    'title' => 'Daftar Sasaran Remaja',
    'icon' => 'ph-user',
    'count' => $remajaData['total'],
    'sasaran' => $remajaData['data'],
    'pagination' => $remajaData,
    'search' => $search_remaja,
    'searchProperty' => 'search_remaja',
    'pageProperty' => 'page_remaja',
    'openModal' => 'openRemajaModal()',
    'editMethod' => 'editRemaja',
    'deleteMethod' => 'deleteRemaja',
    'emptyMessage' => 'Belum ada sasaran remaja terdaftar',
    'importKategori' => 'remaja',
    'exportUrl' => route('superadmin.posyandu.sasaran.pdf', [
        'id' => $posyandu->id_posyandu,
        'kategori' => 'remaja',
    ]),
])

@php
    $dewasaData = $this->getFilteredSasaran($posyandu->sasaran_dewasa, $search_dewasa, $page_dewasa);
@endphp
{{-- Daftar Sasaran Dewasa --}}
@include('livewire.super-admin.posyandu-detail.sasaran-list-item', [
    'title' => 'Daftar Sasaran Dewasa',
    'icon' => 'ph-users',
    'count' => $dewasaData['total'],
    'sasaran' => $dewasaData['data'],
    'pagination' => $dewasaData,
    'search' => $search_dewasa,
    'searchProperty' => 'search_dewasa',
    'pageProperty' => 'page_dewasa',
    'openModal' => 'openDewasaModal()',
    'editMethod' => 'editDewasa',
    'deleteMethod' => 'deleteDewasa',
    'emptyMessage' => 'Belum ada sasaran dewasa terdaftar',
    'importKategori' => 'dewasa',
    'exportUrl' => route('superadmin.posyandu.sasaran.pdf', [
        'id' => $posyandu->id_posyandu,
        'kategori' => 'dewasa',
    ]),
])

@php
    $ibuhamilData = $this->getFilteredSasaran($posyandu->sasaran_ibuhamil, $search_ibuhamil, $page_ibuhamil);
@endphp
{{-- Daftar Sasaran Ibu Hamil --}}
@include('livewire.super-admin.posyandu-detail.sasaran-list-item', [
    'title' => 'Daftar Sasaran Ibu Hamil',
    'icon' => 'ph-heart',
    'count' => $ibuhamilData['total'],
    'sasaran' => $ibuhamilData['data'],
    'pagination' => $ibuhamilData,
    'search' => $search_ibuhamil,
    'searchProperty' => 'search_ibuhamil',
    'pageProperty' => 'page_ibuhamil',
    'openModal' => 'openIbuHamilModal()',
    'editMethod' => 'editIbuHamil',
    'deleteMethod' => 'deleteIbuHamil',
    'emptyMessage' => 'Belum ada sasaran ibu hamil terdaftar',
    'importKategori' => 'ibuhamil',
    'exportUrl' => route('superadmin.posyandu.sasaran.pdf', [
        'id' => $posyandu->id_posyandu,
        'kategori' => 'ibuhamil',
    ]),
])

@php
    $pralansiaData = $this->getFilteredSasaran($posyandu->sasaran_pralansia, $search_pralansia, $page_pralansia);
@endphp
{{-- Daftar Sasaran Pralansia --}}
@include('livewire.super-admin.posyandu-detail.sasaran-list-item', [
    'title' => 'Daftar Sasaran Pralansia',
    'icon' => 'ph-user-circle',
    'count' => $pralansiaData['total'],
    'sasaran' => $pralansiaData['data'],
    'pagination' => $pralansiaData,
    'search' => $search_pralansia,
    'searchProperty' => 'search_pralansia',
    'pageProperty' => 'page_pralansia',
    'openModal' => 'openPralansiaModal()',
    'editMethod' => 'editPralansia',
    'deleteMethod' => 'deletePralansia',
    'emptyMessage' => 'Belum ada sasaran pralansia terdaftar',
    'importKategori' => 'pralansia',
    'exportUrl' => route('superadmin.posyandu.sasaran.pdf', [
        'id' => $posyandu->id_posyandu,
        'kategori' => 'pralansia',
    ]),
])

@php
    $lansiaData = $this->getFilteredSasaran($posyandu->sasaran_lansia, $search_lansia, $page_lansia);
@endphp
{{-- Daftar Sasaran Lansia --}}
@include('livewire.super-admin.posyandu-detail.sasaran-list-item', [
    'title' => 'Daftar Sasaran Lansia',
    'icon' => 'ph-user-gear',
    'count' => $lansiaData['total'],
    'sasaran' => $lansiaData['data'],
    'pagination' => $lansiaData,
    'search' => $search_lansia,
    'searchProperty' => 'search_lansia',
    'pageProperty' => 'page_lansia',
    'openModal' => 'openLansiaModal()',
    'editMethod' => 'editLansia',
    'deleteMethod' => 'deleteLansia',
    'emptyMessage' => 'Belum ada sasaran lansia terdaftar',
    'importKategori' => 'lansia',
    'exportUrl' => route('superadmin.posyandu.sasaran.pdf', [
        'id' => $posyandu->id_posyandu,
        'kategori' => 'lansia',
    ]),
])

