<?php

namespace App\Livewire\Posyandu;

use App\Livewire\SuperAdmin\Traits\ImunisasiCrud;
use App\Models\Imunisasi;
use App\Models\Kader;
use App\Models\Posyandu;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

class KaderImunisasi extends Component
{
    use ImunisasiCrud;

    public $posyandu;
    public $posyanduId;
    public $search = '';

    #[Layout('layouts.posyandudashboard')]

    public function mount()
    {
        // Ambil posyandu dari kader yang login
        $user = Auth::user();
        $kader = Kader::where('id_users', $user->id)->first();

        if (!$kader) {
            abort(403, 'Anda bukan kader terdaftar.');
        }

        $this->posyanduId = $kader->id_posyandu;
        $this->loadPosyandu();
    }

    /**
     * Load data posyandu
     */
    private function loadPosyandu()
    {
        $posyandu = Posyandu::find($this->posyanduId);

        if (!$posyandu) {
            abort(404, 'Posyandu tidak ditemukan');
        }

        $this->posyandu = $posyandu;
    }

    /**
     * Refresh data posyandu (agar Livewire view update)
     */
    protected function refreshPosyandu()
    {
        $this->loadPosyandu();
    }

    /**
     * Override openImunisasiModal untuk set posyandu otomatis
     */
    public function openImunisasiModal($id = null)
    {
        if ($id) {
            $this->editImunisasi($id);
        } else {
            $this->resetImunisasiFields();
            // Set posyandu otomatis dari kader
            $this->id_posyandu_imunisasi = $this->posyanduId;
            $this->loadSasaranList();
            $this->isImunisasiModalOpen = true;
        }
    }

    /**
     * Override updatedIdSasaranImunisasi untuk memastikan method bisa dipanggil
     * Method ini dipanggil otomatis oleh Livewire ketika id_sasaran_imunisasi berubah
     */
    public function updatedIdSasaranImunisasi($value)
    {
        if ($value && !empty($this->sasaranList)) {
            // Cari sasaran dari list berdasarkan ID
            $sasaran = null;
            
            // Jika sudah ada kategori, cari yang sesuai dengan ID dan kategori
            if ($this->kategori_sasaran_imunisasi) {
                $sasaran = collect($this->sasaranList)->first(function($s) use ($value) {
                    return $s['id'] == $value && $s['kategori'] == $this->kategori_sasaran_imunisasi;
                });
            }
            
            // Jika tidak ditemukan dengan kategori, cari berdasarkan ID saja
            if (!$sasaran) {
                $sasaran = collect($this->sasaranList)->firstWhere('id', $value);
            }
            
            if ($sasaran && isset($sasaran['kategori'])) {
                // Set kategori langsung dari list untuk menghindari konflik ID
                $this->kategori_sasaran_imunisasi = $sasaran['kategori'];
            }
        } else {
            $this->kategori_sasaran_imunisasi = '';
        }
    }

    /**
     * Override storeImunisasi untuk validasi posyandu kader
     */
    public function storeImunisasi()
    {
        // Pastikan posyandu sesuai dengan kader
        $this->id_posyandu_imunisasi = $this->posyanduId;

         // Gabungkan hari, bulan, tahun menjadi tanggal imunisasi
        if (method_exists($this, 'combineTanggalImunisasi')) {
            $this->combineTanggalImunisasi();
        }

        // Panggil method dari trait
        $this->validate([
            'id_posyandu_imunisasi' => 'required|exists:posyandu,id_posyandu',
            'id_sasaran_imunisasi' => 'required',
            'kategori_sasaran_imunisasi' => 'required|in:bayibalita,remaja,dewasa,pralansia,lansia',
            'jenis_imunisasi' => 'required|string|max:255',
            'hari_imunisasi' => 'required|numeric|min:1|max:31',
            'bulan_imunisasi' => 'required|numeric|min:1|max:12',
            'tahun_imunisasi' => 'required|numeric|min:1900|max:' . date('Y'),
            'tanggal_imunisasi' => 'required|date',
            'tinggi_badan' => 'nullable|numeric|min:0|max:300',
            'berat_badan' => 'nullable|numeric|min:0|max:300',
            'keterangan' => 'nullable|string',
        ], [
            'id_posyandu_imunisasi.required' => 'Posyandu wajib dipilih.',
            'id_posyandu_imunisasi.exists' => 'Posyandu yang dipilih tidak valid.',
            'id_sasaran_imunisasi.required' => 'Sasaran wajib dipilih.',
            'kategori_sasaran_imunisasi.required' => 'Kategori sasaran wajib dipilih.',
            'kategori_sasaran_imunisasi.in' => 'Kategori sasaran tidak valid.',
            'jenis_imunisasi.required' => 'Jenis imunisasi wajib diisi.',
            'hari_imunisasi.required' => 'Hari imunisasi wajib diisi.',
            'hari_imunisasi.numeric' => 'Hari imunisasi harus berupa angka.',
            'hari_imunisasi.min' => 'Hari imunisasi minimal 1.',
            'hari_imunisasi.max' => 'Hari imunisasi maksimal 31.',
            'bulan_imunisasi.required' => 'Bulan imunisasi wajib diisi.',
            'bulan_imunisasi.numeric' => 'Bulan imunisasi harus berupa angka.',
            'bulan_imunisasi.min' => 'Bulan imunisasi minimal 1.',
            'bulan_imunisasi.max' => 'Bulan imunisasi maksimal 12.',
            'tahun_imunisasi.required' => 'Tahun imunisasi wajib diisi.',
            'tahun_imunisasi.numeric' => 'Tahun imunisasi harus berupa angka.',
            'tahun_imunisasi.min' => 'Tahun imunisasi minimal 1900.',
            'tahun_imunisasi.max' => 'Tahun imunisasi maksimal ' . date('Y') . '.',
            'tanggal_imunisasi.required' => 'Tanggal imunisasi wajib diisi.',
            'tanggal_imunisasi.date' => 'Tanggal imunisasi tidak valid.',
            'tinggi_badan.numeric' => 'Tinggi badan harus berupa angka.',
            'tinggi_badan.min' => 'Tinggi badan minimal 0 cm.',
            'tinggi_badan.max' => 'Tinggi badan maksimal 300 cm.',
            'berat_badan.numeric' => 'Berat badan harus berupa angka.',
            'berat_badan.min' => 'Berat badan minimal 0 kg.',
            'berat_badan.max' => 'Berat badan maksimal 300 kg.',
        ]);

        $data = [
            'id_posyandu' => $this->id_posyandu_imunisasi,
            'id_users' => Auth::id(),
            'id_sasaran' => $this->id_sasaran_imunisasi,
            'kategori_sasaran' => $this->kategori_sasaran_imunisasi,
            'jenis_imunisasi' => $this->jenis_imunisasi,
            'tanggal_imunisasi' => $this->tanggal_imunisasi,
            'tinggi_badan' => $this->tinggi_badan !== '' ? $this->tinggi_badan : null,
            'berat_badan' => $this->berat_badan !== '' ? $this->berat_badan : null,
            'keterangan' => $this->keterangan,
        ];

        if ($this->id_imunisasi) {
            // UPDATE
            $imunisasi = Imunisasi::findOrFail($this->id_imunisasi);
            $imunisasi->update($data);
            session()->flash('message', 'Data Imunisasi berhasil diperbarui.');
        } else {
            // CREATE
            Imunisasi::create($data);
            session()->flash('message', 'Data Imunisasi berhasil ditambahkan.');
        }

        $this->refreshPosyandu();
        $this->closeImunisasiModal();
    }

    public function render()
    {
        // Ambil imunisasi hanya untuk posyandu kader ini dengan filter search
        $query = Imunisasi::where('id_posyandu', $this->posyanduId)
            ->with(['user', 'posyandu']);

        // Filter berdasarkan search
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('jenis_imunisasi', 'like', '%' . $this->search . '%')
                  ->orWhere('keterangan', 'like', '%' . $this->search . '%')
                  ->orWhere('kategori_sasaran', 'like', '%' . $this->search . '%');
            });
        }

        $imunisasiList = $query->orderBy('tanggal_imunisasi', 'desc')->get();

        return view('livewire.posyandu.kader-imunisasi', [
            'title' => 'Imunisasi - ' . $this->posyandu->nama_posyandu,
            'imunisasiList' => $imunisasiList,
            'isImunisasiModalOpen' => $this->isImunisasiModalOpen,
            'id_imunisasi' => $this->id_imunisasi,
            'posyandu' => $this->posyandu,
            'sasaranList' => $this->sasaranList,
        ]);
    }
}
