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

        // Laporan kehadiran hanya jika filter kehadiran dipilih secara eksplisit
        if (kehadiran) {
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
            params.append('kehadiran', kehadiran);
            window.open(@json($kehadiranPdfUrl) + '?' + params.toString(), '_blank');
            return;
        }

        let url = @json($imunisasiPdfBaseUrl);
        const params = new URLSearchParams();
        if (kategori) {
            // Pakai query param agar filter usia/tahun lahir tidak rusak di path URL
            params.append('kategori', kategori);
        }
        if (tahun) params.append('tahun', tahun);
        if (bulan) params.append('bulan', bulan);
        if (jenisVaksin) params.append('jenis_vaksin', jenisVaksin);
        if (namaSasaran) params.append('nama_sasaran', namaSasaran);
        params.append('_t', String(Date.now()));
        window.open(url + (params.toString() ? '?' + params.toString() : ''), '_blank');
    }
</script>
