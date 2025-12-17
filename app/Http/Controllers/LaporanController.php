<?php

namespace App\Http\Controllers;

use App\Models\Imunisasi;
use App\Models\Kader;
use App\Models\Posyandu;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class LaporanController extends Controller
{
    /**
     * Generate laporan imunisasi Posyandu (admin Posyandu) dalam bentuk PDF.
     */
    public function posyanduImunisasiPdf(): Response
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
            ->orderBy('tanggal_imunisasi', 'desc')
            ->get();

        $fileName = 'Laporan-Imunisasi-'.$posyandu->nama_posyandu.'-'.now()->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-imunisasi', [
            'posyandu' => $posyandu,
            'imunisasiList' => $imunisasiList,
            'generatedAt' => now(),
            'user' => $user,
        ], $fileName);
    }

    /**
     * Generate laporan imunisasi Posyandu untuk Super Admin (berdasarkan ID Posyandu).
     */
    public function superadminPosyanduImunisasiPdf(int $id): Response
    {
        $posyandu = Posyandu::findOrFail($id);

        $imunisasiList = Imunisasi::with(['user'])
            ->where('id_posyandu', $posyandu->id_posyandu)
            ->orderBy('tanggal_imunisasi', 'desc')
            ->get();

        $user = Auth::user();

        $fileName = 'Laporan-Imunisasi-'.$posyandu->nama_posyandu.'-'.now()->format('Ymd_His').'.pdf';

        return $this->renderPdf('pdf.laporan-posyandu-imunisasi', [
            'posyandu' => $posyandu,
            'imunisasiList' => $imunisasiList,
            'generatedAt' => now(),
            'user' => $user,
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


