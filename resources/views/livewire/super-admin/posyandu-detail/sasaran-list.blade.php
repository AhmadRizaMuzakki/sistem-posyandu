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
    'emptyMessage' => 'Belum ada sasaran terdaftar'
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
    'emptyMessage' => 'Belum ada sasaran remaja terdaftar'
])

@php
    // Ambil data dari tabel orangtua dengan umur >= 20 dan < 40
    $dewasaData = $this->getOrangtuaByUmur(20, 40, $search_dewasa, $page_dewasa, 'dewasa');
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
    'openModal' => 'openOrangtuaModal()',
    'editMethod' => 'editOrangtua',
    'deleteMethod' => 'deleteOrangtua',
    'emptyMessage' => 'Belum ada sasaran dewasa terdaftar'
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
    'emptyMessage' => 'Belum ada sasaran ibu hamil terdaftar'
])

@php
    // Ambil data dari tabel orangtua dengan umur >= 40 dan < 60
    $pralansiaData = $this->getOrangtuaByUmur(40, 60, $search_pralansia, $page_pralansia, 'pralansia');
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
    'openModal' => 'openOrangtuaModal()',
    'editMethod' => 'editOrangtua',
    'deleteMethod' => 'deleteOrangtua',
    'emptyMessage' => 'Belum ada sasaran pralansia terdaftar'
])

@php
    // Ambil data dari tabel orangtua dengan umur >= 60
    $lansiaData = $this->getOrangtuaByUmur(60, null, $search_lansia, $page_lansia, 'lansia');
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
    'openModal' => 'openOrangtuaModal()',
    'editMethod' => 'editOrangtua',
    'deleteMethod' => 'deleteOrangtua',
    'emptyMessage' => 'Belum ada sasaran lansia terdaftar'
])

