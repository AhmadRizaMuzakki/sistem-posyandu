<?php

namespace App\Livewire\SuperAdmin;

use App\Livewire\SuperAdmin\Traits\BalitaCrud;
use App\Livewire\SuperAdmin\Traits\KaderCrud;
use App\Livewire\SuperAdmin\Traits\RemajaCrud;
use App\Livewire\SuperAdmin\Traits\DewasaCrud;
use App\Livewire\SuperAdmin\Traits\PralansiaCrud;
use App\Livewire\SuperAdmin\Traits\LansiaCrud;
use App\Livewire\SuperAdmin\Traits\IbuHamilCrud;
use App\Models\Posyandu;
use App\Models\User;
use App\Models\Orangtua;
use App\Models\SasaranBayibalita;
use App\Models\SasaranDewasa;
use App\Models\SasaranPralansia;
use App\Models\SasaranLansia;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PosyanduDetail extends Component
{
    use BalitaCrud, KaderCrud, RemajaCrud, DewasaCrud, PralansiaCrud, LansiaCrud, IbuHamilCrud, WithFileUploads;

    public $posyandu;
    public $posyanduId;
    public $skFile;
    public $showUploadModal = false;
    
    // Modal konfirmasi
    public $showConfirmModal = false;
    public $confirmMessage = '';
    public $confirmAction = '';

    // Search properties for each sasaran type
    public $search_bayibalita = '';
    public $search_remaja = '';
    public $search_dewasa = '';
    public $search_pralansia = '';
    public $search_lansia = '';
    public $search_ibuhamil = '';

    // Search property for user dropdown
    public $searchUser = '';

    // Pagination properties for each sasaran type
    public $page_bayibalita = 1;
    public $page_remaja = 1;
    public $page_dewasa = 1;
    public $page_pralansia = 1;
    public $page_lansia = 1;
    public $page_ibuhamil = 1;

    // Items per page
    public $perPage = 5;

    #[Layout('layouts.superadmindashboard')]

    public function mount($id)
    {
        try {
            $decryptedId = decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID tidak valid');
        }

        $this->posyanduId = $decryptedId;
        $this->loadPosyandu();
    }

    /**
     * Load data posyandu dengan relasi
     */
    private function loadPosyandu()
    {
        $relations = [
            'kader.user',
            'sasaran_bayibalita.user',
            'sasaran_bayibalita.orangtua',
            'sasaran_remaja.user',
            'sasaran_remaja.orangtua',
            'sasaran_dewasa.user',
            'sasaran_dewasa.orangtua',
            'sasaran_pralansia.user',
            'sasaran_pralansia.orangtua',
            'sasaran_lansia.user',
            'sasaran_lansia.orangtua',
            'sasaran_ibuhamil',
        ];

        $posyandu = Posyandu::with($relations)->find($this->posyanduId);

        if (!$posyandu) {
            abort(404, 'Posyandu tidak ditemukan');
        }

        $this->posyandu = $posyandu;
    }

    /**
     * Refresh data posyandu + relasi (agar Livewire view update)
     */
    protected function refreshPosyandu()
    {
        $this->loadPosyandu();
    }

    /**
     * Reset pagination when search changes
     */
    public function updatedSearchBayibalita()
    {
        $this->page_bayibalita = 1;
    }

    public function updatedSearchRemaja()
    {
        $this->page_remaja = 1;
    }

    public function updatedSearchDewasa()
    {
        $this->page_dewasa = 1;
    }

    public function updatedSearchPralansia()
    {
        $this->page_pralansia = 1;
    }

    public function updatedSearchLansia()
    {
        $this->page_lansia = 1;
    }

    public function updatedSearchIbuhamil()
    {
        $this->page_ibuhamil = 1;
    }

    /**
     * Upload SK Posyandu dengan validasi keamanan
     */
    public function uploadSk()
    {
        $this->validate([
            'skFile' => [
                'required',
                'file',
                'mimes:pdf,doc,docx',
                'max:5120', // 5MB max
                function ($attribute, $value, $fail) {
                    // Validasi MIME type tambahan untuk keamanan
                    $allowedMimes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                    $fileMime = $value->getMimeType();

                    if (!in_array($fileMime, $allowedMimes)) {
                        $fail('Format file tidak diizinkan. Hanya PDF dan DOC/DOCX yang diperbolehkan.');
                    }

                    // Validasi ekstensi file
                    $extension = strtolower($value->getClientOriginalExtension());
                    $allowedExtensions = ['pdf', 'doc', 'docx'];

                    if (!in_array($extension, $allowedExtensions)) {
                        $fail('Ekstensi file tidak diizinkan.');
                    }

                    // Validasi ukuran file (5MB)
                    if ($value->getSize() > 5242880) {
                        $fail('Ukuran file maksimal 5MB.');
                    }

                    // Validasi nama file (prevent path traversal)
                    $filename = $value->getClientOriginalName();
                    if (preg_match('/\.\./', $filename) || preg_match('/[\/\\\\]/', $filename)) {
                        $fail('Nama file tidak valid.');
                    }
                }
            ],
        ], [
            'skFile.required' => 'File SK harus diupload.',
            'skFile.file' => 'File yang diupload tidak valid.',
            'skFile.mimes' => 'Format file harus PDF, DOC, atau DOCX.',
            'skFile.max' => 'Ukuran file maksimal 5MB.',
        ]);

        try {
            // Generate nama file yang aman
            $dir = public_path('uploads/sk_posyandu');
            if (!File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            if ($this->posyandu->sk_posyandu) {
                $rel = ltrim(str_replace('/storage/', '', $this->posyandu->sk_posyandu), '/');
                $oldFull = uploads_base_path('uploads/' . $rel);
                if (File::exists($oldFull)) {
                    File::delete($oldFull);
                }
            }
            $originalName = $this->skFile->getClientOriginalName();
            $extension = $this->skFile->getClientOriginalExtension();
            $safeName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '_' . time() . '.' . $extension;
            File::copy($this->skFile->getRealPath(), $dir . DIRECTORY_SEPARATOR . $safeName);

            $this->posyandu->update([
                'sk_posyandu' => 'sk_posyandu/' . $safeName
            ]);

            // Refresh data
            $this->loadPosyandu();

            // Reset
            $this->skFile = null;
            $this->showUploadModal = false;

            session()->flash('message', 'File SK berhasil diupload.');
            session()->flash('messageType', 'success');
        } catch (\Exception $e) {
            session()->flash('message', 'Gagal mengupload file SK: ' . $e->getMessage());
            session()->flash('messageType', 'error');
        }
    }

    /**
     * Buka modal konfirmasi
     */
    public function openConfirmModal($action, $message)
    {
        $this->confirmAction = $action;
        $this->confirmMessage = $message;
        $this->showConfirmModal = true;
    }

    /**
     * Tutup modal konfirmasi
     */
    public function closeConfirmModal()
    {
        $this->showConfirmModal = false;
        $this->confirmMessage = '';
        $this->confirmAction = '';
    }

    /**
     * Eksekusi action setelah konfirmasi
     */
    public function executeConfirmAction()
    {
        if ($this->confirmAction === 'deleteSk') {
            $this->deleteSk();
        }
        $this->closeConfirmModal();
    }

    /**
     * Hapus file SK
     */
    public function deleteSk()
    {
        try {
            if ($this->posyandu->sk_posyandu) {
                $rel = ltrim(str_replace('/storage/', '', $this->posyandu->sk_posyandu), '/');
                $full = uploads_base_path('uploads/' . $rel);
                if (File::exists($full)) {
                    File::delete($full);
                }
            }

            $this->posyandu->update([
                'sk_posyandu' => null
            ]);

            $this->loadPosyandu();

            session()->flash('message', 'File SK berhasil dihapus.');
            session()->flash('messageType', 'success');
        } catch (\Exception $e) {
            session()->flash('message', 'Gagal menghapus file SK: ' . $e->getMessage());
            session()->flash('messageType', 'error');
        }
    }

    /**
     * Get filtered and paginated sasaran data
     */
    public function getFilteredSasaran($sasaranCollection, $search, $page)
    {
        $query = $sasaranCollection;

        if (!empty($search)) {
            $query = $query->filter(function ($item) use ($search) {
                return stripos($item->nama_sasaran ?? '', $search) !== false;
            })->values();
        } else {
            $query = $query->values();
        }

        $total = $query->count();
        $totalPages = $total > 0 ? ceil($total / $this->perPage) : 1;

        $paginated = $query->slice(($page - 1) * $this->perPage, $this->perPage);

        return [
            'data' => $paginated,
            'total' => $total,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'per_page' => $this->perPage,
        ];
    }

    /**
     * Get count of orangtua by age range (for statistics)
     */
    public function getOrangtuaCountByUmur($minAge, $maxAge = null)
    {
        $query = Orangtua::query();

        // Filter by age
        if ($maxAge !== null) {
            $query->byAgeRange($minAge, $maxAge);
        } else {
            $query->byMinAge($minAge);
        }

        return $query->count();
    }

    /**
     * Get orangtua data by age range and format as sasaran
     */
    public function getOrangtuaByUmur($minAge, $maxAge = null, $search = '', $page = 1, $type = 'dewasa')
    {
        $query = Orangtua::query();

        // Filter by age
        if ($maxAge !== null) {
            $query->byAgeRange($minAge, $maxAge);
        } else {
            $query->byMinAge($minAge);
        }

        // Filter by search
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('nik', 'like', '%' . $search . '%');
            });
        }

        // Get all results
        $allOrangtua = $query->get();

        // Format data to match sasaran structure
        $formattedData = $allOrangtua->map(function($orangtua) use ($type) {
            // Create pseudo user object for compatibility
            $email = $orangtua->nik . '@gmail.com';
            $pseudoUser = new class($orangtua->nama, $email) {
                public $name;
                public $email;

                public function __construct($name, $email = null) {
                    $this->name = $name;
                    $this->email = $email;
                }

                public function hasRole($role) {
                    return $role === 'orangtua';
                }
            };

            // Cek apakah orangtua ini juga ada di tabel sasaran dengan nik_orangtua
            $orangtuaData = null;
            $sasaranDewasa = \App\Models\SasaranDewasa::where('nik_sasaran', $orangtua->nik)
                ->whereNotNull('nik_orangtua')
                ->where('nik_orangtua', '!=', '-')
                ->with('orangtua')
                ->first();
            if ($sasaranDewasa && $sasaranDewasa->orangtua) {
                $orangtuaData = $sasaranDewasa->orangtua;
            } else {
                $sasaranPralansia = \App\Models\SasaranPralansia::where('nik_sasaran', $orangtua->nik)
                    ->whereNotNull('nik_orangtua')
                    ->where('nik_orangtua', '!=', '-')
                    ->with('orangtua')
                    ->first();
                if ($sasaranPralansia && $sasaranPralansia->orangtua) {
                    $orangtuaData = $sasaranPralansia->orangtua;
                } else {
                    $sasaranLansia = \App\Models\SasaranLansia::where('nik_sasaran', $orangtua->nik)
                        ->whereNotNull('nik_orangtua')
                        ->where('nik_orangtua', '!=', '-')
                        ->with('orangtua')
                        ->first();
                    if ($sasaranLansia && $sasaranLansia->orangtua) {
                        $orangtuaData = $sasaranLansia->orangtua;
                    }
                }
            }

            // Create pseudo sasaran object with getKey method
            return new class($orangtua, $pseudoUser, $type, $orangtuaData) {
                public $nik_sasaran;
                public $nama_sasaran;
                public $no_kk_sasaran;
                public $tempat_lahir;
                public $tanggal_lahir;
                public $jenis_kelamin;
                public $umur_sasaran;
                public $alamat_sasaran;
                public $kepersertaan_bpjs;
                public $nomor_bpjs;
                public $nomor_telepon;
                public $orangtua;
                public $user;
                public $id_sasaran_dewasa;
                public $id_sasaran_pralansia;
                public $id_sasaran_lansia;
                public $nik_orangtua;

                public function __construct($orangtua, $pseudoUser, $type, $orangtuaData) {
                    $this->nik_sasaran = $orangtua->nik;
                    $this->nama_sasaran = $orangtua->nama;
                    $this->no_kk_sasaran = $orangtua->no_kk;
                    $this->tempat_lahir = $orangtua->tempat_lahir;
                    $this->tanggal_lahir = $orangtua->tanggal_lahir;
                    $this->jenis_kelamin = $orangtua->kelamin;
                    $this->umur_sasaran = $orangtua->umur;
                    $this->alamat_sasaran = $orangtua->alamat;
                    $this->kepersertaan_bpjs = $orangtua->kepersertaan_bpjs;
                    $this->nomor_bpjs = $orangtua->nomor_bpjs;
                    $this->nomor_telepon = $orangtua->nomor_telepon;
                    // Set orangtua data jika ada, jika tidak set ke null
                    $this->orangtua = $orangtuaData;
                    $this->user = $pseudoUser;
                    $this->nik_orangtua = $orangtuaData ? $orangtuaData->nik : null;

                    // Set appropriate ID based on type (all null for orangtua data)
                    $this->id_sasaran_dewasa = null;
                    $this->id_sasaran_pralansia = null;
                    $this->id_sasaran_lansia = null;
                }

                public function getKey() {
                    return $this->nik_sasaran; // Use NIK as the key for orangtua records
                }
            };
        });

        $total = $formattedData->count();
        $totalPages = $total > 0 ? ceil($total / $this->perPage) : 1;

        $paginated = $formattedData->slice(($page - 1) * $this->perPage, $this->perPage);

        return [
            'data' => $paginated,
            'total' => $total,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'per_page' => $this->perPage,
        ];
    }

    public function render()
    {
        $daftarPosyandu = Posyandu::select('id_posyandu', 'nama_posyandu')->get();
        // Hanya ambil user dengan role orangtua untuk dropdown di modal sasaran
        $usersQuery = User::whereHas('roles', function ($query) {
            $query->where('name', 'orangtua');
        });

        // Filter users berdasarkan search
        if (!empty($this->searchUser)) {
            $usersQuery->where(function($q) {
                $q->where('name', 'like', '%' . $this->searchUser . '%')
                  ->orWhere('email', 'like', '%' . $this->searchUser . '%');
            });
        }

        $users = $usersQuery->orderBy('name')->get();
        $orangtua = User::whereHas('roles', function ($query) {
            $query->where('name', 'orangtua');
        })->get();

        return view('livewire.super-admin.posyandu-detail', [
            'title' => 'Detail Posyandu - ' . $this->posyandu->nama_posyandu,
            'daftarPosyandu' => $daftarPosyandu,
            'dataPosyandu' => $daftarPosyandu,
            'users' => $users,
            'orangtua' => $orangtua,
            'isSasaranBalitaModalOpen' => $this->isSasaranBalitaModalOpen,
            'isKaderModalOpen' => $this->isKaderModalOpen,
            'isSasaranRemajaModalOpen' => $this->isSasaranRemajaModalOpen,
            'isSasaranDewasaModalOpen' => $this->isSasaranDewasaModalOpen,
            'isSasaranPralansiaModalOpen' => $this->isSasaranPralansiaModalOpen,
            'isSasaranLansiaModalOpen' => $this->isSasaranLansiaModalOpen,
            'isSasaranIbuHamilModalOpen' => $this->isSasaranIbuHamilModalOpen,
            'id_sasaran_bayi_balita' => $this->id_sasaran_bayi_balita,
            'id_kader' => $this->id_kader,
            'id_sasaran_remaja' => $this->id_sasaran_remaja,
            'id_sasaran_dewasa' => $this->id_sasaran_dewasa,
            'id_sasaran_pralansia' => $this->id_sasaran_pralansia,
            'id_sasaran_lansia' => $this->id_sasaran_lansia,
            'id_sasaran_ibuhamil' => $this->id_sasaran_ibuhamil,
        ]);
    }
}
