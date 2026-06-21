{{-- Import Master: satu file Excel berisi semua kategori sasaran --}}
<div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between rounded-lg border border-primary/30 bg-primary/5 px-4 py-3 max-w-full min-w-0">
    <p class="text-sm text-gray-700 min-w-0">
        <i class="ph ph-file-xls text-primary mr-2"></i>
        Satu file Excel berisi beberapa sheet (Bayi Balita, Remaja, Dewasa, Ibu Hamil, Pralansia, Lansia). Kolom tiap sheet sama seperti form—tanpa kolom gabungan.
    </p>
    <button wire:click="openImportModal('master')"
            class="flex items-center justify-center w-full sm:w-auto shrink-0 px-4 py-2.5 text-sm font-medium text-white bg-primary rounded-lg hover:bg-primary/90 transition-colors">
        <i class="ph ph-upload-simple text-lg mr-2"></i>
        Import Master Excel
    </button>
</div>

@php
    $bayibalitaData = $this->getFilteredSasaran($posyandu->sasaran_bayibalita, $search_bayibalita, $page_bayibalita, $perPage);
@endphp
{{-- Daftar Sasaran Bayi/Balita --}}
@include('livewire.posyandu.sasaran-list-item', [
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
    'viewKategori' => 'bayibalita',
    'emptyMessage' => 'Belum ada sasaran terdaftar',
    'importKategori' => 'bayibalita',
    'exportUrl' => route('adminPosyandu.sasaran.pdf', ['kategori' => 'bayibalita']),
    'exportExcelUrl' => route('adminPosyandu.sasaran.excel', ['kategori' => 'bayibalita']),
])

@php
    $remajaData = $this->getFilteredSasaran($posyandu->sasaran_remaja, $search_remaja, $page_remaja);
@endphp
{{-- Daftar Sasaran Remaja --}}
@include('livewire.posyandu.sasaran-list-item', [
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
    'viewKategori' => 'remaja',
    'emptyMessage' => 'Belum ada sasaran remaja terdaftar',
    'importKategori' => 'remaja',
    'exportUrl' => route('adminPosyandu.sasaran.pdf', ['kategori' => 'remaja']),
    'exportExcelUrl' => route('adminPosyandu.sasaran.excel', ['kategori' => 'remaja']),
])

@php
    $dewasaData = $this->getFilteredSasaran($posyandu->sasaran_dewasa, $search_dewasa, $page_dewasa);
@endphp
{{-- Daftar Sasaran Dewasa --}}
@include('livewire.posyandu.sasaran-list-item', [
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
    'viewKategori' => 'dewasa',
    'emptyMessage' => 'Belum ada sasaran dewasa terdaftar',
    'importKategori' => 'dewasa',
    'exportUrl' => route('adminPosyandu.sasaran.pdf', ['kategori' => 'dewasa']),
    'exportExcelUrl' => route('adminPosyandu.sasaran.excel', ['kategori' => 'dewasa']),
])

@php
    $ibuhamilData = $this->getFilteredSasaran($posyandu->sasaran_ibuhamil, $search_ibuhamil, $page_ibuhamil);
@endphp
{{-- Daftar Sasaran Ibu Hamil --}}
@include('livewire.posyandu.sasaran-list-item', [
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
    'viewKategori' => 'ibuhamil',
    'emptyMessage' => 'Belum ada sasaran ibu hamil terdaftar',
    'importKategori' => 'ibuhamil',
    'exportUrl' => route('adminPosyandu.sasaran.pdf', ['kategori' => 'ibuhamil']),
    'exportExcelUrl' => route('adminPosyandu.sasaran.excel', ['kategori' => 'ibuhamil']),
])

@php
    $pralansiaData = $this->getFilteredSasaran($posyandu->sasaran_pralansia, $search_pralansia, $page_pralansia);
@endphp
{{-- Daftar Sasaran Pralansia --}}
@include('livewire.posyandu.sasaran-list-item', [
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
    'viewKategori' => 'pralansia',
    'emptyMessage' => 'Belum ada sasaran pralansia terdaftar',
    'importKategori' => 'pralansia',
    'exportUrl' => route('adminPosyandu.sasaran.pdf', ['kategori' => 'pralansia']),
    'exportExcelUrl' => route('adminPosyandu.sasaran.excel', ['kategori' => 'pralansia']),
])

@php
    $lansiaData = $this->getFilteredSasaran($posyandu->sasaran_lansia, $search_lansia, $page_lansia);
@endphp
{{-- Daftar Sasaran Lansia --}}
@include('livewire.posyandu.sasaran-list-item', [
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
    'viewKategori' => 'lansia',
    'emptyMessage' => 'Belum ada sasaran lansia terdaftar',
    'importKategori' => 'lansia',
    'exportUrl' => route('adminPosyandu.sasaran.pdf', ['kategori' => 'lansia']),
    'exportExcelUrl' => route('adminPosyandu.sasaran.excel', ['kategori' => 'lansia']),
])

