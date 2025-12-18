<?php

namespace App\Livewire\SuperAdmin\Traits;

use App\Models\User;
use App\Models\Kader;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

trait KaderCrud
{
    // Modal State
    public $isKaderModalOpen = false;

    // Field Form Kader
    public $id_kader = null;
    public $nama_kader;
    public $email_kader;
    public $password_kader;
    public $posyandu_id_kader;
    // Tambahan sesuai field Kader.php (kecuali no_hp_kader)
    public $nik_kader;
    public $id_users; // Biasanya diisi oleh sistem/database
    public $tanggal_lahir;
    public $hari_lahir;
    public $bulan_lahir;
    public $tahun_lahir;
    public $alamat_kader;
    public $jabatan_kader;

    /**
     * Buka modal tambah/edit Kader
     */
    public function openKaderModal($id = null)
    {
        if ($id) {
            $this->editKader($id);
        } else {
            $this->resetKaderFields();
            // Pre-fill dengan posyandu saat ini
            $this->posyandu_id_kader = $this->posyanduId;
            $this->isKaderModalOpen = true;
        }
    }

    /**
     * Tutup modal dan reset field
     */
    public function closeKaderModal()
    {
        $this->resetKaderFields();
        $this->isKaderModalOpen = false;
    }

    /**
     * Reset semua field form Kader
     */
    private function resetKaderFields()
    {
        $this->id_kader = null;
        $this->nama_kader = '';
        $this->email_kader = '';
        $this->password_kader = '';
        $this->posyandu_id_kader = '';
        $this->nik_kader = '';
        $this->tanggal_lahir = '';
        $this->hari_lahir = '';
        $this->bulan_lahir = '';
        $this->tahun_lahir = '';
        $this->alamat_kader = '';
        $this->jabatan_kader = '';
    }

    /**
     * Proses simpan data kader, tambah/edit
     */
    public function storeKader()
    {
        // Set posyandu_id_kader dari posyandu yang sedang dibuka jika tidak terisi
        if (empty($this->posyandu_id_kader) && isset($this->posyanduId)) {
            $this->posyandu_id_kader = $this->posyanduId;
        }
        
        // Cek apakah user memiliki hak untuk menambah kader (hanya Ketua dan Superadmin)
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        
        if (!$user) {
            session()->flash('message', 'Anda harus login terlebih dahulu.');
            session()->flash('messageType', 'error');
            return;
        }
        
        $isSuperadmin = $user->hasRole('superadmin');
        $isKetua = false;

        // Cek apakah user adalah kader dengan jabatan Ketua di posyandu yang sama
        if (!$isSuperadmin) {
            $kaderUser = \App\Models\Kader::where('id_users', $user->id)
                ->where('id_posyandu', $this->posyandu_id_kader ?? $this->posyanduId)
                ->where('jabatan_kader', 'Ketua')
                ->first();
            $isKetua = $kaderUser !== null;
        }

        // Get user ID for unique email validation if editing
        $userId = null;
        if ($this->id_kader) {
            $kader = Kader::findOrFail($this->id_kader);
            $userId = $kader->id_users;
        }

        // Jika password diisi, hanya ketua atau superadmin yang bisa membuat user
        if (!empty($this->password_kader) && !$this->id_kader) {
            if (!$isSuperadmin && !$isKetua) {
                session()->flash('message', 'Hanya Ketua dan Superadmin yang dapat membuat akun user untuk kader.');
                session()->flash('messageType', 'error');
                return;
            }
        }

        // Gabungkan hari, bulan, tahun menjadi tanggal lahir
        $this->combineTanggalLahir();

        // Validasi: email dan password hanya required jika password diisi (untuk membuat user)
        $rules = [
            'nama_kader' => 'required|string|max:255',
            'posyandu_id_kader' => 'required|exists:posyandu,id_posyandu',
            'nik_kader' => 'required|string|max:50',
            'hari_lahir' => 'required|numeric|min:1|max:31',
            'bulan_lahir' => 'required|numeric|min:1|max:12',
            'tahun_lahir' => 'required|numeric|min:1900|max:' . date('Y'),
            'tanggal_lahir' => 'required|date',
            'alamat_kader' => 'required|string|max:255',
            'jabatan_kader' => 'required|string|max:100',
        ];

        $messages = [
            'nama_kader.required' => 'Nama kader wajib diisi.',
            'posyandu_id_kader.required' => 'Posyandu wajib dipilih.',
            'posyandu_id_kader.exists' => 'Posyandu yang dipilih tidak valid.',
            'nik_kader.required' => 'NIK kader wajib diisi.',
            'hari_lahir.required' => 'Hari lahir wajib diisi.',
            'hari_lahir.numeric' => 'Hari lahir harus berupa angka.',
            'hari_lahir.min' => 'Hari lahir minimal 1.',
            'hari_lahir.max' => 'Hari lahir maksimal 31.',
            'bulan_lahir.required' => 'Bulan lahir wajib diisi.',
            'bulan_lahir.numeric' => 'Bulan lahir harus berupa angka.',
            'bulan_lahir.min' => 'Bulan lahir minimal 1.',
            'bulan_lahir.max' => 'Bulan lahir maksimal 12.',
            'tahun_lahir.required' => 'Tahun lahir wajib diisi.',
            'tahun_lahir.numeric' => 'Tahun lahir harus berupa angka.',
            'tahun_lahir.min' => 'Tahun lahir minimal 1900.',
            'tahun_lahir.max' => 'Tahun lahir maksimal ' . date('Y') . '.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.date' => 'Tanggal lahir tidak valid.',
            'alamat_kader.required' => 'Alamat kader wajib diisi.',
            'jabatan_kader.required' => 'Jabatan kader wajib diisi.',
        ];

        // Jika sedang edit dan kader sudah punya user, email wajib
        if ($this->id_kader && $userId) {
            $rules['email_kader'] = 'required|string|email|max:255|unique:users,email,' . $userId;
            $rules['password_kader'] = 'nullable|min:8';
            $messages['email_kader.required'] = 'Email wajib diisi.';
            $messages['email_kader.email'] = 'Format email tidak valid.';
            $messages['email_kader.unique'] = 'Email sudah terdaftar.';
            $messages['password_kader.min'] = 'Password minimal 8 karakter.';
        }
        // Jika password diisi (untuk create user baru atau tambah user ke kader yang belum punya), email wajib
        elseif (!empty($this->password_kader)) {
            $rules['email_kader'] = 'required|string|email|max:255|unique:users,email' . ($userId ? ',' . $userId : '');
            $rules['password_kader'] = 'required|min:8';
            $messages['email_kader.required'] = 'Email wajib diisi jika ingin membuat akun user.';
            $messages['email_kader.email'] = 'Format email tidak valid.';
            $messages['email_kader.unique'] = 'Email sudah terdaftar.';
            $messages['password_kader.required'] = 'Password wajib diisi jika ingin membuat akun user.';
            $messages['password_kader.min'] = 'Password minimal 8 karakter.';
        }

        $this->validate($rules, $messages);

        if ($this->id_kader) {
            // UPDATE
            $kader = Kader::findOrFail($this->id_kader);
            
            // Jika kader sudah punya user, update user
            if ($kader->id_users) {
                $user = $kader->user;
                $user->name = $this->nama_kader;
                $user->email = $this->email_kader;
                if ($this->password_kader) {
                    $user->password = Hash::make($this->password_kader);
                }
                $user->save();
            }
            // Jika kader belum punya user dan password diisi, buat user baru
            elseif (!empty($this->password_kader)) {
                // Cek apakah user yang login adalah ketua atau superadmin
                if (!$isSuperadmin && !$isKetua) {
                    session()->flash('message', 'Hanya Ketua dan Superadmin yang dapat membuat akun user untuk kader.');
                    session()->flash('messageType', 'error');
                    return;
                }
                
                $user = User::create([
                    'name' => $this->nama_kader,
                    'email' => $this->email_kader,
                    'password' => Hash::make($this->password_kader),
                ]);
                $user->assignRole('adminPosyandu');
                $kader->id_users = $user->id;
            }

            $kader->id_posyandu = $this->posyandu_id_kader;
            $kader->nama_kader = $this->nama_kader;
            $kader->nik_kader = $this->nik_kader;
            $kader->tanggal_lahir = $this->tanggal_lahir;
            $kader->alamat_kader = $this->alamat_kader;
            $kader->jabatan_kader = $this->jabatan_kader;
            $kader->save();

            session()->flash('message', 'Data Kader berhasil diperbarui.');
        } else {
            // CREATE
            $userId = null;
            
            // Hanya buat user jika password diisi
            if (!empty($this->password_kader)) {
                $user = User::create([
                    'name' => $this->nama_kader,
                    'email' => $this->email_kader,
                    'password' => Hash::make($this->password_kader),
                ]);

                // Assign role kader
                $user->assignRole('adminPosyandu');
                $userId = $user->id;
            }

            // Buat record Kader (dengan atau tanpa id_users)
            Kader::create([
                'id_users' => $userId,
                'nama_kader' => $this->nama_kader,
                'id_posyandu' => $this->posyandu_id_kader,
                'nik_kader' => $this->nik_kader,
                'tanggal_lahir' => $this->tanggal_lahir,
                'alamat_kader' => $this->alamat_kader,
                'jabatan_kader' => $this->jabatan_kader,
            ]);

            if ($userId) {
                session()->flash('message', 'Data Kader dan akun user berhasil ditambahkan.');
            } else {
                session()->flash('message', 'Data Kader berhasil ditambahkan (tanpa akun user).');
            }
        }

        $this->refreshPosyandu();
        $this->closeKaderModal();
    }

    /**
     * Inisialisasi form edit kader
     */
    public function editKader($id)
    {
        $kader = Kader::with('user')->findOrFail($id);

        $this->id_kader = $kader->id_kader;
        // Ambil nama dari nama_kader, jika tidak ada fallback ke user->name
        $this->nama_kader = $kader->nama_kader ?? $kader->user->name ?? '';
        $this->email_kader = $kader->user->email ?? '';
        $this->posyandu_id_kader = $kader->id_posyandu;
        $this->nik_kader = $kader->nik_kader ?? '';
        $this->tanggal_lahir = $kader->tanggal_lahir ?? '';
        
        // Pecah tanggal lahir menjadi hari, bulan, tahun
        if ($kader->tanggal_lahir) {
            $tanggal = Carbon::parse($kader->tanggal_lahir);
            $this->hari_lahir = $tanggal->day;
            $this->bulan_lahir = $tanggal->month;
            $this->tahun_lahir = $tanggal->year;
        } else {
            $this->hari_lahir = '';
            $this->bulan_lahir = '';
            $this->tahun_lahir = '';
        }
        
        $this->alamat_kader = $kader->alamat_kader ?? '';
        $this->jabatan_kader = $kader->jabatan_kader ?? '';
        $this->password_kader = '';

        $this->isKaderModalOpen = true;
    }

    /**
     * Combine hari, bulan, tahun menjadi tanggal lahir
     */
    private function combineTanggalLahir()
    {
        if ($this->hari_lahir && $this->bulan_lahir && $this->tahun_lahir) {
            try {
                $this->tanggal_lahir = Carbon::create(
                    $this->tahun_lahir,
                    $this->bulan_lahir,
                    $this->hari_lahir
                )->format('Y-m-d');
            } catch (\Exception $e) {
                $this->tanggal_lahir = null;
            }
        } else {
            $this->tanggal_lahir = null;
        }
    }

    /**
     * Hapus data kader
     */
    public function deleteKader($id)
    {
        $kader = Kader::findOrFail($id);

        // Hapus user hanya jika tidak digunakan kader lain
        $user = $kader->user;
        $kader->delete();

        // Pastikan user hanya satu kali dipakai (jaga2 penghapusan)
        if ($user && Kader::where('id_users', $user->id)->count() === 0) {
            $user->delete();
        }

        $this->refreshPosyandu();
        session()->flash('message', 'Data Kader berhasil dihapus.');
    }
}
