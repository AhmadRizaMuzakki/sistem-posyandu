@props([
    'kehadiranPdfUrl',
    'idPrefix' => '',
    'functionName' => 'exportGlobeImunisasi',
])

@php
    $filterPrefix = $idPrefix === 'dashboard' ? 'dashboardFilter' : 'filter';
    $ids = [
        'tahun' => $filterPrefix.'TahunGlobe',
        'bulan' => $filterPrefix.'BulanGlobe',
        'kategori' => $filterPrefix.'KategoriGlobe',
        'namaSasaran' => $filterPrefix.'NamaSasaranGlobe',
    ];
@endphp

<script>
    function {{ $functionName }}() {
        const tahunEl = document.getElementById(@json($ids['tahun']));
        const bulanEl = document.getElementById(@json($ids['bulan']));
        const kategoriEl = document.getElementById(@json($ids['kategori']));
        const namaSasaranEl = document.getElementById(@json($ids['namaSasaran']));

        const tahun = tahunEl ? tahunEl.value : '';
        const bulan = bulanEl ? bulanEl.value : '';
        const kategori = kategoriEl ? kategoriEl.value : '';
        const namaSasaran = namaSasaranEl ? namaSasaranEl.value : '';

        if (!tahun) {
            if (typeof window.laporanShowAlert === 'function') {
                window.laporanShowAlert('Tahun wajib dipilih untuk laporan globe.');
            } else if (window.dispatchEvent && typeof CustomEvent !== 'undefined') {
                window.dispatchEvent(new CustomEvent('show-alert', {
                    detail: {
                        message: 'Tahun wajib dipilih untuk laporan globe.',
                        type: 'warning'
                    }
                }));
            } else {
                alert('Tahun wajib dipilih untuk laporan globe.');
            }
            return;
        }

        const params = new URLSearchParams();
        params.append('tahun', tahun);
        if (bulan) {
            params.append('bulan', bulan);
        }
        params.append('laporan', 'globe');
        if (kategori) params.append('kategori', kategori);
        if (namaSasaran) params.append('nama_sasaran', namaSasaran);
        window.open(@json($kehadiranPdfUrl) + '?' + params.toString(), '_blank');
    }
</script>
