<?php

namespace App\Http\Controllers;

use App\Models\Imunisasi;
use App\Models\Kader;
use App\Models\Posyandu;
use App\Models\Pendidikan;
use Dompdf\Dompdf;
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
        $imunisasiList = Imunisasi::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu)
            ->orderBy('tanggal_imunisasi', 'desc')
            ->get()
            ->filter(function ($imunisasi) use ($namaDecoded) {
                $sasaran = $imunisasi->sasaran;
                return $sasaran && $sasaran->nama_sasaran === $namaDecoded;
            })
            ->values();

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
    public function superadminPosyanduImunisasiPdf(int $id, ?string $kategori = null): Response
    {
        $posyandu = Posyandu::findOrFail($id);

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
    public function superadminPosyanduImunisasiPdfByJenisVaksin(int $id, string $jenisVaksin): Response
    {
        $posyandu = Posyandu::findOrFail($id);

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
    public function superadminPosyanduImunisasiPdfByNama(int $id, string $nama): Response
    {
        $posyandu = Posyandu::findOrFail($id);
        $namaDecoded = urldecode($nama);

        // Get all imunisasi for this posyandu
        $imunisasiList = Imunisasi::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu)
            ->orderBy('tanggal_imunisasi', 'desc')
            ->get()
            ->filter(function ($imunisasi) use ($namaDecoded) {
                $sasaran = $imunisasi->sasaran;
                return $sasaran && $sasaran->nama_sasaran === $namaDecoded;
            })
            ->values();

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
    public function superadminPosyanduSasaranPdf(int $id, string $kategori): Response
    {
        $config = $this->getSasaranKategoriConfig($kategori);

        $posyandu = Posyandu::findOrFail($id);

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


