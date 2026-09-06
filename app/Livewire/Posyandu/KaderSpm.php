<?php

namespace App\Livewire\Posyandu;

use App\Helpers\SpmOptions;
use App\Livewire\Posyandu\Traits\PosyanduHelper;
use App\Livewire\Traits\NotificationModal;
use App\Models\Spm;
use App\Models\Orangtua;
use App\Models\SasaranBayibalita;
use App\Models\SasaranDewasa;
use App\Models\SasaranLansia;
use App\Models\SasaranPralansia;
use App\Models\SasaranRemaja;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class KaderSpm extends Component
{
    use NotificationModal;
    use PosyanduHelper;
    use WithPagination;

    public string $search = '';

    public string $filterStatus = '';

    public string $filterKategori = '';

    public string $filterBulan = '';

    public string $filterTahun = '';

    public string $viewMode = 'table';

    public bool $showDetailModal = false;

    public ?int $selectedSpmId = null;

    public string $statusUpdate = '';

    public string $tanggapan = '';

    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public ?int $editingSpmId = null;

    public string $noKk = '';

    public string $judul = '';

    public string $kategori = SpmOptions::SPM_TRANTIBUMLINMAS;

    public string $isiSpm = '';

    public string $noSuratPermohonanRt = '';

    public string $keluargaSearch = '';

    public bool $showKeluargaDropdown = false;

    #[Layout('layouts.posyandudashboard')]
    public function mount(): void
    {
        $this->initializePosyandu();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedFilterKategori(): void
    {
        $this->resetPage();
    }

    public function updatedFilterBulan(): void
    {
        $this->resetPage();
    }

    public function updatedFilterTahun(): void
    {
        $this->resetPage();
    }

    public function setViewMode(string $mode): void
    {
        if (in_array($mode, ['table', 'card'], true)) {
            $this->viewMode = $mode;
        }
    }

    public function openCreateModal(): void
    {
        $this->resetCreateForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetCreateForm();
    }

    protected function resetCreateForm(): void
    {
        $this->noKk = '';
        $this->keluargaSearch = '';
        $this->showKeluargaDropdown = false;
        $this->judul = '';
        $this->kategori = SpmOptions::SPM_TRANTIBUMLINMAS;
        $this->isiSpm = '';
        $this->noSuratPermohonanRt = '';
        $this->resetValidation();
    }

    public function updatedKategori(): void
    {
        if ($this->kategori !== SpmOptions::SPM_PEKERJAAN_UMUM) {
            $this->noSuratPermohonanRt = '';
            $this->resetValidation('noSuratPermohonanRt');
        }
    }

    public function updatedKeluargaSearch(): void
    {
        $term = trim($this->keluargaSearch);

        $matched = $this->getKeluargaList()->first(
            fn ($item) => ($item['nama'] . ' — KK: ' . $item['no_kk']) === $this->keluargaSearch
        );

        if ($matched) {
            $this->noKk = $matched['no_kk'];
            $this->showKeluargaDropdown = false;

            return;
        }

        $this->noKk = '';
        $this->showKeluargaDropdown = $term !== '';
    }

    public function hideKeluargaDropdown(): void
    {
        $this->showKeluargaDropdown = false;
    }

    public function onKeluargaFocus(): void
    {
        if ($this->noKk === '' && trim($this->keluargaSearch) !== '') {
            $this->showKeluargaDropdown = true;
        }
    }

    public function selectKeluarga(string $noKk): void
    {
        $keluarga = $this->getKeluargaList()->firstWhere('no_kk', $noKk);
        if (! $keluarga) {
            return;
        }

        $this->noKk = $keluarga['no_kk'];
        $this->keluargaSearch = $keluarga['nama'] . ' — KK: ' . $keluarga['no_kk'];
        $this->showKeluargaDropdown = false;
        $this->resetValidation('noKk');
    }

    public function clearKeluarga(): void
    {
        $this->noKk = '';
        $this->keluargaSearch = '';
        $this->showKeluargaDropdown = false;
    }

    public function simpanSpm(): void
    {
        $validNoKk = $this->getKeluargaList()->pluck('no_kk')->all();

        $this->validate([
            'noKk' => 'required|in:' . implode(',', $validNoKk),
            'judul' => 'required|string|min:5|max:150',
            'kategori' => 'required|in:' . implode(',', array_keys(SpmOptions::kategoriOptions())),
            'isiSpm' => 'required|string|min:10|max:2000',
            'noSuratPermohonanRt' => $this->kategori === SpmOptions::SPM_PEKERJAAN_UMUM
                ? 'required|string|min:3|max:100'
                : 'nullable|string|max:100',
        ], [
            'noKk.required' => 'Keluarga wajib dipilih.',
            'judul.required' => 'Judul wajib diisi.',
            'judul.min' => 'Judul minimal 5 karakter.',
            'isiSpm.required' => 'Isi / keterangan wajib diisi.',
            'isiSpm.min' => 'Isi / keterangan minimal 10 karakter.',
            'noSuratPermohonanRt.required' => 'No Surat Permohonan RT wajib diisi untuk Bidang Pekerjaan Umum.',
            'noSuratPermohonanRt.min' => 'No Surat Permohonan RT minimal 3 karakter.',
        ]);

        Spm::create([
            'no_kk' => $this->noKk,
            'id_posyandu' => $this->posyanduId,
            'judul' => trim($this->judul),
            'isi_aduan' => trim($this->isiSpm),
            'no_surat_permohonan_rt' => $this->kategori === SpmOptions::SPM_PEKERJAAN_UMUM
                ? trim($this->noSuratPermohonanRt)
                : null,
            'kategori' => $this->kategori,
            'status' => SpmOptions::STATUS_MENUNGGU,
            'user_id' => Auth::id(),
            'tanggal_aduan' => now(),
        ]);

        $this->closeCreateModal();
        $this->resetPage();
        $this->showSuccessNotification('Data 6 SPM berhasil dibuat.');
    }

    public function openEditModal(int $id): void
    {
        $aduan = $this->findSpmForPosyandu($id);
        $orangtua = Orangtua::where('no_kk', $aduan->no_kk)->first();

        $this->editingSpmId = $aduan->id_aduan;
        $this->noKk = (string) $aduan->no_kk;
        $this->keluargaSearch = $orangtua
            ? $orangtua->nama . ' — KK: ' . $aduan->no_kk
            : 'Keluarga — KK: ' . $aduan->no_kk;
        $this->judul = $aduan->judul;
        $this->kategori = $aduan->kategori;
        $this->isiSpm = $aduan->isi_aduan;
        $this->noSuratPermohonanRt = $aduan->no_surat_permohonan_rt ?? '';
        $this->statusUpdate = $aduan->status;
        $this->showKeluargaDropdown = false;
        $this->resetValidation();
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingSpmId = null;
        $this->resetCreateForm();
        $this->statusUpdate = '';
    }

    public function updateSpm(): void
    {
        if (! $this->editingSpmId) {
            return;
        }

        $validNoKk = $this->getKeluargaList()->pluck('no_kk')->all();

        $this->validate([
            'noKk' => 'required|in:' . implode(',', $validNoKk),
            'judul' => 'required|string|min:5|max:150',
            'kategori' => 'required|in:' . implode(',', array_keys(SpmOptions::kategoriOptions())),
            'isiSpm' => 'required|string|min:10|max:2000',
            'noSuratPermohonanRt' => $this->kategori === SpmOptions::SPM_PEKERJAAN_UMUM
                ? 'required|string|min:3|max:100'
                : 'nullable|string|max:100',
            'statusUpdate' => 'required|in:' . implode(',', array_keys(SpmOptions::statusOptions())),
        ], [
            'noKk.required' => 'Keluarga wajib dipilih.',
            'judul.required' => 'Judul wajib diisi.',
            'judul.min' => 'Judul minimal 5 karakter.',
            'isiSpm.required' => 'Isi / keterangan wajib diisi.',
            'isiSpm.min' => 'Isi / keterangan minimal 10 karakter.',
            'noSuratPermohonanRt.required' => 'No Surat Permohonan RT wajib diisi untuk Bidang Pekerjaan Umum.',
            'noSuratPermohonanRt.min' => 'No Surat Permohonan RT minimal 3 karakter.',
            'statusUpdate.required' => 'Status wajib dipilih.',
        ]);

        $aduan = $this->findSpmForPosyandu($this->editingSpmId);
        $aduan->update([
            'no_kk' => $this->noKk,
            'judul' => trim($this->judul),
            'isi_aduan' => trim($this->isiSpm),
            'no_surat_permohonan_rt' => $this->kategori === SpmOptions::SPM_PEKERJAAN_UMUM
                ? trim($this->noSuratPermohonanRt)
                : null,
            'kategori' => $this->kategori,
            'status' => $this->statusUpdate,
        ]);

        $this->closeEditModal();
        $this->showSuccessNotification('Data 6 SPM berhasil diperbarui.');
    }

    public function hapusSpm(int $id): void
    {
        $aduan = $this->findSpmForPosyandu($id);
        $aduan->delete();

        if ($this->selectedSpmId === $id) {
            $this->closeDetailModal();
        }

        if ($this->editingSpmId === $id) {
            $this->closeEditModal();
        }

        $this->showSuccessNotification('Data 6 SPM berhasil dihapus.');
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{no_kk: string, nama: string}>
     */
    protected function getKeluargaList()
    {
        $models = [
            SasaranBayibalita::class,
            SasaranRemaja::class,
            SasaranDewasa::class,
            SasaranPralansia::class,
            SasaranLansia::class,
        ];

        $noKkList = collect();
        foreach ($models as $model) {
            $noKkList = $noKkList->merge(
                $model::where('id_posyandu', $this->posyanduId)
                    ->whereNotNull('no_kk_sasaran')
                    ->where('no_kk_sasaran', '!=', '')
                    ->pluck('no_kk_sasaran')
            );
        }

        $uniqueNoKk = $noKkList->map(fn ($kk) => (string) $kk)->unique()->sort()->values();
        $orangtuaMap = Orangtua::whereIn('no_kk', $uniqueNoKk)->get()->keyBy(fn ($o) => (string) $o->no_kk);

        return $uniqueNoKk->map(function ($kk) use ($orangtuaMap) {
            return [
                'no_kk' => $kk,
                'nama' => $orangtuaMap->get($kk)?->nama ?? 'Keluarga',
            ];
        })->values();
    }

    protected function getFilteredKeluargaList()
    {
        $list = $this->getKeluargaList();
        $term = trim($this->keluargaSearch);

        if ($term === '') {
            return $list;
        }

        $needle = strtolower($term);

        return $list->filter(function ($item) use ($needle) {
            return str_contains(strtolower($item['nama']), $needle)
                || str_contains($item['no_kk'], $needle);
        })->values();
    }

    public function viewSpm(int $id): void
    {
        $aduan = $this->findSpmForPosyandu($id);
        $this->selectedSpmId = $aduan->id_aduan;
        $this->statusUpdate = $aduan->status;
        $this->tanggapan = $aduan->tanggapan ?? '';
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedSpmId = null;
        $this->statusUpdate = '';
        $this->tanggapan = '';
        $this->resetValidation();
    }

    public function simpanTanggapan(): void
    {
        $this->validate([
            'statusUpdate' => 'required|in:' . implode(',', array_keys(SpmOptions::statusOptions())),
            'tanggapan' => 'nullable|string|max:2000',
        ], [
            'statusUpdate.required' => 'Status wajib dipilih.',
        ]);

        $aduan = $this->findSpmForPosyandu($this->selectedSpmId);
        $aduan->update([
            'status' => $this->statusUpdate,
            'tanggapan' => trim($this->tanggapan) !== '' ? trim($this->tanggapan) : null,
        ]);

        $this->closeDetailModal();
        $this->showSuccessNotification('Tanggapan 6 SPM berhasil disimpan.');
    }

    protected function findSpmForPosyandu(int $id): Spm
    {
        return $this->baseQuery()->where('id_aduan', $id)->firstOrFail();
    }

    protected function baseQuery()
    {
        return Spm::with(['user:id,name', 'posyandu:id_posyandu,nama_posyandu'])
            ->where('id_posyandu', $this->posyanduId);
    }

    protected function applyFilters($query)
    {
        if ($this->search !== '') {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('judul', 'like', $term)
                    ->orWhere('isi_aduan', 'like', $term)
                    ->orWhere('no_kk', 'like', $term);
            });
        }

        if ($this->filterStatus !== '' && array_key_exists($this->filterStatus, SpmOptions::statusOptions())) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterKategori !== '' && array_key_exists($this->filterKategori, SpmOptions::kategoriOptions())) {
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

        return $query;
    }

    public function render()
    {
        $spmList = $this->applyFilters(
            $this->baseQuery()->orderByDesc('tanggal_aduan')
        )->paginate(10);

        $noKkList = $spmList->pluck('no_kk')->unique()->filter()->values();
        $orangtuaMap = Orangtua::whereIn('no_kk', $noKkList)
            ->get()
            ->keyBy('no_kk');

        $selectedSpm = null;
        $detailOrangtua = null;
        if ($this->showDetailModal && $this->selectedSpmId) {
            $selectedSpm = $this->baseQuery()
                ->where('id_aduan', $this->selectedSpmId)
                ->first();
            if ($selectedSpm) {
                $detailOrangtua = Orangtua::where('no_kk', $selectedSpm->no_kk)->first();
            }
        }

        return view('livewire.posyandu.kader-spm', [
            'title' => '6 SPM - ' . $this->posyandu->nama_posyandu,
            'posyandu' => $this->posyandu,
            'spmList' => $spmList,
            'orangtuaMap' => $orangtuaMap,
            'selectedSpm' => $selectedSpm,
            'detailOrangtua' => $detailOrangtua,
            'keluargaList' => $this->getKeluargaList(),
            'filteredKeluargaList' => $this->getFilteredKeluargaList(),
            'statusOptions' => SpmOptions::statusOptions(),
            'kategoriOptions' => SpmOptions::kategoriOptions(),
        ]);
    }
}
