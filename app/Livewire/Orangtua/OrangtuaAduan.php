<?php

namespace App\Livewire\Orangtua;

use App\Helpers\AduanOptions;
use App\Livewire\Orangtua\Traits\ResolvesNoKk;
use App\Models\Aduan;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.orangtuadashboard')]
class OrangtuaAduan extends Component
{
    use ResolvesNoKk;

    public string $filterStatus = '';

    public string $filterKategori = '';

    public string $filterBulan = '';

    public string $filterTahun = '';

    public int $filterLimit = 10;

    public int $currentPage = 1;

    /** @var array<int> */
    public array $limitOptions = [5, 10, 25, 50];

    public function mount(): void
    {
        $this->syncFilterFromRequest();
    }

    protected function syncFilterFromRequest(): void
    {
        $status = request()->query('status', '');
        $this->filterStatus = is_string($status) ? trim($status) : '';

        $kategori = request()->query('kategori', '');
        $this->filterKategori = is_string($kategori) ? trim($kategori) : '';

        $bulan = request()->query('bulan', '');
        $this->filterBulan = is_string($bulan) || is_numeric($bulan) ? trim((string) $bulan) : '';

        $tahun = request()->query('tahun', '');
        $this->filterTahun = is_string($tahun) || is_numeric($tahun) ? trim((string) $tahun) : '';

        $limit = request()->query('limit', 10);
        $this->filterLimit = is_numeric($limit) && in_array((int) $limit, $this->limitOptions, true)
            ? (int) $limit
            : 10;

        $page = request()->query('page', 1);
        $this->currentPage = is_numeric($page) && (int) $page >= 1 ? (int) $page : 1;
    }

    public function updatedFilterStatus(): void
    {
        $this->currentPage = 1;
    }

    public function updatedFilterKategori(): void
    {
        $this->currentPage = 1;
    }

    public function updatedFilterBulan(): void
    {
        $this->currentPage = 1;
    }

    public function updatedFilterTahun(): void
    {
        $this->currentPage = 1;
    }

    public function updatedFilterLimit(): void
    {
        $this->filterLimit = (int) $this->filterLimit;
        if (! in_array($this->filterLimit, $this->limitOptions, true)) {
            $this->filterLimit = 10;
        }
        $this->currentPage = 1;
    }

    public function gotoPage(int $page): void
    {
        $this->currentPage = max(1, $page);
    }

    public function previousPage(): void
    {
        $this->currentPage = max(1, $this->currentPage - 1);
    }

    public function nextPage(): void
    {
        $this->currentPage = max(1, $this->currentPage + 1);
    }

    protected function formatPeriodeLabel(): ?string
    {
        $bulanValid = $this->filterBulan !== '' && is_numeric($this->filterBulan)
            && (int) $this->filterBulan >= 1 && (int) $this->filterBulan <= 12;
        $tahunValid = $this->filterTahun !== '' && is_numeric($this->filterTahun);

        if ($bulanValid && $tahunValid) {
            return Carbon::create((int) $this->filterTahun, (int) $this->filterBulan, 1)
                ->locale('id')->translatedFormat('F Y');
        }
        if ($bulanValid) {
            return Carbon::create(now()->year, (int) $this->filterBulan, 1)
                ->locale('id')->translatedFormat('F');
        }
        if ($tahunValid) {
            return (string) (int) $this->filterTahun;
        }

        return null;
    }

    public function render()
    {
        $noKk = $this->resolveNoKk();
        $query = Aduan::with('posyandu:id_posyandu,nama_posyandu')
            ->where('no_kk', $noKk ?? '')
            ->orderByDesc('tanggal_aduan');

        if ($this->filterStatus !== '' && array_key_exists($this->filterStatus, AduanOptions::statusOptions())) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterKategori !== '' && array_key_exists($this->filterKategori, AduanOptions::kategoriOptions())) {
            $query->where('kategori', $this->filterKategori);
        }

        if ($this->filterBulan !== '' && is_numeric($this->filterBulan)
            && (int) $this->filterBulan >= 1 && (int) $this->filterBulan <= 12) {
            $query->whereMonth('tanggal_aduan', (int) $this->filterBulan);
        }

        if ($this->filterTahun !== '' && is_numeric($this->filterTahun)
            && (int) $this->filterTahun >= 2000 && (int) $this->filterTahun <= 2100) {
            $query->whereYear('tanggal_aduan', (int) $this->filterTahun);
        }

        $totalBaris = (clone $query)->count();
        $perPage = max(1, $this->filterLimit);
        $lastPage = max(1, (int) ceil($totalBaris / $perPage));
        $currentPage = min(max(1, $this->currentPage), $lastPage);
        $this->currentPage = $currentPage;

        $aduanList = $query->forPage($currentPage, $perPage)->get();
        $firstItem = $totalBaris > 0 ? (($currentPage - 1) * $perPage) + 1 : 0;
        $lastItem = $totalBaris > 0 ? min($currentPage * $perPage, $totalBaris) : 0;

        $filterAktif = $this->filterStatus !== ''
            || $this->filterKategori !== ''
            || $this->filterBulan !== ''
            || $this->filterTahun !== '';

        return view('livewire.orangtua.orangtua-aduan', [
            'aduanList' => $aduanList,
            'totalBaris' => $totalBaris,
            'firstItem' => $firstItem,
            'lastItem' => $lastItem,
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
            'hasPages' => $lastPage > 1,
            'filterAktif' => $filterAktif,
            'periodeLabel' => $this->formatPeriodeLabel(),
            'statusOptions' => AduanOptions::statusOptions(),
            'kategoriOptions' => AduanOptions::kategoriOptions(),
        ]);
    }
}
