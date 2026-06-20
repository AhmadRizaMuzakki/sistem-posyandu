@props([
    'kehadiranPdfUrl',
    'imunisasiPdfBaseUrl',
    'imunisasiPdfKategoriUrl',
    'idPrefix' => '',
    'functionName' => 'exportFilteredImunisasi',
])

@php
    $filterPrefix = $idPrefix === 'dashboard' ? 'dashboardFilter' : 'filter';
    $ids = [
        'tahun' => $filterPrefix.'TahunImunisasi',
        'bulan' => $filterPrefix.'BulanImunisasi',
        'kategori' => $filterPrefix.'Kategori',
        'jenisVaksin' => $filterPrefix.'JenisVaksin',
        'namaSasaran' => $filterPrefix.'NamaSasaran',
        'kehadiran' => $filterPrefix.'KehadiranImunisasi',
    ];
@endphp

<script>
    if (typeof window.laporanShowAlert !== 'function') {
        window.laporanShowAlert = function (message, type = 'warning') {
            if (window.dispatchEvent && typeof CustomEvent !== 'undefined') {
                window.dispatchEvent(new CustomEvent('show-alert', {
                    detail: { message, type }
                }));
                return;
            }

            alert(message);
        };
    }

    function {{ $functionName }}() {
        const tahunEl = document.getElementById(@json($ids['tahun']));
        const bulanEl = document.getElementById(@json($ids['bulan']));
        const kategoriEl = document.getElementById(@json($ids['kategori']));
        const jenisVaksinEl = document.getElementById(@json($ids['jenisVaksin']));
        const namaSasaranEl = document.getElementById(@json($ids['namaSasaran']));
        const kehadiranEl = document.getElementById(@json($ids['kehadiran']));

        const tahun = tahunEl ? tahunEl.value : '';
        const bulan = bulanEl ? bulanEl.value : '';
        const kategori = kategoriEl ? kategoriEl.value : '';
        const jenisVaksin = jenisVaksinEl ? jenisVaksinEl.value : '';
        const namaSasaran = namaSasaranEl ? namaSasaranEl.value : '';
        const kehadiran = kehadiranEl ? kehadiranEl.value : '';

        const useKehadiranReport = Boolean(kehadiran) || (tahun && bulan && !jenisVaksin && !namaSasaran);

        if (useKehadiranReport) {
            if (!tahun || !bulan) {
                window.laporanShowAlert('Tahun dan bulan wajib dipilih untuk laporan kehadiran imunisasi.');
                return;
            }

            const params = new URLSearchParams();
            params.append('tahun', tahun);
            params.append('bulan', bulan);
            if (kategori) params.append('kategori', kategori);
            if (jenisVaksin) params.append('jenis_vaksin', jenisVaksin);
            if (namaSasaran) params.append('nama_sasaran', namaSasaran);
            if (kehadiran) params.append('kehadiran', kehadiran);
            window.open(@json($kehadiranPdfUrl) + '?' + params.toString(), '_blank');
            return;
        }

        let url = @json($imunisasiPdfBaseUrl);
        if (kategori) {
            url = @json($imunisasiPdfKategoriUrl).replace('__KATEGORI__', encodeURIComponent(kategori));
        }

        const params = new URLSearchParams();
        if (tahun) params.append('tahun', tahun);
        if (bulan) params.append('bulan', bulan);
        if (jenisVaksin) params.append('jenis_vaksin', jenisVaksin);
        if (namaSasaran) params.append('nama_sasaran', namaSasaran);
        window.open(url + (params.toString() ? '?' + params.toString() : ''), '_blank');
    }
</script>
