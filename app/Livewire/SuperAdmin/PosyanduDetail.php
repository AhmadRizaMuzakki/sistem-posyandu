<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Posyandu;
use App\Models\Kader;
use App\Models\Sasaran_Bayibalita;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class PosyanduDetail extends Component
{
    public $posyandu;
    public $posyanduId;

    // Properties untuk form Kader
    public $isKaderModalOpen = false;
    public $id_kader = null; // Untuk mode edit
    public $nik_kader, $id_users, $tanggal_lahir, $alamat_kader, $jabatan_kader;

    // Properties untuk form Sasaran
    public $isSasaranModalOpen = false; // Aliased/modal flag for Sasaran (legacy/root, not used for balita modal anymore)

    public $isSasaranBalitaModalOpen = false; // Renamed property for modal Sasaran Bayi Balita (see instructions)
    public $isModalSasaranRemajaOpen = false;
    public $isModalSasaranDewasaOpen = false;
    public $isModalSasaranPralansiaOpen = false;
    public $isSasaranLansiaModalOpen = false; // >>> Renamed property as requested
    public $isModalSasaranIbuHamilOpen = false;
    public $isSasaranIbuHamilModalOpen = false; // <--- Added property to fix undefined variable

    // Fix: add id_sasaran_bayi_balita for compatibility with form/edit (avoid undefined var)
    public $sasaran_bayi_balita_id = null; // Untuk mode edit, renamed from id_sasaran
    public $id_sasaran_bayi_balita = null; // Ensure this matches the expected variable name in view/form

    public $nama_sasaran, $nik_sasaran, $no_kk_sasaran, $tempat_lahir, $tanggal_lahir_sasaran,
           $jenis_kelamin, $umur_sasaran, $nik_orangtua, $alamat_sasaran,
           $kepersertaan_bpjs, $nomor_bpjs, $nomor_telepon, $id_users_sasaran;

    #[Layout('layouts.superadmindashboard')]

    public function mount($id)
    {
        $sasaranTableExists = Schema::hasTable('sasaran') || Schema::hasTable('sasarans'); // cek kedua kemungkinan nama tabel

        try {
            $decryptedId = decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID tidak valid');
        }

        $this->posyanduId = $decryptedId;

        $relations = ['kader.user'];
        if ($sasaranTableExists) {
            $relations[] = 'sasaran';
        }

        $posyandu = Posyandu::with($relations)->find($decryptedId);
        if (!$posyandu) {
            abort(404, 'Posyandu tidak ditemukan');
        }

        if (!$sasaranTableExists) {
            $posyandu->setRelation('sasaran', collect());
        }

        $this->posyandu = $posyandu;
    }

    // Methods untuk Kader
    public function openKaderModal()
    {
        $this->resetKaderFields();
        $this->isKaderModalOpen = true;
    }

    public function closeKaderModal()
    {
        $this->isKaderModalOpen = false;
        $this->resetKaderFields();
    }

    private function resetKaderFields()
    {
        $this->id_kader = null;
        $this->nik_kader = '';
        $this->id_users = '';
        $this->tanggal_lahir = '';
        $this->alamat_kader = '';
        $this->jabatan_kader = '';
    }

    public function storeKader()
    {
        $this->validate([
            'nik_kader' => 'required|numeric',
            'id_users' => 'required',
            'tanggal_lahir' => 'required|date',
            'alamat_kader' => 'required',
            'jabatan_kader' => 'required',
        ]);

        if ($this->id_kader) {
            // Update mode
            $kader = Kader::findOrFail($this->id_kader);
            $kader->update([
                'nik_kader' => $this->nik_kader,
                'id_users' => $this->id_users,
                'tanggal_lahir' => $this->tanggal_lahir,
                'alamat_kader' => $this->alamat_kader,
                'jabatan_kader' => $this->jabatan_kader,
            ]);
            $message = 'Data Kader berhasil diperbarui.';
        } else {
            // Create mode
            Kader::create([
                'nik_kader' => $this->nik_kader,
                'id_users' => $this->id_users,
                'tanggal_lahir' => $this->tanggal_lahir,
                'alamat_kader' => $this->alamat_kader,
                'jabatan_kader' => $this->jabatan_kader,
                'id_posyandu' => $this->posyanduId,
            ]);

            $user = User::find($this->id_users);
            if ($user) {
                $user->assignRole('adminPosyandu');
            }
            $message = 'Data Kader berhasil ditambahkan.';
        }

        $this->refreshPosyandu();
        $this->closeKaderModal();
        session()->flash('message', $message);
    }

    public function editKader($id)
    {
        $kader = Kader::findOrFail($id);
        $this->id_kader = $kader->id_kader;
        $this->nik_kader = $kader->nik_kader;
        $this->id_users = $kader->id_users;
        $this->tanggal_lahir = $kader->tanggal_lahir;
        $this->alamat_kader = $kader->alamat_kader;
        $this->jabatan_kader = $kader->jabatan_kader;
        $this->openKaderModal();
    }

    public function deleteKader($id)
    {
        $kader = Kader::findOrFail($id);
        $kader->delete();
        $this->refreshPosyandu();
        session()->flash('message', 'Data Kader berhasil dihapus.');
    }

    // Methods untuk Sasaran

    /**
     * Open modal for Sasaran Bayi Balita specifically.
     */
    public function openModalSasaranBayiBalita()
    {
        $this->resetSasaranFields();
        $this->isSasaranModalOpen = true;
        $this->isSasaranBalitaModalOpen = true;
        $this->isModalSasaranRemajaOpen = false;
        $this->isModalSasaranDewasaOpen = false;
        $this->isModalSasaranPralansiaOpen = false;
        $this->isSasaranLansiaModalOpen = false;
        $this->isModalSasaranIbuHamilOpen = false;
        $this->isSasaranIbuHamilModalOpen = false;
    }

    /**
     * Close modal for Sasaran Bayi Balita specifically.
     */
    public function closeModalSasaranBayiBalita()
    {
        $this->isSasaranModalOpen = false;
        $this->isSasaranBalitaModalOpen = false;
        $this->resetSasaranFields();
    }

    public function openSasaranModal()
    {
        $this->resetSasaranFields();
        $this->isSasaranModalOpen = true;
        $this->isSasaranBalitaModalOpen = true;
        $this->isModalSasaranRemajaOpen = true;
        $this->isModalSasaranDewasaOpen = true;
        $this->isModalSasaranPralansiaOpen = true;
        $this->isSasaranLansiaModalOpen = true;
        $this->isModalSasaranIbuHamilOpen = true;
        $this->isSasaranIbuHamilModalOpen = true;
    }

    public function closeSasaranModal()
    {
        $this->isSasaranModalOpen = false;
        $this->isSasaranBalitaModalOpen = false;
        $this->isModalSasaranRemajaOpen = false;
        $this->isModalSasaranDewasaOpen = false;
        $this->isModalSasaranPralansiaOpen = false;
        $this->isSasaranLansiaModalOpen = false;
        $this->isModalSasaranIbuHamilOpen = false;
        $this->isSasaranIbuHamilModalOpen = false;
        $this->resetSasaranFields();
    }

    private function resetSasaranFields()
    {
        $this->sasaran_bayi_balita_id = null; // was $this->id_sasaran
        $this->id_sasaran_bayi_balita = null; // also reset compat variable
        $this->nama_sasaran = '';
        $this->nik_sasaran = '';
        $this->no_kk_sasaran = '';
        $this->tempat_lahir = '';
        $this->tanggal_lahir_sasaran = '';
        $this->jenis_kelamin = '';
        $this->umur_sasaran = null;
        $this->nik_orangtua = '';
        $this->alamat_sasaran = '';
        $this->kepersertaan_bpjs = '';
        $this->nomor_bpjs = '';
        $this->nomor_telepon = '';
        $this->id_users_sasaran = '';
    }

    /**
     * Public method for Bayi Balita (for Livewire compatibility).
     */
    public function storeSasaranBayiBalita()
    {
        $this->validate([
            'nama_sasaran' => 'required',
            'nik_sasaran' => 'required|numeric',
            'tanggal_lahir_sasaran' => 'required|date',
            'jenis_kelamin' => 'required',
            'alamat_sasaran' => 'required',
        ]);

        $umur = null;
        if ($this->tanggal_lahir_sasaran) {
            $umur = Carbon::parse($this->tanggal_lahir_sasaran)->age;
        } elseif (is_numeric($this->umur_sasaran) && $this->umur_sasaran !== '') {
            $umur = (int)$this->umur_sasaran;
        }

        $kk = $this->no_kk_sasaran !== '' ? $this->no_kk_sasaran : null;

        $data = [
            'id_users' => $this->id_users_sasaran !== '' ? $this->id_users_sasaran : null,
            'nama_sasaran' => $this->nama_sasaran,
            'nik_sasaran' => $this->nik_sasaran,
            'no_kk_sasaran' => $kk,
            'tempat_lahir' => $this->tempat_lahir,
            'tanggal_lahir' => $this->tanggal_lahir_sasaran,
            'jenis_kelamin' => $this->jenis_kelamin,
            'umur_sasaran' => $umur,
            'nik_orangtua' => $this->nik_orangtua !== '' ? $this->nik_orangtua : null,
            'alamat_sasaran' => $this->alamat_sasaran,
            'kepersertaan_bpjs' => $this->kepersertaan_bpjs !== '' ? $this->kepersertaan_bpjs : null,
            'nomor_bpjs' => $this->nomor_bpjs !== '' ? $this->nomor_bpjs : null,
            'nomor_telepon' => $this->nomor_telepon !== '' ? $this->nomor_telepon : null,
        ];

        // Use either sasaran_bayi_balita_id or id_sasaran_bayi_balita as the edit identifier
        $editId = $this->sasaran_bayi_balita_id ?: $this->id_sasaran_bayi_balita;
        if ($editId) {
            $sasaran = Sasaran_Bayibalita::findOrFail($editId);
            $sasaran->update($data);
            $message = 'Data Sasaran berhasil diperbarui.';
        } else {
            $data['id_posyandu'] = $this->posyanduId;
            Sasaran_Bayibalita::create($data);
            $message = 'Data Sasaran berhasil ditambahkan.';
        }

        $this->refreshPosyandu();
        $this->closeSasaranModal();
        session()->flash('message', $message);
    }

    public function storeSasaran()
    {
        return $this->storeSasaranBayiBalita();
    }

    public function editSasaran($id)
    {
        $sasaran = Sasaran_Bayibalita::findOrFail($id);
        $this->sasaran_bayi_balita_id = $sasaran->id_sasaran; // was $this->id_sasaran
        $this->id_sasaran_bayi_balita = $sasaran->id_sasaran; // set compat property
        $this->nama_sasaran = $sasaran->nama_sasaran;
        $this->nik_sasaran = $sasaran->nik_sasaran;
        $this->no_kk_sasaran = $sasaran->no_kk_sasaran ?? '';
        $this->tempat_lahir = $sasaran->tempat_lahir ?? '';
        $this->tanggal_lahir_sasaran = $sasaran->tanggal_lahir;
        $this->jenis_kelamin = $sasaran->jenis_kelamin;

        if ($sasaran->tanggal_lahir) {
            $this->umur_sasaran = Carbon::parse($sasaran->tanggal_lahir)->age;
        } else {
            $this->umur_sasaran = $sasaran->umur_sasaran;
        }

        $this->nik_orangtua = $sasaran->nik_orangtua ?? '';
        $this->alamat_sasaran = $sasaran->alamat_sasaran;
        $this->kepersertaan_bpjs = $sasaran->kepersertaan_bpjs ?? '';
        $this->nomor_bpjs = $sasaran->nomor_bpjs ?? '';
        $this->nomor_telepon = $sasaran->nomor_telepon ?? '';
        $this->id_users_sasaran = $sasaran->id_users ?? '';
        $this->openSasaranModal();
    }

    public function deleteSasaran($id)
    {
        $sasaran = Sasaran_Bayibalita::findOrFail($id);
        $sasaran->delete();
        $this->refreshPosyandu();
        session()->flash('message', 'Data Sasaran berhasil dihapus.');
    }

    private function refreshPosyandu()
    {
        $sasaranTableExists = Schema::hasTable('sasaran') || Schema::hasTable('sasarans');
        $relations = ['kader.user'];
        if ($sasaranTableExists) {
            $relations[] = 'sasaran';
        }

        $this->posyandu = Posyandu::with($relations)->find($this->posyanduId);

        if (!$sasaranTableExists) {
            $this->posyandu->setRelation('sasaran', collect());
        }
    }

    public function render()
    {
        $daftarPosyandu = Posyandu::all(['id_posyandu', 'nama_posyandu']);
        $users = User::all();

        return view('livewire.super-admin.posyandu-detail', [
            'title' => 'Detail Posyandu - ' . $this->posyandu->nama_posyandu,
            'daftarPosyandu' => $daftarPosyandu,
            'users' => $users,
            'isModalSasaranBayiBalitaOpen' => $this->isSasaranModalOpen || $this->isSasaranBalitaModalOpen, // Compatibility legacy
            'isSasaranBalitaModalOpen' => $this->isSasaranBalitaModalOpen, // For direct view use if needed
            'isModalSasaranRemajaOpen' => $this->isSasaranModalOpen || $this->isModalSasaranRemajaOpen,
            'isModalSasaranDewasaOpen' => $this->isSasaranModalOpen || $this->isModalSasaranDewasaOpen,
            'isModalSasaranPralansiaOpen' => $this->isSasaranModalOpen || $this->isModalSasaranPralansiaOpen,
            'isSasaranLansiaModalOpen' => $this->isSasaranModalOpen || $this->isSasaranLansiaModalOpen, // Updated key usage
            'isModalSasaranIbuHamilOpen' => $this->isSasaranModalOpen || $this->isModalSasaranIbuHamilOpen || $this->isSasaranIbuHamilModalOpen,
            // Optionally expose id_sasaran_bayi_balita for view if expected
            'id_sasaran_bayi_balita' => $this->id_sasaran_bayi_balita,
        ]);
    }
}
