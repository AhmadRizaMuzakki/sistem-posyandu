<?php

namespace App\Livewire\Posyandu\Traits;

use App\Models\Orangtua;

trait SasaranHelper
{
    /**
     * Create pseudo-user class for orangtua data
     */
    protected function createPseudoUser($nama, $email = null)
    {
        return new class($nama, $email) {
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
    }

    /**
     * Create pseudo-sasaran object for orangtua data
     */
    protected function createPseudoSasaran($orangtua, $type)
    {
        $pseudoUser = $this->createPseudoUser($orangtua->nama);

        return new class($orangtua, $pseudoUser, $type) {
            public $nik_sasaran;
            public $nama_sasaran;
            public $no_kk_sasaran;
            public $tempat_lahir;
            public $tanggal_lahir;
            public $jenis_kelamin;
            public $umur_sasaran;
            public $pekerjaan;
            public $alamat_sasaran;
            public $kepersertaan_bpjs;
            public $nomor_bpjs;
            public $nomor_telepon;
            public $orangtua;
            public $user;
            public $id_sasaran_dewasa;
            public $id_sasaran_pralansia;
            public $id_sasaran_lansia;

            public function __construct($orangtua, $pseudoUser, $type) {
                $this->nik_sasaran = $orangtua->nik;
                $this->nama_sasaran = $orangtua->nama;
                $this->no_kk_sasaran = $orangtua->no_kk;
                $this->tempat_lahir = $orangtua->tempat_lahir;
                $this->tanggal_lahir = $orangtua->tanggal_lahir;
                $this->jenis_kelamin = $orangtua->kelamin;
                $this->umur_sasaran = $orangtua->umur;
                $this->pekerjaan = $orangtua->pekerjaan;
                $this->alamat_sasaran = $orangtua->alamat;
                $this->kepersertaan_bpjs = $orangtua->kepersertaan_bpjs;
                $this->nomor_bpjs = $orangtua->nomor_bpjs;
                $this->nomor_telepon = $orangtua->nomor_telepon;
                $this->orangtua = $orangtua;
                $this->user = $pseudoUser;

                // Set appropriate ID based on type (all null for orangtua data)
                $this->id_sasaran_dewasa = null;
                $this->id_sasaran_pralansia = null;
                $this->id_sasaran_lansia = null;
            }

            public function getKey() {
                return $this->nik_sasaran; // Use NIK as the key for orangtua records
            }
        };
    }

    /**
     * Get orangtua data by age range and format as sasaran
     */
    protected function getOrangtuaByUmur($minAge, $maxAge = null, $search = '', $page = 1, $type = 'dewasa', $perPage = 5)
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
            return $this->createPseudoSasaran($orangtua, $type);
        });

        $total = $formattedData->count();
        $totalPages = $total > 0 ? ceil($total / $perPage) : 1;

        $paginated = $formattedData->slice(($page - 1) * $perPage, $perPage);

        return [
            'data' => $paginated,
            'total' => $total,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'per_page' => $perPage,
        ];
    }

    /**
     * Get filtered and paginated sasaran data
     */
    public function getFilteredSasaran($sasaranCollection, $search, $page, $perPage = 5)
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
        $totalPages = $total > 0 ? ceil($total / $perPage) : 1;

        $paginated = $query->slice(($page - 1) * $perPage, $perPage);

        return [
            'data' => $paginated,
            'total' => $total,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'per_page' => $perPage,
        ];
    }
}

