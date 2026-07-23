<?php

namespace App\Http\Controllers;

use App\Helpers\ImunisasiOptions;
use App\Helpers\SasaranFilterOptions;
use App\Models\Galeri;
use App\Models\Imunisasi;
use App\Models\Jadwal;
use App\Models\Kader;
use App\Models\Pendidikan;
use App\Models\Posyandu;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
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

    private function applyImunisasiOptionalFilters($query, Request $request): void
    {
        $jenisVaksin = $request->query('jenis_vaksin');
        if ($jenisVaksin !== null && $jenisVaksin !== '') {
            $query->whereIn('jenis_imunisasi', ImunisasiOptions::valuesForFilter((string) $jenisVaksin));
        }
    }

    /**
     * Ambil daftar imunisasi untuk laporan PDF (daftar), termasuk filter kategori klasik/usia/tahun lahir.
     */
    private function buildImunisasiListForPdf(Posyandu $posyandu, Request $request, ?string $kategori = null)
    {
        $kategori = $kategori ?: ($request->query('kategori') ? (string) $request->query('kategori') : null);
        if ($kategori === 'semua') {
            $kategori = null;
        }

        $query = Imunisasi::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu);

        if ($kategori && SasaranFilterOptions::isKategori($kategori) && in_array($kategori, SasaranFilterOptions::imunisasiKategoris(), true)) {
            $query->where('kategori_sasaran', $kategori);
        }

        $this->applyImunisasiOptionalFilters($query, $request);
        $this->applyBulanTahunFilter($query, $request);

        $imunisasiList = $query->orderBy('tanggal_imunisasi', 'desc')->get();
        $imunisasiList = $this->filterImunisasiListByNamaSasaran($imunisasiList, $request);

        if ($kategori && SasaranFilterOptions::isExtendedFilter($kategori)) {
            Imunisasi::preloadSasaran($imunisasiList);
            $imunisasiList = $imunisasiList
                ->filter(function ($imunisasi) use ($kategori) {
                    $sasaran = $imunisasi->sasaran;

                    return $sasaran && SasaranFilterOptions::matchesSasaranFilter($sasaran, $kategori);
                })
                ->values();
            Imunisasi::clearSasaranCache();
        }

        return [$imunisasiList, $kategori];
    }

    private function filterImunisasiListByNamaSasaran($imunisasiList, Request $request)
    {
        $namaSasaran = $request->query('nama_sasaran');
        if ($namaSasaran === null || $namaSasaran === '') {
            return $imunisasiList;
        }

        $namaSasaran = trim((string) $namaSasaran);

        Imunisasi::preloadSasaran($imunisasiList);

        $filtered = $imunisasiList
            ->filter(function ($imunisasi) use ($namaSasaran) {
                $sasaran = $imunisasi->sasaran;

                return $sasaran && trim((string) $sasaran->nama_sasaran) === $namaSasaran;
            })
            ->values();

        Imunisasi::clearSasaranCache();

        return $filtered;
    }

    private function buildImunisasiFilterSummary(Request $request, ?string $kategori = null): array
    {
        $tahun = $request->query('tahun');
        $bulan = $request->query('bulan');
        $jenisVaksin = $request->query('jenis_vaksin');
        $namaSasaran = $request->query('nama_sasaran');

        $periode = 'Semua Periode';
        if ($tahun && $bulan && is_numeric($bulan)) {
            $periode = Carbon::create((int) $tahun, (int) $bulan, 1)->locale('id')->translatedFormat('F Y');
        } elseif ($tahun) {
            $periode = 'Tahun '.(int) $tahun;
        } elseif ($bulan && is_numeric($bulan)) {
            $periode = Carbon::create(now()->year, (int) $bulan, 1)->locale('id')->translatedFormat('F').' (semua tahun)';
        }

        return [
            'filterPeriodeLabel' => $periode,
            'filterKategoriLabel' => $kategori ? SasaranFilterOptions::getLabel($kategori) : 'Semua Kategori',
            'filterJenisVaksinLabel' => ($jenisVaksin !== null && $jenisVaksin !== '') ? (string) $jenisVaksin : 'Semua Jenis Vaksin',
            'filterNamaSasaranLabel' => ($namaSasaran !== null && $namaSasaran !== '') ? (string) $namaSasaran : 'Semua Nama Sasaran',
        ];
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

        [$imunisasiList, $resolvedKategori] = $this->buildImunisasiListForPdf($posyandu, $request, $kategori);
        $filterSummary = $this->buildImunisasiFilterSummary($request, $resolvedKategori);

        $kategoriLabel = $resolvedKategori ? SasaranFilterOptions::getLabel($resolvedKategori) : 'Semua';
        $fileName = 'Laporan-Imunisasi-'.str_replace(['/', ' '], ['-', '-'], $kategoriLabel).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-imunisasi', array_merge([
            'posyandu' => $posyandu,
            'imunisasiList' => $imunisasiList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriSasaran' => $resolvedKategori,
            'kategoriLabel' => $kategoriLabel,
        ], $filterSummary), $fileName, 'landscape');
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
        $isGlobeReport = $request->query('laporan') === 'globe';
        $fileName = ($isGlobeReport ? 'Laporan-Kategori-Sasaran-Posyandu-' : 'Laporan-Kehadiran-Imunisasi-').$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-imunisasi-kehadiran', array_merge($data, [
            'posyandu' => $posyandu,
            'user' => $user,
            'generatedAt' => now('Asia/Jakarta'),
            'isGlobeReport' => $isGlobeReport,
        ]), $fileName, 'landscape');
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
        $isGlobeReport = $request->query('laporan') === 'globe';
        $fileName = ($isGlobeReport ? 'Laporan-Kategori-Sasaran-Posyandu-' : 'Laporan-Kehadiran-Imunisasi-').$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-imunisasi-kehadiran', array_merge($data, [
            'posyandu' => $posyandu,
            'user' => Auth::user(),
            'generatedAt' => now('Asia/Jakarta'),
            'isGlobeReport' => $isGlobeReport,
        ]), $fileName, 'landscape');
    }

    /**
     * Build data untuk laporan kehadiran imunisasi: semua sasaran + status hadir/tidak hadir.
     */
    protected function buildImunisasiKehadiranData(Posyandu $posyandu, Request $request): array
    {
        $tahun = $request->query('tahun');
        $bulan = $request->query('bulan');
        $isGlobeReport = $request->query('laporan') === 'globe';

        if ($isGlobeReport) {
            if (! $tahun || ! is_numeric($tahun) || (int) $tahun < 2000 || (int) $tahun > 2100) {
                abort(422, 'Tahun wajib dipilih untuk laporan globe.');
            }
            $tahun = (int) $tahun;
            $bulan = ($bulan && is_numeric($bulan) && (int) $bulan >= 1 && (int) $bulan <= 12)
                ? (int) $bulan
                : null;

            app(\App\Services\SasaranKategoriService::class)->syncForPosyandu($posyandu->id_posyandu);
        } else {
            if (! $tahun || ! $bulan || ! is_numeric($tahun) || ! is_numeric($bulan) || (int) $bulan < 1 || (int) $bulan > 12) {
                abort(422, 'Tahun dan bulan wajib dipilih untuk laporan kehadiran imunisasi.');
            }
            $tahun = (int) $tahun;
            $bulan = (int) $bulan;
        }

        $kategoriFilter = $request->query('kategori') ? (string) $request->query('kategori') : null;
        $kategoris = SasaranFilterOptions::resolveImunisasiKategoris($kategoriFilter);

        $jenisVaksin = $request->query('jenis_vaksin') ? (string) $request->query('jenis_vaksin') : null;
        $kehadiranFilter = $request->query('kehadiran');
        if ($kehadiranFilter !== null && $kehadiranFilter !== '' && $kehadiranFilter !== 'hadir' && $kehadiranFilter !== 'tidak_hadir') {
            $kehadiranFilter = null;
        }
        $namaSasaran = $request->query('nama_sasaran') ? trim((string) $request->query('nama_sasaran')) : null;

        $queryImunisasi = Imunisasi::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu)
            ->whereYear('tanggal_imunisasi', $tahun);
        if ($bulan !== null) {
            $queryImunisasi->whereMonth('tanggal_imunisasi', $bulan);
        }
        if ($jenisVaksin !== null && $jenisVaksin !== '') {
            $queryImunisasi->whereIn('jenis_imunisasi', ImunisasiOptions::valuesForFilter($jenisVaksin));
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
                if ($kategoriFilter && ! SasaranFilterOptions::matchesSasaranFilter($sasaran, $kategoriFilter)) {
                    continue;
                }
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
                    'umur_label' => $this->formatSasaranUmur($sasaran),
                ];
            }
        }

        if ($bulan !== null) {
            $bulanNama = \Carbon\Carbon::create($tahun, $bulan, 1)->locale('id')->translatedFormat('F');
            $periodeLabel = $bulanNama.' '.$tahun;
        } else {
            $periodeLabel = 'Semua Bulan '.$tahun;
        }
        $kategoriLabel = SasaranFilterOptions::getLabel($kategoriFilter);
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
            'galeriFotos' => $this->getGaleriFotosForPdf($posyandu, $bulan, $tahun),
        ];
    }

    private function formatSasaranUmur(object $sasaran): string
    {
        if (! empty($sasaran->tanggal_lahir)) {
            $dob = Carbon::parse($sasaran->tanggal_lahir);
            $now = Carbon::now();
            $totalMonths = (int) $dob->diffInMonths($now);

            if ($totalMonths >= 60) {
                return (int) $dob->diffInYears($now).' th';
            }

            return $totalMonths.' bln';
        }

        if (! is_null($sasaran->umur_sasaran ?? null)) {
            $umur = (int) $sasaran->umur_sasaran;

            return $umur >= 5 ? $umur.' th' : ($umur * 12).' bln';
        }

        return '-';
    }

    /**
     * Foto galeri posyandu untuk lampiran PDF (filter bulan & tahun tanggal_foto).
     *
     * @return array<int, array{caption: ?string, tanggal_formatted: string, data_uri: string}>
     */
    protected function getGaleriFotosForPdf(Posyandu $posyandu, ?int $bulan, int $tahun): array
    {
        $query = Galeri::query()
            ->where('id_posyandu', $posyandu->id_posyandu)
            ->whereNotNull('tanggal_foto')
            ->whereYear('tanggal_foto', $tahun);

        if ($bulan !== null) {
            $query->whereMonth('tanggal_foto', $bulan);
        }

        return $this->mapGaleriItemsToPdfFotos(
            $query->orderBy('tanggal_foto')->orderBy('id')->get()
        );
    }

    /**
     * Ubah koleksi Galeri menjadi array foto siap embed Dompdf.
     */
    protected function mapGaleriItemsToPdfFotos($items): array
    {
        $fotos = [];
        foreach ($items as $item) {
            $fullPath = $item->path ? uploads_safe_full_path($item->path) : null;
            if (! $fullPath || ! is_readable($fullPath)) {
                continue;
            }
            $mime = mime_content_type($fullPath) ?: 'image/jpeg';
            if (! str_starts_with($mime, 'image/')) {
                continue;
            }
            $fotos[] = [
                'caption' => $item->caption,
                'tanggal_formatted' => $item->tanggal_foto->format('d/m/Y'),
                'data_uri' => 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($fullPath)),
            ];
        }

        return $fotos;
    }

    /**
     * Build data & label periode untuk laporan galeri kegiatan berdasarkan tahun/bulan.
     */
    protected function buildGaleriLaporanData(Posyandu $posyandu, Request $request): array
    {
        $tahunRaw = $request->query('tahun');
        $bulanRaw = $request->query('bulan');

        $tahun = is_numeric($tahunRaw) ? (int) $tahunRaw : null;
        $bulan = is_numeric($bulanRaw) ? (int) $bulanRaw : null;

        if ($bulan !== null && ($bulan < 1 || $bulan > 12)) {
            $bulan = null;
        }

        $query = Galeri::query()
            ->where('id_posyandu', $posyandu->id_posyandu)
            ->whereNotNull('tanggal_foto');

        if ($tahun !== null) {
            $query->whereYear('tanggal_foto', $tahun);
        }

        if ($bulan !== null) {
            $query->whereMonth('tanggal_foto', $bulan);
        }

        $galeriFotos = $this->mapGaleriItemsToPdfFotos(
            $query->orderBy('tanggal_foto')->orderBy('id')->get()
        );

        if ($tahun !== null && $bulan !== null) {
            $periodeLabel = Carbon::create($tahun, $bulan, 1)->locale('id')->translatedFormat('F Y');
        } elseif ($tahun !== null) {
            $periodeLabel = 'Tahun '.$tahun;
        } elseif ($bulan !== null) {
            $periodeLabel = Carbon::create(now()->year, $bulan, 1)->locale('id')->translatedFormat('F').' (Semua Tahun)';
        } else {
            $periodeLabel = 'Semua Periode';
        }

        return [
            'galeriFotos' => $galeriFotos,
            'periodeLabel' => $periodeLabel,
            'tahun' => $tahun,
            'bulan' => $bulan,
        ];
    }

    /**
     * Generate laporan gambar kegiatan Posyandu (admin/kader) berdasarkan rentang tanggal.
     */
    public function posyanduGaleriPdf(Request $request): Response
    {
        $user = Auth::user();

        $kader = Kader::with('posyandu')
            ->where('id_users', $user->id)
            ->first();

        if (! $kader || ! $kader->posyandu) {
            abort(403, 'Posyandu untuk akun ini tidak ditemukan.');
        }

        $posyandu = $kader->posyandu;
        $data = $this->buildGaleriLaporanData($posyandu, $request);
        $fileName = 'Laporan-Gambar-Kegiatan-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-galeri', array_merge($data, [
            'posyandu' => $posyandu,
            'user' => $user,
            'generatedAt' => now('Asia/Jakarta'),
        ]), $fileName);
    }

    /**
     * Generate laporan gambar kegiatan Posyandu (super admin) berdasarkan rentang tanggal.
     */
    public function superadminPosyanduGaleriPdf(Request $request, string $id): Response
    {
        try {
            $decryptedId = decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID tidak valid');
        }

        $posyandu = Posyandu::findOrFail($decryptedId);
        $data = $this->buildGaleriLaporanData($posyandu, $request);
        $fileName = 'Laporan-Gambar-Kegiatan-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-galeri', array_merge($data, [
            'posyandu' => $posyandu,
            'user' => Auth::user(),
            'generatedAt' => now('Asia/Jakarta'),
        ]), $fileName);
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

        $decodedJenisVaksin = urldecode($jenisVaksin);
        $query = Imunisasi::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu)
            ->whereIn('jenis_imunisasi', ImunisasiOptions::valuesForFilter($decodedJenisVaksin));
        $this->applyBulanTahunFilter($query, $request);
        $imunisasiList = $query->orderBy('tanggal_imunisasi', 'desc')->get();

        $jenisVaksinLabel = $decodedJenisVaksin;
        $fileName = 'Laporan-Imunisasi-'.str_replace(['/', ' '], ['-', '-'], $jenisVaksinLabel).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-imunisasi', [
            'posyandu' => $posyandu,
            'imunisasiList' => $imunisasiList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriSasaran' => null,
            'kategoriLabel' => 'Jenis Vaksin: '.$jenisVaksinLabel,
            'jenisVaksin' => $jenisVaksinLabel,
        ], $fileName, 'landscape');
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
        ], $fileName, 'landscape');
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

        [$imunisasiList, $resolvedKategori] = $this->buildImunisasiListForPdf($posyandu, $request, $kategori);
        $filterSummary = $this->buildImunisasiFilterSummary($request, $resolvedKategori);

        $user = Auth::user();

        $kategoriLabel = $resolvedKategori ? SasaranFilterOptions::getLabel($resolvedKategori) : 'Semua';
        $fileName = 'Laporan-Imunisasi-'.str_replace(['/', ' '], ['-', '-'], $kategoriLabel).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-imunisasi', array_merge([
            'posyandu' => $posyandu,
            'imunisasiList' => $imunisasiList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriSasaran' => $resolvedKategori,
            'kategoriLabel' => $kategoriLabel,
        ], $filterSummary), $fileName, 'landscape');
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

        $decodedJenisVaksin = urldecode($jenisVaksin);
        $query = Imunisasi::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu)
            ->whereIn('jenis_imunisasi', ImunisasiOptions::valuesForFilter($decodedJenisVaksin));
        $this->applyBulanTahunFilter($query, $request);
        $imunisasiList = $query->orderBy('tanggal_imunisasi', 'desc')->get();

        $user = Auth::user();
        $jenisVaksinLabel = $decodedJenisVaksin;
        $fileName = 'Laporan-Imunisasi-'.str_replace(['/', ' '], ['-', '-'], $jenisVaksinLabel).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-imunisasi', [
            'posyandu' => $posyandu,
            'imunisasiList' => $imunisasiList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriSasaran' => null,
            'kategoriLabel' => 'Jenis Vaksin: '.$jenisVaksinLabel,
            'jenisVaksin' => $jenisVaksinLabel,
        ], $fileName, 'landscape');
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
        ], $fileName, 'landscape');
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
            $spreadsheet = new Spreadsheet;
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
                ? $dob->diffInMonths($now).' bln'
                : $dob->diffInYears($now).' th';
        } elseif (! is_null($item->umur_sasaran)) {
            $umur = (int) $item->umur_sasaran.' th';
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
        return SasaranFilterOptions::getLabel($kategori);
    }

    /**
     * Terapkan filter pendidikan dari query string (kategori / usia / tahun lahir, pendidikan, nama).
     */
    protected function applyPendidikanFilters($query, Request $request): array
    {
        $filterSasaran = $request->query('filter_sasaran') ? (string) $request->query('filter_sasaran') : null;
        $pendidikan = $request->query('pendidikan') ? trim((string) $request->query('pendidikan')) : null;
        $nama = $request->query('nama') ? trim((string) $request->query('nama')) : null;

        if ($filterSasaran) {
            SasaranFilterOptions::applyToPendidikanQuery($query, $filterSasaran);
        }

        if ($pendidikan) {
            $query->where('pendidikan_terakhir', urldecode($pendidikan));
        }

        if ($nama) {
            $query->where('nama', 'like', '%'.urldecode($nama).'%');
        }

        $labelParts = [];
        if ($filterSasaran) {
            $labelParts[] = SasaranFilterOptions::getLabel($filterSasaran);
        }
        if ($pendidikan) {
            $labelParts[] = 'Pendidikan: '.urldecode($pendidikan);
        }
        if ($nama) {
            $labelParts[] = 'Nama: '.urldecode($nama);
        }

        return [
            'kategoriLabel' => ! empty($labelParts) ? implode(' | ', $labelParts) : 'Semua',
            'kategoriPendidikan' => $pendidikan ? urldecode($pendidikan) : null,
        ];
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
    public function superadminPosyanduPendidikanPdf(Request $request, string $id, ?string $kategoriPendidikan = null): Response
    {
        try {
            $decryptedId = decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID tidak valid');
        }

        $posyandu = Posyandu::findOrFail($decryptedId);

        $query = Pendidikan::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu);

        if ($request->query('filter_sasaran') || $request->query('pendidikan') || $request->query('nama')) {
            $filterMeta = $this->applyPendidikanFilters($query, $request);
        } else {
            if ($kategoriPendidikan && $kategoriPendidikan !== 'semua') {
                $query->where('pendidikan_terakhir', urldecode($kategoriPendidikan));
            }
            $filterMeta = [
                'kategoriLabel' => $kategoriPendidikan && $kategoriPendidikan !== 'semua'
                    ? urldecode($kategoriPendidikan)
                    : 'Semua',
                'kategoriPendidikan' => $kategoriPendidikan && $kategoriPendidikan !== 'semua' ? urldecode($kategoriPendidikan) : null,
            ];
        }

        $pendidikanList = $query->orderBy('tanggal_lahir', 'desc')->get();

        $user = Auth::user();

        $kategoriLabel = $filterMeta['kategoriLabel'];
        $fileName = 'Laporan-Pendidikan-'.str_replace(['/', ' '], ['-', '-'], $kategoriLabel).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-pendidikan', [
            'posyandu' => $posyandu,
            'pendidikanList' => $pendidikanList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriPendidikan' => $filterMeta['kategoriPendidikan'],
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
            'kategoriLabel' => 'Kategori: '.$kategoriLabel,
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
            ->where('nama', 'like', '%'.$namaDecoded.'%');

        $pendidikanList = $query->orderBy('tanggal_lahir', 'desc')->get();

        $user = Auth::user();

        $fileName = 'Laporan-Pendidikan-Nama-'.str_replace(['/', ' '], ['-', '-'], $namaDecoded).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-pendidikan', [
            'posyandu' => $posyandu,
            'pendidikanList' => $pendidikanList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriPendidikan' => null,
            'kategoriLabel' => 'Nama: '.$namaDecoded,
        ], $fileName);
    }

    /**
     * Generate laporan pendidikan Posyandu (admin Posyandu) dalam bentuk PDF.
     */
    public function posyanduPendidikanPdf(Request $request, ?string $kategori = null): Response
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

        if ($request->query('filter_sasaran') || $request->query('pendidikan') || $request->query('nama')) {
            $filterMeta = $this->applyPendidikanFilters($query, $request);
        } else {
            if ($kategori && $kategori !== 'semua') {
                $query->where('pendidikan_terakhir', urldecode($kategori));
            }
            $filterMeta = [
                'kategoriLabel' => $kategori && $kategori !== 'semua' ? urldecode($kategori) : 'Semua',
                'kategoriPendidikan' => $kategori && $kategori !== 'semua' ? urldecode($kategori) : null,
            ];
        }

        $pendidikanList = $query->orderBy('tanggal_lahir', 'desc')->get();

        $kategoriLabel = $filterMeta['kategoriLabel'];
        $fileName = 'Laporan-Pendidikan-'.str_replace(['/', ' '], ['-', '-'], $kategoriLabel).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-pendidikan', [
            'posyandu' => $posyandu,
            'pendidikanList' => $pendidikanList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriPendidikan' => $filterMeta['kategoriPendidikan'],
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
            'kategoriLabel' => 'Kategori: '.$kategoriLabel,
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
            ->where('nama', 'like', '%'.$namaDecoded.'%');

        $pendidikanList = $query->orderBy('tanggal_lahir', 'desc')->get();

        $fileName = 'Laporan-Pendidikan-Nama-'.str_replace(['/', ' '], ['-', '-'], $namaDecoded).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-pendidikan', [
            'posyandu' => $posyandu,
            'pendidikanList' => $pendidikanList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriPendidikan' => null,
            'kategoriLabel' => 'Nama: '.$namaDecoded,
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
            ->where('nama', 'like', '%'.$namaDecoded.'%');

        $pendidikanList = $query->orderBy('tanggal_lahir', 'desc')->get();

        $user = Auth::user();

        $fileName = 'Laporan-Pendidikan-Kategori-'.str_replace(['/', ' '], ['-', '-'], $kategoriSasaranDecoded).'-Pendidikan-'.str_replace(['/', ' '], ['-', '-'], $kategoriPendidikanDecoded).'-Nama-'.str_replace(['/', ' '], ['-', '-'], $namaDecoded).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-pendidikan', [
            'posyandu' => $posyandu,
            'pendidikanList' => $pendidikanList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriPendidikan' => $kategoriPendidikanDecoded,
            'kategoriLabel' => 'Kategori: '.$kategoriSasaranDecoded.' | Pendidikan: '.$kategoriPendidikanDecoded.' | Nama: '.$namaDecoded,
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
            'kategoriLabel' => 'Kategori: '.$kategoriSasaranDecoded.' | Pendidikan: '.$kategoriPendidikanDecoded,
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
            ->where('nama', 'like', '%'.$namaDecoded.'%');

        $pendidikanList = $query->orderBy('tanggal_lahir', 'desc')->get();

        $user = Auth::user();

        $fileName = 'Laporan-Pendidikan-Kategori-'.str_replace(['/', ' '], ['-', '-'], $kategoriSasaranDecoded).'-Nama-'.str_replace(['/', ' '], ['-', '-'], $namaDecoded).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-pendidikan', [
            'posyandu' => $posyandu,
            'pendidikanList' => $pendidikanList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriPendidikan' => null,
            'kategoriLabel' => 'Kategori: '.$kategoriSasaranDecoded.' | Nama: '.$namaDecoded,
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
            ->where('nama', 'like', '%'.$namaDecoded.'%');

        $pendidikanList = $query->orderBy('tanggal_lahir', 'desc')->get();

        $user = Auth::user();

        $fileName = 'Laporan-Pendidikan-Pendidikan-'.str_replace(['/', ' '], ['-', '-'], $kategoriPendidikanDecoded).'-Nama-'.str_replace(['/', ' '], ['-', '-'], $namaDecoded).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-pendidikan', [
            'posyandu' => $posyandu,
            'pendidikanList' => $pendidikanList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriPendidikan' => $kategoriPendidikanDecoded,
            'kategoriLabel' => 'Pendidikan: '.$kategoriPendidikanDecoded.' | Nama: '.$namaDecoded,
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
            ->where('nama', 'like', '%'.$namaDecoded.'%');

        $pendidikanList = $query->orderBy('tanggal_lahir', 'desc')->get();

        $fileName = 'Laporan-Pendidikan-Kategori-'.str_replace(['/', ' '], ['-', '-'], $kategoriSasaranDecoded).'-Pendidikan-'.str_replace(['/', ' '], ['-', '-'], $kategoriPendidikanDecoded).'-Nama-'.str_replace(['/', ' '], ['-', '-'], $namaDecoded).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-pendidikan', [
            'posyandu' => $posyandu,
            'pendidikanList' => $pendidikanList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriPendidikan' => $kategoriPendidikanDecoded,
            'kategoriLabel' => 'Kategori: '.$kategoriSasaranDecoded.' | Pendidikan: '.$kategoriPendidikanDecoded.' | Nama: '.$namaDecoded,
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
            'kategoriLabel' => 'Kategori: '.$kategoriSasaranDecoded.' | Pendidikan: '.$kategoriPendidikanDecoded,
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
            ->where('nama', 'like', '%'.$namaDecoded.'%');

        $pendidikanList = $query->orderBy('tanggal_lahir', 'desc')->get();

        $fileName = 'Laporan-Pendidikan-Kategori-'.str_replace(['/', ' '], ['-', '-'], $kategoriSasaranDecoded).'-Nama-'.str_replace(['/', ' '], ['-', '-'], $namaDecoded).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-pendidikan', [
            'posyandu' => $posyandu,
            'pendidikanList' => $pendidikanList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriPendidikan' => null,
            'kategoriLabel' => 'Kategori: '.$kategoriSasaranDecoded.' | Nama: '.$namaDecoded,
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
            ->where('nama', 'like', '%'.$namaDecoded.'%');

        $pendidikanList = $query->orderBy('tanggal_lahir', 'desc')->get();

        $fileName = 'Laporan-Pendidikan-Pendidikan-'.str_replace(['/', ' '], ['-', '-'], $kategoriPendidikanDecoded).'-Nama-'.str_replace(['/', ' '], ['-', '-'], $namaDecoded).'-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-pendidikan', [
            'posyandu' => $posyandu,
            'pendidikanList' => $pendidikanList,
            'generatedAt' => now('Asia/Jakarta'),
            'user' => $user,
            'kategoriPendidikan' => $kategoriPendidikanDecoded,
            'kategoriLabel' => 'Pendidikan: '.$kategoriPendidikanDecoded.' | Nama: '.$namaDecoded,
        ], $fileName);
    }

    /**
     * Build data laporan informasi posyandu (info, statistik kategori, ringkasan pendidikan).
     */
    protected function buildInformasiPosyanduData(Posyandu $posyandu): array
    {
        $posyandu->loadMissing([
            'kader.user',
            'sasaran_bayibalita',
            'sasaran_remaja',
            'sasaran_dewasa',
            'sasaran_ibuhamil',
            'sasaran_pralansia',
            'sasaran_lansia',
        ]);

        $statistikKategori = [
            ['label' => 'Bayi/Balita', 'jumlah' => $posyandu->sasaran_bayibalita->count()],
            ['label' => 'Remaja', 'jumlah' => $posyandu->sasaran_remaja->count()],
            ['label' => 'Dewasa', 'jumlah' => $posyandu->sasaran_dewasa->count()],
            ['label' => 'Ibu Hamil', 'jumlah' => $posyandu->sasaran_ibuhamil->count()],
            ['label' => 'Pralansia', 'jumlah' => $posyandu->sasaran_pralansia->count()],
            ['label' => 'Lansia', 'jumlah' => $posyandu->sasaran_lansia->count()],
        ];

        $totalSasaran = collect($statistikKategori)->sum('jumlah');

        $ketuaKader = $posyandu->kader->first(function ($kader) {
            return strcasecmp((string) ($kader->jabatan_kader ?? ''), 'Ketua') === 0;
        });
        $petugasPosyanduLabel = $ketuaKader
            ? ($ketuaKader->nama_kader ?: ($ketuaKader->user->name ?? '-'))
            : '-';

        $pendidikanData = Pendidikan::where('id_posyandu', $posyandu->id_posyandu)
            ->selectRaw('pendidikan_terakhir, COUNT(*) as jumlah')
            ->groupBy('pendidikan_terakhir')
            ->orderByRaw('
                CASE
                    WHEN pendidikan_terakhir = "Tidak/Belum Sekolah" THEN 1
                    WHEN pendidikan_terakhir = "PAUD" THEN 2
                    WHEN pendidikan_terakhir = "TK" THEN 3
                    WHEN pendidikan_terakhir = "Tidak Tamat SD/Sederajat" THEN 4
                    WHEN pendidikan_terakhir = "Tamat SD/Sederajat" THEN 5
                    WHEN pendidikan_terakhir = "SLTP/Sederajat" THEN 6
                    WHEN pendidikan_terakhir = "SLTA/Sederajat" THEN 7
                    WHEN pendidikan_terakhir = "Diploma I/II" THEN 8
                    WHEN pendidikan_terakhir = "Akademi/Diploma III/Sarjana Muda" THEN 9
                    WHEN pendidikan_terakhir = "Diploma IV/Strata I" THEN 10
                    WHEN pendidikan_terakhir = "Strata II" THEN 11
                    WHEN pendidikan_terakhir = "Strata III" THEN 12
                    ELSE 13
                END
            ')
            ->get();

        $pendidikanRows = $pendidikanData->map(function ($row) {
            return [
                'label' => $row->pendidikan_terakhir ?: '-',
                'jumlah' => (int) $row->jumlah,
            ];
        })->values()->all();

        return [
            'posyandu' => $posyandu,
            'totalSasaran' => $totalSasaran,
            'totalKader' => $posyandu->kader->count(),
            'statistikKategori' => $statistikKategori,
            'pendidikanRows' => $pendidikanRows,
            'totalPendidikan' => collect($pendidikanRows)->sum('jumlah'),
            'statistikChartUri' => \App\Helpers\PdfChartHelper::barChartDataUri($statistikKategori, 640, 300),
            'pendidikanChartUri' => \App\Helpers\PdfChartHelper::pieChartDataUri($pendidikanRows, 560, 220),
            'logoPosyanduUri' => $this->resolvePosyanduLogoDataUri($posyandu),
            'petugasPosyanduLabel' => $petugasPosyanduLabel,
            'generatedAt' => now('Asia/Jakarta'),
        ];
    }

    /**
     * Ambil logo posyandu sebagai data URI untuk DomPDF.
     */
    protected function resolvePosyanduLogoDataUri(Posyandu $posyandu): ?string
    {
        $logoPath = $posyandu->logo_posyandu ?? null;
        if (! $logoPath) {
            return null;
        }

        $path = ltrim(str_replace('\\', '/', (string) $logoPath), '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }
        if (str_starts_with($path, 'uploads/')) {
            $path = substr($path, strlen('uploads/'));
        }

        $fullPath = uploads_safe_full_path($path);
        if (! $fullPath || ! is_readable($fullPath)) {
            return null;
        }

        $mime = mime_content_type($fullPath) ?: 'image/png';
        if (! str_starts_with($mime, 'image/')) {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($fullPath));
    }

    /**
     * Export informasi posyandu (Admin Posyandu / Kader).
     */
    public function posyanduInformasiPdf(): Response
    {
        $user = Auth::user();
        $kader = Kader::with('posyandu')->where('id_users', $user->id)->first();
        if (! $kader || ! $kader->posyandu) {
            abort(403, 'Posyandu untuk akun ini tidak ditemukan.');
        }

        $data = $this->buildInformasiPosyanduData($kader->posyandu);
        $fileName = 'Informasi-Posyandu-'.$kader->posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-informasi-posyandu', array_merge($data, [
            'user' => $user,
        ]), $fileName, 'portrait');
    }

    /**
     * Export informasi posyandu (Supervisor / Super Admin).
     */
    public function superadminPosyanduInformasiPdf(string $id): Response
    {
        try {
            $decryptedId = decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID tidak valid');
        }

        $posyandu = Posyandu::findOrFail($decryptedId);
        $data = $this->buildInformasiPosyanduData($posyandu);
        $fileName = 'Informasi-Posyandu-'.$posyandu->nama_posyandu.'-'.now('Asia/Jakarta')->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-informasi-posyandu', array_merge($data, [
            'user' => Auth::user(),
        ]), $fileName, 'portrait');
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
        $options = new Options;
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultMediaType', 'print');
        $options->set('isFontSubsettingEnabled', true);

        $dompdf = new Dompdf($options);

        $html = view($view, $data)->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', $orientation);
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$fileName.'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
