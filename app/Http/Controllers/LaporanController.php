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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    /**
     * Apply filter bulan & tahun pada query imunisasi.
     */
    private function applyBulanTahunFilter($query, Request $request): void
    {
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun');
        if ($bulan && is_numeric($bulan) && $bulan >= 1 && $bulan <= 12) {
            $query->whereMonth('tanggal_imunisasi', (int) $bulan);
        }
        if ($tahun && is_numeric($tahun) && $tahun >= 2000 && $tahun <= 2100) {
            $query->whereYear('tanggal_imunisasi', (int) $tahun);
        }
    }

    /**
     * Generate laporan imunisasi Posyandu (admin Posyandu) dalam bentuk PDF.
     */
    public function posyanduImunisasiPdf(Request $request, ?string $kategori = null): Response
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

        $this->applyBulanTahunFilter($query, $request);

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
     * Laporan kehadiran imunisasi: semua sasaran per kategori dicocokkan dengan data imunisasi di periode terpilih.
     * Query params: tahun (wajib), bulan (wajib), kategori (opsional), jenis_vaksin (opsional), kehadiran (opsional: hadir|tidak_hadir), nama_sasaran (opsional).
     */
    public function posyanduImunisasiKehadiranPdf(Request $request): Response
    {
        $user = Auth::user();
        $kader = Kader::with('posyandu')->where('id_users', $user->id)->first();
        if (! $kader || ! $kader->posyandu) {
            abort(403, 'Posyandu untuk akun ini tidak ditemukan.');
        }
        $posyandu = $kader->posyandu;
        $data = $this->buildImunisasiKehadiranData($posyandu, $request);
        $fileName = 'Laporan-Kehadiran-Imunisasi-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';
        return $this->renderPdf('pdf.laporan-posyandu-imunisasi-kehadiran', array_merge($data, [
            'posyandu' => $posyandu,
            'user' => $user,
            'generatedAt' => now('Asia/Jakarta'),
        ]), $fileName);
    }

    /**
     * Laporan kehadiran imunisasi untuk Super Admin (berdasarkan ID Posyandu).
     */
    public function superadminPosyanduImunisasiKehadiranPdf(Request $request, string $id): Response
    {
        try {
            $decryptedId = decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID tidak valid');
        }
        $posyandu = Posyandu::findOrFail($decryptedId);
        $data = $this->buildImunisasiKehadiranData($posyandu, $request);
        $fileName = 'Laporan-Kehadiran-Imunisasi-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';
        return $this->renderPdf('pdf.laporan-posyandu-imunisasi-kehadiran', array_merge($data, [
            'posyandu' => $posyandu,
            'user' => Auth::user(),
            'generatedAt' => now('Asia/Jakarta'),
        ]), $fileName);
    }

    /**
     * Build data untuk laporan kehadiran imunisasi: semua sasaran + status hadir/tidak hadir.
     */
    protected function buildImunisasiKehadiranData(Posyandu $posyandu, Request $request): array
    {
        $tahun = $request->query('tahun');
        $bulan = $request->query('bulan');
        if (! $tahun || ! $bulan || ! is_numeric($tahun) || ! is_numeric($bulan) || (int) $bulan < 1 || (int) $bulan > 12) {
            abort(422, 'Tahun dan bulan wajib dipilih untuk laporan kehadiran imunisasi.');
        }
        $tahun = (int) $tahun;
        $bulan = (int) $bulan;

        $kategorisImunisasi = ['bayibalita', 'remaja', 'dewasa', 'pralansia', 'lansia'];
        $kategoriFilter = $request->query('kategori');
        $kategoris = $kategoriFilter ? (in_array($kategoriFilter, $kategorisImunisasi, true) ? [$kategoriFilter] : $kategorisImunisasi) : $kategorisImunisasi;

        $jenisVaksin = $request->query('jenis_vaksin') ? (string) $request->query('jenis_vaksin') : null;
        $kehadiranFilter = $request->query('kehadiran');
        if ($kehadiranFilter !== null && $kehadiranFilter !== '' && $kehadiranFilter !== 'hadir' && $kehadiranFilter !== 'tidak_hadir') {
            $kehadiranFilter = null;
        }
        $namaSasaran = $request->query('nama_sasaran') ? trim((string) $request->query('nama_sasaran')) : null;

        $queryImunisasi = Imunisasi::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu)
            ->whereMonth('tanggal_imunisasi', $bulan)
            ->whereYear('tanggal_imunisasi', $tahun);
        if ($jenisVaksin !== null && $jenisVaksin !== '') {
            $queryImunisasi->where('jenis_imunisasi', $jenisVaksin);
        }
        $imunisasiInPeriod = $queryImunisasi->orderBy('tanggal_imunisasi', 'desc')->get();
        $hadirMap = [];
        foreach ($imunisasiInPeriod as $i) {
            // Normalisasi: kategori di DB bisa "Remaja"/"remaja", pakai lowercase agar cocok dengan $kategoris
            $key = strtolower(trim((string) ($i->kategori_sasaran ?? ''))).'_'.(string) $i->id_sasaran;
            if (! isset($hadirMap[$key])) {
                $hadirMap[$key] = $i;
            }
        }

        $rows = [];
        foreach ($kategoris as $kategori) {
            $config = $this->getSasaranKategoriConfig($kategori);
            $sasarans = $posyandu->{$config['relation']}()->orderBy('nama_sasaran')->get();
            foreach ($sasarans as $sasaran) {
                $idSasaran = $sasaran->getKey();
                $key = $kategori.'_'.(string) $idSasaran;
                $imunisasi = $hadirMap[$key] ?? null;
                $status = $imunisasi ? 'hadir' : 'tidak_hadir';
                if ($namaSasaran !== null && $namaSasaran !== '' && ($sasaran->nama_sasaran ?? '') !== $namaSasaran) {
                    continue;
                }
                if ($kehadiranFilter !== null && $kehadiranFilter !== '' && $status !== $kehadiranFilter) {
                    continue;
                }
                $rows[] = [
                    'sasaran' => $sasaran,
                    'kategori_sasaran' => $kategori,
                    'kategori_label' => $config['label'],
                    'status' => $status,
                    'imunisasi' => $imunisasi,
                ];
            }
        }

        $bulanNama = \Carbon\Carbon::create($tahun, $bulan, 1)->locale('id')->translatedFormat('F');
        $periodeLabel = $bulanNama.' '.$tahun;
        $kategoriLabel = $kategoriFilter ? $this->getKategoriLabel($kategoriFilter) : 'Semua Kategori';
        $jenisVaksinLabel = $jenisVaksin ?: 'Semua Jenis Vaksin';
        $kehadiranLabel = $kehadiranFilter === 'hadir' ? 'Hadir' : ($kehadiranFilter === 'tidak_hadir' ? 'Tidak Hadir' : 'Semua');

        return [
            'rows' => $rows,
            'periodeLabel' => $periodeLabel,
            'tahun' => $tahun,
            'bulan' => $bulan,
            'kategoriLabel' => $kategoriLabel,
            'jenisVaksinLabel' => $jenisVaksinLabel,
            'kehadiranLabel' => $kehadiranLabel,
        ];
    }

    /**
     * Generate laporan imunisasi Posyandu berdasarkan jenis vaksin (admin Posyandu).
     */
    public function posyanduImunisasiPdfByJenisVaksin(Request $request, string $jenisVaksin): Response
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
            ->where('id_posyandu', $posyandu->id_posyandu)
            ->where('jenis_imunisasi', urldecode($jenisVaksin));
        $this->applyBulanTahunFilter($query, $request);
        $imunisasiList = $query->orderBy('tanggal_imunisasi', 'desc')->get();

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
    public function posyanduImunisasiPdfByNama(Request $request, string $nama): Response
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
        $query = Imunisasi::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu);
        $this->applyBulanTahunFilter($query, $request);
        $allImunisasi = $query->orderBy('tanggal_imunisasi', 'desc')->get();

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
    public function superadminPosyanduImunisasiPdf(Request $request, string $id, ?string $kategori = null): Response
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

        $this->applyBulanTahunFilter($query, $request);

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
    public function superadminPosyanduImunisasiPdfByJenisVaksin(Request $request, string $id, string $jenisVaksin): Response
    {
        try {
            $decryptedId = decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID tidak valid');
        }

        $posyandu = Posyandu::findOrFail($decryptedId);

        $query = Imunisasi::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu)
            ->where('jenis_imunisasi', urldecode($jenisVaksin));
        $this->applyBulanTahunFilter($query, $request);
        $imunisasiList = $query->orderBy('tanggal_imunisasi', 'desc')->get();

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
    public function superadminPosyanduImunisasiPdfByNama(Request $request, string $id, string $nama): Response
    {
        try {
            $decryptedId = decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID tidak valid');
        }

        $posyandu = Posyandu::findOrFail($decryptedId);
        $namaDecoded = urldecode($nama);

        // Get all imunisasi for this posyandu
        $query = Imunisasi::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu);
        $this->applyBulanTahunFilter($query, $request);
        $allImunisasi = $query->orderBy('tanggal_imunisasi', 'desc')->get();

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
     * Generate laporan sasaran per kategori untuk Super Admin dalam format Excel.
     */
    public function superadminPosyanduSasaranExcel(string $id, string $kategori): StreamedResponse
    {
        try {
            $decryptedId = decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID tidak valid');
        }

        $posyandu = Posyandu::findOrFail($decryptedId);
        $config = $this->getSasaranKategoriConfig($kategori);

        $query = $posyandu->{$config['relation']}();
        if (! empty($config['with'])) {
            $query->with($config['with']);
        }
        $sasaranList = $query->orderBy('nama_sasaran')->get();

        return $this->downloadSasaranExcel($posyandu, $kategori, $config['label'], $sasaranList);
    }

    /**
     * Generate laporan sasaran per kategori untuk Admin Posyandu dalam format Excel.
     */
    public function posyanduSasaranExcel(string $kategori): StreamedResponse
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

        return $this->downloadSasaranExcel($posyandu, $kategori, $config['label'], $sasaranList);
    }

    /**
     * Build dan kirim file Excel sasaran.
     */
    private function downloadSasaranExcel(Posyandu $posyandu, string $kategori, string $kategoriLabel, $sasaranList): StreamedResponse
    {
        $headers = $this->getSasaranExcelHeaders($kategori);
        $rows = $sasaranList
            ->map(fn ($item) => $this->mapSasaranExcelRowByKategori($item, $kategori))
            ->values()
            ->all();

        $fileName = 'Laporan-Sasaran-'.$kategoriLabel.'-'.$posyandu->nama_posyandu.'-'.now()->format('Ymd_His').'.xlsx';

        return response()->streamDownload(function () use ($headers, $rows) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Laporan Sasaran');
            $sheet->fromArray([$headers], null, 'A1');
            if (! empty($rows)) {
                $sheet->fromArray($rows, null, 'A2');
            }

            $maxColumn = $sheet->getHighestColumn();
            foreach (range('A', $maxColumn) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function getSasaranExcelHeaders(string $kategori): array
    {
        $base = [
            'nik_sasaran',
            'no_kk_sasaran',
            'nama_sasaran',
            'tempat_lahir',
            'tanggal_lahir',
            'jenis_kelamin',
            'status_keluarga',
            'umur',
        ];

        return match ($kategori) {
            'bayibalita' => array_merge($base, [
                'alamat_sasaran',
                'kepersertaan_bpjs',
                'nomor_bpjs',
                'nik_orangtua',
                'nama_orangtua',
                'tempat_lahir_orangtua',
                'pekerjaan_orangtua',
                'pendidikan_orangtua',
            ]),
            'remaja' => array_merge($base, [
                'pendidikan',
                'alamat_sasaran',
                'kepersertaan_bpjs',
                'nomor_bpjs',
                'nomor_telepon',
                'nik_orangtua',
                'nama_orangtua',
                'tempat_lahir_orangtua',
                'pekerjaan_orangtua',
                'pendidikan_orangtua',
            ]),
            'ibuhamil' => array_merge($base, [
                'pekerjaan',
                'pendidikan',
                'alamat_sasaran',
                'rt',
                'rw',
                'kepersertaan_bpjs',
                'nomor_bpjs',
                'nomor_telepon',
                'minggu_kandungan',
                'nama_suami',
                'nik_suami',
                'pekerjaan_suami',
                'status_keluarga_suami',
            ]),
            default => array_merge($base, [
                'pendidikan',
                'pekerjaan',
                'alamat_sasaran',
                'rt',
                'rw',
                'kepersertaan_bpjs',
                'nomor_bpjs',
                'nomor_telepon',
            ]),
        };
    }

    private function mapSasaranExcelRowByKategori($item, string $kategori): array
    {
        $umur = null;
        if (! empty($item->tanggal_lahir)) {
            $dob = Carbon::parse($item->tanggal_lahir);
            $now = Carbon::now();
            $umur = $kategori === 'bayibalita'
                ? $dob->diffInMonths($now) . ' bln'
                : $dob->diffInYears($now) . ' th';
        } elseif (! is_null($item->umur_sasaran)) {
            $umur = (int) $item->umur_sasaran . ' th';
        }

        $base = [
            $item->nik_sasaran ?? null,
            $item->no_kk_sasaran ?? null,
            $item->nama_sasaran ?? null,
            $item->tempat_lahir ?? null,
            $item->tanggal_lahir ? Carbon::parse($item->tanggal_lahir)->format('Y-m-d') : null,
            $item->jenis_kelamin ?? null,
            $item->status_keluarga ?? null,
            $umur,
        ];

        $orangtua = $item->orangtua ?? null;

        return match ($kategori) {
            'bayibalita' => array_merge($base, [
                $item->alamat_sasaran ?? null,
                $item->kepersertaan_bpjs ?? null,
                $item->nomor_bpjs ?? null,
                $item->nik_orangtua ?? null,
                $orangtua->nama ?? null,
                $orangtua->tempat_lahir ?? null,
                $orangtua->pekerjaan ?? null,
                $orangtua->pendidikan ?? null,
            ]),
            'remaja' => array_merge($base, [
                $item->pendidikan ?? null,
                $item->alamat_sasaran ?? null,
                $item->kepersertaan_bpjs ?? null,
                $item->nomor_bpjs ?? null,
                $item->nomor_telepon ?? null,
                $item->nik_orangtua ?? null,
                $orangtua->nama ?? null,
                $orangtua->tempat_lahir ?? null,
                $orangtua->pekerjaan ?? null,
                $orangtua->pendidikan ?? null,
            ]),
            'ibuhamil' => array_merge($base, [
                $item->pekerjaan ?? null,
                $item->pendidikan ?? null,
                $item->alamat_sasaran ?? null,
                $item->rt ?? null,
                $item->rw ?? null,
                $item->kepersertaan_bpjs ?? null,
                $item->nomor_bpjs ?? null,
                $item->nomor_telepon ?? null,
                $item->minggu_kandungan ?? null,
                $item->nama_suami ?? null,
                $item->nik_suami ?? null,
                $item->pekerjaan_suami ?? null,
                $item->status_keluarga_suami ?? null,
            ]),
            default => array_merge($base, [
                $item->pendidikan ?? null,
                $item->pekerjaan ?? null,
                $item->alamat_sasaran ?? null,
                $item->rt ?? null,
                $item->rw ?? null,
                $item->kepersertaan_bpjs ?? null,
                $item->nomor_bpjs ?? null,
                $item->nomor_telepon ?? null,
            ]),
        };
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


