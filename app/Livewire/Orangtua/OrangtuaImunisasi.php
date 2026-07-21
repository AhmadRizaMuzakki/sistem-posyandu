<?php

namespace App\Livewire\Orangtua;

use App\Livewire\Orangtua\Traits\ImunisasiAnalyticsTrait;
use App\Models\Imunisasi;
use App\Models\SasaranBayibalita;
use App\Models\SasaranRemaja;
use App\Models\SasaranDewasa;
use App\Models\SasaranPralansia;
use App\Models\SasaranLansia;
use App\Services\AntropometriService;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.orangtuadashboard')]
class OrangtuaImunisasi extends Component
{
    use ImunisasiAnalyticsTrait;

    /** Filter: nama sasaran (dari query ?sasaran=) */
    public string $filterNama = '';

    public string $filterBulan = '';

    public string $filterTahun = '';

    /** Jumlah baris per halaman */
    public int $filterLimit = 10;

    /** Halaman aktif tabel riwayat */
    public int $riwayatPage = 1;

    /** @var array<int> */
    public array $limitOptions = [5, 10, 25, 50];

    public function mount(): void
    {
        $this->syncFilterFromRequest();
    }

    protected function syncFilterFromRequest(): void
    {
        $sasaran = request()->query('sasaran', '');
        $this->filterNama = is_string($sasaran) ? trim($sasaran) : '';

        $bulan = request()->query('bulan', '');
        $this->filterBulan = is_string($bulan) || is_numeric($bulan) ? trim((string) $bulan) : '';

        $tahun = request()->query('tahun', '');
        $this->filterTahun = is_string($tahun) || is_numeric($tahun) ? trim((string) $tahun) : '';

        $limit = request()->query('limit', 10);
        $this->filterLimit = is_numeric($limit) && in_array((int) $limit, $this->limitOptions, true)
            ? (int) $limit
            : 10;

        $page = request()->query('page', 1);
        $this->riwayatPage = is_numeric($page) && (int) $page >= 1 ? (int) $page : 1;
    }

    public function updatedFilterNama(): void
    {
        $this->riwayatPage = 1;
    }

    public function updatedFilterBulan(): void
    {
        $this->riwayatPage = 1;
    }

    public function updatedFilterTahun(): void
    {
        $this->riwayatPage = 1;
    }

    public function updatedFilterLimit(): void
    {
        $this->filterLimit = (int) $this->filterLimit;
        if (! in_array($this->filterLimit, $this->limitOptions, true)) {
            $this->filterLimit = 10;
        }
        $this->riwayatPage = 1;
    }

    public function gotoRiwayatPage(int $page): void
    {
        $this->riwayatPage = max(1, $page);
    }

    public function previousRiwayatPage(): void
    {
        $this->riwayatPage = max(1, $this->riwayatPage - 1);
    }

    public function nextRiwayatPage(): void
    {
        $this->riwayatPage = max(1, $this->riwayatPage + 1);
    }

    /**
     * @param  \Illuminate\Support\Collection  $imunisasiList
     * @return array{
     *     rows: \Illuminate\Support\Collection,
     *     totalBaris: int,
     *     tampilBaris: int,
     *     currentPage: int,
     *     lastPage: int,
     *     perPage: int,
     *     firstItem: int,
     *     lastItem: int,
     *     hasPages: bool
     * }
     */
    protected function buildRiwayatRows($imunisasiList): array
    {
        $rows = collect();

        foreach ($imunisasiList as $item) {
            foreach ($item['imunisasi'] as $imunisasi) {
                $rows->push([
                    'sasaran' => $item['sasaran'],
                    'imunisasi' => $imunisasi,
                ]);
            }
        }

        $rows = $rows->sortByDesc(fn ($row) => $row['imunisasi']->tanggal_imunisasi)->values();
        $totalBaris = $rows->count();
        $perPage = max(1, $this->filterLimit);
        $lastPage = max(1, (int) ceil($totalBaris / $perPage));
        $currentPage = min(max(1, $this->riwayatPage), $lastPage);
        $this->riwayatPage = $currentPage;

        $paginatedRows = $rows->forPage($currentPage, $perPage)->values();
        $firstItem = $totalBaris > 0 ? (($currentPage - 1) * $perPage) + 1 : 0;
        $lastItem = $totalBaris > 0 ? min($currentPage * $perPage, $totalBaris) : 0;

        return [
            'rows' => $paginatedRows,
            'totalBaris' => $totalBaris,
            'tampilBaris' => $paginatedRows->count(),
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
            'perPage' => $perPage,
            'firstItem' => $firstItem,
            'lastItem' => $lastItem,
            'hasPages' => $lastPage > 1,
        ];
    }

    /**
     * Bangun allSasaran dan imunisasiList (dengan filter).
     */
    protected function getImunisasiData()
    {
        $noKk = $this->resolveNoKk();
        $allSasaran = collect();

        if ($noKk) {
            $kategoriMap = [
                ['model' => SasaranBayibalita::class, 'id' => 'id_sasaran_bayibalita', 'slug' => 'bayibalita'],
                ['model' => SasaranRemaja::class, 'id' => 'id_sasaran_remaja', 'slug' => 'remaja'],
                ['model' => SasaranDewasa::class, 'id' => 'id_sasaran_dewasa', 'slug' => 'dewasa'],
                ['model' => SasaranPralansia::class, 'id' => 'id_sasaran_pralansia', 'slug' => 'pralansia'],
                ['model' => SasaranLansia::class, 'id' => 'id_sasaran_lansia', 'slug' => 'lansia'],
            ];

            foreach ($kategoriMap as $cfg) {
                $sasaranList = $cfg['model']::where('no_kk_sasaran', $noKk)->get();
                foreach ($sasaranList as $s) {
                    $allSasaran->push([
                        'id' => $s->{$cfg['id']},
                        'kategori' => $cfg['slug'],
                        'nama' => $s->nama_sasaran,
                        'nik' => $s->nik_sasaran,
                        'tanggal_lahir' => $s->tanggal_lahir,
                        'jenis_kelamin' => $s->jenis_kelamin,
                    ]);
                }
            }
        }

        $sasaranConditions = $allSasaran->map(fn ($s) => [
            'id' => $s['id'],
            'kategori' => $s['kategori'],
        ])->toArray();

        $allImunisasi = collect();
        if (!empty($sasaranConditions)) {
            $query = Imunisasi::where(function ($q) use ($sasaranConditions) {
                foreach ($sasaranConditions as $cond) {
                    $q->orWhere(function ($subQ) use ($cond) {
                        $subQ->where('id_sasaran', $cond['id'])
                            ->where('kategori_sasaran', $cond['kategori']);
                    });
                }
            });

            $this->applyBulanTahunToImunisasiQuery($query, $this->filterBulan, $this->filterTahun);

            $allImunisasi = $query->orderBy('tanggal_imunisasi', 'desc')->get();
        }

        $groupedImunisasi = $allImunisasi->groupBy(fn ($im) => $im->kategori_sasaran . '_' . $im->id_sasaran);

        $imunisasiList = collect();
        foreach ($allSasaran as $sasaran) {
            if ($this->filterNama !== '' && trim($sasaran['nama']) !== trim($this->filterNama)) {
                continue;
            }

            $key = $sasaran['kategori'] . '_' . $sasaran['id'];
            $imunisasi = $groupedImunisasi->get($key, collect());

            if ($imunisasi->count() > 0) {
                $imunisasiList->push(['sasaran' => $sasaran, 'imunisasi' => $imunisasi]);
            }
        }

        return [
            'allSasaran' => $allSasaran,
            'imunisasiList' => $imunisasiList,
            'namaSasaranList' => $allSasaran->pluck('nama')->unique()->sort()->values()->toArray(),
        ];
    }

    public function render()
    {
        $data = $this->getImunisasiData();
        $antropometri = app(AntropometriService::class);
        $sasaranAnalytics = $this->getSasaranUntukAnalytics();
        $filterNamaAktif = trim($this->filterNama) !== '';
        $filterBulanTahunAktif = $this->hasBulanTahunFilter($this->filterBulan, $this->filterTahun);
        $filterAktif = $filterNamaAktif || $filterBulanTahunAktif;
        $periodeLabel = $this->formatBulanTahunLabel($this->filterBulan, $this->filterTahun);

        if ($filterNamaAktif) {
            $sasaranAnalytics = $sasaranAnalytics->filter(
                fn ($s) => trim($s['nama']) === trim($this->filterNama)
            )->values();
        }

        $analytics = $filterNamaAktif
            ? $this->getImunisasiAnalytics($sasaranAnalytics, $antropometri, $this->filterBulan, $this->filterTahun)
            : [
                'grafikPertumbuhan' => [],
                'grafikImunisasiJenis' => ['labels' => [], 'data' => []],
                'penilaianPerKategori' => [],
                'totalImunisasi' => 0,
            ];

        $riwayat = $this->buildRiwayatRows($data['imunisasiList']);

        return view('livewire.orangtua.orangtua-imunisasi', [
            'imunisasiList' => $data['imunisasiList'],
            'riwayatRows' => $riwayat['rows'],
            'totalBaris' => $riwayat['totalBaris'],
            'tampilBaris' => $riwayat['tampilBaris'],
            'riwayatCurrentPage' => $riwayat['currentPage'],
            'riwayatLastPage' => $riwayat['lastPage'],
            'riwayatFirstItem' => $riwayat['firstItem'],
            'riwayatLastItem' => $riwayat['lastItem'],
            'riwayatHasPages' => $riwayat['hasPages'],
            'allSasaran' => $data['allSasaran'],
            'namaSasaranList' => $data['namaSasaranList'],
            'filterAktif' => $filterAktif,
            'filterNamaAktif' => $filterNamaAktif,
            'filterBulanTahunAktif' => $filterBulanTahunAktif,
            'filterNama' => trim($this->filterNama),
            'filterBulan' => $this->filterBulan,
            'filterTahun' => $this->filterTahun,
            'filterLimit' => $this->filterLimit,
            'periodeLabel' => $periodeLabel,
            'grafikPertumbuhan' => $analytics['grafikPertumbuhan'],
            'grafikImunisasiJenis' => $analytics['grafikImunisasiJenis'],
            'penilaianPerKategori' => $analytics['penilaianPerKategori'],
            'totalImunisasi' => $analytics['totalImunisasi'],
        ]);
    }
}
