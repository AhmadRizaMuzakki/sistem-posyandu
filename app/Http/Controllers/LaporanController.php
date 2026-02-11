<?php

namespace App\Http\Controllers;

use App\Models\Imunisasi;
use App\Models\Jadwal;
use App\Models\Kader;
use App\Models\Posyandu;
use App\Models\Pendidikan;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Dompdf\Options;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class LaporanController extends Controller
{
    /**
     * Generate laporan imunisasi Posyandu (admin Posyandu) dalam bentuk PDF.
     */
    public function posyanduImunisasiPdf(?string $kategori = null): Response
    {
        $user = Auth::user();

        $kader = Kader::with('posyandu')
            ->where('id_users', $user->id)
            ->first();

        if (! $kader || ! $kader->posyandu) {
            abort(403, 'Posyandu untuk akun ini tidak ditemukan.');
        }

        $posyandu = $kader->posyandu;

        $query = Imunisasi::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu);

        // Filter berdasarkan kategori sasaran jika ada
        if ($kategori && $kategori !== 'semua') {
            $query->where('kategori_sasaran', $kategori);
        }

        $imunisasiList = $query->orderBy('tanggal_imunisasi', 'desc')->get();

        $kategoriLabel = $kategori && $kategori !== 'semua' ? $this->getKategoriLabel($kategori) : 'Semua';
        $fileName = 'Laporan-Imunisasi-'.str_replace(['/', ' '], ['-', '-'], $kategoriLabel).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-imunisasi', [
            'posyandu' => $posyandu,
            'imunisasiList' => $imunisasiList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriSasaran' => $kategori && $kategori !== 'semua' ? $kategori : null,
            'kategoriLabel' => $kategoriLabel,
        ], $fileName);
    }

    /**
     * Generate laporan imunisasi Posyandu berdasarkan jenis vaksin (admin Posyandu).
     */
    public function posyanduImunisasiPdfByJenisVaksin(string $jenisVaksin): Response
    {
        $user = Auth::user();

        $kader = Kader::with('posyandu')
            ->where('id_users', $user->id)
            ->first();

        if (! $kader || ! $kader->posyandu) {
            abort(403, 'Posyandu untuk akun ini tidak ditemukan.');
        }

        $posyandu = $kader->posyandu;

        $imunisasiList = Imunisasi::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu)
            ->where('jenis_imunisasi', urldecode($jenisVaksin))
            ->orderBy('tanggal_imunisasi', 'desc')
            ->get();

        $jenisVaksinLabel = urldecode($jenisVaksin);
        $fileName = 'Laporan-Imunisasi-'.str_replace(['/', ' '], ['-', '-'], $jenisVaksinLabel).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-imunisasi', [
            'posyandu' => $posyandu,
            'imunisasiList' => $imunisasiList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriSasaran' => null,
            'kategoriLabel' => 'Jenis Vaksin: '.$jenisVaksinLabel,
            'jenisVaksin' => $jenisVaksinLabel,
        ], $fileName);
    }

    /**
     * Generate laporan imunisasi Posyandu berdasarkan nama sasaran (admin Posyandu).
     */
    public function posyanduImunisasiPdfByNama(string $nama): Response
    {
        $user = Auth::user();

        $kader = Kader::with('posyandu')
            ->where('id_users', $user->id)
            ->first();

        if (! $kader || ! $kader->posyandu) {
            abort(403, 'Posyandu untuk akun ini tidak ditemukan.');
        }

        $posyandu = $kader->posyandu;
        $namaDecoded = urldecode($nama);

        // Get all imunisasi for this posyandu
        $allImunisasi = Imunisasi::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu)
            ->orderBy('tanggal_imunisasi', 'desc')
            ->get();

        // Preload sasaran untuk menghindari N+1 query
        Imunisasi::preloadSasaran($allImunisasi);

        $imunisasiList = $allImunisasi
            ->filter(function ($imunisasi) use ($namaDecoded) {
                $sasaran = $imunisasi->sasaran;
                return $sasaran && $sasaran->nama_sasaran === $namaDecoded;
            })
            ->values();

        // Clear cache setelah selesai
        Imunisasi::clearSasaranCache();

        $fileName = 'Laporan-Imunisasi-'.str_replace(['/', ' '], ['-', '-'], $namaDecoded).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-imunisasi', [
            'posyandu' => $posyandu,
            'imunisasiList' => $imunisasiList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriSasaran' => null,
            'kategoriLabel' => 'Nama: '.$namaDecoded,
            'namaSasaran' => $namaDecoded,
        ], $fileName);
    }

    /**
     * Generate laporan imunisasi Posyandu untuk Super Admin (berdasarkan ID Posyandu).
     */
    public function superadminPosyanduImunisasiPdf(string $id, ?string $kategori = null): Response
    {
        try {
            $decryptedId = decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID tidak valid');
        }

        $posyandu = Posyandu::findOrFail($decryptedId);

        $query = Imunisasi::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu);

        // Filter berdasarkan kategori sasaran jika ada
        if ($kategori && $kategori !== 'semua') {
            $query->where('kategori_sasaran', $kategori);
        }

        $imunisasiList = $query->orderBy('tanggal_imunisasi', 'desc')->get();

        $user = Auth::user();

        $kategoriLabel = $kategori && $kategori !== 'semua' ? $this->getKategoriLabel($kategori) : 'Semua';
        $fileName = 'Laporan-Imunisasi-'.str_replace(['/', ' '], ['-', '-'], $kategoriLabel).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-imunisasi', [
            'posyandu' => $posyandu,
            'imunisasiList' => $imunisasiList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriSasaran' => $kategori && $kategori !== 'semua' ? $kategori : null,
            'kategoriLabel' => $kategoriLabel,
        ], $fileName);
    }

    /**
     * Generate laporan imunisasi Posyandu berdasarkan jenis vaksin untuk Super Admin.
     */
    public function superadminPosyanduImunisasiPdfByJenisVaksin(string $id, string $jenisVaksin): Response
    {
        try {
            $decryptedId = decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID tidak valid');
        }

        $posyandu = Posyandu::findOrFail($decryptedId);

        $imunisasiList = Imunisasi::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu)
            ->where('jenis_imunisasi', urldecode($jenisVaksin))
            ->orderBy('tanggal_imunisasi', 'desc')
            ->get();

        $user = Auth::user();
        $jenisVaksinLabel = urldecode($jenisVaksin);
        $fileName = 'Laporan-Imunisasi-'.str_replace(['/', ' '], ['-', '-'], $jenisVaksinLabel).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-imunisasi', [
            'posyandu' => $posyandu,
            'imunisasiList' => $imunisasiList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriSasaran' => null,
            'kategoriLabel' => 'Jenis Vaksin: '.$jenisVaksinLabel,
            'jenisVaksin' => $jenisVaksinLabel,
        ], $fileName);
    }

    /**
     * Generate laporan imunisasi Posyandu berdasarkan nama sasaran untuk Super Admin.
     */
    public function superadminPosyanduImunisasiPdfByNama(string $id, string $nama): Response
    {
        try {
            $decryptedId = decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID tidak valid');
        }

        $posyandu = Posyandu::findOrFail($decryptedId);
        $namaDecoded = urldecode($nama);

        // Get all imunisasi for this posyandu
        $allImunisasi = Imunisasi::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu)
            ->orderBy('tanggal_imunisasi', 'desc')
            ->get();

        // Preload sasaran untuk menghindari N+1 query
        Imunisasi::preloadSasaran($allImunisasi);

        $imunisasiList = $allImunisasi
            ->filter(function ($imunisasi) use ($namaDecoded) {
                $sasaran = $imunisasi->sasaran;
                return $sasaran && $sasaran->nama_sasaran === $namaDecoded;
            })
            ->values();

        // Clear cache setelah selesai
        Imunisasi::clearSasaranCache();

        $user = Auth::user();
        $fileName = 'Laporan-Imunisasi-'.str_replace(['/', ' '], ['-', '-'], $namaDecoded).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-imunisasi', [
            'posyandu' => $posyandu,
            'imunisasiList' => $imunisasiList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriSasaran' => null,
            'kategoriLabel' => 'Nama: '.$namaDecoded,
            'namaSasaran' => $namaDecoded,
        ], $fileName);
    }

    /**
     * Generate laporan sasaran per kategori untuk Super Admin.
     */
    public function superadminPosyanduSasaranPdf(string $id, string $kategori): Response
    {
        try {
            $decryptedId = decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID tidak valid');
        }

        $config = $this->getSasaranKategoriConfig($kategori);

        $posyandu = Posyandu::findOrFail($decryptedId);

        $query = $posyandu->{$config['relation']}();

        if (! empty($config['with'])) {
            $query->with($config['with']);
        }

        $sasaranList = $query->orderBy('nama_sasaran')->get();

        $user = Auth::user();

        $fileName = 'Laporan-Sasaran-'.$config['label'].'-'.$posyandu->nama_posyandu.'-'.now()->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.sasaran-per-kategori', [
            'posyandu' => $posyandu,
            'sasaranList' => $sasaranList,
            'kategori' => $kategori,
            'kategoriLabel' => $config['label'],
            'generatedAt' => now(),
            'user' => $user,
        ], $fileName, 'landscape');
    }

    /**
     * Generate laporan sasaran per kategori untuk Posyandu (admin Posyandu).
     */
    public function posyanduSasaranPdf(string $kategori): Response
    {
        $user = Auth::user();

        $kader = Kader::with('posyandu')
            ->where('id_users', $user->id)
            ->first();

        if (! $kader || ! $kader->posyandu) {
            abort(403, 'Posyandu untuk akun ini tidak ditemukan.');
        }

        $posyandu = $kader->posyandu;
        $config = $this->getSasaranKategoriConfig($kategori);

        $query = $posyandu->{$config['relation']}();

        if (! empty($config['with'])) {
            $query->with($config['with']);
        }

        $sasaranList = $query->orderBy('nama_sasaran')->get();

        $fileName = 'Laporan-Sasaran-'.$config['label'].'-'.$posyandu->nama_posyandu.'-'.now()->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.sasaran-per-kategori', [
            'posyandu' => $posyandu,
            'sasaranList' => $sasaranList,
            'kategori' => $kategori,
            'kategoriLabel' => $config['label'],
            'generatedAt' => now(),
            'user' => $user,
        ], $fileName, 'landscape');
    }

    /**
     * Get label kategori sasaran.
     */
    protected function getKategoriLabel(string $kategori): string
    {
        $labels = [
            'bayibalita' => 'Bayi dan Balita',
            'remaja' => 'Remaja',
            'dewasa' => 'Dewasa',
            'pralansia' => 'Pralansia',
            'lansia' => 'Lansia',
            'ibuhamil' => 'Ibu Hamil',
        ];

        return $labels[$kategori] ?? ucfirst($kategori);
    }

    /**
     * Mapping konfigurasi kategori sasaran.
     */
    protected function getSasaranKategoriConfig(string $kategori): array
    {
        $map = [
            'bayibalita' => [
                'label' => 'Bayi dan Balita',
                'relation' => 'sasaran_bayibalita',
                'with' => ['orangtua'],
            ],
            'remaja' => [
                'label' => 'Remaja',
                'relation' => 'sasaran_remaja',
                'with' => ['orangtua'],
            ],
            'dewasa' => [
                'label' => 'Dewasa',
                'relation' => 'sasaran_dewasa',
                'with' => ['orangtua'],
            ],
            'pralansia' => [
                'label' => 'Pralansia',
                'relation' => 'sasaran_pralansia',
                'with' => ['orangtua'],
            ],
            'lansia' => [
                'label' => 'Lansia',
                'relation' => 'sasaran_lansia',
                'with' => ['orangtua'],
            ],
            'ibuhamil' => [
                'label' => 'Ibu Hamil',
                'relation' => 'sasaran_ibuhamil',
                'with' => [],
            ],
        ];

        if (! isset($map[$kategori])) {
            abort(404, 'Kategori sasaran tidak dikenal.');
        }

        return $map[$kategori];
    }

    /**
     * Generate laporan pendidikan Posyandu untuk Super Admin (berdasarkan ID Posyandu).
     */
    public function superadminPosyanduPendidikanPdf(string $id, ?string $kategoriPendidikan = null): Response
    {
        try {
            $decryptedId = decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID tidak valid');
        }

        $posyandu = Posyandu::findOrFail($decryptedId);

        $query = Pendidikan::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu);

        // Filter berdasarkan kategori pendidikan jika ada
        if ($kategoriPendidikan && $kategoriPendidikan !== 'semua') {
            $query->where('pendidikan_terakhir', urldecode($kategoriPendidikan));
        }

        $pendidikanList = $query->orderBy('tanggal_lahir', 'desc')->get();

        $user = Auth::user();

        $kategoriLabel = $kategoriPendidikan && $kategoriPendidikan !== 'semua' 
            ? urldecode($kategoriPendidikan) 
            : 'Semua';
        $fileName = 'Laporan-Pendidikan-'.str_replace(['/', ' '], ['-', '-'], $kategoriLabel).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-pendidikan', [
            'posyandu' => $posyandu,
            'pendidikanList' => $pendidikanList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriPendidikan' => $kategoriPendidikan && $kategoriPendidikan !== 'semua' ? urldecode($kategoriPendidikan) : null,
            'kategoriLabel' => $kategoriLabel,
        ], $fileName);
    }

    /**
     * Generate laporan pendidikan Posyandu untuk Super Admin berdasarkan kategori sasaran.
     */
    public function superadminPosyanduPendidikanPdfByKategoriSasaran(string $id, string $kategoriSasaran): Response
    {
        try {
            $decryptedId = decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID tidak valid');
        }

        $posyandu = Posyandu::findOrFail($decryptedId);

        $query = Pendidikan::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu)
            ->where('kategori_sasaran', urldecode($kategoriSasaran));

        $pendidikanList = $query->orderBy('tanggal_lahir', 'desc')->get();

        $user = Auth::user();

        $kategoriLabel = urldecode($kategoriSasaran);
        $fileName = 'Laporan-Pendidikan-Kategori-'.str_replace(['/', ' '], ['-', '-'], $kategoriLabel).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-pendidikan', [
            'posyandu' => $posyandu,
            'pendidikanList' => $pendidikanList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriPendidikan' => null,
            'kategoriLabel' => 'Kategori: ' . $kategoriLabel,
        ], $fileName);
    }

    /**
     * Generate laporan pendidikan Posyandu untuk Super Admin berdasarkan nama sasaran.
     */
    public function superadminPosyanduPendidikanPdfByNama(string $id, string $nama): Response
    {
        try {
            $decryptedId = decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID tidak valid');
        }

        $posyandu = Posyandu::findOrFail($decryptedId);
        $namaDecoded = urldecode($nama);

        $query = Pendidikan::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu)
            ->where('nama', 'like', '%' . $namaDecoded . '%');

        $pendidikanList = $query->orderBy('tanggal_lahir', 'desc')->get();

        $user = Auth::user();

        $fileName = 'Laporan-Pendidikan-Nama-'.str_replace(['/', ' '], ['-', '-'], $namaDecoded).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-pendidikan', [
            'posyandu' => $posyandu,
            'pendidikanList' => $pendidikanList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriPendidikan' => null,
            'kategoriLabel' => 'Nama: ' . $namaDecoded,
        ], $fileName);
    }

    /**
     * Generate laporan pendidikan Posyandu (admin Posyandu) dalam bentuk PDF.
     */
    public function posyanduPendidikanPdf(?string $kategori = null): Response
    {
        $user = Auth::user();

        $kader = Kader::with('posyandu')
            ->where('id_users', $user->id)
            ->first();

        if (! $kader || ! $kader->posyandu) {
            abort(403, 'Posyandu untuk akun ini tidak ditemukan.');
        }

        $posyandu = $kader->posyandu;

        $query = Pendidikan::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu);

        // Filter berdasarkan kategori pendidikan jika ada
        if ($kategori && $kategori !== 'semua') {
            $query->where('pendidikan_terakhir', urldecode($kategori));
        }

        $pendidikanList = $query->orderBy('tanggal_lahir', 'desc')->get();

        $kategoriLabel = $kategori && $kategori !== 'semua' ? urldecode($kategori) : 'Semua';
        $fileName = 'Laporan-Pendidikan-'.str_replace(['/', ' '], ['-', '-'], $kategoriLabel).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-pendidikan', [
            'posyandu' => $posyandu,
            'pendidikanList' => $pendidikanList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriPendidikan' => $kategori && $kategori !== 'semua' ? urldecode($kategori) : null,
            'kategoriLabel' => $kategoriLabel,
        ], $fileName);
    }

    /**
     * Generate laporan pendidikan Posyandu (admin Posyandu) berdasarkan kategori sasaran.
     */
    public function posyanduPendidikanPdfByKategoriSasaran(string $kategoriSasaran): Response
    {
        $user = Auth::user();

        $kader = Kader::with('posyandu')
            ->where('id_users', $user->id)
            ->first();

        if (! $kader || ! $kader->posyandu) {
            abort(403, 'Posyandu untuk akun ini tidak ditemukan.');
        }

        $posyandu = $kader->posyandu;

        $query = Pendidikan::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu)
            ->where('kategori_sasaran', urldecode($kategoriSasaran));

        $pendidikanList = $query->orderBy('tanggal_lahir', 'desc')->get();

        $kategoriLabel = urldecode($kategoriSasaran);
        $fileName = 'Laporan-Pendidikan-Kategori-'.str_replace(['/', ' '], ['-', '-'], $kategoriLabel).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-pendidikan', [
            'posyandu' => $posyandu,
            'pendidikanList' => $pendidikanList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriPendidikan' => null,
            'kategoriLabel' => 'Kategori: ' . $kategoriLabel,
        ], $fileName);
    }

    /**
     * Generate laporan pendidikan Posyandu (admin Posyandu) berdasarkan nama sasaran.
     */
    public function posyanduPendidikanPdfByNama(string $nama): Response
    {
        $user = Auth::user();

        $kader = Kader::with('posyandu')
            ->where('id_users', $user->id)
            ->first();

        if (! $kader || ! $kader->posyandu) {
            abort(403, 'Posyandu untuk akun ini tidak ditemukan.');
        }

        $posyandu = $kader->posyandu;
        $namaDecoded = urldecode($nama);

        $query = Pendidikan::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu)
            ->where('nama', 'like', '%' . $namaDecoded . '%');

        $pendidikanList = $query->orderBy('tanggal_lahir', 'desc')->get();

        $fileName = 'Laporan-Pendidikan-Nama-'.str_replace(['/', ' '], ['-', '-'], $namaDecoded).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-pendidikan', [
            'posyandu' => $posyandu,
            'pendidikanList' => $pendidikanList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriPendidikan' => null,
            'kategoriLabel' => 'Nama: ' . $namaDecoded,
        ], $fileName);
    }

    /**
     * Generate laporan pendidikan Posyandu untuk Super Admin berdasarkan kombinasi kategori sasaran + pendidikan + nama.
     */
    public function superadminPosyanduPendidikanPdfByAllFilters(string $id, string $kategoriSasaran, string $kategoriPendidikan, string $nama): Response
    {
        try {
            $decryptedId = decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID tidak valid');
        }

        $posyandu = Posyandu::findOrFail($decryptedId);
        $kategoriSasaranDecoded = urldecode($kategoriSasaran);
        $kategoriPendidikanDecoded = urldecode($kategoriPendidikan);
        $namaDecoded = urldecode($nama);

        $query = Pendidikan::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu)
            ->where('kategori_sasaran', $kategoriSasaranDecoded)
            ->where('pendidikan_terakhir', $kategoriPendidikanDecoded)
            ->where('nama', 'like', '%' . $namaDecoded . '%');

        $pendidikanList = $query->orderBy('tanggal_lahir', 'desc')->get();

        $user = Auth::user();

        $fileName = 'Laporan-Pendidikan-Kategori-'.str_replace(['/', ' '], ['-', '-'], $kategoriSasaranDecoded).'-Pendidikan-'.str_replace(['/', ' '], ['-', '-'], $kategoriPendidikanDecoded).'-Nama-'.str_replace(['/', ' '], ['-', '-'], $namaDecoded).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-pendidikan', [
            'posyandu' => $posyandu,
            'pendidikanList' => $pendidikanList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriPendidikan' => $kategoriPendidikanDecoded,
            'kategoriLabel' => 'Kategori: ' . $kategoriSasaranDecoded . ' | Pendidikan: ' . $kategoriPendidikanDecoded . ' | Nama: ' . $namaDecoded,
        ], $fileName);
    }

    /**
     * Generate laporan pendidikan Posyandu untuk Super Admin berdasarkan kombinasi kategori sasaran + pendidikan.
     */
    public function superadminPosyanduPendidikanPdfByKategoriSasaranAndPendidikan(string $id, string $kategoriSasaran, string $kategoriPendidikan): Response
    {
        try {
            $decryptedId = decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID tidak valid');
        }

        $posyandu = Posyandu::findOrFail($decryptedId);
        $kategoriSasaranDecoded = urldecode($kategoriSasaran);
        $kategoriPendidikanDecoded = urldecode($kategoriPendidikan);

        $query = Pendidikan::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu)
            ->where('kategori_sasaran', $kategoriSasaranDecoded)
            ->where('pendidikan_terakhir', $kategoriPendidikanDecoded);

        $pendidikanList = $query->orderBy('tanggal_lahir', 'desc')->get();

        $user = Auth::user();

        $fileName = 'Laporan-Pendidikan-Kategori-'.str_replace(['/', ' '], ['-', '-'], $kategoriSasaranDecoded).'-Pendidikan-'.str_replace(['/', ' '], ['-', '-'], $kategoriPendidikanDecoded).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-pendidikan', [
            'posyandu' => $posyandu,
            'pendidikanList' => $pendidikanList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriPendidikan' => $kategoriPendidikanDecoded,
            'kategoriLabel' => 'Kategori: ' . $kategoriSasaranDecoded . ' | Pendidikan: ' . $kategoriPendidikanDecoded,
        ], $fileName);
    }

    /**
     * Generate laporan pendidikan Posyandu untuk Super Admin berdasarkan kombinasi kategori sasaran + nama.
     */
    public function superadminPosyanduPendidikanPdfByKategoriSasaranAndNama(string $id, string $kategoriSasaran, string $nama): Response
    {
        try {
            $decryptedId = decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID tidak valid');
        }

        $posyandu = Posyandu::findOrFail($decryptedId);
        $kategoriSasaranDecoded = urldecode($kategoriSasaran);
        $namaDecoded = urldecode($nama);

        $query = Pendidikan::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu)
            ->where('kategori_sasaran', $kategoriSasaranDecoded)
            ->where('nama', 'like', '%' . $namaDecoded . '%');

        $pendidikanList = $query->orderBy('tanggal_lahir', 'desc')->get();

        $user = Auth::user();

        $fileName = 'Laporan-Pendidikan-Kategori-'.str_replace(['/', ' '], ['-', '-'], $kategoriSasaranDecoded).'-Nama-'.str_replace(['/', ' '], ['-', '-'], $namaDecoded).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-pendidikan', [
            'posyandu' => $posyandu,
            'pendidikanList' => $pendidikanList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriPendidikan' => null,
            'kategoriLabel' => 'Kategori: ' . $kategoriSasaranDecoded . ' | Nama: ' . $namaDecoded,
        ], $fileName);
    }

    /**
     * Generate laporan pendidikan Posyandu untuk Super Admin berdasarkan kombinasi pendidikan + nama.
     */
    public function superadminPosyanduPendidikanPdfByPendidikanAndNama(string $id, string $kategoriPendidikan, string $nama): Response
    {
        try {
            $decryptedId = decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID tidak valid');
        }

        $posyandu = Posyandu::findOrFail($decryptedId);
        $kategoriPendidikanDecoded = urldecode($kategoriPendidikan);
        $namaDecoded = urldecode($nama);

        $query = Pendidikan::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu)
            ->where('pendidikan_terakhir', $kategoriPendidikanDecoded)
            ->where('nama', 'like', '%' . $namaDecoded . '%');

        $pendidikanList = $query->orderBy('tanggal_lahir', 'desc')->get();

        $user = Auth::user();

        $fileName = 'Laporan-Pendidikan-Pendidikan-'.str_replace(['/', ' '], ['-', '-'], $kategoriPendidikanDecoded).'-Nama-'.str_replace(['/', ' '], ['-', '-'], $namaDecoded).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-pendidikan', [
            'posyandu' => $posyandu,
            'pendidikanList' => $pendidikanList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriPendidikan' => $kategoriPendidikanDecoded,
            'kategoriLabel' => 'Pendidikan: ' . $kategoriPendidikanDecoded . ' | Nama: ' . $namaDecoded,
        ], $fileName);
    }

    /**
     * Generate SK Posyandu untuk Super Admin (berdasarkan ID Posyandu).
     */
    public function superadminPosyanduSkPdf(string $id): Response
    {
        try {
            $decryptedId = decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID tidak valid');
        }

        $posyandu = Posyandu::with([
            'kader.user',
            'sasaran_bayibalita',
            'sasaran_remaja',
            'sasaran_dewasa',
            'sasaran_ibuhamil',
            'sasaran_pralansia',
            'sasaran_lansia',
        ])->findOrFail($decryptedId);

        $user = Auth::user();

        // Hitung total sasaran
        $totalSasaran = $posyandu->sasaran_bayibalita->count() +
                        $posyandu->sasaran_remaja->count() +
                        $posyandu->sasaran_dewasa->count() +
                        $posyandu->sasaran_ibuhamil->count() +
                        $posyandu->sasaran_pralansia->count() +
                        $posyandu->sasaran_lansia->count();

        $fileName = 'SK-Posyandu-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.sk-posyandu', [
            'posyandu' => $posyandu,
            'totalSasaran' => $totalSasaran,
            'totalKader' => $posyandu->kader->count(),
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
        ], $fileName);
    }

    /**
     * Generate laporan pendidikan Posyandu (admin Posyandu) berdasarkan kombinasi kategori sasaran + pendidikan + nama.
     */
    public function posyanduPendidikanPdfByAllFilters(string $kategoriSasaran, string $kategoriPendidikan, string $nama): Response
    {
        $user = Auth::user();

        $kader = Kader::with('posyandu')
            ->where('id_users', $user->id)
            ->first();

        if (! $kader || ! $kader->posyandu) {
            abort(403, 'Posyandu untuk akun ini tidak ditemukan.');
        }

        $posyandu = $kader->posyandu;
        $kategoriSasaranDecoded = urldecode($kategoriSasaran);
        $kategoriPendidikanDecoded = urldecode($kategoriPendidikan);
        $namaDecoded = urldecode($nama);

        $query = Pendidikan::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu)
            ->where('kategori_sasaran', $kategoriSasaranDecoded)
            ->where('pendidikan_terakhir', $kategoriPendidikanDecoded)
            ->where('nama', 'like', '%' . $namaDecoded . '%');

        $pendidikanList = $query->orderBy('tanggal_lahir', 'desc')->get();

        $fileName = 'Laporan-Pendidikan-Kategori-'.str_replace(['/', ' '], ['-', '-'], $kategoriSasaranDecoded).'-Pendidikan-'.str_replace(['/', ' '], ['-', '-'], $kategoriPendidikanDecoded).'-Nama-'.str_replace(['/', ' '], ['-', '-'], $namaDecoded).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-pendidikan', [
            'posyandu' => $posyandu,
            'pendidikanList' => $pendidikanList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriPendidikan' => $kategoriPendidikanDecoded,
            'kategoriLabel' => 'Kategori: ' . $kategoriSasaranDecoded . ' | Pendidikan: ' . $kategoriPendidikanDecoded . ' | Nama: ' . $namaDecoded,
        ], $fileName);
    }

    /**
     * Generate laporan pendidikan Posyandu (admin Posyandu) berdasarkan kombinasi kategori sasaran + pendidikan.
     */
    public function posyanduPendidikanPdfByKategoriSasaranAndPendidikan(string $kategoriSasaran, string $kategoriPendidikan): Response
    {
        $user = Auth::user();

        $kader = Kader::with('posyandu')
            ->where('id_users', $user->id)
            ->first();

        if (! $kader || ! $kader->posyandu) {
            abort(403, 'Posyandu untuk akun ini tidak ditemukan.');
        }

        $posyandu = $kader->posyandu;
        $kategoriSasaranDecoded = urldecode($kategoriSasaran);
        $kategoriPendidikanDecoded = urldecode($kategoriPendidikan);

        $query = Pendidikan::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu)
            ->where('kategori_sasaran', $kategoriSasaranDecoded)
            ->where('pendidikan_terakhir', $kategoriPendidikanDecoded);

        $pendidikanList = $query->orderBy('tanggal_lahir', 'desc')->get();

        $fileName = 'Laporan-Pendidikan-Kategori-'.str_replace(['/', ' '], ['-', '-'], $kategoriSasaranDecoded).'-Pendidikan-'.str_replace(['/', ' '], ['-', '-'], $kategoriPendidikanDecoded).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-pendidikan', [
            'posyandu' => $posyandu,
            'pendidikanList' => $pendidikanList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriPendidikan' => $kategoriPendidikanDecoded,
            'kategoriLabel' => 'Kategori: ' . $kategoriSasaranDecoded . ' | Pendidikan: ' . $kategoriPendidikanDecoded,
        ], $fileName);
    }

    /**
     * Generate laporan pendidikan Posyandu (admin Posyandu) berdasarkan kombinasi kategori sasaran + nama.
     */
    public function posyanduPendidikanPdfByKategoriSasaranAndNama(string $kategoriSasaran, string $nama): Response
    {
        $user = Auth::user();

        $kader = Kader::with('posyandu')
            ->where('id_users', $user->id)
            ->first();

        if (! $kader || ! $kader->posyandu) {
            abort(403, 'Posyandu untuk akun ini tidak ditemukan.');
        }

        $posyandu = $kader->posyandu;
        $kategoriSasaranDecoded = urldecode($kategoriSasaran);
        $namaDecoded = urldecode($nama);

        $query = Pendidikan::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu)
            ->where('kategori_sasaran', $kategoriSasaranDecoded)
            ->where('nama', 'like', '%' . $namaDecoded . '%');

        $pendidikanList = $query->orderBy('tanggal_lahir', 'desc')->get();

        $fileName = 'Laporan-Pendidikan-Kategori-'.str_replace(['/', ' '], ['-', '-'], $kategoriSasaranDecoded).'-Nama-'.str_replace(['/', ' '], ['-', '-'], $namaDecoded).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-pendidikan', [
            'posyandu' => $posyandu,
            'pendidikanList' => $pendidikanList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriPendidikan' => null,
            'kategoriLabel' => 'Kategori: ' . $kategoriSasaranDecoded . ' | Nama: ' . $namaDecoded,
        ], $fileName);
    }

    /**
     * Generate laporan pendidikan Posyandu (admin Posyandu) berdasarkan kombinasi pendidikan + nama.
     */
    public function posyanduPendidikanPdfByPendidikanAndNama(string $kategoriPendidikan, string $nama): Response
    {
        $user = Auth::user();

        $kader = Kader::with('posyandu')
            ->where('id_users', $user->id)
            ->first();

        if (! $kader || ! $kader->posyandu) {
            abort(403, 'Posyandu untuk akun ini tidak ditemukan.');
        }

        $posyandu = $kader->posyandu;
        $kategoriPendidikanDecoded = urldecode($kategoriPendidikan);
        $namaDecoded = urldecode($nama);

        $query = Pendidikan::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu)
            ->where('pendidikan_terakhir', $kategoriPendidikanDecoded)
            ->where('nama', 'like', '%' . $namaDecoded . '%');

        $pendidikanList = $query->orderBy('tanggal_lahir', 'desc')->get();

        $fileName = 'Laporan-Pendidikan-Pendidikan-'.str_replace(['/', ' '], ['-', '-'], $kategoriPendidikanDecoded).'-Nama-'.str_replace(['/', ' '], ['-', '-'], $namaDecoded).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-pendidikan', [
            'posyandu' => $posyandu,
            'pendidikanList' => $pendidikanList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriPendidikan' => $kategoriPendidikanDecoded,
            'kategoriLabel' => 'Pendidikan: ' . $kategoriPendidikanDecoded . ' | Nama: ' . $namaDecoded,
        ], $fileName);
    }

    /**
     * Generate SK Posyandu untuk Admin Posyandu.
     */
    public function posyanduSkPdf(): Response
    {
        $user = Auth::user();

        $kader = Kader::with('posyandu')
            ->where('id_users', $user->id)
            ->first();

        if (! $kader || ! $kader->posyandu) {
            abort(403, 'Posyandu untuk akun ini tidak ditemukan.');
        }

        $posyandu = Posyandu::with([
            'kader.user',
            'sasaran_bayibalita',
            'sasaran_remaja',
            'sasaran_dewasa',
            'sasaran_ibuhamil',
            'sasaran_pralansia',
            'sasaran_lansia',
        ])->findOrFail($kader->posyandu->id_posyandu);

        // Hitung total sasaran
        $totalSasaran = $posyandu->sasaran_bayibalita->count() +
                        $posyandu->sasaran_remaja->count() +
                        $posyandu->sasaran_dewasa->count() +
                        $posyandu->sasaran_ibuhamil->count() +
                        $posyandu->sasaran_pralansia->count() +
                        $posyandu->sasaran_lansia->count();

        $fileName = 'SK-Posyandu-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.sk-posyandu', [
            'posyandu' => $posyandu,
            'totalSasaran' => $totalSasaran,
            'totalKader' => $posyandu->kader->count(),
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
        ], $fileName);
    }

    /**
     * Laporan Absensi (Jadwal) - Admin Posyandu. Filter: bulan, presensi (hadir/tidak_hadir).
     */
    public function posyanduAbsensiPdf(Request $request): Response
    {
        $user = Auth::user();
        $kader = Kader::with('posyandu')->where('id_users', $user->id)->first();
        if (! $kader || ! $kader->posyandu) {
            abort(403, 'Posyandu untuk akun ini tidak ditemukan.');
        }
        $posyandu = $kader->posyandu;
        $bulan = (int) $request->query('bulan', now()->month);
        $tahun = (int) $request->query('tahun', now()->year);
        $presensi = $request->query('presensi'); // hadir, tidak_hadir, atau kosong = semua

        $query = Jadwal::with(['petugasKesehatan'])
            ->where('id_posyandu', $posyandu->id_posyandu)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun);
        if ($presensi === 'hadir') {
            $query->where('presensi', 'hadir');
        } elseif ($presensi === 'tidak_hadir') {
            // Tidak hadir = yang dijadwalkan tapi tidak hadir: explicit tidak_hadir ATAU belum_hadir (belum dicentang hadir)
            $query->whereIn('presensi', ['tidak_hadir', 'belum_hadir']);
        }
        $jadwalList = $query->orderBy('tanggal')->orderBy('id_jadwal')->get();

        $bulanLabel = Carbon::create($tahun, $bulan, 1)->locale('id')->translatedFormat('F Y');
        $presensiLabel = $presensi === 'hadir' ? 'Hadir' : ($presensi === 'tidak_hadir' ? 'Tidak Hadir' : 'Semua');
        $fileName = 'Laporan-Absensi-'.$presensiLabel.'-'.$bulanLabel.'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-absensi', [
            'posyandu' => $posyandu,
            'jadwalList' => $jadwalList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'bulanLabel' => $bulanLabel,
            'presensiLabel' => $presensiLabel,
        ], $fileName);
    }

    /**
     * Laporan Absensi (Jadwal) - Super Admin. Filter: bulan, presensi (hadir/tidak_hadir).
     */
    public function superadminPosyanduAbsensiPdf(Request $request, string $id): Response
    {
        try {
            $decryptedId = decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID tidak valid');
        }
        $posyandu = Posyandu::findOrFail($decryptedId);
        $bulan = (int) $request->query('bulan', now()->month);
        $tahun = (int) $request->query('tahun', now()->year);
        $presensi = $request->query('presensi');

        $query = Jadwal::with(['petugasKesehatan'])
            ->where('id_posyandu', $posyandu->id_posyandu)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun);
        if ($presensi === 'hadir') {
            $query->where('presensi', 'hadir');
        } elseif ($presensi === 'tidak_hadir') {
            // Tidak hadir = yang dijadwalkan tapi tidak hadir: explicit tidak_hadir ATAU belum_hadir (belum dicentang hadir)
            $query->whereIn('presensi', ['tidak_hadir', 'belum_hadir']);
        }
        $jadwalList = $query->orderBy('tanggal')->orderBy('id_jadwal')->get();

        $bulanLabel = Carbon::create($tahun, $bulan, 1)->locale('id')->translatedFormat('F Y');
        $presensiLabel = $presensi === 'hadir' ? 'Hadir' : ($presensi === 'tidak_hadir' ? 'Tidak Hadir' : 'Semua');
        $fileName = 'Laporan-Absensi-'.$presensiLabel.'-'.$bulanLabel.'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-absensi', [
            'posyandu' => $posyandu,
            'jadwalList' => $jadwalList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => Auth::user(),
            'bulanLabel' => $bulanLabel,
            'presensiLabel' => $presensiLabel,
        ], $fileName);
    }

    /**
     * Helper umum untuk render view Blade menjadi PDF dengan Dompdf.
     */
    protected function renderPdf(string $view, array $data, string $fileName, string $orientation = 'portrait'): Response
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);

        $html = view($view, $data)->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', $orientation);
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }
}


