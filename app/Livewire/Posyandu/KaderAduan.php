<?php

namespace App\Livewire\Posyandu;

use App\Helpers\AduanOptions;
use App\Livewire\Posyandu\Traits\PosyanduHelper;
use App\Livewire\Traits\NotificationModal;
use App\Models\Aduan;
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

class KaderAduan extends Component
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

    public ?int $selectedAduanId = null;

    public string $statusUpdate = '';

    public string $tanggapan = '';

    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public ?int $editingAduanId = null;

    public string $noKk = '';

    public string $judul = '';

    public string $kategori = AduanOptions::SPM_KESEHATAN;

    public string $isiAduan = '';

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
        $this->kategori = AduanOptions::SPM_KESEHATAN;
        $this->isiAduan = '';
        $this->resetValidation();
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

    public function simpanAduan(): void
    {
        $validNoKk = $this->getKeluargaList()->pluck('no_kk')->all();

        $this->validate([
            'noKk' => 'required|in:' . implode(',', $validNoKk),
            'judul' => 'required|string|min:5|max:150',
            'kategori' => 'required|in:' . implode(',', array_keys(AduanOptions::kategoriOptions())),
            'isiAduan' => 'required|string|min:10|max:2000',
        ], [
            'noKk.required' => 'Keluarga wajib dipilih.',
            'judul.required' => 'Judul aduan wajib diisi.',
            'judul.min' => 'Judul aduan minimal 5 karakter.',
            'isiAduan.required' => 'Isi aduan wajib diisi.',
            'isiAduan.min' => 'Isi aduan minimal 10 karakter.',
        ]);

        Aduan::create([
            'no_kk' => $this->noKk,
            'id_posyandu' => $this->posyanduId,
            'judul' => trim($this->judul),
            'isi_aduan' => trim($this->isiAduan),
            'kategori' => $this->kategori,
            'status' => AduanOptions::STATUS_MENUNGGU,
            'user_id' => Auth::id(),
            'tanggal_aduan' => now(),
        ]);

        $this->closeCreateModal();
        $this->resetPage();
        $this->showSuccessNotification('Aduan berhasil dibuat.');
    }

    public function openEditModal(int $id): void
    {
        $aduan = $this->findAduanForPosyandu($id);
        $orangtua = Orangtua::where('no_kk', $aduan->no_kk)->first();

        $this->editingAduanId = $aduan->id_aduan;
        $this->noKk = (string) $aduan->no_kk;
        $this->keluargaSearch = $orangtua
            ? $orangtua->nama . ' — KK: ' . $aduan->no_kk
            : 'Keluarga — KK: ' . $aduan->no_kk;
        $this->judul = $aduan->judul;
        $this->kategori = $aduan->kategori;
        $this->isiAduan = $aduan->isi_aduan;
        $this->statusUpdate = $aduan->status;
        $this->showKeluargaDropdown = false;
        $this->resetValidation();
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingAduanId = null;
        $this->resetCreateForm();
        $this->statusUpdate = '';
    }

    public function updateAduan(): void
    {
        if (! $this->editingAduanId) {
            return;
        }

        $validNoKk = $this->getKeluargaList()->pluck('no_kk')->all();

        $this->validate([
            'noKk' => 'required|in:' . implode(',', $validNoKk),
            'judul' => 'required|string|min:5|max:150',
            'kategori' => 'required|in:' . implode(',', array_keys(AduanOptions::kategoriOptions())),
            'isiAduan' => 'required|string|min:10|max:2000',
            'statusUpdate' => 'required|in:' . implode(',', array_keys(AduanOptions::statusOptions())),
        ], [
            'noKk.required' => 'Keluarga wajib dipilih.',
            'judul.required' => 'Judul aduan wajib diisi.',
            'judul.min' => 'Judul aduan minimal 5 karakter.',
            'isiAduan.required' => 'Isi aduan wajib diisi.',
            'isiAduan.min' => 'Isi aduan minimal 10 karakter.',
            'statusUpdate.required' => 'Status wajib dipilih.',
        ]);

        $aduan = $this->findAduanForPosyandu($this->editingAduanId);
        $aduan->update([
            'no_kk' => $this->noKk,
            'judul' => trim($this->judul),
            'isi_aduan' => trim($this->isiAduan),
            'kategori' => $this->kategori,
            'status' => $this->statusUpdate,
        ]);

        $this->closeEditModal();
        $this->showSuccessNotification('Aduan berhasil diperbarui.');
    }

    public function hapusAduan(int $id): void
    {
        $aduan = $this->findAduanForPosyandu($id);
        $aduan->delete();

        if ($this->selectedAduanId === $id) {
            $this->closeDetailModal();
        }

        if ($this->editingAduanId === $id) {
            $this->closeEditModal();
        }

        $this->showSuccessNotification('Aduan berhasil dihapus.');
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

    public function viewAduan(int $id): void
    {
        $aduan = $this->findAduanForPosyandu($id);
        $this->selectedAduanId = $aduan->id_aduan;
        $this->statusUpdate = $aduan->status;
        $this->tanggapan = $aduan->tanggapan ?? '';
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedAduanId = null;
        $this->statusUpdate = '';
        $this->tanggapan = '';
        $this->resetValidation();
    }

    public function simpanTanggapan(): void
    {
        $this->validate([
            'statusUpdate' => 'required|in:' . implode(',', array_keys(AduanOptions::statusOptions())),
            'tanggapan' => 'nullable|string|max:2000',
        ], [
            'statusUpdate.required' => 'Status wajib dipilih.',
        ]);

        $aduan = $this->findAduanForPosyandu($this->selectedAduanId);
        $aduan->update([
            'status' => $this->statusUpdate,
            'tanggapan' => trim($this->tanggapan) !== '' ? trim($this->tanggapan) : null,
        ]);

        $this->closeDetailModal();
        $this->showSuccessNotification('Tanggapan aduan berhasil disimpan.');
    }

    protected function findAduanForPosyandu(int $id): Aduan
    {
        return $this->baseQuery()->where('id_aduan', $id)->firstOrFail();
    }

    protected function baseQuery()
    {
        return Aduan::with(['user:id,name', 'posyandu:id_posyandu,nama_posyandu'])
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

        return $query;
    }

    public function render()
    {
        $aduanList = $this->applyFilters(
            $this->baseQuery()->orderByDesc('tanggal_aduan')
        )->paginate(10);

        $noKkList = $aduanList->pluck('no_kk')->unique()->filter()->values();
        $orangtuaMap = Orangtua::whereIn('no_kk', $noKkList)
            ->get()
            ->keyBy('no_kk');

        $selectedAduan = null;
        $detailOrangtua = null;
        if ($this->showDetailModal && $this->selectedAduanId) {
            $selectedAduan = $this->baseQuery()
                ->where('id_aduan', $this->selectedAduanId)
                ->first();
            if ($selectedAduan) {
                $detailOrangtua = Orangtua::where('no_kk', $selectedAduan->no_kk)->first();
            }
        }

        return view('livewire.posyandu.kader-aduan', [
            'title' => 'Aduan - ' . $this->posyandu->nama_posyandu,
            'posyandu' => $this->posyandu,
            'aduanList' => $aduanList,
            'orangtuaMap' => $orangtuaMap,
            'selectedAduan' => $selectedAduan,
            'detailOrangtua' => $detailOrangtua,
            'keluargaList' => $this->getKeluargaList(),
            'filteredKeluargaList' => $this->getFilteredKeluargaList(),
            'statusOptions' => AduanOptions::statusOptions(),
            'kategoriOptions' => AduanOptions::kategoriOptions(),
        ]);
    }
}
